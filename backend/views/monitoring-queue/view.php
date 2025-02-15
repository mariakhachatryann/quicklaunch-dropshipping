<?php

use common\models\ImportQueue;
use common\models\MonitoringQueue;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\MonitoringQueue */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Monitoring Queues', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="monitoring-queue-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
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
                'attribute' => 'product_id',
                'format' => 'raw',
                'value' => function (MonitoringQueue  $monitoringQueue) {
                    return Html::a($monitoringQueue->product_id, Url::toRoute(['/product/view', 'id' => $monitoringQueue->product_id]));
                }
            ],
            [
                'attribute' => 'status',
                'value' => function (MonitoringQueue  $monitoringQueue) {
                    return (MonitoringQueue::MONITORING_QUEUE_STATUSES[$monitoringQueue->status]);
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
            'created_at:datetime',
            'updated_at:datetime',
            'error_msg:ntext',
            'content:ntext',
        ],
    ]) ?>

</div>
