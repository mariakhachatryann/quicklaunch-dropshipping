<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Select Niche';
?>
<div class="card card-body">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'niche_id')->dropDownList(
            ArrayHelper::map($niches, 'id', 'name'),
            ['prompt' => 'Select Niche', 'class' => 'form-select']
        )->label('Niche') ?>

        <div class="form-group">
            <?= Html::submitButton('Select Niche', ['class' => 'btn btn-primary']) ?>
        </div>

    <?php ActiveForm::end(); ?>
</div>
