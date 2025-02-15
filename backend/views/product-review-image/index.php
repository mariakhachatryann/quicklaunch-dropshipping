<?php

use yii\helpers\Html;
use backend\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ProductReviewImageSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Product Review Images';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-review-image-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Product Review Image', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'product_review_id',
            'image_url:url',
            'created_at',
            'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
