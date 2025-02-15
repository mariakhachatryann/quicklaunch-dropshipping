<?php
use yii\helpers\Url;

?>
<div class="chatbox">
    <div class="chatbox-close"></div>
    <div class="custom-tab-1">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#tickets">Tickets</a>
            </li>
        </ul>
        <div class="tab-content">
                <div class="tab-pane fade active show" id="tickets" role="tabpanel">
                <div class="card mb-sm-3 mb-md-0 contacts_card">
                    <div class="card-body contacts_body p-0 dz-scroll" id="DZ_W_Contacts_Body1">
                        <ul class="contacts">
                            <li class="name-first-letter">Opened Tickets</li>
                            <?php foreach ($tickets as $ticket) : ?>
                            <li>
                                <a href="<?= Url::toRoute(['site/chat', 'lead_id' => $ticket->id]) ?>">
                                    <div class="d-flex bd-highlight">
                                        <div class="img_cont primary"><?= mb_substr($ticket->subject_id ? $ticket->subject->title : 'Other', 0, 1, "UTF-8")?><i class="icon fa fa-user-plus"></i></div>
                                        <div class="user_info">
                                            <span><?= $ticket->subject_id ? $ticket->subject->title : 'Other'; ?></span>
                                            <p class="text-primary"><?= date('d M, Y H:i', $ticket->created_at) ?> </p>
                                            <p><?=  $ticket->message ?> </p>
                                        </div>
                                    </div>
                                </a>

                            </li>
                        <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="card-footer"></div>
                </div>
            </div>

        </div>
    </div>
</div>
