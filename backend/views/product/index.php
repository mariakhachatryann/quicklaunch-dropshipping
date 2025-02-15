<?php

use common\models\AvailableSite;
use common\models\Product;
use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\StringHelper;
use common\models\User;
use kartik\select2\Select2;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ProductSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Products';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-index">

    <h1><?= Html::encode($this->title) ?></h1>

	<?= GridView::widget([
		'summary' => false,
		'dataProvider' => Product::getProductsBySites($searchModel->user_id),
		'columns' => [
			[
				'attribute' => 'name',
				'value' => function ($data) {
					return $data['name'] ?: '-';
				}
			],
			'total',
			'today'
		]
	]) ?>
    <p>
        <?= Html::a('Create Product', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

	<div class="table-responsive">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'shopify_id',
            [
                'attribute' => 'title',
                'format' => 'raw',
                'value' => function($model) {
                    $title = StringHelper::truncateWords($model->title, 10);
                    return Html::a($title, $model->handleUrl, ['target' => '_blank']);
                },

            ],
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
                'value' => function(Product $model) {
                    return Html::a( $model->user->username, Url::toRoute(['/user/view', 'id' => $model->user_id]));
                },

            ],
            [
                'attribute' => 'src_product_url',
                'format' => 'raw',
                'value' => function($model) {
                    if(!$model->site) {
                        return '';
                    }

                    return Html::a(parse_url($model->src_product_url, PHP_URL_HOST), $model->src_product_url, ['target' => '_blank']);
                },

            ],
			[
				'attribute' => 'site_id',
				'filter' => Select2::widget([
					'model' => $searchModel,
					'attribute' => 'site_id',
					'data' => AvailableSite::getSitesDropdown(),
					'options' => ['placeholder' => 'Select Site'],
					'pluginOptions' => [
						'allowClear' => true
					],
				]),
				'value' => function (Product $product) {
                    if($product->site){
                        return $product->site->name;

                    }
					return '';
				}
			],
            'sku',
            'is_published:boolean',
            'is_deleted:boolean',
            'monitoring_stock:boolean',
            'monitoring_price:boolean',
            [
                'attribute' => 'imported_from',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'imported_from',
                    'data' => Product::IMPORTED_FROM_TYPES,
                    'options' => ['placeholder' => 'Select'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]),
                'value' => function (Product $product) {
                    return Product::IMPORTED_FROM_TYPES[$product->imported_from] ?? '';
                }
            ],
            //'product_data:ntext',
            [
                'attribute' => 'monitored_at',
                'format' => 'datetime',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'monitored_at',
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

            //'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
	</div>

</div>
