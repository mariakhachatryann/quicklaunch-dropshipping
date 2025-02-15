<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AlertCaptcha */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="alert-captcha-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'import_queue_id')->textInput() ?>

    <?= $form->field($model, 'handler')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <?= $form->field($model, 'admin_id')->textInput() ?>

    <?= $form->field($model, 'taken_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
