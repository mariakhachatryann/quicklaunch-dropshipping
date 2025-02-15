<?php

use common\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Plan;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput() ?>

    <?= $form->field($model, 'email')->textInput() ?>

    <?= $form->field($model, 'password')->textInput() ?>

    <?= $form->field($model, 'plan_id')->dropDownList(Plan::getPlans())?>

    <?= $form->field($model, 'plan_status')->dropDownList(User::$planStatuses) ?>

    <?= $form->field($model, 'custom_plan_visible')->checkbox() ?>

    <?= $form->field($model, 'has_left_review')->checkbox() ?>

	<?= $form->field($model, 'left_review_at')->input('date') ?>

	<?= $form->field($model, 'is_manual_plan')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
