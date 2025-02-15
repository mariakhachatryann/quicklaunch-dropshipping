<?php

use common\models\Plan;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PromoCode */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="promo-code-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'userName')->textInput() ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'plan_id')->dropDownList(Plan::getPlans())  ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'active_until')->input('date') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
