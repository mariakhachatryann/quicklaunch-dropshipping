<?php

namespace frontend\controllers;


use backend\models\ProductReviewSearch;
use common\helpers\PublitioAPI;
use common\models\Notification;
use common\models\Product;
use common\models\ProductReview;
use common\models\ProductReviewImage;
use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\rest\Controller;

class ReviewController extends Controller
{
    public function beforeAction($action)
    {
        header("Access-Control-Allow-Origin: *");
        return parent::beforeAction($action);
    }
    
    public function actionGetReviews($id, $page)
    {
        $product = Product::findOne(['shopify_id' => $id]);
        if ($product && $product->user->userSetting->import_reviews) {
            $query = $product->getProductReviews()
                ->andWhere(['status' => ProductReview::STATUS_PUBLISHED]);
            
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => $product->user->userSetting->review_limit_per_page,
                ],
            ]);
            
            return [
                'reviews' => $dataProvider->getModels(),
                'total' => $dataProvider->getTotalCount(),
                'reviews_label' => $product->user->userSetting->reviews_label,
                'rate' => $product->rate
            ];
        }
        return [];
        
    }
    
    public function actionGetUserReviews()
    {
        $user = Yii::$app->user->identity;
        /* @var User $user */
        $reviews = $user->getProductsReviews()->limit(3)->all();
        if (empty($reviews)) {
            $reviews = [
                [
                    'reviewer_name' => 'James',
                    'rate' => '5',
                    'review' => 'Super everything is even better than described, very satisfied with the order,
                         i recommend the order to everyone very quickly a little more, 2 weeks ',
                    'date' => 1569534400,
                    'user_id' => Yii::$app->user->identity,
                ],
                [
                    'reviewer_name' => 'Amanda',
                    'rate' => '5',
                    'review' => 'Beautiful. Good quality fabric. Well cut. Original longer behind than front.',
                    'date' => 1499834900,
                    'user_id' => Yii::$app->user->identity,
                ],
                [
                    'reviewer_name' => 'Lily',
                    'rate' => '5',
                    'review' => 'Mega fast delivery, thank you, dress quality, liked it, recommend, thank you seller',
                    'date' => 1551534400,
                    'user_id' => Yii::$app->user->identity,
                ],
                [
                    'reviewer_name' => 'Tom',
                    'rate' => '4',
                    'review' => 'With the seller did not communicate. The dress is cool, very satisfied. Thank you))',
                    'date' => 1551834400,
                    'user_id' => Yii::$app->user->identity,
                ],
            ];
        }
        return $reviews;
        
    }
    
    
    public function actionGetReviewTemplate($shop)
    {
        $user = User::findOne(['username' => $shop]);
        $reviewLimit = ArrayHelper::getValue($user, 'plan.review_limit', 0);
        $userReviews = ProductReview::find()->where(['user_id' => $user->id])->count();
        
        $userSettings = $user->userSetting;
        $contents = $this->renderPartial('get-review-template', compact('userSettings', 'reviewLimit', 'userReviews'));
        
        return $contents;
    }
    
    public function actionAddReview()
    {
        $model = new ProductReview();
        if ($model->load(\Yii::$app->request->post(), '') && $model->validate()) {
            $user = User::findByUsername($model->shop);

            if (!$user) {
                return false;
            }

            $model->user_id = $user->id;
            $model->date = time();
            $product = $user->getProducts()->where(['shopify_id' => Yii::$app->request->post('shopify_product_id')])->one();
            if ($product) {
                $model->product_id = $product->id;
            }
            $model->status = ProductReview::STATUS_PUBLISHED;
            if ($model->save()) {
                if ($product) {
                    $notification = new Notification();
                    $notification->user_id = $user->id;
                    $notification->subject = 'There are new review for product '.$product->title;
                    $notification->text = 'New Product Review';
                    $notification->url = '/product-review?ProductReviewSearch%5Bid%5D='.$model->id;
                    $notification->notification_type = Notification::INFO_NOTIFICATION;
                    $notification->save();
                }

                $publitio_api = new PublitioAPI(\Yii::$app->params['publitioApiKey'], \Yii::$app->params['publitioApiSecret']);
                if (!empty($_FILES['image'])) {
                    $tempName = $_FILES['image']['tmp_name'];
                    if (!empty($tempName)) {
                        $response = $publitio_api->upload_file($tempName, "file");
                        $response = json_decode($response, true);
                        if ($response['success']) {
                            $reviewImage = new ProductReviewImage();
                            $reviewImage->product_review_id = $model->id;
                            $reviewImage->image_url = $response['url_preview'];
                            $reviewImage->save();
                        }
                    }
                }

                $image = !empty($reviewImage->image_url) ? $reviewImage->image_url : '';
                return [
                    'status' => 1,
                    'date' => $model->formattedDate,
                    'image' => $image,
                ];
            }
        }
        return [
            'status' => 0,
            'models' => $model->getErrors()
        ];
    }
    
    
}