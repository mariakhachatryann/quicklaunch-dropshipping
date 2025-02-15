<?php

namespace frontend\controllers;


use common\models\BulkMonitoring;
use common\models\BulkMonitoringItem;
use common\models\Feature;
use common\models\Product;
use common\models\User;
use yii\helpers\ArrayHelper;

class BulkMonitoringController extends UserController
{

    public function actionCreate(): string
    {
        if (\Yii::$app->request->isPost) {
            /* @var User $user */
            $user = \Yii::$app->user->identity;
            $planFeatures = Feature::getPlanSettingkeys($user);

            if (isset($planFeatures['products_bulk_monitoring'])) {
                $productsIds = \Yii::$app->request->post('ids');
                $products = is_array($productsIds) ? $user->getProducts()->where(['IN', 'id', $productsIds])->all() : null;
                $limits = $user->getLimits();

                if (count($products) > $limits['monitoringLimit']) {
                    return json_encode([
                        'success' => true,
                        'message' => 'Monitoring limit is expired'
                    ]);
                }

                if ($products) {
                    $model = new BulkMonitoring();
                    $model->user_id = $user->id;
                    $model->save();

                    foreach ($products as $product) {
                        /* @var Product $product */

                        if (!$product->monitoring_price && !$product->monitoring_stock && !$product->monitoring_reviews) {
                            continue;
                        }

                        $bulkMonitoringItem = new BulkMonitoringItem();
                        $bulkMonitoringItem->bulk_monitoring_id = $model->id;
                        $bulkMonitoringItem->url = $product->src_product_url;
                        $bulkMonitoringItem->status = BulkMonitoringItem::STATUS_PENDING;
                        $bulkMonitoringItem->product_id = $product->id;
                        $bulkMonitoringItem->save();
                    }

                    $items = $model->bulkMonitoringItems;
                    $statuses = ArrayHelper::map($items, 'product_id', 'status');
                    $ids = ArrayHelper::map($items, 'product_id', 'id');
                    $active = count(array_keys($statuses)) ? array_keys($statuses)[0] : false;

                    return json_encode([
                        'success' => true,
                        'statuses' => $statuses,
                        'ids' => $ids,
                        'active' => $active,
                        'monitoring_id' => $model->id,
                    ]);
                }
            }
        }

        return json_encode([
            'success' => true,
        ]);
    }

    public function actionUpdateStatuses()
    {
        if (\Yii::$app->request->isPost) {
            /* @var User $user */
            $user = \Yii::$app->user->identity;

            if (!$user) {
                return json_encode([
                    'success' => false,
                ]);
            }

            $id = \Yii::$app->request->post('id');
            /* @var BulkMonitoring $model */
            $model = $user->getBulkMonitorings()->where(['id' => $id])->one();

            if (!$model) {
                return json_encode([
                    'success' => false,
                ]);
            }

            /* @var BulkMonitoringItem[] $items */
            $items = $model->bulkMonitoringItems;

            foreach($items as $key => $item) {
                $now = time();

                if ($now - $item->created_at >= (($key + 1) * BulkMonitoring::MAX_IMPORT_TIME) && $item->status === BulkMonitoringItem::STATUS_PENDING) {
                    $item->status = BulkMonitoringItem::STATUS_ERROR;
                }
            }

            $statuses = ArrayHelper::map($items, 'product_id', 'status');
            $ids = ArrayHelper::map($items, 'product_id', 'id');
            $active = false;
            foreach ($statuses as $key => $value) {
                if ($value == BulkMonitoringItem::STATUS_PENDING && !$active) {
                    $active = $key;
                }
            }
            return json_encode([
                'success' => true,
                'statuses' => $statuses,
                'ids' => $ids,
                'active' => $active
            ]);
        }

    }

}