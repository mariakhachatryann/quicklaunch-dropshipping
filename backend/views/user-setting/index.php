<?php

use yii\helpers\Html;
use backend\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSettingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Settings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-setting-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create User Setting', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'user_id',
            'price_markup',
            'price_amount',
            'price_percentage',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
