<?php

use common\helpers\ShopifyClient;
use common\helpers\OrderHelper;
use yii\helpers\Html;
use common\models\User;
use yii\helpers\Url;


$date = $model['date'];
$total = $model['total_price'];
$address = $model['address'];
$customer = $model['customer'];

?>
<tr class="">
    <td class="py-2">
        <div class="custom-control custom-checkbox checkbox-success">
            <input type="checkbox" class="form-check-input" data-id="<?= $model['id'] ?>" data-toggle="tooltip" title="Select">
        </div>
    </td>
    <td class="py-2">
        <a href="<?= Yii::$app->user->identity->shopUrl.'/admin/orders/'.$model['id'] ?>" target="_blank">
            <strong>#<?= 1000 + $model['number'] ?></strong>
        </a> by <strong><?= $customer['customer_fullName'] ?? 'No name'?></strong><br/>
        <a href="mailto:<?= $customer['customer_email'] ?? ''?>"><?= $customer['customer_email'] ?? 'No email'?></a></td>
<!--    <td class="py-2 text-center">--><?php //= $date->format(\common\models\Product::DATE_DISPLAY_FORMAT)?><!--</td>-->
    </td>
    <td >
        <span class="badge <?= $model['financial_status'] == OrderHelper::STATUS_PAID ? 'badge-success' : 'badge-primary' ?>">
            <?= $model['financial_status']?><iconify-icon icon="material-symbols:check-small"></iconify-icon>
        </span>
    </td>
    <td>
        <?php if($model['fulfillment_status']) : ?>
        <span class="badge  badge-success ">
            <?= $model['fulfillment_status'] ?><iconify-icon icon="material-symbols:check-small"></iconify-icon>
        </span>
        <?php else : ?>
            <span class="bold badge badge-dark ">
            Unfulfilled
        </span>
        <?php endif ?>
    </td>
    <td class="">$<?= $total ?>
    </td>
    <td class="py-2 text-center text-right">
        <a href="<?=Url::toRoute(['order/view', 'id' => $model['id'] ])?>" class="btn btn-primary px-4 btn-sm ml-2"><iconify-icon icon="solar:eye-broken" class="fs-3"></iconify-icon></a>
    </td>
</tr>

