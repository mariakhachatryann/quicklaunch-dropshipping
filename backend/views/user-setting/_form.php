<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\UserSetting */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-setting-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'price_markup')->textInput() ?>

    <?= $form->field($model, 'price_amount')->textInput() ?>

    <?= $form->field($model, 'price_percentage')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
