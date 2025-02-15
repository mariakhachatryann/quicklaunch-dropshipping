<?php

use backend\models\Admin;
use backend\models\AdminSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use backend\widgets\GridView;
use yii\web\View;

/* @var $this View */
/* @var $searchModel AdminSearch */
/* @var $dataProvider ActiveDataProvider */

$this->title = 'Admins';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="admin-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Html::a('Create New Admin', ['create'], ['class' => 'btn btn-success']) ?></p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'username',
            'email:email',
			[
				'attribute' => 'status',
				'filter' => Admin::STATUSES,
				'value' => function (Admin $model) {
					return Admin::STATUSES[$model->status] ?? $model->status;
				}
			],
			[
				'attribute' => 'role_type',
				'filter' => Admin::ROLES,
				'value' => function (Admin $model) {
					return Admin::ROLES[$model->role_type] ?? $model->role_type;
				}
			],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
