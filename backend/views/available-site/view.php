<?php

use common\models\AvailableSite;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\AvailableSite */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Available Sites', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="available-site-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
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
            'has_reviews:boolean',
        ],
    ]) ?>

</div>
