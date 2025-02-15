<?php

use bajadev\ckeditor\CKEditor;
use common\models\Product;

/* @var $this yii\web\View */
/* @var $product Product */
/* @var $currencies \common\models\Currency */
/* @var $allow_change_currency boolean */
/* @var $allow_custom_pricing_rules boolean */
/* @var $allow_monitor_now boolean */
/* @var $allow_variant_price_markup boolean */
/* @var $allow_review_monitoring boolean */
/* @var $reviewsRemainsLimit integer */
/* @var $user_has_remaining_monitorings boolean */
/* @var \yii\data\ActiveDataProvider $variantChangesDataProvider */
/* @var \yii\data\ActiveDataProvider $variantsDataProvider */

/* @var $variantSearch frontend\models\ProductVariantSearch */

use frontend\assets\AppAsset;
use frontend\widgets\RateWidget;
use yii\grid\GridView;
use yii\helpers\{Html, Url};
use yii\widgets\ActiveForm;
use common\helpers\HelpTextHelper;

$data = json_decode($product->product_data, true);
$price = $data['price'];

$this->title = $product->title;
$this->params['breadcrumbs'][] = ['label' => 'Imported products', 'url' => ['/product']];
$this->params['breadcrumbs'][] = $this->title;
$model = $product;
?>
<div class="modal fade" id="subscribeModal" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>

            </div>
            <div class="modal-body">
                <h4><strong>This features is not available in Your plan</strong></h4>
                <h4><strong>You can change Your plan <a href="<?= Url::to(['/profile/subscribe']) ?>">here</a></strong>
                </h4>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editVariantModal" tabindex="-1" aria-labelledby="editVariantModal" aria-hidden="true">
    <div class="modal-dialog modal-lg editVariantModal"
        <?php if (!$allow_variant_price_markup) : ?>
            data-disabled="true"
        <?php endif ?>
         compare_at_price_by_amount="<?= $model->productPriceMarkup->compare_at_price_by_amount ?>"
         price_by_percent="<?= $model->productPriceMarkup->price_by_percent ?>"
         price_markup="<?= $model->productPriceMarkup->price_markup ?>"
         compare_at_price_markup="<?= $model->productPriceMarkup->compare_at_price_markup ?>"
         price_by_amount="<?= $model->productPriceMarkup->price_by_amount ?>"
         compare_at_price_by_percent="<?= $model->productPriceMarkup->compare_at_price_by_percent ?>"
    >
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">
                    Edit product variant
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div>
                    <div class="loading" style="display:none">
                        <div class="sk-three-bounce">
                            <div class="sk-child sk-bounce1"></div>
                            <div class="sk-child sk-bounce2"></div>
                            <div class="sk-child sk-bounce3"></div>
                        </div>
                    </div>
                    <div class="hasBorder text-left variantControl form-row">
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <div style="text-align: center;" class="form-group">
                    <button class="btn btn-primary" id="editVariant">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div
        class="modal fade bd-example-modal-lg text-center"
        data-keyboard="false"
        data-backdrop="static"
        id="editProductPrice"
        role="dialog"
