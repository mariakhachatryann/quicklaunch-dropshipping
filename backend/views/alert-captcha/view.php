<?php

use common\models\AlertCaptcha;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AlertCaptcha */

$this->title = 'Alert Captcha ID - ' . $model->id;
?>

<h1><?= Html::encode($this->title) ?></h1>

<p>
    <?= Html::a('Solve', ['solve-captcha', 'id' => $model->id], [
        'class' => 'btn btn-success',
        'data' => [
            'method' => 'post',
        ],
    ]) ?>
    <?= Html::a('Cancel', ['cancel-captcha', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'method' => 'post',
            'confirm' => 'Are you sure you want to cancel this alert captcha?',
        ],
    ]) ?>

<h2>Alert Captcha</h2>
<?php
?>

<div class="row">
    <div class="col-md-6">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                [
                    'attribute' => 'admin_id',
                    'label' => 'Admin Username',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->admin
                            ? Html::a($model->admin->username, ['admin-user/view', 'id' => $model->admin->id], ['target' => '_blank'])
                            : 'Not Set';
                    },
                ],

                [
                    'attribute' => 'created_at',
                    'format' => ['datetime', 'php:Y-m-d H:i:s'],
                ],
                'duration',
                [
                    'attribute' => 'handler',
                    'value' => $model->handler ?? '(not set)',
                ],
            ],
        ]) ?>
    </div>

    <div class="col-md-6">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                [
                    'attribute' => 'status',
                    'value' => AlertCaptcha::STATUSES[$model->status] ?? '(unknown)',
                ],
                [
                    'attribute' => 'taken_at',
                    'format' => ['datetime', 'php:Y-m-d H:i:s'],
                ],
                [
                    'attribute' => 'updated_at',
                    'format' => ['datetime', 'php:Y-m-d H:i:s'],
                ],
                [
                    'attribute' => 'import_queue_id',
                    'format' => 'raw',
                    'value' => Html::a($model->import_queue_id, ['import-queues/view', 'id' => $model->import_queue_id], ['target' => '_blank']),
                ],
            ],
        ]) ?>
    </div>
</div>


<h2>Import Queue</h2>
<div class="row">
    <div class="col-md-6">
        <?= DetailView::widget([
            'model' => $model->importQueue,
            'attributes' => [
                [
                    'attribute' => 'country',
                    'value' => \common\models\ImportQueue::COUNTRY_MAP[$model->importQueue->country] ?? 'N/A',
                ],
                [
                    'attribute' => 'created_at',
                    'format' => ['datetime', 'php:Y-m-d H:i:s'],
                ],
                'fail_count',
                'handler',
                'id',
                'import_reviews',
                [
                    'attribute' => 'monitoring_queue_id',
                    'label' => 'Monitoring Queue',
                    'value' => $model->importQueue->monitoringQueue ? Html::a($model->importQueue->monitoringQueue->id, ['monitoring-queue/view', 'id' => $model->importQueue->monitoringQueue->id]) : 'N/A',
                    'format' => 'raw',
                ],

            ],
        ]) ?>
    </div>

    <div class="col-md-6">
        <?= DetailView::widget([
            'model' => $model->importQueue,
            'attributes' => [
                [
                    'attribute' => 'processing_ip',
                    'label' => 'Processing IP',
                    'format' => 'raw',
                    'value' => $model->importQueue->processing_ip ?? 'N/A',
                ],
                [
                    'attribute' => 'site_id',
                    'label' => 'Site',
                    'value' => $model->importQueue->site ? Html::a($model->importQueue->site->name, ['available-site/view', 'id' => $model->importQueue->site->id]) : 'N/A',
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'status',
                    'value' => \common\models\ImportQueue::STATUSES[$model->importQueue->status] ?? 'N/A',
                ],
                'type',
                [
                    'attribute' => 'updated_at',
                    'format' => ['datetime', 'php:Y-m-d H:i:s'],
                ],
                [
                    'attribute' => 'url',
                    'label' => 'URL',
                    'value' => $model->importQueue->url ? Html::a($model->importQueue->url, $model->importQueue->url) : 'N/A',
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'user_id',
                    'label' => 'User',
                    'value' => $model->importQueue->user ? Html::a($model->importQueue->user->username, ['user/view', 'id' => $model->importQueue->user->id]) : 'N/A',
                    'format' => 'raw',
                ],
            ],
        ]) ?>
    </div>
</div>


<h2>Monitoring Queue</h2>
<?php if ($model->importQueue->monitoringQueue): ?>
    <div class="row">
        <div class="col-md-6">
            <?= DetailView::widget([
                'model' => $model->importQueue->monitoringQueue,
                'attributes' => [
                    [
                        'attribute' => 'id',
                        'label' => 'ID',
                    ],
                    [
                        'attribute' => 'product_id',
                        'label' => 'Product ID',
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'Status',
                        'value' => \common\models\MonitoringQueue::MONITORING_QUEUE_STATUSES[$model->importQueue->monitoringQueue->status] ?? 'Unknown',
                    ],
                    [
                        'attribute' => 'created_at',
                        'label' => 'Created At',
                        'value' => $model->importQueue->monitoringQueue->created_at ?? 'N/A',
                        'format' => ['datetime', 'php:Y-m-d H:i:s'],
                    ],
                ]
            ]) ?>
        </div>
        <div class="col-md-6">
            <?= DetailView::widget([
                'model' => $model->importQueue->monitoringQueue,
                'attributes' => [
                    [
                        'attribute' => 'updated_at',
                        'label' => 'Updated At',
                        'value' => $model->importQueue->monitoringQueue->updated_at ?? 'N/A',
                        'format' => ['datetime', 'php:Y-m-d H:i:s'],
                    ],
                    [
                        'attribute' => 'error_msg',
                        'label' => 'Error Message',
                        'value' => $model->importQueue->monitoringQueue->error_msg ? $model->importQueue->monitoringQueue->error_msg : 'No error message',
                    ],
                    [
                        'attribute' => 'import_reviews',
                        'label' => 'Import Reviews',
                        'value' => $model->importQueue->monitoringQueue->import_reviews ? 'Yes' : 'No',
                    ],
                ],
            ]) ?>
        </div>
    </div>
<?php endif ?>
</p>

<div class="alert-captcha-details">
</div>
