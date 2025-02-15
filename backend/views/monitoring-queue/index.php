<?php

use common\models\ImportQueue;
use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\Url;
use common\models\MonitoringQueue;

/* @var $this yii\web\View */
/* @var $totalCount int */
/* @var $searchModel backend\models\MonitoringQueueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Monitoring Queues';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="monitoring-queue-index">

    <h1><?= Html::encode($this->title) ?></h1>
    
    
    <p>Total products for monitorring: <?=$totalCount?></p>


    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'header' => 'User id',
                'format' => 'raw',
                'value' => function (MonitoringQueue  $monitoringQueue) {
                    return Html::a($monitoringQueue->product->user_id, Url::toRoute(['/user/view', 'id' => $monitoringQueue->product->user_id]));
                }
            ],
            [
                'attribute' => 'product_id',
                'format' => 'raw',
                'value' => function (MonitoringQueue  $monitoringQueue) {
                    return Html::a($monitoringQueue->product_id, Url::toRoute(['/product/update', 'id' => $monitoringQueue->product_id]), ['target' => '_blank']);
                }
            ],
            [
                'label' => 'Import Queue Id',
                'format' => 'raw',
                'value' => function(MonitoringQueue $model) {
                    if ($model->importQueue) {
                        return Html::a($model->importQueue->id, Url::toRoute(['import-queues/view', 'id' => $model->importQueue->id]), ['target' => '_blank', 'title' => $model->importQueue->id]);
                    }
                },
            ],
            [
                'label' => 'Import Queue Status',
                'format' => 'raw',
                'value' => function(MonitoringQueue $model) {
                    if ($model->importQueue) {
                        return ImportQueue::STATUSES[$model->importQueue->status];
                    }
                },
            ],
            [
                'attribute' => 'product_url',
                'format' => 'raw',
                'value' => function (MonitoringQueue  $monitoringQueue) {
                    return Html::a($monitoringQueue->product->src_product_url, $monitoringQueue->product->src_product_url, ['target' => '_blank']);
                }
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'filter' => MonitoringQueue::MONITORING_QUEUE_STATUSES,
                'value' => function (MonitoringQueue  $monitoringQueue) {
                    return (MonitoringQueue::MONITORING_QUEUE_STATUSES[$monitoringQueue->status]);
                }
            ],
            [
                'attribute' => 'created_at',
                'value' => 'created_at',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'convertFormat' => true,
                    'attribute' => 'dateRangeCreated',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d'
                        ],

                    ],
                ])
            ],
            [
                'attribute' => 'updated_at',
                'value' => 'updated_at',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'convertFormat' => true,
                    'attribute' => 'dateRangeUpdated',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d'
                        ],

                    ],
                ])
            ],
            'error_msg:ntext',

            [
                'class' => 'yii\grid\ActionColumn',
                'visibleButtons' => ['delete' => true, 'update' => false, 'view' => true],
            ],
        ],
    ]); ?>


</div>
