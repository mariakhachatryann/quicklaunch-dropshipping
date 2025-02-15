<?php

use yii\data\ActiveDataProvider;
use backend\widgets\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use common\helpers\CountryHelper;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">

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

        <?php if ($model->status != \common\models\User::STATUS_DELETED): ?>
            <?= Html::a('Inactive', ['inactive', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to inactive this item?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
        <?= Html::a('Login', Url::to(['/site/login-user','userId' => $model->id]), ['target' => '_blank', 'class' => 'btn btn-danger']); ?>
        <?= Html::a('Activate Basic Plan', Url::to(['/user/set-basic-plan', 'id' => $model->id]), ['class' => 'btn btn-warning']); ?>
    </p>

   <?= $this->render('_userDetailView', compact('model')) ?>

    <pre>
<!--        --><?php
//        try {
//            print_r($model->getShopifyApi()->getRecurringApplicationChargeManager()->findAll());
//        } catch (\Exception $exception) {
//
//        }
//
//        try {
//            print_r($model->getShopifyApi()->getWebhookManager()->findAll());
//        } catch (\Exception $exception) {
//
//        }
        
//        ?>
    </pre>

    <?= GridView::widget([
        'dataProvider' => new ActiveDataProvider(['query' => $model->getUserCharges(),]),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'charge_id',
            'name',
            'api_client_id',
            'price',
            'status',
            [
                'attribute' => 'billing_on',
                'value' => 'billing_on',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'created_at',
                'value' => 'created_at',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'updated_at',
                'value' => 'updated_at',
                'format' => 'datetime',
            ],
            'test',
            [
                'attribute' => 'activated_on',
                'value' => 'activated_on',
                'format' => 'datetime',
            ],
            [
                'attribute' => 'canceled_on',
                'value' => 'canceled_on',
                'format' => 'datetime',
            ],
            'trial_days',
            'capped_amount',
            [
                'attribute' => 'trial_ends_a_on',
                'value' => 'trial_ends_a_on',
                'format' => 'datetime',
            ],
            'balance_used',
            'balance_remaining',
            'risk_level',
        ],
    ]); ?>

</div>
