<?php

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ImportQueueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $alertCaptchaSearchModel backend\models\AlertCaptchaSearch */
/* @var $alertCaptchaDataProvider yii\data\ActiveDataProvider */

use common\models\AlertCaptcha;
use common\models\AvailableSite;
use common\models\ImportQueue;
use common\models\MonitoringQueue;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\bootstrap\Html;
use backend\widgets\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

$js = <<< JS
setInterval(function(){
    $.pjax.reload({container:'#grid-pjax'});
}, 5000);
JS;

$this->registerJs($js);

$this->registerJs("
    $(document).on('change', '#is-online-toggle', function () {
        let isOnline = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: '" . \yii\helpers\Url::to(['failed-queue/toggle-online-status']) . "',
            type: 'POST',
            data: {is_online: isOnline, _csrf: yii.getCsrfToken()},
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while updating the status.');
            }
        });
    });
");


?>
    <div class="is-online-switcher" style="float: right; margin-right: 20px;">
    <label class="switch">
        <input type="checkbox" id="is-online-toggle" <?= Yii::$app->user->identity->is_online ? 'checked' : '' ?>>
        <span class="slider round"></span>
    </label>
    </div>
    <div class="alert-captcha-index">

        <h1>Alert Captchas</h1>

        <?php Pjax::begin(['id' => 'grid-pjax']); ?>

        <?= GridView::widget([
            'dataProvider' => $alertCaptchaDataProvider,
            'filterModel' => $alertCaptchaSearchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'id',
                'import_queue_id',
                'attribute' => 'handler',
                [
                    'attribute' => 'status',
                    'filter' => AlertCaptcha::STATUSES,
                    'format' => 'raw',
                    'value' => function($model) {
                        if ($model->status === AlertCaptcha::STATUS_PENDING) {
                            return Html::a('Take', Url::to(['/alert-captcha/take', 'id' => $model->id]),['class' => 'btn btn-success'] );
                        }
                        return Html::tag('p', AlertCaptcha::STATUSES[$model->status]);
                    }
                ],
                [
                    'attribute' => 'site_id',
                    'filter' => Select2::widget([
                        'model' => $alertCaptchaSearchModel,
                        'attribute' => 'site_id',
                        'data' => AvailableSite::getSitesDropdown(),
                        'options' => ['placeholder' => 'Select Site'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]),
                    'value' => function (AlertCaptcha $model) {
                        if ($model->importQueue->site) {
                            return $model->importQueue->site->name;

                        }
                        return '';
                    }
                ],
                [
                    'attribute' => 'url',
                    'format' => 'raw',
                    'value' => function(AlertCaptcha $model) {
                        return Html::a(parse_url($model->importQueue->url, PHP_URL_HOST), $model->importQueue->url, ['target' => '_blank', 'title' => $model->importQueue->url]);
                    },
                ],
                [
                    'attribute' => 'country',
                    'filter' => ImportQueue::COUNTRY_MAP,
                    'value' => function (AlertCaptcha $model) {
                        return ImportQueue::COUNTRY_MAP[$model->importQueue->country] ?? null;
                    }
                ],
                [
                    'attribute' => 'type',
                    'filter' => ImportQueue::TYPE_MAP,
                    'value' => function (AlertCaptcha $model) {
                        return ImportQueue::TYPE_MAP[$model->importQueue->type] ?? null;
                    }
                ],
                [
                    'attribute' => 'processing_ip',
                    'value' => function (AlertCaptcha $model) {
                        return $model->importQueue->processing_ip;
                    }
                ],
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
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view}',
                    'buttons' => [
                        'view' => function ($url, $model) {
                            return Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::to(['/alert-captcha/view', 'id' => $model->id]), [
                                'title' => 'View',
                                'class' => 'btn btn-primary btn-sm',
                            ]);
                        }
                    ],
                ],

            ],
        ]); ?>


        <?php Pjax::end()?>


    </div>

<h1>Failed Queues</h1>
<h2><?= date('Y-m-d H:i:s')?></h2>
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
            'attribute' => 'url',
            'format' => 'raw',
            'value' => function(ImportQueue $model) {
                return Html::a(parse_url($model->url, PHP_URL_HOST), $model->url, ['target' => '_blank', 'title' => $model->url]);
            },
        ],
        'attribute' => 'handler',
        [
            'attribute' => 'country',
            'filter' => ImportQueue::COUNTRY_MAP,
            'value' => function (ImportQueue $model) {
                return ImportQueue::COUNTRY_MAP[$model->country] ?? null;
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
            'attribute' => 'type',
            'filter' => ImportQueue::TYPE_MAP,
            'value' => function (ImportQueue $model) {
                return ImportQueue::TYPE_MAP[$model->type] ?? null;
            }
        ],
        [
            'attribute' => 'processing_ip',
            'value' => function (ImportQueue $model) {
                return $model->processing_ip;
            }
        ],
        [
            'attribute' => 'created_at',
            'label' => 'Created At',
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
            'value' => function(ImportQueue $model) {
                return $model->created_at;
            }
        ],

        [
            'attribute' => 'updated_at',
            'label' => 'Updated At',
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
            'value' => function(ImportQueue $model) {
                return $model->updated_at;
            }
        ],
    ],
]); ?>
<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 34px;
        height: 20px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 20px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }

    input:checked + .slider {
        background-color: #2196F3;
    }

    input:checked + .slider:before {
        transform: translateX(14px);
    }
</style>


