<?php


namespace frontend\controllers\api;


use common\models\BulkImport;
use common\models\Feature;
use common\models\MultipleImportItem;
use common\models\Product;
use common\models\ProductBulkImportItem;
use common\models\User;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class BulkImportController extends ApiController
{

    public function actionImportItems()
    {
        $user = Yii::$app->user->identity;
        /* @var  $user User*/
        $planFeatures = Feature::getPlanSettingkeys(Yii::$app->user->identity);
        if (!isset($planFeatures['bulk_import']) && !$planFeatures['bulk_import']) {
            return [
                'success' => false
            ];
        }

        if (Yii::$app->request->post('type') === BulkImport::FROM_EXTENSION) {
            $bulkImport = new BulkImport();
            $bulkImport->category_url = Yii::$app->request->post('url');
            $bulkImport->user_id = $user->id;
            if (!($bulkImport->validate() && $bulkImport->save())) {
                return [
                    'success' => false,
                    'err' => $bulkImport->getErrors()
                ];
            }
        } else {
            $bulkImport = $user->getBulkImports()->where(['id' => Yii::$app->request->post('id')])->one();

            if (!$bulkImport) {
                return [
                    'success' => false
                ];
            }
        }

        $products = Yii::$app->request->post('products') ?? [];
        $existingProducts = [];
        $statuses = [];
        $activeIndexUrl = null;
        foreach ($products as $product) {
            $model = new ProductBulkImportItem();
            $model->bulk_import_id = $bulkImport->id;
            $model->url = $product['link'];
            $model->status = ProductBulkImportItem::STATUS_PENDING;
            $existingProduct = $user->getProducts()->where(['src_product_url' => explode('?',$model->url)[0]])->one();
            /* @var Product $existingProduct */

            if ($existingProduct) {
                $model->status = ProductBulkImportItem::STATUS_SUCCESS;
                $model->product_id = $existingProduct->id;
                $existingProducts[$model->url] = $existingProduct->id;
                $statuses[$model->url] = $model->status;
            } else if(is_null($activeIndexUrl)) {
                $activeIndexUrl = $model->url;
                $statuses[$model->url] = $model->status;
            }

            if (!($model->validate() && $model->save())) {
                return [
                    'success' => false,
                    'err' => $model->getErrors()
                ];
            }
        }

        $isCompleted = !in_array(ProductBulkImportItem::STATUS_PENDING, $statuses) && count($statuses);

        return [
            'success' => true,
            'productsIds' => $existingProducts,
            'statuses' => $statuses,
            'activeIndexUrl' => $activeIndexUrl,
            'id' => $bulkImport->id,
            'isCompleted' => $isCompleted
        ];
    }

    public function actionUpdateItems($id)
    {
        $planFeatures = Feature::getPlanSettingkeys(Yii::$app->user->identity);
        if (!$planFeatures['bulk_import']) {
            return [
                'success' => false
            ];
        }

        $bulkImport = $this->findModel($id);
        /* @var $bulkImport BulkImport*/
        $items = $bulkImport->productBulkImportItems;
        $activeIndexUrl = null;

        foreach ($items as $key => $item) {
            if ($item->created_at) {
                $now = time();
                if ($now - $item->created_at >= (($key + 1) * BulkImport::MAX_IMPORT_TIME) && $item->status === ProductBulkImportItem::STATUS_PENDING) {
                    $item->status = ProductBulkImportItem::STATUS_ERROR;
                } elseif(is_null($activeIndexUrl) && $item->status === ProductBulkImportItem::STATUS_PENDING) {
                    $activeIndexUrl = $item->url;
                }
            } else {
                $item->created_at = time();
            }
            $item->save();
        }
        $statuses = ArrayHelper::map($items, 'url', 'status');
        $productIds = ArrayHelper::map($items, 'url', 'product_id');
        $isCompleted = !in_array(ProductBulkImportItem::STATUS_PENDING, $statuses);

        return [
            'success' => true,
            'data' => compact('statuses', 'productIds', 'isCompleted', 'activeIndexUrl')
        ];
    }

    protected function findModel($id)
    {
        $user = Yii::$app->user->identity;
        /* @var User $user */
        $model = $user->getBulkImports()->where([BulkImport::tableName() . '.id' => $id])->one();

        if ($model) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionPublishProducts()
    {
        $user = Yii::$app->user->identity;
        /* @var User $user */

        $productIds = Yii::$app->request->post('productIds') ?? [];
        $products = $user->getProducts()->where(['in', 'id', $productIds])->all();
        if ($products) {
            foreach ($products as $product) {
                /* @var $product Product*/
                if (!$product->is_published) {
                    $product->addToPublishQueue();
                }
            }
        }

        return ['success' => true];
    }

}