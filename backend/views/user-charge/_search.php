<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\UserChargeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-charge-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'charge_id') ?>

    <?= $form->field($model, 'user_id') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'api_client_id') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'billing_on') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'test') ?>

    <?php // echo $form->field($model, 'activated_on') ?>

    <?php // echo $form->field($model, 'canceled_on') ?>

    <?php // echo $form->field($model, 'trial_days') ?>

    <?php // echo $form->field($model, 'capped_amount') ?>

    <?php // echo $form->field($model, 'trial_ends_a_on') ?>

    <?php // echo $form->field($model, 'balance_used') ?>

    <?php // echo $form->field($model, 'balance_remaining') ?>

    <?php // echo $form->field($model, 'risk_level') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
