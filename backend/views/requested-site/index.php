<?php

use common\models\RequestedSite;
use common\models\User;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\RequestedSiteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Requested Sites';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="requested-site-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Requested Site', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'url:url',
            [
                'attribute' => 'user_id',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'user_id',
                    'data' => User::getUsers(),
                    'options' => ['placeholder' => 'Select a user ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
                'format' => 'raw',
                'value' => function(RequestedSite $model) {
                    return Html::a($model->user->username, Url::toRoute(['/user/view', 'id' => $model->user_id]));
                },

            ],
            [
                'attribute' => 'status',
                'value' => function (RequestedSite $model) {
                    return RequestedSite::STATUSES[$model->status] ?? null;
                },
                'filter' => RequestedSite::STATUSES
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'created_at',
                    'convertFormat' => true,
                    'startAttribute' => 'datetime_min',
                    'endAttribute' => 'datetime_max',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d'
                        ],
                    ],
                ]),
            ],
            [
                'attribute' => 'updated_at',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'updated_at',
                    'convertFormat' => true,
                    'startAttribute' => 'update_datetime_min',
                    'endAttribute' => 'update_datetime_max',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d'
                        ],
                    ],
                ]),

            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
