<?php

use common\models\AvailableSite;
use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CreditSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Credits';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="available-site-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Available Site', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

<!--    --><?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'amount',
            'status',
            'shopify_credit_id',
            [
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a($model->user->username, Url::to(['/user/view','id' => $model->user_id]),['target'=>'_blank'] );
                },
            ],
            [
                'attribute' => 'plan_id',
                'value'     => function ($model) {
                    return $model->plan->name;
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
                    return date('Y-m-d H:i:s', $model->created_at);
                }
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
