<?php

use common\models\AvailableSite;
use common\models\ImportQueue;
use common\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ImportQueue */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="import-queues-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'site_id')->dropDownList(AvailableSite::getSitesDropdown(), [
        'prompt' => 'Select'
    ])->label('Site')
    ?>

    <?= $form->field($model, 'user_id')->dropDownList(User::getUsers()) ?>

    <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList(ImportQueue::STATUSES, [
        'prompt' => 'Select'
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
