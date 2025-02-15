<?php

use yii\helpers\Html;
use backend\widgets\GridView;
use common\models\EmailTemplate;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\EmailTemplateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Email Templates';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="email-template-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Email Template', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'key',
            'subject',
            [
                'attribute' => 'status',
                'value' => function(EmailTemplate $model) {
                    return $model->status ? 'Active' : 'Inactive';

                }
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'php:Y-m-d H:i:s']
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
