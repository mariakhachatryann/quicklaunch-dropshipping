<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 15.04.2019
 * Time: 18:25
 */

namespace frontend\controllers\api;

use backend\models\ProductSearch;
use common\helpers\ShopifyClient;
use common\models\BulkMonitoringItem;
use common\models\EmailTemplate;
use common\models\Feature;
use common\models\MonitoringQueue;
use common\models\Plan;
use common\models\ProductReview;
use common\models\ProductReviewImage;
use common\models\ProductUrl;
use common\models\User;
use common\models\UserSetting;
use Exception;
use frontend\models\api\Product;
use frontend\models\api\ProductData;
use frontend\models\api\ProductGenerateContent;
use Orhanerday\OpenAi\OpenAi;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\rest\ActiveController;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;


class ProductController extends ApiController
{
    public $modelClass = Product::class;

    public function beforeAction($action)
    {
       if (parent::beforeAction($action)) {
           $user = Yii::$app->user->identity;

           if (!$user->plan || $user->plan_status != Plan::PLAN_ACTIVE) {
               throw new ForbiddenHttpException('You do not have an active plan');
           }
           if ($user->plan->product_limit) {
               if ($user->getproducts()->count() >= $user->plan->product_limit) {
                   throw new ForbiddenHttpException(
                       '<div style="text-align: center">
                                <div>Your plan for importing products is over the limit</div>'.
                                 Html::a('Please subscribe', Url::toRoute(['/profile/subscribe'], true)).
                                '</div>'

                   );
               }
             /*  if ( $user->getProducts()->sum('count_variants') >= $user->plan->variant_limit) {
                   throw new ForbiddenHttpException(
                       '<div style="text-align: center">
                                <div>Your plan for importing variants is over the limit</div>'.
                       Html::a('Please subscribe', Url::toRoute(['/profile/subscribe'], true), ['style' => 'color:blue']).
                       '</div>'

                   );
               }*/
           }

           return true;
       }

       return false;

    }

