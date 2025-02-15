<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 29.05.2019
 * Time: 14:45
 */

namespace frontend\controllers\api;


use common\models\ProductReviewImage;
use common\models\User;
use frontend\models\api\Product;
use frontend\models\api\ProductReview;
use Yii;
use yii\base\BaseObject;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;


class ProductReviewController extends ApiController
{
    protected $loginRequired = false;

    public function actionShowReviews()
    {
        $id = Yii::$app->request->getBodyParam('id');
        $product = Product::findOne(['shopify_id' => $id, 'is_deleted' => Product::PRODUCT_IS_NOT_DELETED]);
        if ($product) {
            $dataProvider = new ActiveDataProvider([
                'query' => $product->getProductReviews()->with('productReviewImages'),
            ]);

            return $dataProvider->getModels();
        }

        return [];
    }

    public function actionUpdateReviews()
    {
        $user = Yii::$app->user->identity;
        /* @var  $user User */
        $reviews = Yii::$app->request->post('reviews', []);
        $productId = Yii::$app->request->post('product_id');
        //$product = $user->getProducts()->where(['id' => $productId])->one();
        $product = Product::findOne($productId);
        $user = $product->user;
        $models = [];
        if ($product) {
            foreach ($reviews as $review) {
                $hash = md5($review['feedback'] . $review['name'] . strtotime($review['date']));
                if (!$product->getProductReviews()->where(['review_hash' => $hash])->one()) {
                    $model = new ProductReview();
                    $model->user_id = $user->id;
                    $model->product_id = $product->id;
                    $model->reviewer_name = $review['name'];
                    $model->date = strtotime($review['date']);
                    $model->rate = $review['star'];
                    $model->review = $review['feedback'];
                    $model->review_hash = $hash;
                    if (!$model->validate()) {
                        Yii::$app->response->statusCode = 400;
                        Yii::$app->response->data = Json::encode($model->getErrors());
                        return Yii::$app->response;
                    }
                    if (!$model->save()) {
                        return $model->getErrors();
                    } elseif (!empty($review['reviewImages'])) {
                        foreach ($review['reviewImages'] as $image) {
                            $reviewImage = new ProductReviewImage();
                            $reviewImage->product_review_id = $model->id;
                            $reviewImage->image_url = $image;
                            $reviewImage->save();
                        }
                    }
                }
            }
        }
        Yii::$app->response->statusCode = 200;
        Yii::$app->response->data = ['status' => 1];

        return Yii::$app->response;
    }


}


