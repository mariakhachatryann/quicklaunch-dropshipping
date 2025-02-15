<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PlanStatistic */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="plan-statistic-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'plan_id')->textInput() ?>

    <?= $form->field($model, 'total')->textInput() ?>

    <?= $form->field($model, 'date')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
