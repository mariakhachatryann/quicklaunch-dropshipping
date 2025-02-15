<?php

namespace frontend\models;

use common\models\User;
use common\helpers\OrderHelper;
use Exception;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use Shopify\ApiVersion;
use Shopify\Auth\FileSessionStorage;
use Shopify\Context;
use Shopify\Exception\ShopifyException;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

/**
 * ProductSearch represents the model behind the search form of `common\models\Product`.
 */
class OrderSearch extends Model
{

    public $financial_status;
    public $fulfillment_status;
    public $status;
    public $limit;
    public $ids;
    public $created_at_max;
    public $created_at_min;


    public function rules()
    {
        return [
            [['financial_status', 'fulfillment_status','status', 'limit','ids','created_at_max','created_at_min'], 'safe'],
            ['financial_status', 'default', 'value'=>OrderHelper::STATUS_ANY],
            ['fulfillment_status', 'default', 'value'=>OrderHelper::FULFILLMENT_STATUS_ANY],
            ['status', 'default', 'value'=>OrderHelper::STATUS_ANY],
            ['limit', 'default', 'value'=>OrderHelper::DEFAULT_LIMIT],
            ['created_at_max', 'default', 'value'=>date("Y-m-d",strtotime(OrderHelper::DEFAULT_CREATED_AT_MAX))],
            ['created_at_min', 'default', 'value'=>date("Y-m-d",strtotime(OrderHelper::DEFAULT_CREATED_AT_MIN))],
        ];
    }

    public function search($params)
    {

        $this->load($params);

        $user = Yii::$app->user->identity;
        /* @var  $user User */
        if ($user) {
            $client = $user->getShopifyApi();
        }

        $created_at_max = date(DATE_ISO8601, strtotime($this->created_at_max));
        $created_at_min = date(DATE_ISO8601, strtotime($this->created_at_min));

        $query = <<<GRAPHQL
            {
              orders(first: 5) {
                edges {
                  node {
                    id
                    name
                    totalPriceSet {
                      shopMoney {
                        amount
                        currencyCode
                      }
                    }
                    customer {
                      firstName
                      lastName
                    }
                    createdAt
                  }
                }
              }
            }
        GRAPHQL;

        $ordersData = [];
        $response = $client->query(['query' => $query]);
        $data = $response->getDecodedBody();

        if (isset($data['data']['orders']['edges'])) {
            $orderService = new OrderHelper();

            foreach ($data['data']['orders']['edges'] as $orderEdge) {
                $order = $orderEdge['node'];
                $orderData = $orderService->getShopifyOrderMainInfo($order);
                $ordersData[] = $orderData;
            }
        }

        return new ArrayDataProvider([
            'allModels' => $ordersData,
            'pagination' => false,
        ]);
    }
}
