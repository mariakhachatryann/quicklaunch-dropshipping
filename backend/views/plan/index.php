<?php

use kartik\daterange\DateRangePicker;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use backend\widgets\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PlanSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $planStatisticDate string */
/* @var $statistics array */
/* @var $statisticsForTable array */
/* @var $range array */

$this->title = 'Plans';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsVar('statistics', $statistics);
$this->registerJsVar('range', $range);

$this->registerJsFile('/vendor/chart.js/Chart.bundle.min.js');
$this->registerJsFile(Yii::getAlias('@web/js/plan-statistics.js'), ['depends' => \yii\web\JqueryAsset::class]);
?>
<div class="plan-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Plan', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'description',
            'price',
            'trial_days',
            [
                'attribute' => 'created_at',
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

	<div class="row">
		<div class="col-md-12">
			<h3>Plan Statistics</h3>
			<form id="statistics_range">
			<?= DateRangePicker::widget([
				'name' => 'planStatisticDate',
				'value' => $planStatisticDate,
				'convertFormat' => true,
				'pluginOptions' => [
					'locale' => [
						'format' => 'Y-m-d'
					],
				],
			]) ?>
			</form>
			<div class="row">
				<div class="col-md-8">
					<?= GridView::widget([
						'dataProvider' => new ArrayDataProvider([
							'allModels' => $statisticsForTable,
						]),
						'summary' => '',
					]) ?>
				</div>
			</div>
			<canvas id="lineChart_3" height="150"></canvas>
		</div>
	</div>

</div>
