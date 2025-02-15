<?php

use yii\helpers\Html;
use backend\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ProductVariantSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Product Variants';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-variant-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Product Variant', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'product_id',
            'shopify_variant_id',
            'option1',
            'option2',
            'option3',
            'sku',
            'default_sku',
            'price',
            'compare_at_price',
            'inventory_quantity',
            'inventory_item_id',
            'updated_at:datetime',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
