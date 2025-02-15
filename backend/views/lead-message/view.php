<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\LeadMessage */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Lead Messages', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="lead-message-view">

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
            [
                'attribute' => 'lead_id',
                'value'     => function ($model) {
                    return  $model->lead ? $model->lead->message : '';
                }
            ],
            [
                'attribute' => 'user_id',
                'value'     => function ($model) {
                    return $model->user->username;
                }
            ],
            'message:ntext',
            [
                'attribute' => 'image',
                'format' => 'raw',
                'value'     => function ($model) {
                    return Html::img($model->imageUrl, ['style' => 'width:150px', 'class' => 'leadImageSmall']);
                }
            ],
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
        ],
    ]) ?>


    <?php foreach ($model->images as $image) : ?>
        <img src="<?= $image->imageUrl?>" alt="" class="leadImageSmall">
    <?php endforeach;?>

    <div class="leadImageZoom" style="display:none">
        <img class="leadImageZoomPreview" alt="">
    </div>


</div>
