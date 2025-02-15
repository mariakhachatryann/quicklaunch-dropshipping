<?php

use common\helpers\CountryHelper;
use common\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/** @var User $model */

?>
<?= DetailView::widget([
	'model' => $model,
	'attributes' => [
		'id',
		[
			'attribute' => 'username',
			'format' => 'raw',
			'value' => Html::a($model->username, Url::to(['/site/login-user', 'userId' => $model->id]), ['target' => '_blank', 'class' => 'btn btn-danger'])

		],
		'has_left_review:boolean',
		'left_review_at:date',
		'is_manual_plan:boolean',
		'email:email',
		[
			'attribute' => 'status',
			'value' => function (User $model) {
				return $model->getStatus();
			}
		],
		[
			'attribute' => 'plan_id',
			'value' => function (User $model) {
				return $model->plan->name ?? null;
			}
		],
		[
			'attribute' => 'plan_status',
			'value' => function (User $model) {
				return $model->planStatusName();
			}
		],
		[
			'attribute' => 'country_code',
			'label' => 'Country',
			'filter' => CountryHelper::getCountries(),
			'value' => function (User $model) {
				return CountryHelper::getCountry($model->country_code);
			},
		],
		[
			'attribute' => 'promo_code_id',
			'label' => 'Promo',
			'value' => function (User $model) {
				return ArrayHelper::getValue($model, 'promoCode.code') . "(-" . ArrayHelper::getValue($model, 'promoCode.price') . ")";
			},
		],
		[
			'attribute' => 'fail_count',
			'label' => 'Fails',
			'value' => $model->fail_count,
		],

		[
			'attribute' => 'username',
			'label' => 'User products',
			'format' => 'raw',
			'value' => function (User $model) {
				return Html::a($model->getProducts()->count() . ' products', Url::to(['/product', 'ProductSearch[user_id]' => $model->id]));
			},
		],
		[
			'attribute' => 'shopify_details',
			'format' => 'raw',
			'value' => function (User $model) {
				return '<pre>' . print_r(json_decode($model->shopify_details, true), true) . '</pre>';
			}
		],
		'created_at:datetime',
		'updated_at:datetime',
	],
]) ?>

