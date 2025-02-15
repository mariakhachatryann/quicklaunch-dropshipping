<?php
/* @var $subjects \common\models\Subject[] */
/* @var $model Lead */
/* @var $this \yii\web\View */

use common\models\Lead;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\JqueryAsset;

$this->title = 'Contact us';
$this->registerJsFile(Yii::getAlias('@web/js/lead.js'), ['depends' => JqueryAsset::class]);

?>
<div class="card card-body col-12">
    <?php $form = ActiveForm::begin(['method' => 'post', 'options' => ['enctype' => 'multipart/form-data']]) ?>
        <div class="row">
            <div class="form-floating">
                <?= $form->field($model, 'subject_id')->dropDownList($subjects, ['class' => 'form-select leadSubject'])->label('Subject') ?>
            </div>
        </div>
    <div>
        <?= $form->field($model, 'additional_data')->textInput() ?>
    </div>
    <div class="form-group">
        <label for="">Message</label>
        <textarea id="editorLead" class="form-control" rows="5" name="Lead[message]"></textarea>
    </div>
    <div id="editorLead" contenteditable="true" name="Lead[message]"></div>
    <div class="form-group">
        <div class="input-group mb-3">
            <div class="custom-file">
                <?= $form->field($file, 'imageFile')->fileInput(['class' => 'custom-file-input'])->label('Choose file', ['class' => 'custom-file-label']) ?>
            </div>
        </div>
    </div>
    <div id="imagePreview"></div>
    <div class="leadImageZoom" style="display:none">
        <img class="leadImageZoomPreview" alt="">
    </div>
        <div class="form-group pull-right">
            <?= Html::submitButton('Send', ['class' => 'btn btn-primary mb-2 leadSend']) ?>
        </div>
        <?php ActiveForm::end(); ?>
</div>
