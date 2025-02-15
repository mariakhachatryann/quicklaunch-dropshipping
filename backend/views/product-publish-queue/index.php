<?php

use common\models\ProductPublishQueue;
use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ProductPublishQueueSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Product Publish Queues';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-publish-queue-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Product Publish Queue', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'user_id',
                'header' => 'User ID',
                'format' => 'raw',
                'value' => function (ProductPublishQueue $productPublishQueue) {
                    return Html::a($productPublishQueue->product->user_id, Url::to(['/user/view', 'id' => $productPublishQueue->product->user_id]));
                }
            ],
            [
                'attribute' => 'product_id',
                'format' => 'raw',
                'value' => function (ProductPublishQueue  $productPublishQueue) {
                    return Html::a($productPublishQueue->product_id, Url::toRoute(['/product/view', 'id' => $productPublishQueue->product_id]));
                }
            ],

            [
                'attribute' => 'status',
                'format' => 'raw',
                'filter' => ProductPublishQueue::STATUSES,
                'value' => function (ProductPublishQueue  $productPublishQueue) {
                    return Html::a(ProductPublishQueue::STATUSES[$productPublishQueue->status], $productPublishQueue->product->src_product_url, ['target' => '_blank']);
                }
            ],
            'response_text',
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

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
