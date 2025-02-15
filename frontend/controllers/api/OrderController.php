<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 30.05.2019
 * Time: 12:19
 */

namespace frontend\controllers\api;


use common\models\Product;
use common\models\User;
use frontend\models\api\ProductData;
use Slince\Shopify\Model\Orders\Common\OrderAddress;
use Yii;
use yii\web\NotFoundHttpException;

class OrderController extends ApiController
{
    public function actionShow()
    {
        /* @var $model ProductData */

        $user = Yii::$app->user->identity;

        /* @var  $user User*/
        if ($user) {
            $client = $user->getShopifyApi();
        }
        $orders = $client->getOrderManager()->findAll();
        $query = <<<QUERY
          query {
            orders(first: 10) {
              edges {
                node {
                  id
                }
              }
            }
          }
        QUERY;

        $response = $user->getShopifyApi()->query($query);
        $orders = json_decode($response->getBody(), true)['data']['orders']['edges'];

        $ordersData = [];
        foreach ($orders as $order) {

            $orderData = [];
            $orderData['id'] = $order->getId();
            $orderData['date'] = $order->getProcessedAt();
            $orderData['number'] = $order->getNumber();

            try {
                $address = @$order->getShippingAddress();
            } catch (\TypeError $exception) {
                $address = new OrderAddress();
            }

            $orderData['address'] = ProductData::getAddress($address);
            $orderData['lineItems'] = [];
            foreach ($order->getLineItems() as $lineItem) {
                $item = [];

                $item['product_id'] = $lineItem->getProductId();
                $product = Product::findOne(['shopify_id' => $item['product_id']]);

                if ($product) {
                    $item['product_src_url'] = $product->src_product_url;
                }
                $item['title'] = $lineItem->getName();
                $item['price'] = $lineItem->getPrice();
                $item['variant_id'] = $lineItem->getVariantId();
                $item['variant_title'] = $lineItem->getVariantTitle();

                $orderData['lineItems'][] = $item;

            }
            $ordersData[] = $orderData;
        }
        if (!empty($ordersData)) {
            return $ordersData;
        }
        throw new NotFoundHttpException("You don't have any orders yet");

    }
}