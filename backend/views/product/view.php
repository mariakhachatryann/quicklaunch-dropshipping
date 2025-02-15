<?php

use common\models\Product;
use Slince\Shopify\Model\Products\Variant;
use backend\widgets\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var \yii\data\ActiveDataProvider $variantsDataProvider */
/* @var \yii\data\ActiveDataProvider $variantChangesDataProvider */
/* @var \yii\data\ActiveDataProvider $variantsShopifyDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="product-view">

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
            'title',
            [
                'attribute' => 'user_id',
                'value'     => function ($model) {
                    return  $model->user->username;
                }
            ],
            'src_product_url:url',
            'sku',
            'is_published:boolean',
            'is_deleted:boolean',
            'monitoring_stock:boolean',
            'monitoring_price:boolean',
            [
                'attribute' => 'imported_from',
                'value'     => function ($model) {
                    return Product::IMPORTED_FROM_TYPES[$model->imported_from] ?? '';
                }
            ],
            [
                'attribute' => 'monitored_at',
                'value'     => function ($model) {
                    return date('Y-m-d H:i:s', $model->monitored_at);
                }
            ],

            [
                'attribute' => 'created_at',
                'value'     => function ($model) {
                    return date('Y-m-d H:i:s', $model->created_at);
                }
            ],
            [
                'attribute' => 'updated_at',
                'value'     => function ($model) {
                    return date('Y-m-d H:i:s', $model->updated_at);
                }
            ],

            [
                'attribute' => 'site_id',
                'label' => 'Site',
                'value'     => function ($model) {
                    if($model->site){
                        return $model->site->name;
                    }
                }
            ],


            [
                'attribute' => 'handle',
                'value'     => function ($model) {
                    return $model->getHandleUrl() ;

                },
                'format'=> 'url'
            ],

            [
                'attribute' => 'shopify_id',
                'value'     => function ($model) {
                    return $model->shopify_id;
                }
            ],

            [
                'attribute' => 'count_variants',
                'value'     => function ($model) {
                    return $model->count_variants;
                }
            ],


            [
                'attribute' => 'product_data',
                'format' => 'raw',
                'value'     => function ($model) {
                    return '<pre>'.print_r(json_decode($model->product_data, true), true).'</pre>';
                }
            ],


        ],
    ]) ?>

    <div class="product-variants">
        <h3> Product variants</h3>
        <?= GridView::widget([
            'dataProvider' => $variantsDataProvider,
            'layout'=>"{items}{pager}",
            'columns' => \common\helpers\GridColumnsHelper::getVariantChangeOptions($model),

        ]) ?>
    </div>

    <div class="product-variants">
        <h3> Product shopify variants</h3>
        <?= GridView::widget([
            'dataProvider' => $variantsShopifyDataProvider,
            'layout'=>"{items}{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'id',
                    'value' => function (Variant $variant) {
                        return $variant->getId();
                    }
                ],
                [
                    'attribute' => 'title',
                    'value' => function (Variant $variant) {
                        return $variant->getTitle();
                    }
                ],
                [
                    'attribute' => 'sku',
                    'value' => function (Variant $variant) {
                        return $variant->getSku();
                    }
                ],
                [
                    'attribute' => 'price',
                    'value' => function (Variant $variant) {
                        return $variant->getPrice();
                    }
                ],
                [
                    'attribute' => 'inventoryQuantity',
                    'value' => function (Variant $variant) {
                        return $variant->getInventoryQuantity();
                    }
                ],
            ]

        ]) ?>
    </div>

    <div class="product-variant-changes">
        <h3> Product variant changes</h3>
        <?= GridView::widget([
            'dataProvider' => $variantChangesDataProvider,
            'layout'=>"{items}{pager}",
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'variant_id',
                    'value'     => function ($model) {
                        $options = array_filter([$model->variant->option1, $model->variant->option2, $model->variant->option3]);
                        return implode(',', $options);
                    }
                ],
                'old_price',
                'new_price',
                'old_compare_at_price',
                'new_compare_at_price',
                'old_inventory_quantity',
                'new_inventory_quantity',
                [
                    'attribute' => 'created_at',
                    'value'     => function ($model) {
                        return date('Y-m-d H:i:s', $model->created_at);
                    }
                ],
                [
                    'attribute' => 'updated_at',
                    'value'     => function ($model) {
                        return date('Y-m-d H:i:s', $model->updated_at);
                    }
                ],
            ],
        ]) ?>
    </div>



</div>
