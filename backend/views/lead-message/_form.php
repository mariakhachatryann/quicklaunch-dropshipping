<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use bajadev\ckeditor\CKEditor;

/* @var $this yii\web\View */
/* @var $model common\models\LeadMessage */
/* @var $leadMessage common\models\LeadMessage */
/* @var $form yii\widgets\ActiveForm */
/* @var $lead \common\models\Lead*/
?>

<div class="lead-message-form">



    <p><label class="control-label">Message</label></p>
    <div class="form-group lead_message">
        <?= $lead->message?>
    </div>
    <?php if (!empty($leadMessage)):?>
        <p><label class="control-label">Reply to message</label></p>
        <div class="form-group lead_message">
            <?= $leadMessage->message?>
        </div>
    <?php endif?>

    <?php if ($lead->image):?>
    <div class="form-group">
            <?= Html::img('/backend/web/uploads/lead_images/'.$lead->image,['style' => 'width:200px'])?>
    </div>
    <?php endif;?>

    <label class="control-label">User</label>
    <div class="form-group">
        <?= $lead->user->username?>
    </div>

    <?php $form = ActiveForm::begin(); ?>

    <?php if ($model->isNewRecord): ?>
        <div class="prepare_message">
            <?= $form->field($model, 'prepare_message')->textarea(['class' => 'form-control', 'rows' => 6]); ?>
            <button type="button" data-lead-id="<?= $lead->id ?>" data-message-id="<?= Yii::$app->request->get('message_id') ?>" class="btn btn-primary generate-message">Generate Message</button>
        </div>
    <?php endif; ?>


    <?= $form->field($model, 'message')->widget(CKEditor::class, [
        'editorOptions' => [
            'preset' => 'full',
            'inline' => false,
            'filebrowserBrowseUrl' => 'browse-images',
            'filebrowserUploadUrl' => 'upload-images',
            'extraPlugins' => 'imageuploader',
        ],
    ]); ?>

    <div class="form-group">
        <label for="">Textarea for images Copy/Past Drag/Drop</label>
        <div id="leadmessage-messageImage" contenteditable="true" style="border: 1px solid"></div>
    </div>

    <div id="imagePreview"></div>

    <?php if(!empty($file)): ?>
        <?= $form->field($file, 'imageFile')->fileInput()->label('Upload image') ?>
    <?php endif?>

    <div class="form-group">
        <?= Html::submitButton( 'Save', ['class' => 'btn btn-success leadSend']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <div class="leadImageZoom" style="display:none">
        <img class="leadImageZoomPreview" alt="">
    </div>

</div>
