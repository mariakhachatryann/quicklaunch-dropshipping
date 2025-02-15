<?php

use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\ArrayHelper;
use common\models\Plan;
use common\models\User;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

	<?= GridView::widget([
		'summary' => false,
		'dataProvider' => User::getUsersWithPlans(),
		'columns' => [
			[
				'attribute' => 'name',
				'value' => function ($data) {
					return $data['name'] ?: 'Uninstalled';
				}
			],
			'total',
			'today'
		]
	]) ?>

    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

	<div class="table-responsive">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'attribute' => 'username',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a($model->username, $model->shopUrl,['target' => '_blank']);
                },

            ],

            'email:email',

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
            //'verification_token',
            [
                'attribute' => 'plan_id',
                'value' => function($model) {
                    return ArrayHelper::getValue($model, 'plan.name');
                },
                'filter'=>ArrayHelper::map(Plan::find()->asArray()->all(), 'id', 'name'),
            ],
            [
                'attribute' => 'plan_status',
                'value' => function($model) {
                    return $model->planStatusName();
                },
                 'filter'=>User::$planStatuses,
            ],
            [
                'attribute' => 'country_code',
                'label' => 'Country',
                'value' => function($model) {
                    return \common\helpers\CountryHelper::getCountry($model->country_code);
                },
                'filter' => \common\helpers\CountryHelper::getCountries()
            ],
            [
                'attribute' => 'promo_code_id',
                'label' => 'Promo',
                'value' => function($model) {
                    $promo = ArrayHelper::getValue($model, 'promoCode.code')."(-".ArrayHelper::getValue($model, 'promoCode.price').")";
                    return $promo;
                },
            ],
            [
                    'attribute' => 'fail_count',
                    'label' => 'Fails',
                    'value' => 'fail_count',
            ],

            [
                'header' => 'User products',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::tag('p', Html::a($model->getProducts()->count().' products', Url::to(['/product','ProductSearch[user_id]' => $model->id]) ), ['class' =>'text-center']);
                },
                'filter' => false,
            ],
			'has_left_review:boolean',
			[
				'attribute' => 'left_review_at',
				'format' => 'datetime',
				'filter' => DateRangePicker::widget([
					'model' => $searchModel,
					'attribute' => 'left_review_at',
					'convertFormat' => true,
					'startAttribute' => 'review_datetime_min',
					'endAttribute' => 'review_datetime_max',
					'pluginOptions' => [
						'locale' => [
							'format' => 'Y-m-d'
						],
					],
				]),
			],
			'is_manual_plan:boolean',
			[
                'header' => 'Login',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::tag('p', Html::a('login', Url::to(['/site/login-user','userId' => $model->id]),['target'=>'_blank'] ), ['class' =>'text-center']);
                },
                'filter' => false,
            ],

            ['class' => 'yii\grid\ActionColumn'],
            [
                'header' => 'Credit',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::tag('p', Html::a('Add Credit', Url::to(['/credit/create','userId' => $model->id]),['target'=>'_blank', 'class' => 'btn btn-success'] ), ['class' =>'text-center']);
                },
                'filter' => false,
            ],

        ],
    ]); ?>
	</div>


</div>
