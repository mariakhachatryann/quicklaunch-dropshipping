<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ProductVariant */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-variant-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'product_id')->textInput() ?>

    <?= $form->field($model, 'shopify_variant_id')->textInput() ?>

    <?= $form->field($model, 'img')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'option1')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'option2')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'option3')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'default_sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'price')->textInput() ?>

    <?= $form->field($model, 'compare_at_price')->textInput() ?>

    <?= $form->field($model, 'inventory_quantity')->textInput() ?>

    <?= $form->field($model, 'inventory_item_id')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
