<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserChargeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'User Charges';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-charge-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'charge_id',
            [
                'label' => 'User',
                'attribute' => 'username',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a($model->user->username, Url::toRoute(['/user/view', 'id' => $model->user_id]));
                },
            ],
            'name',
            'api_client_id',
            'price',
            'status',
            [
                'attribute' => 'billing_on',
                'value' => 'billing_on',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'convertFormat' => true,
                    'attribute' => 'billingOnRange',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d H:i:s'
                        ],

                    ],
                ])
            ],
            [
                'attribute' => 'created_at',
                'value' => 'created_at',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'convertFormat' => true,
                    'attribute' => 'createdAtRange',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d H:i:s'
                        ],

                    ],
                ])
            ],
            [
                'attribute' => 'updated_at',
                'value' => 'updated_at',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'convertFormat' => true,
                    'attribute' => 'updatedAtRange',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d H:i:s'
                        ],

                    ],
                ])
            ],
            'test',
            [
                'attribute' => 'activated_on',
                'value' => 'activated_on',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'convertFormat' => true,
                    'attribute' => 'activatedOnRange',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d H:i:s'
                        ],

                    ],
                ])
            ],
            [
                'attribute' => 'canceled_on',
                'value' => 'canceled_on',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'convertFormat' => true,
                    'attribute' => 'canceledOnRange',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d H:i:s'
                        ],

                    ],
                ])
            ],
            'trial_days',
            'capped_amount',
            [
                'attribute' => 'trial_ends_a_on',
                'value' => 'trial_ends_a_on',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'convertFormat' => true,
                    'attribute' => 'trialEndsAOnRange',
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d H:i:s'
                        ],

                    ],
                ])
            ],
            'balance_used',
            'balance_remaining',
            'risk_level',

            [
                'class' => 'yii\grid\ActionColumn',
                'visibleButtons' => ['delete' => true, 'update' => false, 'view' => true],
            ],
        ],
    ]); ?>


</div>
