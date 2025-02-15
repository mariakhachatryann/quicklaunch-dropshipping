<?php

use yii\helpers\Html;
use common\models\Notification;

$this->title = $notification->subject;
?>
<div class="card">
    <div class="card-header d-block">
        <h1 class="card-title"><?= Html::encode($this->title) ?></h1>
    </div>
    <div class="card-body">
        <!-- Default accordion -->
        <li>
            <div class="timeline-panel">
                <div class="media mr-2 media-<?= Notification::notificationDivStyle($notification->notification_type)?>"></div>
                <div class="media-body">
                    <div class="callout callout-<?= Notification::notificationDivStyle($notification->notification_type)?>">
                    <p> <?= $notification->text ?> </p>
                    <p class="d-block"><?= date(\common\models\Product::DATE_DISPLAY_FORMAT,$notification->updated_at)?></p>
                    </div>
                </div>

            </div>
        </li>
    </div>
</div>