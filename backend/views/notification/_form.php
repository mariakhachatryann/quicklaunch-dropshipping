<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Notification;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model \common\models\Notification */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="plan-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'subject')->textInput() ?>

    <?= $form->field($model, 'text')->textarea() ?>

    <?= $form->field($model, 'notification_type')->dropDownList(Notification::$notificationTypes) ?>

    <?= $form->field($model, 'url') ?>

    <?= $form->field($model, 'user_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
