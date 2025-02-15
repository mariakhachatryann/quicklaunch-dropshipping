<?php

use common\models\AlertCaptcha;
use common\models\ImportQueue;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AlertCaptchaSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Alert Captchas';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alert-captcha-index">

    <h1><?= Html::encode($this->title) ?></h1>

<!--    --><?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'label' => 'Import Queue Id',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->importQueue->id;
                },
            ],
            [
                'label' => 'Site',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->importQueue && $model->importQueue->site) {
                        return Html::a($model->importQueue->site->name, ['import-queues/view', 'id' => $model->importQueue->id], ['target' => '_blank']);
                    }
                    return 'No Site Assigned';
                },
            ],
            [
                'label' => 'Import Queue Status',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->importQueue) {
                        return ImportQueue::STATUSES[$model->importQueue->status] ?? 'Unknown Status';
                    }
                    return 'No Queue Found';
                },
            ],
            [
                'label' => 'Handler',
                'value' => function ($model) {
                   return $model->importQueue->handler;
                },
            ],
            [
                'attribute' => 'status',
                'label' => 'Status',
                'format' => 'html',
                'value' => function ($model) use ($searchModel) {
                    return AlertCaptcha::STATUSES[$model->status] ?? 'Unknown';
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'status',
                    AlertCaptcha::STATUSES,
                    ['prompt' => 'Select Status', 'class' => 'form-control']
                ),
            ],
            [
                'attribute' => 'taken_at',
                'label' => 'Taken At',
                'value' => function ($model) {
                    return $model->taken_at;
                },
                'format' => ['datetime', 'php:Y-m-d H:i:s'],
                'filter' => \yii\helpers\Html::activeInput('text', $searchModel, 'taken_at', [
                    'class' => 'form-control datepicker',
                    'placeholder' => 'Select date',
                ]),
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Created At',
                'value' => function ($model) {
                    return $model->created_at;
                },
                'format' => ['datetime', 'php:Y-m-d H:i:s'],
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Updated At',
                'value' => function ($model) {
                    return $model->updated_at;
                },
                'format' => ['datetime', 'php:Y-m-d H:i:s'],
            ],
            'duration',
            [
                'attribute' => 'admin_id',
                'label' => 'Captcha Solver',
                'value' => function ($model) {
                    return $model->admin ? $model->admin->username : 'Not Assigned';
                },
                'filter' => Html::activeDropDownList(
                    $searchModel,
                    'admin_id',
                    ArrayHelper::map($captchaSolvers, 'id', 'username'),
                    ['prompt' => 'Select Admin', 'class' => 'form-control']
                ),

            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>



</div>
