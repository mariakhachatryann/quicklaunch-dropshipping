<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use bajadev\ckeditor\CKEditor;
use common\models\EmailTemplate;

/* @var $this yii\web\View */
/* @var $model common\models\EmailTemplate */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="email-template-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'key')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subject')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'content')->widget(CKEditor::class, [
        'editorOptions' => [
            'preset' => 'full',
            'inline' => false,
            'filebrowserBrowseUrl' => 'browse-images',
            'filebrowserUploadUrl' => 'upload-images',
            'extraPlugins' => 'imageuploader',
        ],
    ]); ?>

    <?= $form->field($model, 'status')->dropDownList([EmailTemplate::STATUS_INACTIVE => 'Inactive', EmailTemplate::STATUS_ACTIVE => 'Active']) ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
