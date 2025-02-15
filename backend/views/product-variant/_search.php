<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ProductVariantSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-variant-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'product_id') ?>

    <?= $form->field($model, 'img') ?>

    <?= $form->field($model, 'option1') ?>

    <?= $form->field($model, 'option2') ?>

    <?php // echo $form->field($model, 'option3') ?>

    <?php // echo $form->field($model, 'sku') ?>

    <?php // echo $form->field($model, 'default_sku') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'compare_at_price') ?>

    <?php // echo $form->field($model, 'inventory_quantity') ?>

    <?php // echo $form->field($model, 'inventory_item_id') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
