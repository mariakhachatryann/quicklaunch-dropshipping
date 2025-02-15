<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 11.06.2019
 * Time: 10:50
 */

namespace frontend\controllers;


use common\models\User;
use common\models\Product;
use common\helpers\OrderHelper;
use frontend\models\api\ProductData;
use frontend\models\OrderSearch;
use phpDocumentor\Reflection\DocBlock\Tags\Author;
use Yii;
use yii\data\ArrayDataProvider;
use yii\web\NotFoundHttpException;

class OrderController extends UserController
{
    public function actionIndex()
    {

        $searchModel = new OrderSearch();
        $searchModel->validate();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

//        $orders = [
//            [
//                'id' => '1001',
//                'number' => '1001',
//                'date' => '2025-01-01',
//                'financial_status' => 'Paid',
//                'fulfillment_status' => 'Fulfilled',
//                'total_price' => '$150.00',
//                'address' => '123 Main St, Springfield',
//                'customer' => 'John Doe',
//            ],
//            [
//                'id' => '1002',
//                'number' => '1002',
//                'date' => '2025-01-02',
//                'financial_status' => 'Pending',
//                'fulfillment_status' => 'Unfulfilled',
//                'total_price' => '$200.00',
//                'address' => '456 Elm St, Shelbyville',
//                'customer' => 'Jane Smith',
//            ],
//            [
//                'id' => '1003',
//                'number' => '1003',
//                'date' => '2025-01-03',
//                'financial_status' => 'Refunded',
//                'fulfillment_status' => 'Cancelled',
//                'total_price' => '$50.00',
//                'address' => '789 Oak St, Ogdenville',
//                'customer' => 'Robert Brown',
//            ],
//            [
//                'id' => '1004',
//                'number' => '1004',
//                'date' => '2025-01-04',
//                'financial_status' => 'Paid',
//                'fulfillment_status' => 'In Progress',
//                'total_price' => '$300.00',
//                'address' => '101 Pine St, Capital City',
//                'customer' => 'Emily White',
//            ],
//            [
//                'id' => '1005',
//                'number' => '1005',
//                'date' => '2025-01-05',
//                'financial_status' => 'Paid',
//                'fulfillment_status' => 'Shipped',
//                'total_price' => '$120.00',
//                'address' => '202 Birch St, North Haverbrook',
//                'customer' => 'Chris Green',
//            ],
//        ];
//
//        $dataProvider = new ArrayDataProvider([
//            'allModels' => $orders,
//            'pagination' => [
//                'pageSize' => 10,
//            ],
//            'sort' => [
//                'attributes' => ['Order', 'Date', 'Financial Status', 'Fulfillment Status', 'Amount', 'Address', 'Customer'],
//            ],
//        ]);

        return $this->render('index', compact('dataProvider','searchModel'));
    }

    public function actionView($id)
    {

        /* @var $model ProductData */

        $user = Yii::$app->user->identity;

        /* @var  $user User*/
        if ($user) {
            $client = $user->getShopifyApi();
        }

        $query = <<<QUERY
          query {
            order(id: "{$id}") {
              id
              name
              totalPriceSet {
                presentmentMoney {
                  amount
                }
              }
              lineItems(first: 10) {
                nodes {
                  id
                  name
                }
              }
            }
          }
        QUERY;

        $response = $client->query($query);
        $responseBody = $response->getBody();
        $responseData = json_decode($responseBody, true);
        $orderService = new OrderHelper();
        $orderData = $orderService->getShopifyOrderFullInfo($responseData['data']['order']);

        return $this->render('view', compact('orderData'));
    }

}