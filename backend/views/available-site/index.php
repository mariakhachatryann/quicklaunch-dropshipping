<?php

use common\models\AvailableSite;
use yii\helpers\Html;
use backend\widgets\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AvailableSiteSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Available Sites';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="available-site-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Available Site', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            [
                'attribute' => 'logo',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::img('/uploads/logos/' . $model->logo, ['style' => ['max-height' => '100px', 'max-width' => '100px'], 'class' => 'img-responsive']);
                }
            ],
            'url:url',
            [
                'attribute' => 'created_at',
                'value'     => function ($model) {
                    return date('Y-m-d H:i:s', $model->created_at);
                }
            ],
            [
                'attribute' => 'updated_at',
                'value'     => function ($model) {
                    return date('Y-m-d H:i:s', $model->created_at);
                }
            ],
            'scrap_internal:boolean',
            'monitor_available:boolean',
            'import_by_queue:boolean',
            'import_by_extension:boolean',
            'is_new:boolean',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
