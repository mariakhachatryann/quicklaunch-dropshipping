<?php

use common\models\AlertCaptcha;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $solver backend\models\Admin */
/* @var $dailyAverage float */
/* @var $monthlyAverage float */
/* @var $dailyChartData array */
/* @var $monthlyChartData array */


$this->title = 'View Solver: ' . Html::encode($solver->username);
$this->params['breadcrumbs'][] = ['label' => 'Captcha Solvers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile('https://code.highcharts.com/highcharts.js', [
    'position' => View::POS_HEAD,
]) ?>
<div class="solver-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <h4><strong>Daily Average <?= Html::encode($dailyAverage) ?> seconds</strong></h4>
    <h4><strong>Monthly Average <?= Html::encode($monthlyAverage) ?> seconds</strong></h4>

    <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-primary']) ?>

</div>

<div style="margin-top: 50px">
    <button id="show-daily" class="btn btn-info">Show Daily</button>
    <button id="show-monthly" class="btn btn-info">Show Monthly</button>
</div>

<div id="container" style="width:100%; height:400px; margin-top: 20px;"></div>

<?php
$dailyDataJson = json_encode($dailyChartData);
$monthlyDataJson = json_encode($monthlyChartData);
$this->registerJs("
    const dailyData = {$dailyDataJson};
    const monthlyData = {$monthlyDataJson};

    function renderChart(data, title) {
        Highcharts.chart('container', {
            chart: {
                type: 'column'
            },
            title: {
                text: title,
                align: 'left'
            },
            xAxis: {
                categories: data.labels,
                crosshair: true
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Average Duration (seconds)'
                }
            },
            tooltip: {
                valueSuffix: ' seconds'
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [{
                name: 'Duration',
                data: data.data
            }]
        });
    }

    renderChart(dailyData, 'Average Solving Duration (Daily)');

    document.getElementById('show-daily').addEventListener('click', function () {
        renderChart(dailyData, 'Average Solving Duration (Daily)');
    });

    document.getElementById('show-monthly').addEventListener('click', function () {
        renderChart(monthlyData, 'Average Solving Duration (Monthly)');
    });
");

?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'id',
        [
            'attribute' => 'status',
            'label' => 'Status',
            'format' => 'html',
            'value' => function ($model) {
                return AlertCaptcha::STATUSES[$model->status] ?? 'Unknown';
            },
            'filter' => Html::activeDropDownList(
                $searchModel,
                'status',
                AlertCaptcha::STATUSES,
                ['prompt' => 'All', 'class' => 'form-control']
            ),
        ],
        'created_at:datetime',
        'taken_at:datetime',
        'updated_at:datetime',
        'duration'
    ],
]); ?>