>
    <div class="modal-dialog modal-lg shop-modal editProductPrice"
        <?php if (!$allow_custom_pricing_rules) : ?>
            data-disabled="true"
        <?php endif ?>
    >
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">
                    Edit product pricing
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="w-100">
                    <div class="hasBorder text-left variantControl form-row">
                        <div>
                            <div class="hasBorder text-left productPriceControl form-row">
                                <div class="form-group col-md-6">
                                    <label class="mb-0"> Price markup :</label>
                                    <select name="price_markup"
                                            value="<?= $product->productPriceMarkup->price_markup ?>"
                                            class="form-select mb-3 productPriceMarkupEditSelect">
                                        <option value="0"
                                            <?php if ($product->productPriceMarkup->price_markup == 0): ?>
                                                selected=""
                                            <?php endif ?>
                                        > By Percent
                                        </option>
                                        <option value="1"
                                            <?php if ($product->productPriceMarkup->price_markup == 1): ?>
                                                selected=""
                                            <?php endif ?>
                                        >By Amount
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="mb-0"> Compare at price markup :</label>
                                    <select name="compare_at_price_markup "
                                            value="<?= $product->productPriceMarkup->compare_at_price_markup ?>"
                                            class="form-select mb-3 productPriceMarkupEditSelect">
                                        <option value="0"
                                            <?php if ($product->productPriceMarkup->compare_at_price_markup == 0): ?>
                                                selected=""
                                            <?php endif ?>
                                        > By Percent
                                        </option>
                                        <option value="1"
                                            <?php if ($product->productPriceMarkup->compare_at_price_markup == 1): ?>
                                                selected=""
                                            <?php endif ?>
                                        >By Amount
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6" style="display:none">
                                    <label class="mb-0" for="price_by_amount"> Price by amount :</label>
                                    <input type="number" name="price_by_amount"
                                           value="<?= $product->productPriceMarkup->price_by_amount ?>"
                                           class="form-control mb-3 valueNotNegative">
                                </div>
                                <div class="form-group col-md-6" style="">
                                    <label class="mb-0" for="price_by_percent"> Price by percent :</label>
                                    <input type="number" name="price_by_percent"
                                           value="<?= $product->productPriceMarkup->price_by_percent ?>"
                                           class="form-control mb-3 valueNotNegative">
                                </div>
                                <div class="form-group col-md-6" style="display:none">
                                    <label class="mb-0" for="compare_at_price_by_amount"> Compare at price by amount
                                        :</label>
                                    <input type="number" name="compare_at_price_by_amount"
                                           value="<?= $product->productPriceMarkup->compare_at_price_by_amount ?>"
                                           class="form-control mb-3 valueNotNegative">
                                </div>
                                <div class="form-group col-md-6" style="">
                                    <label class="mb-0" for="compare_at_price_by_percent"> Compare at price by
                                        percent :</label>
                                    <input type="number" name="compare_at_price_by_percent"
                                           value="<?= $product->productPriceMarkup->compare_at_price_by_amount ?>"
                                           class="form-control mb-3 valueNotNegative">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="loading justify-content-center w-100" style="display:none">
                    <div class="sk-three-bounce">
                        <div class="sk-child sk-bounce1"></div>
                        <div class="sk-child sk-bounce2"></div>
                        <div class="sk-child sk-bounce3"></div>
                    </div>
                </div>
                <div style="text-align: center;" class="form-group">
                    <button class="btn btn-primary" id="editProductPricing">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<div
        class="modal bd-example-modal-lg text-center"
        data-keyboard="false"
        data-backdrop="static"
        id="editProductDescriptionModal"
        role="dialog"
>
    <div class="modal-dialog modal-lg shop-modal editProductPrice">
        <div class="modal-content">
            <div class="modal-header d-flex align-items-center">
                <h4 class="modal-title" id="myLargeModalLabel">
                    Edit product description
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <?=CKEditor::widget([
                    'name' => 'content',
                    'options' => ['id' => 'editor1', 'rows' => 15],
                    'value' => $data['body_html']
                ]);?>
            </div>
            <div class="modal-footer">
                <div class="loading justify-content-center w-100" style="display:none">
                    <div class="sk-three-bounce">
                        <div class="sk-child sk-bounce1"></div>
                        <div class="sk-child sk-bounce2"></div>
                        <div class="sk-child sk-bounce3"></div>
                    </div>
                </div>
                <div style="text-align: center;" class="form-group">
                    <button class="btn btn-primary" id="saveDescriptionChanges" data-id="<?=$product->id?>">
                        Save
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>

