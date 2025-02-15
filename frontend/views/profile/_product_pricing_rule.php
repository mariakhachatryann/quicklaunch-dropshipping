<?php
/* @var frontend\models\ProductPricingRule $model */

use yii\helpers\Url;

?>

<tr class="productPricingRuleItem" data-id="<?= $model->id ?>">
    <td class="tet-center product_pricing_rule_min_price" data-value="<?= $model->price_min ?>">
        <?= $model->price_min ?>
    </td>
    <td class="tet-center product_pricing_rule_max_price" data-value="<?= $model->price_max ?>">
        <?= $model->price_max ?>
    </td>
    <td class="tet-center product_pricing_rule_price_markup" data-value="<?= $model->price_markup ?>">
        <?= $model->price_markup ? 'By Amount' : 'By Percent' ?>
    </td>
    <td class="tet-center product_pricing_rule_compare_at_price_markup" data-value="<?= $model->compare_at_price_markup ?>">
        <?= $model->compare_at_price_markup ? 'By Amount' : 'By Percent' ?>
    </td>
    <td class="tet-center product_pricing_rule_price_by_percent" data-value="<?= $model->price_by_percent ?>">
        <?= $model->price_by_percent ?>
    </td>
    <td class="tet-center product_pricing_rule_price_by_amount" data-value="<?= $model->price_by_amount ?>">
        <?= $model->price_by_amount ?>
    </td>
    <td class="tet-center product_pricing_rule_compare_at_price_by_percent" data-value="<?= $model->compare_at_price_by_percent ?>">
        <?= $model->compare_at_price_by_percent ?>
    </td>
    <td class="tet-center product_pricing_rule_compare_at_price_by_amount" data-value="<?= $model->compare_at_price_by_amount ?>">
        <?= $model->compare_at_price_by_amount ?>
    </td>
    <td class="d-flex pb-4 pt-4 mt-1">
        <a data-id="<?= $model->id ?>" href="<?=Url::toRoute(['product-pricing-rule/delete', 'id' => $model->id])?>" data-method="post" title="Delete" data-toggle="tooltip" data-confirm="Are you sure you want delete this product pricing rule?" class="btn btn-danger shadow btn-xs sharp deleteProductPricingRule"><i class="fa fa-trash"></i></a>
        <a title="Edit" data-id="<?= $model->id ?>" class="btn btn-success shadow btn-xs sharp mr-1 editProductPricingRuleButton" ><i class="fa fa-pencil"></i></a>
    </td>
</tr>



