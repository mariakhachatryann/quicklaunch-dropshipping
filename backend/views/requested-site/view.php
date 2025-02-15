<?php

use common\models\RequestedSite;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\RequestedSite */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Requested Sites', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="requested-site-view">

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
            'url:url',
            [
                'attribute' => 'user_id',
                'value' => function (RequestedSite $model) {
                    return $model->user->username;
                }
            ],
            [
                'attribute' => 'status',
                'value' => function (RequestedSite $model) {
                    return RequestedSite::STATUSES[$model->status] ?? null;
                },
            ],
            [
                'attribute' => 'created_at',
                'value' => function (RequestedSite $model) {
                    return date('Y-m-d H:i:s', $model->created_at);
                }
            ],
            [
                'attribute' => 'updated_at',
                'value' => function (RequestedSite $model) {
                    return date('Y-m-d H:i:s', $model->updated_at);
                }
            ],
        ],
    ]) ?>

</div>
