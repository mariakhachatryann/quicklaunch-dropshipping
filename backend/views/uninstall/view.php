<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Uninstall */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Uninstalls', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="uninstall-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
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
                'label' => 'User',
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a($model->user->username, Url::toRoute(['/user/view', 'id' => $model->user_id]),['target' => '_blank']);
                },
            ],
            [
                'attribute' => 'plan_id',
                'value' => function ($model) {
                    return $model->plan->name ?? null;
                }
            ],
            [
                'label' => 'Duration (in hours)',
                'attribute' => 'duration',
                'value' => $model->duration
            ],
            'uninstalled_at',
        ],
    ]) ?>

</div>
