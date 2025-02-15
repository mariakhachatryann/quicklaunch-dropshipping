<?php

use common\models\Plan;
use kartik\daterange\DateRangePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UninstallSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Uninstalls';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="uninstall-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'label' => 'User',
                'attribute' => 'username',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a($model->user->username, Url::toRoute(['/user/view', 'id' => $model->user_id]),['target' => '_blank']);
                },
            ],
            [
                'attribute' => 'plan_id',
                'value' => function ($model) {
                    return $model->plan->name ?? null;
                },
                'filter'=>ArrayHelper::map(Plan::find()->asArray()->all(), 'id', 'name'),
            ],
            'duration',
            [
                'attribute' => 'uninstalled_at',
                'value' => 'uninstalled_at',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'convertFormat' => true,
                    'attribute' => 'dateRangeUninstall',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d'
                        ],

                    ],
                ])
            ],

            [
                    'class' => 'yii\grid\ActionColumn',
                    'visibleButtons' => ['delete' => true, 'update' => false, 'view' => true],
            ],
        ],
    ]); ?>


</div>
