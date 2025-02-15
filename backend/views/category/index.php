<?php

use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\Url;
use richardfan\sortable\SortableGridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Categories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="category-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Category', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <?= SortableGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,

        // SortableGridView Configurations
        'sortUrl' => Url::to(['sortItem']),
        'sortingPromptText' => 'Loading...',

        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name:ntext',
            [
                'attribute' => 'niche_id',
                'value' => function ($model) {
                    return $model->niche->name;
                },
                'label' => 'Niche Name'
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>



</div>
