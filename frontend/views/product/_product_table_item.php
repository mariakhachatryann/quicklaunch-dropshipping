<?php
use common\models\Product;
use yii\helpers\{Html, Url};
use frontend\widgets\RateWidget;
use yii\widgets\ActiveForm;

/* @var Product $model*/
?>
<tr data-url="<?=$model->src_product_url?>">
    <td class="productListSelectAction">
        <div class="form-check">
            <input type="checkbox" class="form-check-input selectProduct" data-id="<?= $model->id ?>" data-toggle="tooltip" title="Select">
        </div>
    </td>
    <td>
        <?php if($model->getHandleUrl()):?>
        <a href="<?=$model->getHandleUrl()?>" target="_blank" title="View in shopify">
        <?php endif?>
            <div class="d-flex align-items-center">
                <?=$model->productImage ? Html::img($model->productImage, ['class' => 'rounded-circle mr-2', 'width' => '50', 'alt' => $model->title]) : ''?>
                <abbr title="<?=$model->title?>" class="text-decoration-none">
                    <span class="text-nowrap">
                        <?= strlen($model->title) > 25 ? substr($model->title, 0, 25) . '...' : $model->title ?>
                    </span>
                </abbr>
            </div>
        <?php if($model->getHandleUrl()):?>
        </a>
        <?php endif?>
    </td>
    <td>
        <div class="d-flex gap-2">
            <?=$this->render('_action_buttons', compact('model'))?>
        </div>
    </td>
    <td><?=Html::a($model->shopify_id, $model->getShopifyEditUrl(), ['target' => '_blank', 'title' => 'Edit in Shopify'])?></td>
    <td><?=Html::a($model->sku, $model->getSrcUrl(), ['target' => '_blank', 'View in supplier site'])?></td>
    <td class="text-nowrap"><?=$model->displayPrice?></td>
    <td class="text-nowrap"><?=date(Product::DATE_DISPLAY_FORMAT, $model->created_at)?></td>
    <td class="text-nowrap"><div class="d-flex align-items-center">
            <?php if ($model->is_published) :?>
                <i class="fa fa-circle text-success mr-1"></i>Published
            <?php else:?>
                <i class="fa fa-circle text-danger mr-1"></i> Not Published
            <?php endif;?>
        </div>
    </td>
	<td class="text-nowrap">
		<div class="custom-control custom-checkbox mb-3 check-xs text-center">
			<input type="checkbox" value="1" data-id="<?= $model->id ?>"
				   class="monitoring-checkbox custom-control-input" <?= $model->monitoring_price ? 'checked' : '' ?>
				   id="monitoring-price-checkbox-<?= $model->id ?>"
				   data-type="price">
			<label class="custom-control-label" for="monitoring-price-checkbox-<?= $model->id ?>"></label>
		</div>
	</td>
	<td class="text-nowrap">
		<div class="custom-control custom-checkbox mb-3 check-xs text-center">
			<input type="checkbox" value="1" data-id="<?= $model->id ?>"
				   class="monitoring-checkbox custom-control-input" <?= $model->monitoring_stock ? 'checked' : '' ?>
				   id="monitoring-stock-checkbox-<?= $model->id ?>"
				   data-type="stock">
			<label class="custom-control-label" for="monitoring-stock-checkbox-<?= $model->id ?>"></label>
		</div>
	</td>
    <?php  if (Yii::$app->params['enableReview']): ?>
<!--    <td class="text-center">-->
<!--        (--><?php //=$model->reviewsCount?><!-- reviews)-->
<!--        --><?php //=Html::a(RateWidget::widget(['rate' => $model->rate]), $model->getReviewsUrl(), ['class' => 'text-nowrap'])?>
<!--    </td>-->
    <?php endif; ?>
</tr>