<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UserCharge */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-charge-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'charge_id')->textInput() ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'api_client_id')->textInput() ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'billing_on')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'test')->textInput() ?>

    <?= $form->field($model, 'activated_on')->textInput() ?>

    <?= $form->field($model, 'canceled_on')->textInput() ?>

    <?= $form->field($model, 'trial_days')->textInput() ?>

    <?= $form->field($model, 'capped_amount')->textInput() ?>

    <?= $form->field($model, 'trial_ends_a_on')->textInput() ?>

    <?= $form->field($model, 'balance_used')->textInput() ?>

    <?= $form->field($model, 'balance_remaining')->textInput() ?>

    <?= $form->field($model, 'risk_level')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
