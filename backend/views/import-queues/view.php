<?php

use common\models\ImportQueue;
use common\models\MonitoringQueue;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ImportQueue */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Import Queues', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
YiiAsset::register($this);
?>
<div class="import-queues-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'site_id',
                'value' => function (ImportQueue $model) {
                    if ($model->site) {
                        return $model->site->name;
                    }
                }
            ],
            [
                'attribute' => 'user_id',
                'value' => function (ImportQueue $model) {
                    if ($model->user) {
                        return $model->user->username;
                    }
                }
            ],
            'url:url',
            [
                'attribute' => 'status',
                'value' => function (ImportQueue $model) {
                    return ImportQueue::STATUSES[$model->status] ?? $model->status;
                }
            ],
            'processing_ip',
            'handler',
             [
                 'attribute' => 'created_at',
                 'value' => function (ImportQueue $model) {
                     return date('Y-m-d H:i:s', $model->created_at);
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
                'attribute' => 'country',
                'value' => function (ImportQueue $model) {
                  return ImportQueue::COUNTRY_MAP[$model->country] ?? null;
                }
              ],
            [
                'attribute' => 'updated_at',
                'value' => function (ImportQueue $model) {
                    return date('Y-m-d H:i:s', $model->updated_at);
                }
            ],
        ],
    ]) ?>
    <pre>
        <?php print_r($model->getContent())?>
    </pre>

</div>
