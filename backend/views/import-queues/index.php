<?php

use common\models\AvailableSite;
use common\models\ImportQueue;
use common\models\MonitoringQueue;
use common\models\User;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ImportQueueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Import Queues';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="import-queues-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Import Queues', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'site_id',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'site_id',
                    'data' => AvailableSite::getSitesDropdown(),
                    'options' => ['placeholder' => 'Select Site'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
                'value' => function (ImportQueue $model) {
                    if ($model->site) {
                        return $model->site->name;

                    }
                    return '';
                }
            ],
            [
                'attribute' => 'monitoring_queue_id',
                'format' => 'raw',
                'value' => function(ImportQueue $model) {
                    if ($model->monitoringQueue) {
                        return Html::a($model->monitoring_queue_id, Url::toRoute(['monitoring-queue/view', 'id' => $model->monitoring_queue_id]), ['target' => '_blank', 'title' => $model->monitoring_queue_id]);
                    }
                },
            ],
            [
                'label' => 'Monitoring Queue Status',
                'format' => 'raw',
                'value' => function(ImportQueue $model) {
                    if ($model->monitoringQueue) {
                        return MonitoringQueue::MONITORING_QUEUE_STATUSES[$model->monitoringQueue->status];
                    }
                },
            ],
            [
                'attribute' => 'user_id',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'user_id',
                    'data' => User::getUsers(),
                    'options' => ['placeholder' => 'Select User'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
                'value' => function(ImportQueue $model) {
                    return $model->user->username;
                },
            ],
            [
                'attribute' => 'url',
                'format' => 'raw',
                'value' => function(ImportQueue $model) {

                    return Html::a(parse_url($model->url, PHP_URL_HOST), $model->url, ['target' => '_blank', 'title' => $model->url]);
                },
            ],
            [
                'attribute' => 'status',
                'filter' => ImportQueue::STATUSES,
                'value' => function (ImportQueue $model) {
                    return (ImportQueue::STATUSES[$model->status]);
                }
            ],
            'processing_ip',
            'handler',
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'created_at',
                    'convertFormat' => true,
                    'startAttribute' => 'datetime_min',
                    'endAttribute' => 'datetime_max',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d'
                        ],
                    ],
                ]),
            ],
            [
                'attribute' => 'country',
                'filter' => ImportQueue::COUNTRY_MAP,
                'value' => function (ImportQueue $model) {
                    return ImportQueue::COUNTRY_MAP[$model->country] ?? null;
                }
            ],
            [
                'attribute' => 'type',
                'filter' => ImportQueue::TYPE_MAP,
                'value' => function (ImportQueue $model) {
                    return ImportQueue::TYPE_MAP[$model->type] ?? null;
                }
            ],
            'import_reviews',
            [
                'attribute' => 'updated_at',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'updated_at',
                    'convertFormat' => true,
                    'startAttribute' => 'update_datetime_min',
                    'endAttribute' => 'update_datetime_max',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d'
                        ],
                    ],
                ]),

            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
