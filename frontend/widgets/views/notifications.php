<?php
use frontend\models\api\Notification;
use yii\helpers\Url;

?>

<a class="nav-link position-relative" href="javascript:void(0)" id="drop2" aria-expanded="false">
    <iconify-icon icon="solar:bell-bing-line-duotone" class="fs-6"></iconify-icon>
</a>
<div class="dropdown-menu content-dd dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
    <div class="d-flex align-items-center justify-content-between py-3 px-7">
        <h5 class="mb-0 fs-5 fw-semibold">Notifications</h5>
        <span class="badge text-bg-primary rounded-4 px-3 py-1 lh-sm"><?= $countNewNotifications ?> new</span>
    </div>
    <div class="message-body" data-simplebar>
        <?php if (!empty($notifications)):?>
            <?php foreach ($notifications as $notification):?>
                <a href="<?=Url::toRoute($notification->notificationUrl)?>" class="py-6 px-7 d-flex align-items-center dropdown-item gap-3">
                    <span class="flex-shrink-0 bg-danger-subtle rounded-circle round d-flex align-items-center justify-content-center fs-6 text-danger">
                        <iconify-icon icon="solar:bell-outline"></iconify-icon>
                    </span>
                    <div class="w-75 d-inline-block ">
                        <h6 class="d-block mb-1 fw-semibold"><?= $notification->subject ?></h6>
                        <span class="d-block fs-2"><?= date(\common\models\Product::DATE_DISPLAY_FORMAT, $notification->date)?></span>
                        <span class="d-block text-truncate text-truncate fs-11"><?= $notification->text ?></span>
                    </div>
                </a>
            <?php endforeach;?>
        <?php endif; ?>
    </div>
    <div class="py-6 px-7 mb-1">
        <button class="btn btn-primary w-100">
            <a href="<?= Url::toRoute('/notifications/index') ?>" class="text-white text-decoration-none">See All Notifications</a>
        </button>
    </div>
</div>