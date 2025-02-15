<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CaptchaSolverLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Captcha Solver Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="captcha-solver-log-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Captcha Solver Log', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            [
                'attribute' => 'admin_id',
                'value' => function ($model) {
                    return $model->admin->username;
                },
                'label' => 'Solver',
            ],
            [
                'attribute' => 'activated_at',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDatetime($model->activated_at, 'php:Y-m-d H:i:s');
                },
                'label' => 'Activated At',
            ],
            [
                'attribute' => 'deactivated_at',
                'value' => function ($model) {
                    return Yii::$app->formatter->asDatetime($model->deactivated_at, 'php:Y-m-d H:i:s');
                },
                'label' => 'Deactivated At',
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
