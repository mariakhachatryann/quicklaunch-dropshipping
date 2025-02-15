<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\rating\StarRating;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\ProductReview */
/* @var $form yii\widgets\ActiveForm */
/* @var $edit Boolean */
?>

<div class="product-review-form">

    <?php $form = ActiveForm::begin([
        'enableClientScript' => false,
    ]); ?>

    <?= $form->field($model, 'reviewer_name')->textInput(['maxlength' => true]) ?>


    <?php if(!isset($edit)): ?>
        <?= $form->field($model, 'rate')->input('number', ['min' => 1, 'max'=>5, 'step' => 0.5 ])?>
    <?php endif ?>

    <?= $form->field($model, 'review')->textarea(['rows' => 6]) ?>


    <?= $form->field($model, 'date')->widget(DatePicker::class, [
        'options' => ['class' => 'form-control']
    ])?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