<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-xl-3 col-lg-6  col-md-6 col-xxl-5">
                <div class="product-detail-content">
                    <div class="new-arrival-content pr">
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6  col-md-6 col-xxl-7 w-full">
                <div class="product-detail-content">
                    <div class="new-arrival-content pr">
                        <div class="d-flex w-full justify-content-start gap-2">
                            <?= $this->render('_action_buttons', compact('model', 'allow_custom_pricing_rules', 'allow_monitor_now', 'user_has_remaining_monitorings')) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-xl-3 col-lg-6  col-md-6 col-xxl-5 ">
                <!-- Tab panes -->
                <div class="tab-content">
                    <?php if (!empty($data['images'])): ?>
                        <?php foreach ($data['images'] as $key => $image): ?>
                            <div role="tabpanel" class="tab-pane fade <?= !$key ? 'show active' : '' ?>"
                                 id="image_<?= $key ?>">
                                <img class="img-fluid" src="<?= $image ?>" alt="">
                            </div>
                        <?php endforeach; ?>
                    <?php endif ?>
                </div>
                <div class="tab-slide-content new-arrival-product mb-4 mb-xl-0">
                    <!-- Nav tabs -->
                    <ul class="nav slide-item-list mt-3" role="tablist">
                        <?php foreach ($data['images'] as $key => $image): ?>
                            <li role="presentation" <?= !$key ? 'class="show"' : '' ?> >
                                <a href="#image_<?= $key ?>" role="tab"
                                   data-toggle="tab" <?= !$key ? 'class="active" aria-selected="true"' : 'class="" aria-selected="false"' ?>>
                                    <img class="img-fluid" src="<?= $image ?>" alt="" width="50">
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <!--Tab slider End-->
            <div class="col-xl-9 col-lg-6  col-md-6 col-xxl-7 col-sm-12">
                <div class="product-detail-content">
                    <!--Product details-->
                    <div class="new-arrival-content pr">
                        <h4 class="text-black"><?= $product->title ?></h4>
                        <?php if (Yii::$app->params['enableReview']): ?>
                            <div class="star-rating mb-2">
                                <?= Html::a(RateWidget::widget(['rate' => $product->rate]), $product->getReviewsUrl(), ['class' => 'text-nowrap']) ?>
                                <span class="review-text">  (<?= $product->reviewsCount ?> reviews) / </span>
                                <a class="product-review"
                                   href="<?= Url::toRoute(['/product-review/create', 'product_id' => $product->id]) ?>">Write
                                    a review?</a>
                            </div>
                        <?php endif; ?>
                        <p class="price"><?= $product->displayPrice ?></p>
                        <p>Availability: <span class="item"> In stock <i class="fa fa-shopping-basket"></i></span></p>
                        <p>Product code: <span class="item"><?= $product->sku ?></span></p>
                        <p>Brand: <span class="item"><?= $data['brand'] ?></span></p>
                        <p class="text-content"><?= $data['body_html'] ?></p>
                        <!--<p>Product tags:&nbsp;&nbsp;
                            <span class="badge badge-success light">bags</span>
                            <span class="badge badge-success light">clothes</span>
                            <span class="badge badge-success light">shoes</span>
                            <span class="badge badge-success light">dresses</span>
                        </p>-->
                        <!--                                    <p class="text-content">There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don't look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing.</p>-->
                        <div class="filtaring-area my-3">
                            <?php foreach ($data['options'] as $key => $option): ?>
                                <div class="size-filter">
                                    <?php if (isset($option['name'])): ?>
                                        <h4 class="m-b-15"><?= $option['name'] ?></h4>
                                    <?php endif ?>
                                    <?php foreach ($option['values'] as $key => $value): ?>
                                        <?php if ($value) : ?>
                                            <?php if (strpos($value, 'http') !== false) : ?>
                                                <img src="<?= $value ?>" class="options-image">
                                            <?php else : ?>
                                                <div class="btn-group size-btn" data-toggle="buttons">
                                                    <label class="btn btn-outline-primary light btn-sm size-btn-label">
                                                        <input type="radio"
                                                               class="position-absolute invisible size-btn-radio"
                                                               name="options" id="option5">
                                                        <?= $value ?>
                                                    </label>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="custom-control custom-checkbox mb-3 check-xs">

                            <p><input type="checkbox" value="1" class="monitoring-checkbox custom-control-input"
                                      data-id="<?= $product->id ?>" data-type="stock"
                                    <?= $product->monitoring_stock ? 'checked' : '' ?>
                                      id="monitoring-checkbox-stock-<?= $product->id ?>">
                                <label class="custom-control-label" for="monitoring-checkbox-stock-<?= $product->id ?>">Enable
                                    stock monitoring</label>
                            </p>

                            <p><input type="checkbox" value="1" class="monitoring-checkbox custom-control-input"
                                      data-id="<?= $product->id ?>" data-type="price"
                                    <?= $product->monitoring_price ? 'checked' : '' ?>
                                      id="monitoring-checkbox-price-<?= $product->id ?>">
                                <label class="custom-control-label" for="monitoring-checkbox-price-<?= $product->id ?>">Enable
                                    price monitoring</label>
                            </p>

                            <p><input type="checkbox" value="1" class="
                                <?php if (!$allow_review_monitoring) : ?>
                                    multipleImportDisabledButton
                                <?php elseif ($reviewsRemainsLimit <= 0): ?>
                                    reviewRemainsLimitExpired
                                <?php else: ?>
                                    monitoring-checkbox
                                <?php endif; ?>
                             custom-control-input"
                                      data-id="<?= $product->id ?>" data-type="review"
                                    <?= $product->monitoring_reviews ? 'checked' : '' ?>
                                      id="monitoring-checkbox-review-<?= $product->id ?>">
                                <label class="custom-control-label"
                                       for="monitoring-checkbox-review-<?= $product->id ?>">Enable
                                    review monitoring</label>


                            </p>
                            <div class="monitorReviewRate"
                                <?php if (!$product->monitoring_reviews) : ?>
                                    style="display: none"
                                <?php endif; ?>
                            >
                                <label for="minReviewsRate">Minimum Reviews Rate For Monitoring</label>
                                <p>
                                    <?php for ($i = 0; $i < $product->monitoring_reviews_min_rate; $i++): ?>
                                        <i class="fa fa-star monitoring_review_min_rate" data-id="<?= $i ?>"
                                           style="color:#efc20f;font-size:20px"></i>
                                    <?php endfor; ?>
                                    <?php for ($i = 0; $i < 5 - $product->monitoring_reviews_min_rate; $i++): ?>
                                        <i style="font-size:20px" class="fa fa-star monitoring_review_min_rate"
                                           data-id="<?= $i ?>"></i>
                                    <?php endfor; ?>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap">
