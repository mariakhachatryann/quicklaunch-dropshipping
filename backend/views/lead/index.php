<?php

use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use backend\widgets\GridView;
use common\models\Lead;
use common\models\Subject;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\LeadSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Leads';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lead-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            [
                'label' => 'Subject',
                'attribute' => 'subject_id',
                'filter' => Subject::getAllSubjects(),
                'value' => function($model) {
                    return $model->subject_id ? $model->subject->title : 'Other';
                },

            ],
            [
                'label' => 'Message',
                'attribute' => 'message',
                'value' => function($model) {
                    return \yii\helpers\StringHelper::truncateWords($model->message, 15);
                },
            ],
			[
				'label' => 'Additional Data',
				'attribute' => 'additional_data',
				'format' => 'raw',
				'value' => function (Lead $model) {
					return Html::a('Link', $model->additional_data, ['target' => '_blank']);
				}
			],
            [
                'label' => 'User',
                'attribute' => 'username',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a($model->user->username, Url::toRoute(['/user/view', 'id' => $model->user_id]),['target' => '_blank']);
                },
            ],
            [
                'attribute' => 'status',
                'filter' => Lead::LEAD_STATUSES,
                'format' => 'raw',
                'contentOptions' => ['class' => 'product-status'],
                'label' => 'Status',
                'value' => function ($model) {
                    $color = !$model->status ? 'label-danger' : 'label-success';
                    return "<span class='label $color'>".Lead::LEAD_STATUSES[$model->status]."</span>";

                }
            ],
            [
                'attribute' => 'answered',
                'format' => 'raw',
                'value' => function (Lead $model) {

                    $lastSender = $model->getlastMessageSender();
                    $isAnswered = $lastSender === 0;
                    $color = $isAnswered || $model->status == Lead::CLOSED ? 'label-success' : 'label-danger';
                    return "<span class='label $color'>". ($isAnswered ? 'Yes' : 'No')."</span>";
                }


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
            //'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
