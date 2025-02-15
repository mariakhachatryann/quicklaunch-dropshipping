<?php

use common\models\Product;
use yii\helpers\Html;
use common\models\Notification;
use yii\helpers\Url;

$this->title = 'Notifications';
$this->params['breadcrumbs'][] = $this->title;
?>
<!-- Default accordion -->
<?php  if(!empty($notifications)) : ?>
<?php foreach ($notifications as $key => $notification):?>
<div class="card">
    <div class="card-body">
        <h3><?= $notification->subject ?></h3>
       <?php if($notification->url ) : ?>
        <a href="<?= Url::toRoute($notification->url) ?>">
       <?php endif ?>
            <div class="media-body p-2">
                <p class=" bd-highlight"> <?= $notification->text ?> </p>
                <p class="d-block"><?= date(Product::DATE_DISPLAY_FORMAT,$notification->updated_at)?></p>
            </div>
        <?php if($notification->url ) : ?>
            </a>
        <?php endif ?>
    </div>
</div>
    <?php endforeach;?>
<?php else : ?>
    <div class="empty-tickets">
        <h4> No notifications yet </h4>
    </div>
<?php endif ?>