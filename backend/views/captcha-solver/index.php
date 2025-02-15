<?php

use backend\models\Admin;
use kartik\daterange\DateRangePicker;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel common\models\CaptchaSearch */
/* @var $captchaSolvers backend\models\Admin[] */
/* @var $solverDuration array */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Captcha Solvers';
$this->params['breadcrumbs'][] = $this->title;
?>

<h1><?= Html::encode($this->title) ?></h1>
<div class="col-md-4">

<?php $form = ActiveForm::begin(['method' => 'get']); ?>
<?= $form->field($searchModel, 'date_range')->widget(DateRangePicker::class, [
    'model' => $searchModel,
    'attribute' => 'date_range',
    'pluginOptions' => [
        'format' => 'yyyy-mm-dd',
        'opens' => 'left',
        'locale' => [
            'format' => 'YYYY-MM-DD',
            'separator' => ' to ',
        ],
    ]
]); ?>
<?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>

<?php ActiveForm::end(); ?>
</div>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'username',
        'email',
        [
            'label' => 'Average Duration',
            'value' => function ($model) use ($searchModel) {
                return $model->getAverageDuration($searchModel) ?? 'N/A';
            },
        ],
        [
            'label' => 'Last Taken Time',
            'value' => function ($model) {
                return $model->lastAlertCaptcha
                    ? Yii::$app->formatter->asDatetime($model->lastAlertCaptcha->taken_at)
                    : 'N/A';
            },
        ],
        [
            'label' => 'Status',
            'value' => function ($model) {
                return $model->is_online
                    ? Html::tag('span', 'Online', ['class' => 'text-success'])
                    : Html::tag('span', 'Offline', ['class' => 'text-danger']);
            },
            'format' => 'raw',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{view}',
            'buttons' => [
                'view' => function ($url, $model) {
                    return Html::a('View', ['captcha-solver/view', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']);
                },
            ],
        ],
    ],
]); ?>

