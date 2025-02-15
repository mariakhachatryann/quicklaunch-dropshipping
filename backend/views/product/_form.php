<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'user_id')->dropDownList(User::getUsers()) ?>

    <?= $form->field($model, 'src_product_url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'shopify_id') ?>

    <?= $form->field($model, 'is_published')->dropDownList(['No', 'Yes']) ?>

    <?= $form->field($model, 'monitoring_stock')->dropDownList(['No', 'Yes']) ?>

    <?= $form->field($model, 'monitoring_price')->dropDownList(['No', 'Yes']) ?>
    <?= $form->field($model, 'site_id')->dropDownList(\common\models\AvailableSite::getSitesDropdown()) ?>


<!--    $form->field($model, 'created_at')->textInput()

    $form->field($model, 'updated_at')->textInput()-->

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
