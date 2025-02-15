<?php
/* @var $setting \common\models\UserSetting */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \frontend\models\ProductPricingRuleSearch */
/* @var array $feauturesDescriptions */
/* @var array $availableFeatures */
/* @var \common\models\Currency[] $currencies */

/* @var $this \yii\web\View */

use common\models\UserSetting;
use frontend\assets\AppAsset;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

$this->title = 'Settings';
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsVar('siteSettings', $setting->attributes);
$this->registerJsVar('date_format', $setting->dateFormat($js=true));
$this->registerJsVar('formats', UserSetting::$jsDateFormats);

?>
<?php //print_r($setting->attributes);die; ?>
    <div class="settings">
        <div class="modal fade" id="subscribeModal" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <h4><strong>This features is not available in Your plan</strong></h4>
                        <h4><strong>You can change Your plan <a href="<?= Url::to(['/profile/subscribe']) ?>">here</a></strong></h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <?php $form = ActiveForm::begin(['method' => 'post', 'enableClientValidation'=>false, 'options' => ['enctype' => 'multipart/form-data']]) ?>

        <div class="row">
            <div class="col-xl-12 col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4>Store branding</h4>
                            <div class="help-block ml-1 settingHelpBlock">
                                <iconify-icon icon="solar:question-circle-linear" width="24px" data-toggle="modal" data-target="#priceSettingInfo"></iconify-icon>
                                <div class="pulse-css"></div>
                            </div>
                        </div>
                        <div class="basic-form">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div >
                                        <?= $form->field($setting, 'store_name')->input('text')?>
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
<!--                                    <div >-->
<!--                                        --><?php //= $form->field($setting, 'logo')->fileInput(['accept' => 'image/*'])->label('Upload Store Logo') ?>
<!--                                    </div>-->

                                    <div class="custom-file">
                                        <?= $form->field($setting, 'logo')->fileInput(['class' => 'custom-file-input'])->label('Choose file', ['class' => 'custom-file-label']) ?>
                                    </div>
                                </div>
                                <div id="imagePreview"></div>
                                <div class="leadImageZoom" style="display:none">
                                    <img class="leadImageZoomPreview" alt="">
                                </div>
                            </div>
                        </div>
                        <div>
                            <section>
                                <div class="">
                                    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-12 col-lg-12">

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4>Product Settings</h4>
                            <div class="help-block ml-1 settingHelpBlock">
                                <iconify-icon icon="solar:question-circle-linear" width="24px" data-toggle="modal" data-target="#priceSettingInfo"></iconify-icon>
                                <div class="pulse-css"></div>
                            </div>
                        </div>
                        <div class="basic-form">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div >
                                      <?= $form->field($setting, 'measurement')->dropDownList(UserSetting::MEASUREMENTS, ['class' => 'form-select'])?>
                                    </div>
                                </div>
                                <div class="card col-md-12">
                                    <div class="card-body px-0">
                                        <h4>
                                            Default Pricing Rules
                                        </h4>
                                        <div class="basic-form">
                                            <div class="form-row">
                                                <div class="form-group col-md-6">
                                                  <?= $form->field($setting, 'price_markup')
                                                    ->dropDownList(UserSetting::$priceMarkups, ['class' => 'form-select price-markup', 'v-model' => 'priceMarkup'])
                                                    ->label(null, ['data-toggle' => 'tooltip', 'title' => "price-markup"]) ?>
                                                </div>
                                                <div class="form-group col-md-6">
                                                  <?= $form->field($setting, 'compare_at_price_markup')
                                                    ->dropDownList(UserSetting::$priceMarkups, ['class' => 'form-select compare_at_price_markup', 'v-model' => 'comparePriceMarkup'])
                                                    ->label(null, ['data-toggle' => 'tooltip', 'title' => "comparePriceMarkup"]) ?>
                                                </div>
                                                <div class="form-group col-md-6" v-show="priceMarkup == 1">
                                                    <div class="price-by-amount" >
                                                      <?= $form->field($setting, 'price_by_amount')->textInput(['v-model' => 'price_by_amount', 'type' => 'number', 'min' => 0, 'step' => 'any'])->label(null, ['data-toggle' => 'tooltip', 'title' => "price_by_amount"]) ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6"  v-show="priceMarkup == 0">
                                                    <div class="price_by_percent">
                                                      <?= $form->field($setting, 'price_by_percent')->textInput(['v-model' => 'price_by_percent', 'type' => 'number', 'min' => 0, 'step' => 'any'])->label(null, ['data-toggle' => 'tooltip', 'title' => "price_by_percent"]) ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6" v-show="comparePriceMarkup == 1">
                                                    <div class="compare_at_price_by_amount" >
                                                      <?= $form->field($setting, 'compare_at_price_by_amount')->textInput(['v-model' => 'compare_at_price_by_amount', 'type' => 'number', 'min' => 0, 'step' => 'any'])->label(null, ['data-toggle' => 'tooltip', 'title' => '']) ?>
                                                    </div>
                                                </div>
                                                <div class="form-group col-md-6"  v-show="comparePriceMarkup == 0">
                                                    <div class="compare_at_price_by_percent">
                                                      <?= $form->field($setting, 'compare_at_price_by_percent')->textInput(['v-model' => 'compare_at_price_by_percent', 'type' => 'number', 'min' => 0, 'step' => 'any'])->label(null, ['data-toggle' => 'tooltip', 'title' => "compare_at_price_by_amount"]) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <section>
                                <div>
                                    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-12 col-lg-12 pricingrulecontainerr <?php if(!$setting->custom_pricing_rules) : ?> d-none <?php endif ?>">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4>Pricing Rules By Product Price</h4>
                            <div class="help-block ml-1 settingHelpBlock">
                                <iconify-icon icon="solar:question-circle-linear" width="24px" data-toggle="modal" data-target="#pricingRuleInfo"></iconify-icon>
                                <div class="pulse-css"></div>
                            </div>
                        </div>
                        <div class="basic-form">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <?php if ($dataProvider->totalCount): ?>
                                        <table class="table small-table table-responsive-lg">
                                            <thead>
                                            <tr>
                                                <td>Min Price</td>
                                                <td>Max Price</td>
                                                <td>Price Markup</td>
                                                <td>Compare At Price Markup</td>
                                                <td>Price By Percent</td>
                                                <td>Price By Amount</td>
                                                <td>Compare At Price By Percent</td>
                                                <td>Compare At Price By Amount</td>
                                                <td>Actions</td>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?= ListView::widget([
                                                'dataProvider' => $dataProvider,
                                                'id' => 'product_list',
                                                'emptyTextOptions' => ['style' => 'display:none;'],
                                                'itemView' => '_product_pricing_rule',
                                                'summary' => '',
                                                'options' => [
                                                    'tag' => 'div',
                                                    'class' => 'row'
                                                ],
                                                'itemOptions' => [
                                                    'tag' => 'div',
                                                    'class' => 'col-md-12',
                                                ],
                                                'pager' => ['class' => 'frontend\widgets\Bootstrap4LinkPager'],
                                            ])?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <label>You don't have custom pricing rules yet</label>
                                    <?php endif?>
                                </div>
                            </div>

                        </div>
                        <div>
                            <section>
                                <div class="mt-5">
                                    <a class="btn btn-primary createProductPricingRule">
                                        Create Product Pricing Rule
                                    </a>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-12 col-lg-12 defaultCurrencyRate <?php if(!$setting->use_default_currency || !$availableFeatures['product_currency_convertor']) : ?> d-none <?php endif ?>">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Currency Settings</h4>
                        <div class="help-block settingHelpBlock">
                            <i class="fa fa-question-circle" data-toggle="modal" data-target="#currencyConvertorInfo"></i>
                            <div class="pulse-css"></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="basic-form">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <?= $form->field($setting, 'default_currency_id')
                                        ->dropDownList(
                                            $currencies,
                                            ['class' => 'form-control form-control-md'])
                                        ->label('Supplier currency', ['data-toggle' => 'tooltip', 'title' => 'Supplier Currency']) ?>
                                </div>
                                <div class="form-group col-md-6">
                                    <?= $form->field($setting, 'currency_id')
                                        ->dropDownList(
                                                $currencies,
                                                ['class' => 'form-control form-control-md'])
                                        ->label('Shopify currency', ['data-toggle' => 'tooltip', 'title' => 'Shopify Currency']) ?>
                                </div>
                                <div class="form-group col-md-12">
                                    <button class="saveSettingDefaultCurrency btn btn-primary">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-12 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4>Product Variant Settings</h4>
                            <div class="help-block ml-1 settingHelpBlock">
                                <iconify-icon icon="solar:question-circle-linear" width="24px" data-toggle="modal" data-target="#variantSettingInfo"></iconify-icon>
                                <div class="pulse-css"></div>
                            </div>
                        </div>
                        <div class="basic-form">
                            <div class="form-check-inline">
                                <div class="form-check <?= !$availableFeatures['sku_import_type'] ? 'paidFeature' : ''?>">
                                    <?= $form->field($setting, 'sku_import_type')
                                        ->checkbox([
                                            'data-toggle' => 'toggle',
                                            'disabled' => !$availableFeatures['sku_import_type'],
                                            'class' => (!$availableFeatures['sku_import_type'] ? 'paidFeature' : '') . ' form-check-input'
                                        ])
                                        ->label('Enable different SKU for each variant', [
                                            'data-toggle' => 'tooltip',
                                            'title' => $feauturesDescriptions['sku_import_type'],
                                            'class' => 'form-check-label'
                                        ])?>
                                </div>
                            </div>

                            <div class="form-check-inline">
                                <div class="form-check  <?= !$availableFeatures['price_import_type'] ? 'paidFeature' : ''?>">
                                    <?= $form->field($setting, 'price_import_type')
                                        ->checkbox([
                                            'data-toggle' => 'toggle',
                                            'disabled' => !$availableFeatures['price_import_type'],
                                            'class' => (!$availableFeatures['price_import_type'] ? 'paidFeature' : '') . ' form-check-input'
                                        ])
                                        ->label('Enable different price for each variant', [
                                            'data-toggle' => 'tooltip',
                                            'title' => $feauturesDescriptions['price_import_type'],
                                            'class' => 'form-check-label'
                                        ])?>
                                </div>
                            </div>

                            <div class="form-check-inline">
                                <div class="form-check <?= !$availableFeatures['stock_count_import_type'] ? 'paidFeature' : ''?>">
                                    <?= $form->field($setting, 'stock_count_import_type')
                                        ->checkbox([
                                            'data-toggle' => 'toggle',
                                            'disabled' => !$availableFeatures['stock_count_import_type'],
                                            'class' => (!$availableFeatures['stock_count_import_type'] ? 'paidFeature' : '') . ' form-check-input'
                                        ])
                                        ->label('Enable different quantity for each variant', [
                                            'data-toggle' => 'tooltip',
                                            'title' => $feauturesDescriptions['stock_count_import_type'],
                                            'class' => 'form-check-label'
                                        ])?>
                                </div>
                            </div>


                            <div class="form-check-inline">
                                <div class="form-check <?= !$availableFeatures['image_import_type'] ? 'paidFeature' : ''?>">
                                    <?= $form->field($setting, 'image_import_type')
                                        ->checkbox([
                                            'data-toggle' => 'toggle',
                                            'disabled' => !$availableFeatures['image_import_type'],
                                            'class' => (!$availableFeatures['image_import_type'] ? 'paidFeature' : '') . ' form-check-input'
                                        ])
                                        ->label('Enable different images for each variant', [
                                            'data-toggle' => 'tooltip',
                                            'title' => $feauturesDescriptions['image_import_type'],
                                            'class' => 'form-check-label'
                                        ])?>
                                </div>
                            </div>

                            <div class="form-check-inline">
                                <div class="form-check <?= !$availableFeatures['change_variants_option_name'] ? 'paidFeature' : ''?>">
                                    <?= $form->field($setting, 'change_variants_option_name')
                                        ->checkbox([
                                            'data-toggle' => 'toggle',
                                            'disabled' => !$availableFeatures['change_variants_option_name'],
                                            'class' => (!$availableFeatures['change_variants_option_name'] ? 'paidFeature' : '') . ' form-check-input'
                                        ])
                                        ->label('Enable change option name', [
                                            'data-toggle' => 'tooltip',
                                            'title' => $feauturesDescriptions['change_variants_option_name'],
                                            'class' => 'form-check-label'
                                        ])?>
                                </div>
                            </div>

                            <div class="form-check-inline">
                                <div class="form-check custom_pricing_rules <?= !$availableFeatures['custom_pricing_rules'] ? 'paidFeature' : ''?>">
                                    <?= $form->field($setting, 'custom_pricing_rules')
                                        ->checkbox([
                                            'data-toggle' => 'toggle',
                                            'disabled' => !$availableFeatures['custom_pricing_rules'],
                                            'class' => (!$availableFeatures['custom_pricing_rules'] ? 'paidFeature' : 'custom_pricing_rules_check') . ' form-check-input'
                                        ])
                                        ->label('Enable Pricing Rules By Product', [
                                            'data-toggle' => 'tooltip',
                                            'title' => $feauturesDescriptions['custom_pricing_rules'],
                                            'class' => 'form-check-label'
                                        ])?>
                                </div>
                            </div>

                            <div class="form-check-inline">
                                <div class="form-check <?= !$availableFeatures['product_currency_convertor'] ? 'paidFeature' : ''?>">
                                    <?= $form->field($setting, 'use_default_currency')
                                        ->checkbox([
                                            'data-toggle' => 'toggle',
                                            'disabled' => !$availableFeatures['product_currency_convertor'],
                                            'class' => (!$availableFeatures['product_currency_convertor'] ? 'paidFeature' : 'default_currency_check') . ' form-check-input'
                                        ])
                                        ->label('Enable Automatic Currency Conversion', [
                                            'data-toggle' => 'tooltip',
                                            'title' => $feauturesDescriptions['product_currency_convertor'],
                                            'class' => 'form-check-label'
                                        ])?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <section>
                                <div class="mt-5">
                                    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
            <?php  if (Yii::$app->params['enableReview']): ?>
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-2">
                            <h4>Product Reviews Settings</h4>
                        </div>
                        <div class="basic-form">
                            <div class="form-row">
                                <input type="hidden" id="currentToken" value="<?= Yii::$app->user->identity->access_token ?>">
                            </div>
                            <section>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <?= $form->field($setting, 'import_reviews')->checkbox(['data-toggle' => 'toggle'])
                                            ->label('Enable product reviews feature and import', ['data-toggle' => 'tooltip',
                                                'title' => "If you activate this feature, supplier product reviews will be imported into your Shopify store"]) ?>
                                    </div>
                                </div>
                                <div class="import_review_control"
                                     <?php if (!$setting->import_reviews): ?>
                                         style="display:none"
                                     <?php endif ?>
                                     >
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?= $form->field($setting, 'date_format')->dropDownList(UserSetting::$jsDateFormats, ['class' => 'form-select', 'v-model' => 'date_format'])->label(null) ?>
                                        </div>
                                    </div>
                                    <div class="row">

                                        <div class="col-md-12">
                                            <?= $form->field($setting, 'enable_add_reviews')->checkbox(['data-toggle' => 'toggle'])->label('Enable add reviews from customers', ['data-toggle' => 'tooltip', 'title' => 'Enable your store customers to submit new reviews']) ?>
                                        </div>

                                        <div class="col-md-12"
                                         <?php if (!$setting->enable_add_reviews): ?>
                                             style="display:none"
                                         <?php endif ?>
                                        >
                                            <?= $form->field($setting, 'enable_add_review_images')->checkbox(['data-toggle' => 'toggle'])->label('Enable add review images', ['data-toggle' => 'tooltip', 'title' => 'Enable your store customers to submit images in reviews']) ?>
                                        </div>
                                        <div class="col-md-12">
                                            <?= $form->field($setting, 'reviews_auto_publish')->checkbox(['data-toggle' => 'toggle'])->label('Autopublish new added reviews', ['data-toggle' => 'tooltip', 'title' => 'Autopublish your customers reviews']) ?>
                                        </div>

                                        <div class="col-md-12">
                                            <?= $form->field($setting, 'review_limit_per_page')->input('number', ['max' => 100, 'min' => 0])->label('Reviews Limit Per Page', ['data-toggle' => 'tooltip', 'title' => 'Reviews Limit Per Page']) ?>
                                        </div>

                                        <div class="col-md-12">
                                            <?= $form->field($setting, 'reviews_label')->input('text')->label('Reviews Label', ['data-toggle' => 'tooltip', 'title' => 'Reviews Label']) ?>
                                        </div>
                                    </div>
                                    <colorpicker :color="defaultColor" v-model="defaultColor"></colorpicker>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?= $form->field($setting, 'review_text_color')->hiddenInput(['data-toggle' => 'tooltip', 'v-model' => 'defaultColor'])->label(false) ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <?= $form->field($setting, 'review_fontsize')->hiddenInput(['data-toggle' => 'tooltip', 'v-model' => 'fontSizeDifference'])->label(false) ?>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-primary" @click="increaseFontSize">
                                                Text size +
                                            </button>
                                            <button type="button" class="btn btn-danger" @click="decreaseFontSize">
                                                Text size -
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>

                        <div>
                            <section>
                                <div class="mt-4">
                                    <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
                                </div>
                            </section>
                        </div>


                        <div class="row mt-5">
                            <div class="col-md-12">
                                <small><i>If you wish to allow customers to add reviews for products not imported by the Shionimporter app,
                                    you'll need to copy this short code and paste it into your Shopify product description.
                                        This code will be replaced with the reviews section on your shop.</i></small>
                            </div>
                            <div class="col-md-12">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" value="[shionimporter-reviews]" id="clipboardInput">
