<?php

use common\models\ProductPublishQueue;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ProductPublishQueue */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Product Publish Queues', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="product-publish-queue-view">

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
                'attribute' => 'product_id',
                'format' => 'raw',
                'value' => function (ProductPublishQueue  $productPublishQueue) {
                    return Html::a($productPublishQueue->product_id, Url::toRoute(['/product/view', 'id' => $productPublishQueue->product_id]));
                }
            ],
            'response_text',
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function (ProductPublishQueue  $productPublishQueue) {
                    return Html::a(ProductPublishQueue::STATUSES[$productPublishQueue->status], $productPublishQueue->product->src_product_url, ['target' => '_blank']);
                }
            ],
            'created_at:datetime',
            'updated_at:datetime',
        ],
    ]) ?>

</div>