<!--                            --><?php //if (!$product->is_published && !$product->activePublishQueue): ?>
                                <div class="shopping-cart mt-3">
                                    <a data-method="post"
                                       data-confirm="Are you sure you want publish this product to Shopify?"
                                       class="btn btn-primary btn-lg"
                                       href="<?= Url::toRoute(['product/publish', 'id' => $product->id]) ?>">Publish</a>
                                </div>

<!--                            --><?php //endif; ?>
                            <div class="ml-3 shopping-cart mt-3">
                                <a href="<?= $model->src_product_url ?>?shionMonitorProductNow=<?= $model->id ?>"
                                   data-disabled="<?= !$allow_monitor_now || (!$product->monitoring_reviews && $product->monitoring_price && $product->monitoring_stock) ?>"
                                   target="_blank"
                                   title="Monitor Now"
                                   data-toggle="tooltip"
                                   class="btn btn-primary btn-lg <?= !$allow_monitor_now ? 'shionMonitorNowDisabled' : '' ?> <?= !$user_has_remaining_monitorings && $allow_monitor_now ? 'monitoringRemainDisabled' : '' ?>
                                   <?= !$product->monitoring_reviews && !$product->monitoring_price && !$product->monitoring_stock ? 'disabled' : '' ?>
                                   classDisabledShionExtension productMonitorNowButton"
                                   data-text="To monitor product now, you need to install the Chrome Extension">Monitor
                                    Now</a>

                            </div>
                        </div>
                        <div class="currencyLoading" style="display:none">
                            <div class="sk-three-bounce">
                                <div class="sk-child sk-bounce1"></div>
                                <div class="sk-child sk-bounce2"></div>
                                <div class="sk-child sk-bounce3"></div>
                            </div>
                        </div>
                        <div class="currencyContainer mt-3">
                            <div class="row d-flex flex-wrap ">
                                <div class="form-group col-md-6">
                                    <p class="m-0">

                                        <label for="supplierCurrency"
                                               title="Supplier currency"
                                        >Supplier currency</label>
                                    </p>
                                    <select class="form-select" name=""
                                            data-old-value="<?= $product->default_currency_id ?>"
                                            id="supplierCurrency"
                                        <?php if (!$allow_change_currency) : ?>
                                            data-disabled="true"
                                        <?php endif ?>
                                            class="form-control form-control-md productCurrencySelector">
                                        <?php foreach ($currencies as $currency): ?>

                                            <option
                                                <?php if ($currency->id === $product->default_currency_id || (is_null($product->default_currency_id) && $currency->code === \common\models\Currency::DEFAULT_CURRENCY)) : ?>
                                                    selected="selected"
                                                <?php endif ?>
                                                    value="<?= $currency->id ?>">
                                                <?= $currency->code ?> (<?= $currency->name ?>)

                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6 mr-3">
                                    <p class="m-0">

                                        <label for="shopifyCurrency"
                                               title="Supplier currency"
                                        >Shopify currency</label>
                                    </p>
                                    <select
                                            class="form-select"
                                            name=""
                                            id="shopifyCurrency"
                                            data-old-value="<?= $product->currency_id ?>"
                                        <?php if (!$allow_change_currency) : ?>
                                            data-disabled="true"
                                        <?php endif ?>
                                            class="form-control form-control-md-4 productCurrencySelector">
                                        <?php foreach ($currencies as $currency): ?>
                                            <option
                                                <?php if ($currency->id === $product->currency_id || (is_null($product->currency_id) && $currency->code === \common\models\Currency::DEFAULT_CURRENCY)) : ?>
                                                    selected="selected"
                                                <?php endif ?>
                                                    value="<?= $currency->id ?>">
                                                <?= $currency->code ?> (<?= $currency->name ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <p class="m-0">

                                        <label for="currencyRate"
                                               title="Supplier Rate"
                                        >Currency rate</label>
                                    </p>
                                    <input type="number"
                                           data-old-value="<?= $product->currency_rate ?? 1 ?>"
                                        <?php if (!$allow_change_currency) : ?>
                                            data-disabled="true"
                                        <?php endif ?> class="form-control form-control-md-4 " id="currencyRate"
                                           data-old-rate="<?= $product->currency_rate ?? 1 ?>"
                                           value="<?= $product->currency_rate ?>">
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="autoUpdateCurrencyRate"
                                           title="Supplier Rate"
                                    >Automatically Update Currency Rate</label>
                                    <input type="checkbox" class="ml-2" <?php if (!$allow_change_currency) : ?>
                                        data-disabled="true"
                                    <?php endif ?> id="autoUpdateCurrencyRate"
                                        <?php if ($product->update_currency_rate): ?>
                                            checked
                                        <?php endif ?>>
                                </div>
                                <div class="form-group col-md-12">
                                    <button <?php if (!$allow_change_currency) : ?>
                                        data-disabled="true"
                                    <?php endif ?> class="btn btn-primary saveProductCurrencyRateChanges disabled">Save
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <h3> Product variants</h3>
            <div class="basic-form">
                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                ]); ?>
                <div class="row">
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($variantSearch, 'sku')->input('text', ['class' => 'form-control'])->label('SKU') ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($variantSearch, 'inventory_quantity')->input('text', ['class' => 'form-control'])->label('Inventory Quantity') ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($variantSearch, 'price')->input('number', ['class' => 'form-control'])->label('Price') ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($variantSearch, 'compare_at_price')->input('number', ['class' => 'form-control'])->label('Compare At Price') ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($variantSearch, 'updated_at_min')->input('date', ['class' => 'form-control']) ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($variantSearch, 'updated_at_max')->input('date', ['class' => 'form-control']) ?>
                    </div>
                    <div class="form-group col-md-3 mt-4">
                        <?= Html::button('Search', ['type' => 'submit', 'class' => 'btn btn-primary mb-2']) ?>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <div class="row mb-5">
            <div class="product-variants" style="max-width: 100%">
                <?= GridView::widget([
                    'dataProvider' => $variantsDataProvider,
                    'tableOptions' => [
                        'id' => 'variantsDatatable',
                        'class' => 'table-hover table-responsive-variants'
                    ],
                    'columns' => \common\helpers\GridColumnsHelper::getVariantOptions($model),
                ]) ?>
            </div>
        </div>


        <div class="row mt-3">
            <h3>Monitoring results</h3>
            <div class="product-variant-changes">
                <?= GridView::widget([
                    'dataProvider' => $variantChangesDataProvider,
                    'pager' => ['class' => 'frontend\widgets\Bootstrap4LinkPager'],
                    'layout' => "{items}{pager}",
                    'tableOptions' => [
                        'id' => 'variantChangesDatatable',
                        'class' => 'table-hover table-responsive-variants '
                    ],
                    'columns' => [
                        ['class' => 'yii\grid\SerialColumn'],
                        [
                            'attribute' => 'variant_id',
                            'value' => function ($model) {
                                $options = array_filter([$model->variant->option1, $model->variant->option2, $model->variant->option3]);
                                return implode(' | ', $options);
                            }
                        ],
                        'old_price',
                        'new_price',
                        'old_compare_at_price',
                        'new_compare_at_price',
                        'old_inventory_quantity',
                        'new_inventory_quantity',
                        [
                            'attribute' => 'updated_at',
                            'label' => 'Change detected at',
                            'value' => function ($model) {
                                return date('d, M Y', $model->updated_at);
                            }
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
</div>


<div id="product-import-alert" <?php if (Yii::$app->session->hasFlash('create')): ?> class="create"
    data-title="<?= HelpTextHelper::getHelpText('product_create_alert', 'title') ?>" data-text="<?= HelpTextHelper::getHelpText('product_create_alert', 'text') ?>"
<?php elseif (Yii::$app->session->hasFlash('update')): ?> class="update"
    data-title="<?= HelpTextHelper::getHelpText('product_update_alert', 'title') ?>" data-text="<?= HelpTextHelper::getHelpText('product_update_alert', 'text') ?>"
<?php elseif (Yii::$app->session->hasFlash('publishQueueSet')): ?> class="publishQueueSet"
    data-title="<?= HelpTextHelper::getHelpText('product_publish_alert', 'title') ?>" data-text="<?= HelpTextHelper::getHelpText('product_publish_alert', 'text') ?>"
<?php endif ?> >
</div>

<?php

if (Yii::$app->session->hasFlash('create') ||
    Yii::$app->session->hasFlash('update') ||
    Yii::$app->session->hasFlash('publishQueueSet')
) {
    $this->registerJsFile('@web/js/importProduct/product-import-alert.js', ['depends' => [AppAsset::class]]);
}
?>

