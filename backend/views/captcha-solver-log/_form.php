<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\CaptchaSolverLog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="captcha-solver-log-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'admin_id')->textInput() ?>

    <?= $form->field($model, 'activated_at')->textInput() ?>

    <?= $form->field($model, 'deactivated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
