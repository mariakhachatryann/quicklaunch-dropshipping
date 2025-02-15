<?php

use backend\models\Admin;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Admin */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="admin-form">

    <?php $form = ActiveForm::begin(); ?>

	<?= $form->field($model, 'username')->textInput() ?>

	<?= $form->field($model, 'email')->textInput() ?>

	<?= $form->field($model, 'password')->passwordInput() ?>

	<?= $form->field($model, 'role_type')->dropDownList(Admin::ROLES) ?>

	<?= $form->field($model, 'status')->dropDownList(Admin::STATUSES) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
