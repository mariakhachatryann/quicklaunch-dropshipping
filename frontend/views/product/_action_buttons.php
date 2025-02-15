<?php

use yii\helpers\Url;

/* @var \common\models\Product $model*/
/* @var boolean $allow_custom_pricing_rules*/
/* @var boolean $allow_monitor_now*/
/* @var boolean $user_has_remaining_monitorings*/
?>
<?php if ($model->is_deleted):?>
    <p class="text-center">
        <a href="<?=Url::toRoute(['product/restore', 'id' => $model->id])?>"
           title="Restore" data-toggle="tooltip" class="btn btn-danger shadow btn-xs sharp mr-1"
           data-method="post" data-confirm="This product has been deleted, do you want restore it?"
        >
            <i class="fa fa-undo"></i>
        </a>

    </p>
<?php else:?>
    <a href="<?=$model->getSrcUrl() ?>" target="_blank" title="View on <?=$model->siteName ?>" data-toggle="tooltip" class="btn btn-dark shadow btn-xs sharp"><iconify-icon icon="solar:eye-broken"></iconify-icon></i></a>
    <a href="<?=Url::toRoute(['product/view', 'id' => $model->id])?>" title="Preview" data-toggle="tooltip" class="btn btn-primary shadow btn-xs sharp"><iconify-icon icon="solar:eye-broken"></iconify-icon></a>
    <!--<a href="<?/*=Url::toRoute(['product/create', 'id' => $model->id])*/?>" title="Edit" data-toggle="tooltip" class="btn btn-primary shadow btn-xs sharp mr-1"><i class="fa fa-pencil"></i></a>-->
    <a href="<?=$model->getHandleUrl()?>" target="_blank" title="View on Shopify" data-toggle="tooltip" class="btn btn-success shadow btn-xs sharp viewOnShopifyBtn <?= !$model->is_published ? 'disabled' : '' ?>"><iconify-icon icon="solar:eye-broken"></iconify-icon></a>
    <a href="<?=$model->getShopifyEditUrl()?>" target="_blank" title="Edit on Shopify" data-toggle="tooltip" class="btn btn-success shadow btn-xs sharp editOnShopifyBtn <?= !$model->is_published ? 'disabled' : '' ?>"><iconify-icon icon="solar:pen-broken"></iconify-icon></a>
    <?php if(isset($allow_custom_pricing_rules)): ?>
        <a title="Edit pricing" data-toggle="tooltip" class="btn btn-warning shadow btn-xs sharp editProductPricingRules" ><iconify-icon icon="solar:dollar-minimalistic-broken""></iconify-icon></a>
    <?php endif?>
    <a href="<?=$model->src_product_url?>?shionaddreviews=true&product_id_from_shion=<?=$model->id?>" target="_blank" title="Import New Reviews" data-toggle="tooltip" class="btn btn-warning shadow btn-xs sharp classDisabledShionExtension shionMonitorNow" data-text="To add new reviews for previously imported products, you need to install the Chrome Extension" style="background-color:#13b497;border:none;box-shadow:none!important;-webkit-box-shadow:none!important"><iconify-icon icon="solar:star-line-duotone"></iconify-icon></a>
    <?php if(isset($allow_monitor_now)) : ?>
        <a href="<?=$model->src_product_url?>?shionMonitorProductNow=<?=$model->id?>" data-disabled="<?=!$allow_monitor_now?>" target="_blank" title="Monitor Now" data-toggle="tooltip" class="btn btn-warning shadow btn-xs sharp <?=!$allow_monitor_now ? 'shionMonitorNowDisabled' : '' ?> <?= !$user_has_remaining_monitorings && $allow_monitor_now ? 'monitoringRemainDisabled' : '' ?> classDisabledShionExtension" data-text="To monitor product now, you need to install the Chrome Extension">
            <iconify-icon icon="tabler:reload"></iconify-icon>
        </a>
    <?php endif ?>
    <a href="<?=Url::toRoute(['product/delete', 'id' => $model->id])?>" title="Delete" data-toggle="tooltip" data-method="post" data-confirm="Are you sure you want delete this product?" class="btn btn-danger shadow btn-xs sharp"><iconify-icon icon="solar:trash-bin-minimalistic-broken"></iconify-icon></a>
    <?php if(isset($allow_custom_pricing_rules)): ?>
        <a title="Edit Description" data-toggle="tooltip" class="btn btn-primary shadow btn-xs sharp" id="editProductDescription"><iconify-icon icon="tabler:copy"></iconify-icon></a>
    <?php endif?>
<?php endif?>