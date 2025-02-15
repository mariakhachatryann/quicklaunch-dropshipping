<?php


namespace frontend\controllers;


use common\models\Product;
use common\models\ProductVariant;
use common\models\VariantPriceMarkup;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProductVariantController extends UserController
{

    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
      $behaviors = parent::behaviors();
      $behaviors['verbs'] = [
        'class' => VerbFilter::class,
        'actions' => [
          'edit' => ['POST'],
        ],
      ];
      return $behaviors;
    }

    public function actionUpdate($id)
    {
      Yii::$app->response->format = Response::FORMAT_JSON;
      $model = $this->findModel($id);

      if ($model->load(Yii::$app->request->post()) && $model->save()) {
        if ($model->getFromProductMarkup) {
          if ($model->variantPriceMarkup) {
            $model->variantPriceMarkup->delete();
          }
        } else {
          $variantPriceMarkupModel = $model->variantPriceMarkup ?? new VariantPriceMarkup();
          if (!($variantPriceMarkupModel->load(Yii::$app->request->post()) && $variantPriceMarkupModel->save())) {
            Yii::$app->response->statusCode = 400;
            return $variantPriceMarkupModel->getErrors();
          }
        }

        if ($model->product->is_published) {
          try {
            $user = $model->product->user;
            $productDataModel = $user->getProductDataModel();
            $productDataModel->setProduct($model->product);
            $productDataModel->updateShopifyVariant($model);
            $productDataModel->updateShopifyVariantsInventory([
              $model->shopify_variant_id => true
            ]);
          } catch (\Exception $exception) {
            Yii::$app->response->statusCode = 400;
            return $exception->getMessage();
          }
        }

        Yii::$app->response->statusCode = 200;
        return ['success' => true];
      } else {
        Yii::$app->response->statusCode = 400;
        return $model->getMessage();
      }

      Yii::$app->response->statusCode = 400;
      return $model->getErrors();
    }

    public function actionDelete($id)
    {
      $this->findModel($id)->delete();

      return $this->redirect(Yii::$app->request->referrer);

    }

    protected function findModel($id)
    {
      if (($model = ProductVariant::findOne(['id' => $id])) !== null) {
        if ($model->product->user->id === Yii::$app->user->id) {
          return $model;
        }
      }
      throw new NotFoundHttpException('The requested page does not exist.');
    }
}