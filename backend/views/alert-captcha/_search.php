<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\AlertCaptchaSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="alert-captcha-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'import_queue_id') ?>

    <?= $form->field($model, 'handler') ?>

    <?= $form->field($model, 'status') ?>

    <?= $form->field($model, 'created_at') ?>

    <?php  echo $form->field($model, 'updated_at') ?>

    <?php  echo $form->field($model, 'admin_id') ?>

    <?php  echo $form->field($model, 'taken_at') ?>

    <?php  echo $form->field($model, 'duration') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