    public function actionMonitorNow()
    {
        $id = Yii::$app->request->post('id');
        $data = Yii::$app->request->post('data');
        $user = Yii::$app->user->identity;
        /* @var $user User */
        $product = $user->getProducts()->where(['id' => $id])->one();
        /* @var $product Product */

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found'
            ];
        }

        if (!$product->monitoring_reviews && !$product->monitoring_price && !$product->monitoring_stock) {
            return [
                'success' => false,
                'message' => 'Monitoring not allowed'
            ];
        }

        $planFeatures = Feature::getPlanSettingkeys(Yii::$app->user->identity);

        if (!$planFeatures['monitor_now']) {
            return [
                'success' => false,
                'message' => 'Upgrade your plan!'
            ];
        }

        if (!($user->limits['monitoringRemainsLimit'] > 0)) {
            return [
                'success' => false,
                'message' => 'Product monitoring limit is expired'
            ];
        }

        $queue = new MonitoringQueue();
        $queue->product_id = $product->id;
        $queue->import_reviews = $user->userSetting->import_reviews;
        $queue->status = MonitoringQueue::STATUS_PENDING;
        $queue->save();

        if (isset($data['status']) && $data['status'] == 404) {
            $queue->status = MonitoringQueue::STATUS_SUCCESS;
            $queue->save();
            $result = MonitoringQueue::STATUS_SUCCESS;
        } else {
            $result = $queue->monitorQueues($data, true);
            $queue->status = $result ? MonitoringQueue::STATUS_SUCCESS : MonitoringQueue::STATUS_ERROR;
            $queue->save();
        }

        if (!empty($data['bulk_product_monitor'])) {
            /* @var Product $product */
            $product = $user->getProducts()->where(['id' => $data['monitoring_product_id']])->one();
            /* @var BulkMonitoringItem $monitoringProductItem */
            $monitoringProductItem = $product->getBulkMonitoringItems()
                ->where('status', BulkMonitoringItem::STATUS_PENDING)
                ->where(['id' => $data['monitoring_product_item_id']])->one();

            if ($monitoringProductItem) {
                $monitoringProductItem->status = $result == MonitoringQueue::STATUS_SUCCESS ? BulkMonitoringItem::STATUS_APPLIED : BulkMonitoringItem::STATUS_ERROR;
                $monitoringProductItem->save();
            }
        }


        return [
            'success' => true,
        ];
    }

    public function actionIndex()
    {
        $products = Product::find()->andWhere(['is_deleted' => Product::PRODUCT_IS_NOT_DELETED]);
        if ($products) {
            $dataProvider = new ActiveDataProvider([
                'query' => $products,
            ]);
            return $dataProvider->getModels();
        }
        return [];
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        //  throw new ForbiddenHttpException('access token is invalid');
        // check if the user can access $action and $model
        // throw ForbiddenHttpException if access should be denied
    }

    public function actionBySku($sku)
    {
        $product = Product::findOne(['sku' => $sku, 'user_id' => Yii::$app->user->identity->id]);
        if (!$product) {
            throw  new NotFoundHttpException('Product not found');
        }

        return $product;
    }

    public function actionProductsBySku($sku)
    {
        $products = Product::find()->where(['sku' => $sku, 'user_id' => Yii::$app->user->identity->id])->all();
        if (empty($products)) {
            throw  new NotFoundHttpException('Product not found');
        }
        return $products;
    }

    public function actionCreate()
    {
        $user = Yii::$app->user->identity;
        /* @var $user User */
        $model = $user->getProductDataModel();

        $data = Yii::$app->request->bodyParams;
        if (empty($data['body_html'])) {
            $data['body_html'] = '';
        }

        if ($model->load($data, '') && $model->validate()) {
			return $model->createProduct($user);
        }

        return ['status' => 0, 'errors' => $model->getErrors()];
    }

    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;
        /* @var  $user User*/
        if ($user) {
            $client = $user->getShopifyApi();
        }
        $model = new ProductData(['client' => $client]);

        $product = Product::findOne(['id' => $id, 'user_id' => Yii::$app->user->identity->id, 'is_deleted' => Product::PRODUCT_IS_NOT_DELETED]);

        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }
        $model->setProduct($product);
        /* @var $product Product */
        if ($model->load(Yii::$app->request->bodyParams, '') && $model->validate()) {
            ProductReview::deleteAll(['product_id' => $product->shopify_id]);

            $model->updateShopifyProduct($product->shopify_id);
            $product = $model->saveProduct($user);
            $model->updateUserSetting($user);
            $model->setVariantImages();
            if ($user->userSetting->import_reviews) {
                $model->createProductReviews($product);
            }
            Yii::$app->session->setFlash('update', 'Your Product is Updated');
            return [
                'status' => 1,
                'id' => $product->shopify_id,
                'shopifyView' => "{$user->shopUrl}/products/$product->handle",
                'view' => Url::toRoute(['product/view', 'id' => $product->id]),
                'edit' => "{$user->shopUrl}/admin/products/$product->shopify_id",
            ];

        }

        return $model;
    }

    public function actionGetProductReviews($productId)
    {
        $product = Product::findOne(['id' => $productId, 'user_id' => Yii::$app->user->identity->id, 'is_deleted' => Product::PRODUCT_IS_NOT_DELETED]);

        $reviews = \frontend\models\api\ProductReview::find()->andWhere(['product_id' => $product->id])->with('productReviewImages')->all();
        return $reviews;
    }

    public function actionView($id = null, $sku = null, $shopify_id = null, $handle = null)
    {
        $user = Yii::$app->user->identity;
        /* @var $user User*/
        $product = $user->getProducts()->andFilterWhere([
            'id' => $id,
            'sku' => $sku,
            'shopify_id' => $shopify_id,
            'handle' => $handle,
            'is_deleted' => Product::PRODUCT_IS_NOT_DELETED
        ])->limit(1)->one();

        if($product) {
            return $product;
        }
        return [
          'status' => 0,
          'message' => 'Product is not found'
        ];

    }

    public function actionGetDataFromContent()
    {
        $user = Yii::$app->user->identity;

        $model = new ProductUrl();
        if ($model->load(Yii::$app->request->post(), '') && $model->validate()) {
            if (!$model->content) {
                throw new InvalidArgumentException('Content is missing!');
            }
            $site = $model->getSite();
            if (!$site) {
                throw new NotFoundHttpException('Invalid product url!');
            }
            $availableSites = $user->plan->getPlanSites()->select(['site_id'])->column();
            if (!in_array($site->id, $availableSites)) {
                throw new ForbiddenHttpException('Site is not available in your plan!');
            }
            $model->setUser($user);
            $model->url = str_replace('-&-', '', $model->url);
            $productData = $model->getProductData();
            $productData = json_decode($productData, true);
            return $productData;
        } else {
            return $model->getErrors();
        }
    }

    public function actionGenerateContent()
    {
        $model = new ProductGenerateContent();
        if ($model->load(Yii::$app->request->post(), '') && $model->validate()) {
            try {
                return [
                    'success' => true,
                    'content' => trim($model->generateContent(), '"')
                ];
            } catch (Exception $exception) {
                Yii::error([$exception->getMessage(), $model->attributes], 'GenerateAiContentError');
            }

        }

        return [
            'success' => false
        ];
    }

}