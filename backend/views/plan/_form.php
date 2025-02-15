<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Plan */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="plan-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'price')->input('number', ['step' => 'any']) ?>
    
    <?= $form->field($model, 'old_price')->input('number', ['step' => 'any']) ?>
    

    <?= $form->field($model, 'trial_days')->input('number') ?>

    <?= $form->field($model, 'featureIds')->checkboxList($features)->label('Available features') ?>

    <?= $form->field($model, 'siteIds')->checkboxList($sites)->label('Available sites') ?>

    <?= $form->field($model, 'product_limit')->input('number', ['step' => 'any']) ?>

    <?= $form->field($model, 'monitoring_limit')->input('number') ?>

    <?= $form->field($model, 'review_limit')->input('number') ?>

    <?= $form->field($model, 'is_custom')->checkbox() ?>

    <?= $form->field($model, 'color')->input('color') ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
