<?php

use common\models\Plan;
use yii\helpers\Html;
use backend\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PromoCodeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Promo Codes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="promo-code-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Promo Code', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'label' => 'User',
                'attribute' => 'user_id',
                'value' => function($model) {
                    return $model->user->username;
                },
            ],
            'code',
            [
                'label' => 'Plan',
                'attribute' => 'plan_id',
                'filter' => Plan::getPlans(),
                'value' => function($model) {
                    return $model->plan->name;
                },
            ],
            'price',
            [
                'attribute' => 'active_until',
                'format' => ['date', 'php:Y-m-d']
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],
            //'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
