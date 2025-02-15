<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\UserCharge */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'User Charges', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-charge-view">

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
            'charge_id',
            [
                'label' => 'User',
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a($model->user->username, Url::toRoute(['/user/view', 'id' => $model->user_id]),['target' => '_blank']);
                },
            ],
            'name',
            'api_client_id',
            'price',
            'status',
            'billing_on',
            'created_at',
            'updated_at',
            'test',
            'activated_on',
            'canceled_on',
            'trial_days',
            'capped_amount',
            'trial_ends_a_on',
            'balance_used',
            'balance_remaining',
            'risk_level',
        ],
    ]) ?>

</div>
