<?php

/* @var common\models\LeadMessage $message*/
/* @var common\models\Lead $lead*/
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = 'Ticket';
?>
<div class="card">
    <div class="card-body">
        <h4 class="card-title chat cardTitle" data-id="<?=$lead->id?>"> Lead subject - "<?= $lead->subject_id ? $lead->subject->title : 'Other'; ?>"</h4>
<!--        <div class="border-bottom my-2"></div>-->

        <div class="widget-sta">
<!--            <div class="">-->
                <small class="text-muted"><?= date('d M Y, H:i', $lead->created_at) ?></small>
               <p><?= $lead->message ?></p>
                <?= Html::img('/backend/web/uploads/lead_images/' . $lead->image, ['class' => 'attachment-img leadImageSmall', 'style' => 'width:100px']) ?>
<!--            </div>-->
        </div>
<!--        --><?php //if (!empty($lead->leadMessages)) : ?>
            <div id="DZ_W_TimeLine" class="widget-timeline dz-scroll ps ps--active-y">
                <ul class="timeline">
                    <div class="border-bottom my-2"></div>

                    <?php foreach ($lead->leadMessages as $message) : ?>

                        <li>
                            <div class="timeline-badge <?= !$message->sender ? 'primary' : 'info' ?>"></div>
                            <div class="timeline-panel text-muted">
                                <h6><?= $message->sender ? $lead->user->username : 'ShionImporter'?></h6>
                                <p><?= date('d M Y, H:i', $message->created_at) ?></p>
                                <?= $message->message ?>
                                <?= $message->image ? Html::tag('p', Html::img('/backend/web/uploads/lead_message_images/' . $message->image, ['class' => 'attachment-img leadImageSmall', 'style' => 'width:100px'])) : '' ?>
                                <?php if($message->images): ?>
                                    <?php foreach ($message->images as $img): ?>
                                        <?= $message->image ? Html::tag('p', Html::img('/backend/web/uploads/lead_message_images/' . $img->name, ['class' => 'attachment-img leadImageSmall', 'style' => 'width:100px'])) : '' ?>
                                    <?php endforeach;?>
                                <?php endif?>
                            </div>
                        </li>
                        <div class="border-bottom my-2"></div>
                    <?php endforeach; ?>
                </ul>
            </div>
<!--        --><?php //endif; ?>
        <div class="leadImageZoom" style="display:none">
            <img class="leadImageZoomPreview" alt="">
        </div>

<!--        <div class="card text-bg-secondary text-white">-->
            <?php if ($lead->status == \common\models\Lead::CLOSED) : ?>
                <p class="text-danger">Ticket is closed</p>
            <?php else: ?>
                <div class="basic-form">
                    <?php $form = ActiveForm::begin(['method' => 'post']); ; ?>
                    <div class="form-group">
                        <div class="form-group">
                            <label for="editorLead">Message</label>
                            <textarea class="form-control" id="editorLead" contenteditable="true" name="LeadMessage[message]"></textarea>
                        </div>

                        <div class="input-group mb-3">
<!--                            <div class="input-group-prepend">-->
<!--                                <span class="input-group-text">Upload</span>-->
<!--                            </div>-->
                            <div class="custom-file">
                                <?= $form->field($file, 'imageFile')->fileInput(['class' => 'custom-file-input'])->label('Choose file', ['class' => 'custom-file-label']) ?>
                            </div>
                        </div>
                    </div>
                    <div id="imagePreview"></div>
                    <div class="form-group pull-right">
                        <?= Html::submitButton('Send', ['class' => 'btn btn-primary btn-sm mb-2 leadSend']) ?>
                        <?= Html::a('Close Ticket', ['site/close-chat', 'lead_id' => $lead->id], ['class' => 'btn btn-danger btn-sm mb-2']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>

            <?php endif; ?>
<!--        </div>-->
    </div>
</div>

