<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AvailableSite */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="available-site-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="<?= !$model->logo ? "hide" : "" ?>">
        <img src="/uploads/logos/<?= $model->logo ?>" style="width: 100px; height: 100px" alt="">
    </div>

    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'color')->textInput() ?>

    <?= $form->field($model, 'scrap_internal')->checkbox() ?>

    <?= $form->field($model, 'monitor_available')->checkbox() ?>

    <?= $form->field($model, 'import_by_queue')->checkbox() ?>

    <?= $form->field($model, 'import_by_extension')->checkbox() ?>

    <?= $form->field($model, 'is_new')->checkbox() ?>

    <?= $form->field($model, 'has_reviews')->checkbox() ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
