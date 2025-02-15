<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 08.06.2019
 * Time: 13:44
 */

namespace frontend\controllers;


use backend\models\ProductReviewSearch;
use backend\models\ProductSearch;
use common\models\Product;
use common\models\ProductReview;
use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\web\NotFoundHttpException;


class ProductReviewController extends UserController
{
    protected function findModel($id)
    {
        if (($model = ProductReview::find()
                ->andWhere([ProductReview::tableName().'.id' => $id])
                ->innerJoinWith('product')
                ->andWhere([Product::tableName().'.user_id' => \Yii::$app->user->identity->id])->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionIndex()
    {
        $searchModel = new ProductReviewSearch();
        $params = Yii::$app->request->queryParams;
        $params['ProductReviewSearch']['user_id'] = Yii::$app->user->id;
        $dataProvider = $searchModel->search($params);
        Yii::$app->view->params['switchMenu'] = false;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionCreate($product_id = null)
    {
        $model = new ProductReview();

        $model->product_id = $product_id;
        if (Product::findOne(['id' => $product_id, 'user_id' => Yii::$app->user->identity->id ])) {

            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $model->date = strtotime($model->date);
                $model->user_id = Yii::$app->user->identity->id;
                $model->save();
                return $this->redirect(['index', 'product_id' => $model->product_id]);
            }

            return $this->render('create', [
                'model' => $model,
            ]);
        }
        throw new NotFoundHttpException();


    }

    /**
     * Updates an existing ProductReview model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @param integer $status
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $data['ProductReview']['date'] = strtotime($data['ProductReview']['date']);
            if ($model->load($data) && $model->validate()) {
                Yii::$app->session->setFlash('success', 'Product review has been updated!');
                $model->save();
                return $this->redirect(['update', 'id' => $model->id]);
            }
        }
        return $this->render('update', compact('model'));
    }

    public function actionPublish($id, $product_id)
    {
        $user = Yii::$app->user->identity;
        /* @var  $user User*/
        $product = $user->getProducts()->where(['id' => $product_id])->one();
        /* @var  $product Product*/

        if ($product) {
            $review = $product->getProductReviews()->where(['id' => $id])->one();
            if ($review) {
                /* @var ProductReview $review*/
                $review->status = $review->getIsPublished() ? ProductReview::STATUS_PENDING : ProductReview::STATUS_PUBLISHED;
                $review->save();
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
        return null;
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
}