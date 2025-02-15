<?php

use yii\helpers\Html;
use backend\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PlanStatisticSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Plan Statistics';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-statistic-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Plan Statistic', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'plan_id',
            'total',
            'date',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
