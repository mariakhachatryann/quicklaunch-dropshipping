<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AvailableSite */
/* @var $form yii\widgets\ActiveForm */
/* @var $user_id integer */
/* @var $plans array */
/* @var $user_id integer */
?>

<div class="available-site-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'user_id')->hiddenInput(['value' => $user_id])->label('') ?>

    <?= $form->field($model, 'plan_id')->dropDownList($plans) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