<!--                                    <div class="input-group-button">-->
                                        <button type="button" onclick="myFunction()" class="btn btn-primary btn-sm">Copy</button>
<!--                                    </div>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h4>Product reviews preview</h4>
                        <div class="box box-warning">
                            <div class="section setting-section long">
                                <div class="box-header with-border">
                                    <div class="row">
                                        <h3 class="box-title"></h3>
                                    </div>
                                </div>
                                <div v-for="review in reviews" class="single-review-block border mb-4 p-2" style="border-radius: 10px">
                                    <div class="reviewer_name">
                                        <p v-bind:style="{ fontSize: reviewerNameFontSize + 'px'  }"><strong>{{review.reviewer_name}}</strong></p>
                                        <rate :length="5" :readonly="true" :value="review.rate"></rate>
                                    </div>
                                    <div class="review_text" v-bind:style="{ fontSize: reviewFontSize + 'px' }">{{review.review}}</div>
                                    <div class="review_date" v-bind:style="{ fontSize: reviewDateFontSize + 'px' }">
                                        <small>{{ review.date | moment(formattedDate(formats,date_format))}}</small>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="box box-warning">
                            <div class="section setting-section long">
                                <div class="box-header with-border">
                                    <div class="row">
                                        <h3 class="box-title"></h3>
                                    </div>
                                </div>
                                <div id="add-review"
                                    <?php if(!$setting->enable_add_reviews || !$setting->import_reviews): ?>
                                        style="display: none"
                                    <?php endif ?>
                                >
                                    <input disabled="disabled" type="text" style="color: red; display: block;width: 94%; margin: 3%!important;padding: 10px 18px"  class="reviewer_name_input" placeholder="Name">
                                    <textarea disabled="disabled"  name="reviewText" class="review-text-input"
                                              placeholder="Leave your review here"
                                              style=" display: block;width: 94%; margin: 3%;padding: 10px 18px"
                                    ></textarea>
                                    <div class="rating-stars">
                                        <rate :length="5" class="rate-input"></rate>
                                    </div>
                                    <input style=" margin: 3%;
                                            <?php if(!$setting->enable_add_review_images): ?>
                                            display: none"
                                           <?php else: ?>
                                            "
                                            <?php endif ?>
                                            type="file" disabled="disabled"  id="reviewImage" >
                                    <button type="submit" disabled="disabled" class="addReview"> Save</button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif ?>
        </div>
        <?php ActiveForm::end() ?>
        <div id="priceSettingInfo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span>
                                Reviews
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            Shein Importer allows you easily create your own profitable
                            business with a single click. First of all, it is necessary to choose the
                            best-selling product from Shein.com and then copy the necessary
                            product URL and put it here (we can upload here a photo). Shein
                            importer gives you the best chance to save your time and import your
                            preferable product in your store. The key of the application is that with
                            the product all characteristics are automatically imported to the store.
                            By the way, when you get the main views of the product, you can
                            complete or edit all the details such as title, description, etc. First of
                            all, you should mention the product name, sometimes you can also
                            edit or save the same name. As all information is updated
                            automatically, you should not add the product ID or quantity. For
                            completing the other details such as images, prices, tags, reviews, etc,
                            you need to follow the next instructions.
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="variantSettingInfo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span>
                                Reviews
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            Shein Importer allows you easily create your own profitable
                            business with a single click. First of all, it is necessary to choose the
                            best-selling product from Shein.com and then copy the necessary
                            product URL and put it here (we can upload here a photo). Shein
                            importer gives you the best chance to save your time and import your
                            preferable product in your store. The key of the application is that with
                            the product all characteristics are automatically imported to the store.
                            By the way, when you get the main views of the product, you can
                            complete or edit all the details such as title, description, etc. First of
                            all, you should mention the product name, sometimes you can also
                            edit or save the same name. As all information is updated
                            automatically, you should not add the product ID or quantity. For
                            completing the other details such as images, prices, tags, reviews, etc,
                            you need to follow the next instructions.
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="skuImportTypeInfo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span>
                                Sku Import Type
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <?= $feauturesDescriptions['sku_import_type'] ?>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="stockCountInfo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span>
                                Different quantity for each variant
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <?= $feauturesDescriptions['stock_count_import_type'] ?>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="priceImportInfo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span>
                                Different price for each variant
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <?= $feauturesDescriptions['price_import_type'] ?>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="diffImagesInfo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span>
                                Different images for each variant
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <?= $feauturesDescriptions['image_import_type'] ?>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="optionNameInfo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span>
                                Change option name
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <?= $feauturesDescriptions['change_variants_option_name'] ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="pricingRuleInfo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span>
                                Pricing Rules By Product
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <?= $feauturesDescriptions['custom_pricing_rules'] ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="currencyConvertorInfo" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span>
                                Currency Settings
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div>
                            <?= $feauturesDescriptions['product_currency_convertor'] ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="productPricingRuleModal" class="modal  bd-example-modal-md" role="dialog" >
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span>
                                Product Pricing Rule
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="basic-form">
                            <div class="form-row">
                                <div class="form-group col-md-6" data-field-name="price_min">
                                    <label for="productPricingRuleMinValue">Min Price</label>
                                    <input id="productPricingRuleMinValue" placeholder="Min Price" type="number" class="valueNotNegative productPricingRuleMinValue form-control">
                                </div>
                                <div class="form-group col-md-6" data-field-name="price_max">
                                    <label for="productPricingRuleMaxValue">Max Price</label>
                                    <input id="productPricingRuleMaxValue" placeholder="Max Price" type="number" class="valueNotNegative productPricingRuleMaxValue form-control">
                                </div>
                                <div class="form-group col-md-6" data-field-name="price_markup">
                                    <label for="productPricingRulePriceMarkupValue">Price Markup</label>
                                    <select name="" id="productPricingRulePriceMarkupValue" class="form-control productPricingRulePriceMarkupValue pricingRuleMarkupChanger" data-action="1">
                                        <option value="0">By Percent</option>
                                        <option value="1">By Amount</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6" data-field-name="compare_at_price_markup">
                                    <label for="productPricingRuleCompareAtPriceMarkupValue">Compare At Price Markup</label>
                                    <select name="" id="productPricingRuleCompareAtPriceMarkupValue" class="form-control productPricingRuleCompareAtPriceMarkupValue pricingRuleMarkupChanger" data-action="2">
                                        <option value="0">By Percent</option>
                                        <option value="1">By Amount</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6 productPricingRulePriceByPercentContainer" data-field-name="price_by_percent">
                                    <label for="productPricingRulePriceByPercent">Price By Percent</label>
                                    <input type="number" value="0" id="productPricingRulePriceByPercent" placeholder="Price By Percent" class="form-control valueNotNegative productPricingRulePriceByPercent">
                                </div>
                                <div class="form-group col-md-6 d-none productPricingRulePriceByAmountContainer" data-field-name="price_by_amount">
                                    <label for="productPricingRulePriceByAmount ">Price By Amount</label>
                                    <input id="productPricingRulePriceByAmount" value="0" placeholder="Price By Amount" type="number" class="form-control valueNotNegative productPricingRulePriceByAmount">
                                </div>
                                <div class="form-group col-md-6 productPricingRuleCompareAtPriceByPercentContainer" data-field-name="compare_at_price_by_percent">
                                    <label for="productPricingRuleCompareAtPriceByPercent">Compare At Price By Percent</label>
                                    <input type="number" value="0" id="productPricingRuleCompareAtPriceByPercent" placeholder="Compare At Price By Percent" class="form-control valueNotNegative productPricingRuleCompareAtPriceByPercent">
                                </div>
                                <div class="form-group col-md-6 d-none productPricingRuleCompareAtPriceByAmountContainer" data-field-name="compare_at_price_by_amount">
                                    <label for="productPricingRuleCompareAtPriceByAmount">Compare At Price By Amount</label>
                                    <input type="number" value="0" placeholder="Compare At Price By Amount" id="productPricingRuleCompareAtPriceByAmount" class="form-control valueNotNegative productPricingRuleCompareAtPriceByAmount">
                                </div>
                                <div class="form-group col-md-12">
                                    <div class="currencyLoading d-none">
                                        <div class="sk-three-bounce">
                                            <div class="sk-child sk-bounce1"></div>
                                            <div class="sk-child sk-bounce2"></div>
                                            <div class="sk-child sk-bounce3"></div>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary saveProductPricingRule">
                                        Save
                                    </button>
                                </div>
                                <div class="col-md-12 d-none productPricingRulesErrors">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
$this->registerJsFile('@web/js/importProduct/package/vue.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/vue-rate.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/vue-color.min.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/vue-moment.min.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/moment.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/settingVue.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/clipboard.js', ['depends' => [AppAsset::class]]);







