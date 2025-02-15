<?php
/** @var $this \yii\web\View
 * @var $sites AvailableSite[]
 * @var $trainingVideos Video[]
 * @var $planSiteIds int[]
 * @var $allowMultipleImport boolean
 * @var $allowBulkImport boolean
 * @var $requestedSiteModel RequestedSite
 */

use common\helpers\VideoHelper;
use common\models\AvailableSite;
use common\models\Product;
use common\models\RequestedSite;
use common\models\Video;
use frontend\assets\AppAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use common\helpers\HelpTextHelper;
use yii\widgets\ActiveForm;

$this->title = 'Import product';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/importProduct/style.css', ['depends' => [AppAsset::class]]);
$this->registerCssFile('@web/css/importProduct/bootstrap-vue.css', ['depends' => [AppAsset::class]]);
$this->registerCssFile('@web/css/importProduct/dataTable.css', ['depends' => [AppAsset::class]]);
$this->registerCssFile('@web/css/importProduct/lightbox.css', ['depends' => [AppAsset::class]]);
$this->registerCssFile('@web/css/importProduct/pretty-checkbox.min.css', ['depends' => [AppAsset::class]]);
$this->registerCssFile('@web/css/importProduct/bootstrap-select.min.css', ['depends' => [AppAsset::class]]);

$this->registerJsVar('getDataUrl', URL::toRoute('product/import-product-data'));
$this->registerJsVar('getReviewsUrl', URL::toRoute('product/import-product-reviews'));
$this->registerJsVar('token', Yii::$app->user->identity->access_token);
$this->registerJsVar('editData', $productData);

?>

    <div class="review-settings">

        <div id="import-app">
            <div class="row scrap-url-block">
                <div class="col-md-12">
                    <div class="box box-warning">
                        <div class="section setting-section">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Import new product</h4> <br />
                                            <div class="help-block" @click="openHelpModal('import_product')">
                                                <i class="fa fa-question-circle"></i>
                                                <div class="pulse-css"></div>
                                            </div>
                                            <?php foreach ($trainingVideos as $video): ?>


                                                <?php if (!in_array($video->id, Video::IMPORT_SPECIFIC_VIDEO_IDS)):?>
                                                    <div class="help-block"
                                                         @click="openHelpModal('training_video_<?= $video->id ?>')">
                                                        <i class="fa fa-play"></i>
                                                        <div class="pulse-css"></div>
                                                    </div>
                                                <?php endif;?>
                                            <?php endforeach; ?>

                                            <div class="col-md-7 ml-4 timeline-panel text-muted planChangeMessage">
                                                <p class="mb-0">
                                                    Subscribe to higher plans to get more features!
                                                    <a class="linkToPlans" href="/profile/subscribe">Check the list of the plans</a>
                                                </p>
                                            </div>

                                        </div>
                                        <div class="card-body">
                                            <h6><i>
                                                    Highly recommend using the <a target="_blank" href="<?=Yii::$app->params['chromeExtensionUrl']?>">Google Chrome extension</a>
                                                    for optimal performance when importing products from Shein.
                                                    Some features may not work as effectively through the dashboard. <br><br>
                                                </i></h6> <br>

                                            <p>
                                                <a class="btn btn-warning" href="https://www.youtube.com/watch?v=UDze48k-ins" target="_blank">Check training video</a>
                                                <a class="btn btn-primary" href="<?=Yii::$app->params['chromeExtensionUrl']?>" target="_blank">Use Chrome Extension!</a></p>
                                            <div>
                                                <h6>Available Sites for Import</h6>
                                                <ol class="list-icons d-flex flex-column align-items-start">
                                                    <?php foreach ($sites as $site) : ?>

                                                        <?php $itemClass = in_array($site->id, $planSiteIds) ? 'check' : 'times' ?>
                                                        <li style="display:inline-block; position: relative">
                                                            <?= Html::a(
                                                                Html::tag('span',
                                                                    Html::tag('i', $site->is_new ?' <div class="pulse-css" title="New Site" data-toggle="tooltip"></div>' : '',
                                                                        [
                                                                            'class' =>  'fa fa-' . $itemClass . ' text-primary'
                                                                        ]),
                                                                    [
                                                                        'class' => 'align-middle mr-2'
                                                                    ]
                                                                ) . ' ' . $site->name, $site->url, ['target' => '_blank']) ?>
                                                            <?= $site->import_by_extension ?
                                                                Html::a(
                                                                    Html::tag('i', '', ['class' => 'fa fa-chrome']),
                                                                    Yii::$app->params['chromeExtensionUrl'],
                                                                    ['style' => 'font-size: 24px', 'target' => '_blank']
                                                                )
                                                                : ''?>
                                                            <?=$site->has_reviews ? Html::tag('span',
                                                                Html::tag('i','',
                                                                    [
                                                                        'class' =>  'fa fa-star text-orange',
                                                                        'data-toggle' => "tooltip",
                                                                        'title' => "Has Reviews"
                                                                    ]),
                                                                [
                                                                    'class' => 'align-middle mr-2'
                                                                ]
                                                            ) : ''?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ol>
                                                <button type="button" @click="openRequestSiteModal()" class="btn btn-primary btn-sm mb-4 ml-0">Request a new site</button>

                                            </div>
                                            <h6>Paste product URL you want to import</h6>
                                            <div class="input-group mb-3">
                                                <input
                                                        v-on:keyup.enter="getAddData"
                                                        id="import-label"
                                                        type="text"
                                                        v-model='scrapUrl'
                                                        placeholder="https://www.shein.com/Tartan-Panel-Drop-Shoulder-Colorblock-Blouse-p-1716074-cat-1733.html?scici=navbar_WomenHomePage~~tab01navbar05menu03dir04~~5_3_4~~itemPicking_00103343~~SPcCccWomenCategory~~0~~50001"
                                                        class="form-control border-0"
                                                >
                                                <div class="input-group-append">
                                                    <button @click="getAddData"  :class="`btn btn-primary ml-0 ${loading ? 'disabled' : ''}`" type="button">Import</button>
                                                </div>
                                            </div>
                                            <div>
                                                <!--											 --><?php /*= Html::a('Multiple Import', ['/multiple-import/create'], ['class' => 'btn btn-primary classDisabledShionExtension', 'data-text' => 'To import multiple products, you need to install the Chrome Extension']) */?>
                                                <?= Html::a('Bulk Import From Category Page', ['/bulk-import/create'], ['class' => 'btn btn-primary', 'data-text' => 'To Bulk Import, you need to install the Chrome Extension']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div :style="{display: showData}" class="main-wrapper">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Product details</h4>
                                <div class="help-block" @click="openHelpModal('product_details')">
                                    <i class="fa fa-question-circle"></i>
                                    <div class="pulse-css"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="basic-form">
                                    <form>
                                        <div class="form-group">
                                            <div class="form-group-header">
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('title','text') ?>"
                                                        title="<?= HelpTextHelper::getHelpText('title','title') ?>"
                                                        @click="showSubscribeLinkModal(!disabledImportType.generateAiContent)"
                                                >
                                                    Title
                                                </label>
                                                <div class="form-group ml-3"
                                                     data-container="body"
                                                     data-toggle="hover"
                                                     data-placement="right"
                                                     data-content="Generate AI title for this product"
                                                     title="Generate AI title"

                                                >
                                                    <input v-model="generateTitle" type="checkbox" style="cursor: pointer"
                                                           :disabled="generatingTitle || !disabledImportType.generateAiContent">
                                                </div>
                                                <div class="edit-action">
                                                    <input
                                                            v-if="displayEditAllSecCheckbox"
                                                            @change="editScrap('title')"
                                                            v-model="editAllSecCheckbox.title"
                                                            type="checkbox"
                                                            class="edit-checkbox"
                                                    >

                                                    <template v-if="editLoading['title']">
                                                        <div class="edit-loading">
                                                            <div class="sk-three-bounce">
                                                                <div class="sk-child sk-bounce1"></div>
                                                                <div class="sk-child sk-bounce2"></div>
                                                                <div class="sk-child sk-bounce3"></div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <input
                                                    class="form-control form-control-md"
                                                    type="text"
                                                    placeholder="title"
                                                    v-model="title"
                                            >
                                        </div>
                                        <div class="form-group">
                                            <label
                                                    data-container="body"
                                                    data-toggle="hover"
                                                    data-placement="right"
                                                    data-content=" <?= HelpTextHelper::getHelpText('product_id','text') ?>"
                                                    title=" <?= HelpTextHelper::getHelpText('product_id','title') ?>"
                                            >
                                                Product Id
                                            </label>
                                            <input
                                                    class="form-control form-control-md"
                                                    type="text"
                                                    placeholder="sku"
                                                    v-model="finalSKU"
                                            >
                                        </div>
                                        <div class="form-group">
                                            <label
                                                    data-container="body"
                                                    data-toggle="hover"
                                                    data-placement="right"
                                                    data-content=" <?= HelpTextHelper::getHelpText('productType','text') ?>"
                                                    title=" <?= HelpTextHelper::getHelpText('productType','title') ?>"
                                            >
                                                Product Type
                                            </label>
                                            <input
                                                    class="form-control form-control-md"
                                                    type="text"
                                                    placeholder="product type"
                                                    v-model="productType"
                                            >
                                        </div>
                                        <div class="form-group">
                                            <label
                                                    data-container="body"
                                                    data-toggle="hover"
                                                    data-placement="right"
                                                    data-content="<?= HelpTextHelper::getHelpText('collection','text') ?>"
                                                    title="<?= HelpTextHelper::getHelpText('collection','title') ?>"
                                            >
                                                Collection
                                            </label>
                                            <select
                                                    class="form-control form-control-md"
                                                    v-model="selectedCollection"
                                                    @change="changeSelectedCollection"
                                            >
                                                <option value="">Select Shopify collection</option>
                                                <option
                                                        v-for="collection of collectionsArray "
                                                        :value="collection.id"
                                                >
                                                    {{collection.title}}
                                                </option>
                                                <option
                                                        value="create_new"
                                                >
                                                    Create new
                                                </option>
                                            </select>
                                        </div>
                                        <div v-if="showCreateCollectionInput" class="form-group col-md-12">
                                            <label>New Collection Name</label>
                                            <input v-model="newCollectionValue" class="form-control form-control-md" />
                                        </div>
                                        <div class="form-group">
                                            <label
                                                    data-container="body"
                                                    data-toggle="hover"
                                                    data-placement="right"
                                                    data-content="<?= strip_tags(HelpTextHelper::getHelpText('quantity','text')) ?>"
                                                    title="<?= HelpTextHelper::getHelpText('quantity','title') ?>"
                                            >
                                                Quantity
                                            </label>
                                            <input
                                                    class="form-control form-control-md"
                                                    type="number"
                                                    placeholder="quantity"
                                                    v-model="stockCount"
                                                    :disabled="quantityStatus"
                                            >
                                        </div>
                                        <div class="form-group">
                                            <div class="form-group-header">
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('description','text') ?>"
                                                        title="<?= HelpTextHelper::getHelpText('description','title') ?>"
                                                        @click="showSubscribeLinkModal(!disabledImportType.generateAiContent)"
                                                >
                                                    Description
                                                </label>
                                                <div class="form-group ml-3"
                                                     data-container="body"
                                                     data-toggle="hover"
                                                     data-placement="right"
                                                     data-content="Generate AI description for this product"
                                                     title="Generate AI description">
                                                    <input v-model="generateDescription" type="checkbox" style="cursor: pointer" :disabled="generatingDescription || !disabledImportType.generateAiContent">
                                                </div>
                                                <div class="edit-action">
                                                    <input
                                                            v-if="displayEditAllSecCheckbox"
                                                            @change="editScrap('description')"
                                                            v-model="editAllSecCheckbox.description"
                                                            type="checkbox"
                                                            class="edit-checkbox"
                                                    >

                                                    <template v-if="editLoading['description']">
                                                        <div class="edit-loading">
                                                            <div class="sk-three-bounce">
                                                                <div class="sk-child sk-bounce1"></div>
                                                                <div class="sk-child sk-bounce2"></div>
                                                                <div class="sk-child sk-bounce3"></div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                            <ckeditor
                                                    :editor="editor"
                                                    v-model="description"
                                            >
                                            </ckeditor>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    Currency Convertor
                                </h4>
                                <div class="help-block" @click="openHelpModal('currency_converter')">
                                    <i class="fa fa-question-circle"></i>
                                    <div class="pulse-css"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="basic-form">
                                    <div class="form-group col-md-12">
                                        <label for="currencyConvertor"
                                               data-container="body"
                                               data-toggle="hover"
                                               data-placement="right"
                                               data-content="Enable currency convertor"
                                               title="Enable currency convertor"
                                               class="mr-2"
                                        >Enable currency converter</label>
                                        <input id="currencyConvertor"
                                               data-container="body"
                                               data-toggle="hover"
                                               data-placement="right"
                                               data-content="Enable currency convertor"
                                               title="Enable currency convertor"
                                               @click="enableCurrencyConvertor"
                                               v-model="currencyConvertorEnabled"
                                               type="checkbox"
                                        >

                                    </div>
                                    <div class="col-md-12 p-0 d-flex flex-wrap" v-if="currencyConvertorEnabled && disabledImportType.product_currency_convertor">
                                        <div class="form-group col-md-4 pl-0">
                                            <label for="supplierCurrency"
                                                   data-container="body"
                                                   data-toggle="hover"
                                                   data-placement="right"
                                                   data-content="Supplier currency"
                                                   title="Supplier currency"
                                            >Supplier currency</label>
                                            <select
                                                    @change="changeCurrency"
                                                    name=""
                                                    id="supplierCurrency"
                                                    v-model="supplierCurrency"
                                                    class="form-control form-control-md">
                                                <option :selected="supplierCurrency === currency.id" :value="currency.id" v-for="(currency, index) in currencies">
                                                    {{currency.code}} ({{currency.name}})
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="shopifyCurrency"
                                                   data-container="body"
                                                   data-toggle="hover"
                                                   data-placement="right"
                                                   data-content="Supplier currency"
                                                   title="Supplier currency"
                                            >Shopify currency</label>
                                            <select
                                                    @change="changeCurrency"
                                                    name=""
                                                    id="shopifyCurrency"
                                                    v-model="shopifyCurrency"
                                                    class="form-control form-control-md-4">
                                                <option :selected="supplierCurrency === currency.id" :value="currency.id" v-for="(currency, index) in currencies">
                                                    {{currency.code}} ({{currency.name}})
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label for="currencyRate"
                                                   data-container="body"
                                                   data-toggle="hover"
                                                   data-placement="right"
                                                   data-content="Supplier currency"
                                                   title="Supplier currency"
                                            >Currency rate</label>
                                            <input type="number" class="form-control form-control-md-4 cursor-pointer" id="currencyRate" v-model="currencyRate">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Regular price markup</h4>
                                <div class="help-block" @click="openHelpModal('reg_price_markup')">
                                    <i class="fa fa-question-circle"></i>
                                    <div class="pulse-css"></div>
                                </div>
                                <div class="help-block"
                                     @click="openHelpModal('training_video_<?= Video::IMPORT_VIDEO_PRICE ?>')">
                                    <i class="fa fa-play"></i>
                                    <div class="pulse-css"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div v-if="minPriceMarkup !== null">
                                    <p><i>Applied pricing rule "{{minPriceMarkup}} - {{maxPriceMarkup}}"
                                        </i></p>
                                </div>
                                <div v-else>
                                    <p><i>Pricing rule was not applied you can create custom pricing rules from <a href="<?= URL::toRoute('profile/settings') ?>" target="_blank"> <b>settings</b></a>
                                        </i></p>
                                </div>
                                <div class="basic-form">
                                    <form>
                                        <div class="form-row">
                                            <div class="form-group col-md-3">
                                                <div class="form-group-header" >
                                                    <label
                                                            data-container="body"
                                                            data-toggle="hover"
                                                            data-placement="right"
                                                            data-content="<?= HelpTextHelper::getHelpText('price','text') ?>"
                                                            title="<?= HelpTextHelper::getHelpText('price','title') ?>"
                                                    >
                                                        Price
                                                    </label>
                                                    <div class="edit-action">
                                                        <input
                                                                v-if="displayEditAllSecCheckbox"
                                                                @change="editScrap('price')"
                                                                v-model="editAllSecCheckbox.price"
                                                                type="checkbox"
                                                                class="edit-checkbox"
                                                        />

                                                        <template v-if="editLoading['price']">
                                                            <div class="edit-loading">
                                                                <div class="sk-three-bounce">
                                                                    <div class="sk-child sk-bounce1"></div>
                                                                    <div class="sk-child sk-bounce2"></div>
                                                                    <div class="sk-child sk-bounce3"></div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                                <input
                                                        type="number"
                                                        class="form-control form-control-md"
                                                        placeholder="price"
                                                        v-model.number="price"
                                                        :disabled="priceReadonly"
                                                >
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('final_price','text') ?>"
                                                        title="<?= HelpTextHelper::getHelpText('final_price','title') ?>"
                                                >
                                                    Final price
                                                </label>

                                                <input
                                                        type="number"
                                                        class="form-control form-control-md"
                                                        placeholder="final price"
                                                        v-model.number="finalPrice"
                                                >
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('price_policy','text') ?>"
                                                        title="<?= HelpTextHelper::getHelpText('price_policy','title')  ?>"
                                                >
                                                    Price Policy
                                                </label>
                                                <select v-model="priceSelected" class="form-control form-control-md">
                                                    <option value="0"> By Percent</option>
                                                    <option value="1">By Amount</option>
                                                </select>
                                            </div>
                                            <div :style="{display:checkperCent}" class="form-group col-md-3">
                                                <div class="form-group-header" >
                                                    <label
                                                            data-container="body"
                                                            data-toggle="hover"
                                                            data-placement="right"
                                                            data-content="<?= HelpTextHelper::getHelpText('percent','text')  ?>"
                                                            title="<?= HelpTextHelper::getHelpText('percent','title') ?>"
                                                    >
                                                        Percent
                                                    </label>
                                                    <div class="edit-action">
                                                        <input
                                                                type="checkbox"
                                                                v-if="displayEditAllSecCheckbox"
                                                                @change="editScrap('priceByPercent')"
                                                                v-model="editAllSecCheckbox.priceByPercent"
                                                                class="edit-checkbox"
                                                        />

                                                        <template v-if="editLoading['priceByPercent']">
                                                            <div class="edit-loading">
                                                                <div class="sk-three-bounce">
                                                                    <div class="sk-child sk-bounce1"></div>
                                                                    <div class="sk-child sk-bounce2"></div>
                                                                    <div class="sk-child sk-bounce3"></div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                                <input
                                                        type="number"
                                                        class="form-control form-control-md"
                                                        v-model.number="priceByPercent"
                                                >
                                            </div>
                                            <div :style="{display:checkByAmount}" class="form-group col-md-3">
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('by_amount','text')  ?>"
                                                        title="<?= HelpTextHelper::getHelpText('by_amount','title') ?>"
                                                >
                                                    By Amount
                                                </label>
                                                <input
                                                        type="checkbox"
                                                        v-if="displayEditAllSecCheckbox"
                                                        @change="editScrap('priceByAmount')"
                                                        v-model="editAllSecCheckbox.priceByAmount"
                                                        class="edit-checkbox"
                                                />
                                                <input
                                                        type="number"
                                                        class="form-control form-control-md"
                                                        v-model.number="priceByAmount"
                                                >
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Compare at price markup</h4>
                                <div class="help-block" @click="openHelpModal('comp_price_markup')">
                                    <i class="fa fa-question-circle"></i>
                                    <div class="pulse-css"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="basic-form">
                                    <form>
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('final_compare_at_price','text') ?>"
                                                        title="<?= HelpTextHelper::getHelpText('final_compare_at_price','title') ?>"
                                                >
                                                    Final compare At Price
                                                </label>
                                                <input
                                                        type="number"
                                                        class="form-control form-control-md"
                                                        placeholder="final compare at price"
                                                        v-model.number="finalCompareAtPrice"
                                                >
                                            </div>
                                            <div class="form-group col-md-4">
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('compare_at_price_policy','text') ?>"
                                                        title="<?= HelpTextHelper::getHelpText('compare_at_price_policy','title') ?>"
                                                >
                                                    Compare At Price Policy
                                                </label>
                                                <select v-model="compareAtPriceSelected" class="form-control form-control-md">
                                                    <option value="0"> By Percent</option>
                                                    <option value="1">By Amount</option>
                                                </select>
                                            </div>
                                            <div :style="{display:checkCompareAtPricePerCent}" class="form-group col-md-4">
                                                <div class="form-group-header" >
                                                    <label
                                                            data-container="body"
                                                            data-toggle="hover"
                                                            data-placement="right"
                                                            data-content="<?= HelpTextHelper::getHelpText('percent','text') ?>"
                                                            title="<?= HelpTextHelper::getHelpText('percent','title')?>"
                                                    >
                                                        Percent
                                                    </label>

                                                    <div class="edit-action">
                                                        <input
                                                                type="checkbox"
                                                                v-if="displayEditAllSecCheckbox"
                                                                @change="editScrap('compareAtPriceByPercent')"
                                                                v-model="editAllSecCheckbox.compareAtPriceByPercent"
                                                                class="edit-checkbox"
                                                        />

                                                        <template v-if="editLoading['compareAtPriceByPercent']">
                                                            <div class="edit-loading">
                                                                <div class="sk-three-bounce">
                                                                    <div class="sk-child sk-bounce1"></div>
                                                                    <div class="sk-child sk-bounce2"></div>
                                                                    <div class="sk-child sk-bounce3"></div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>

                                                <input
                                                        type="number"
                                                        class="form-control form-control-md"
                                                        v-model.number="compareAtPriceByPercent"
                                                >
                                            </div>
                                            <div :style="{display:checkCompareAtPriceByAmount}" class="form-group col-md-4">
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('by_amount','text') ?>"
                                                        title="<?= HelpTextHelper::getHelpText('by_amount','title') ?>"
                                                >
                                                    By Amount
                                                </label>
                                                <input
                                                        type="checkbox"
                                                        v-if="displayEditAllSecCheckbox"
                                                        @change="editScrap('compareAtPriceByAmount')"
                                                        v-model="editAllSecCheckbox.compareAtPriceByAmount"
                                                        class="edit-checkbox"
                                                />
                                                <input
                                                        type="number"
                                                        class="form-control form-control-md"
                                                        v-model.number="compareAtPriceByAmount"
                                                >
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Product additional details</h4>
                                <div class="help-block" @click="openHelpModal('product_additional_details')">
                                    <i class="fa fa-question-circle"></i>
                                    <div class="pulse-css"></div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="basic-form">
                                    <form>
                                        <div class="form-row">
                                            <div class="form-group col-md-12">
                                                <div class="form-group-header">
                                                    <label
                                                            data-container="body"
                                                            data-toggle="hover"
                                                            data-placement="right"
                                                            data-content="<?= HelpTextHelper::getHelpText('brand','text')?>"
                                                            title="<?=  HelpTextHelper::getHelpText('brand','title')?>"
                                                    >
                                                        Brand
                                                    </label>

                                                    <div class="edit-action">
                                                        <input
                                                                type="checkbox"
                                                                v-if="displayEditAllSecCheckbox"
                                                                @change="editScrap('brand')"
                                                                v-model="editAllSecCheckbox.brand"
                                                                class="edit-checkbox"
                                                        />

                                                        <template v-if="editLoading['brand']">
                                                            <div class="edit-loading">
                                                                <div class="sk-three-bounce">
                                                                    <div class="sk-child sk-bounce1"></div>
                                                                    <div class="sk-child sk-bounce2"></div>
                                                                    <div class="sk-child sk-bounce3"></div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                                <input
                                                        type="text"
                                                        class="form-control form-control-md"
                                                        placeholder="brand"
                                                        v-model="brand"
                                                >
                                            </div>
                                            <div class="form-group col-md-6">
                                                <div class="form-group-header">
                                                    <label
                                                            data-container="body"
                                                            data-toggle="hover"
                                                            data-placement="right"
                                                            data-content="<?=  HelpTextHelper::getHelpText('weight','text') ?>"
                                                            title="<?= HelpTextHelper::getHelpText('weight','title') ?>"
                                                    >
                                                        Weight
                                                    </label>
                                                    <div class="edit-action">
                                                        <input
                                                                type="checkbox"
                                                                v-if="displayEditAllSecCheckbox"
                                                                @change="editScrap('weight')"
                                                                v-model="editAllSecCheckbox.weight"
                                                                class="edit-checkbox"
                                                        />
                                                        <template v-if="editLoading['weight']">
                                                            <div class="edit-loading">
                                                                <div class="sk-three-bounce">
                                                                    <div class="sk-child sk-bounce1"></div>
                                                                    <div class="sk-child sk-bounce2"></div>
                                                                    <div class="sk-child sk-bounce3"></div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>

                                                <input
                                                        type="number"
                                                        class="form-control form-control-md"
                                                        placeholder="weight"
                                                        v-model.number="weight"
                                                >
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('weight_type','text') ?>"
                                                        title="<?= HelpTextHelper::getHelpText('weight_type','title') ?>"
                                                >
                                                    Weight type
                                                </label>
                                                <select v-model="selectedWeightUnit" class="form-control form-control-md">
                                                    <option></option>
                                                    <option
                                                            v-for="unit in weightUnitForHtml"
                                                            :value="unit"
                                                    >
                                                        {{unit}}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 variants">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Variants</h4>
                                <div class="help-block" @click="openHelpModal('variants')">
                                    <i class="fa fa-question-circle"></i>
                                    <div class="pulse-css"></div>
                                </div>
                                <div class="help-block"
                                     @click="openHelpModal('training_video_<?= Video::IMPORT_VIDEO_VARIANTS ?>')">
                                    <i class="fa fa-play"></i>
                                    <div class="pulse-css"></div>
                                </div>
                                <div class="edit-action">
                                    <input
                                            v-if="displayEditAllSecCheckbox"
                                            @change="editScrap('allOptions')"
                                            v-model="editAllSecCheckbox.allOptions"
                                            type="checkbox"
                                            class="edit-checkbox"
                                    >
                                    <template v-if="editLoading['allOptions']">
                                        <div class="edit-loading">
                                            <div class="sk-three-bounce">
                                                <div class="sk-child sk-bounce1"></div>
                                                <div class="sk-child sk-bounce2"></div>
                                                <div class="sk-child sk-bounce3"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="basic-form">
                                    <form>
                                        <div class="form-group">
                                            <label  class="radio-inline mr-3">
                                                <input
                                                        value="0"
                                                        v-model.number="radioSelectAllOptions"
                                                        type="radio"
                                                        name="variantsOption"
                                                        id="allVariants"
                                                >
                                                <span class="checkmark"></span>
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('variants_hover','text') ?>"
                                                        title="<?= HelpTextHelper::getHelpText('variants_hover','title') ?>"
                                                        for="allVariants"
                                                >
                                                    All variants
                                                </label>
                                            </label>
                                            <label  class="radio-inline mr-3">
                                                <input
                                                        value="1"
                                                        v-model.number="radioSelectAllOptions"
                                                        type="radio"
                                                        name="variantsOption"
                                                        id="selectVariants"
                                                >
                                                <span class="checkmark"></span>
                                                <label
                                                        data-container="body"
                                                        data-toggle="hover"
                                                        data-placement="right"
                                                        data-content="<?= HelpTextHelper::getHelpText('select_variants','text')?>"
                                                        title="<?= HelpTextHelper::getHelpText('select_variants','title') ?>"
                                                        for="selectVariants"
                                                >
                                                    Select variants
                                                </label>
                                            </label>
                                        </div>
                                    </form>
                                </div>
                                <template>
                                    <div  :style="{display:displayVariationList}" class="table-responsive" >
                                        <table v-if="allOptions.length>1" class="table table-responsive-md">
                                            <thead>
                                            <tr v-for="(tr,index) in allOptions" v-if="index<1">
                                                <th></th>
                                                <th>
                                                    <label
                                                            data-container="body"
                                                            data-toggle="hover"
                                                            data-placement="right"
                                                            data-content="<?= HelpTextHelper::getHelpText('check_uncheck','text')?>"
                                                            title="<?= HelpTextHelper::getHelpText('check_uncheck','title')?>"
                                                            class="check-all-variants"
                                                    >
                                                        <input v-model="allCheckedOrUncheckedVariations" type="checkbox" >
                                                    </label>
                                                </th>
                                                <th v-for="(td,index) in tr" @click="showSubscribeLinkModal(td.name in disabledImportType ? !disabledImportType[td.name] : !disabledImportType.forVariants)">
                                                    <div class="variant-th">
                                                        <label
                                                                data-container="body"
                                                                data-toggle="hover"
                                                                data-placement="right"
                                                                :data-content=tooltipObject[td.name]?tooltipObject[td.name]:tooltipObject['forVariants']
                                                                :title="td.name=== 'CompareAtPrice' ? 'Compare At Price' : td.name"
                                                        >
                                                            <div class="variant-th-checkbox" v-if="td.name !== 'CompareAtPrice'">

                                                                <input v-if="td.name === 'Quantity'"
                                                                       @change="changeReadonlyQuantity"
                                                                       type="checkbox"
                                                                       class="form-check-input"
                                                                       :value=index
                                                                       v-model="tdDisabledArr"
                                                                       :disabled="td.name in disabledImportType ? !disabledImportType[td.name] : !disabledImportType.forVariants"
                                                                >
                                                                <input v-else
                                                                       type="checkbox"
                                                                       class="form-check-input"
                                                                       :value=index
                                                                       v-model="tdDisabledArr"
                                                                       :disabled="td.name in disabledImportType ? !disabledImportType[td.name] : !disabledImportType.forVariants"
                                                                >

                                                            </div>
                                                            <div class="mt-1">{{td.name=== 'CompareAtPrice' ? 'Compare At Price' : td.name}}</div>
                                                        </label>
                                                    </div>
                                                </th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-for="(tr,index) in allOptions" v-if="index>0">
                                                <td class="td-number">{{index}}</td>
                                                <td class="td-checkbox">
                                                    <input
                                                            type="checkbox"
                                                            :value="index"
                                                            :disabled="!chooseVariationsItems.includes(index) && chooseVariationsItems.length>=numberMaxVariant ?true:false"
                                                            v-model="chooseVariationsItems"
                                                    >
                                                </td>
                                                <td v-for="(td,trIndex) in tr.slice(0,tr.length-1)" :class="allOptions[0][trIndex].name">
                                                    <template v-if="allOptions[0][trIndex].name === 'Price'">
                                                        <input
                                                                type="text"
                                                                class="variant-input form-control form-control-md h-100"
                                                                :disabled="tdDisabled(trIndex,'price')"
                                                                :change="changeVariantsPrice(index,trIndex,td.name)"
                                                                v-if="td.input"
                                                                v-model="allOptions[index][trIndex].name"
                                                        >
                                                    </template>
                                                    <template v-else-if="allOptions[0][trIndex].name === 'CompareAtPrice'">
                                                        <input
                                                                type="text"
                                                                class="variant-input form-control form-control-md h-100 w-75 ml-3"
                                                                :disabled="tdDisabled(trIndex, 'CompareAtPrice')"
                                                                v-if="td.input"
                                                                v-model="allOptions[index][trIndex].name"
                                                        >
                                                    </template>
                                                    <template v-else-if="allOptions[0][trIndex].name === 'Quantity'">
                                                        <input
                                                                type="number"
                                                                class="variant-input form-control form-control-md  h-100"
                                                                :disabled="tdDisabled(trIndex, 'quantity')"
                                                                @input="changeVariationsName(allOptions[0][trIndex].name,td.name,index,trIndex,event)"
                                                                :value="allOptions[index][trIndex].name"
                                                        >
                                                    </template>
                                                    <template v-else>
                                                        <img
                                                                class="variant_img"
                                                                :style="{opacity:tdDisabled(trIndex,1)}"
                                                                @click="tdDisabled(trIndex,1) === 0.2 ?'': preferImage(td.name,index,trIndex)"
                                                                v-if="td.type=='img'"
                                                                :src="td.name"
                                                                @load="variantImageLoaded"
                                                        >
                                                        <div
                                                                v-if="td.type=='img'"
                                                                :style="{display:displayVariantImageLoad}"
                                                                class="lds-spinner loading_small"
                                                        >
                                                            <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>

                                                        </div>
                                                        <input
                                                                type="text"
                                                                class="variant-input form-control form-control-md  h-100"
                                                                :disabled="tdDisabled(trIndex)"
                                                                @input="changeVariationsName(allOptions[0][trIndex].name,td.name,index,trIndex,event)"
                                                                v-else-if="td.input"
                                                                :value="allOptions[index][trIndex].name"
                                                                @blur="changeRepeatVariantName(allOptions[0][trIndex].name,trIndex, index, event)"
                                                                @focus="getFocusVariantInputValue(allOptions[0][trIndex].name,td.name,trIndex,event)"
                                                        >
                                                        <span v-else>{{td.name}}</span>
                                                    </template>
                                                </td>
                                                <td>
                                                    <template>
                                                        <label
                                                                @click="openVariantPriceMarkupModal(index)"
                                                                data-content="Set custom price markup for this variant"
                                                                title="Variant Price Markup"
                                                                data-container="body"
                                                                data-toggle="hover"
                                                                data-placement="right"
                                                        >
                                                            <i class="fa fa-usd" :class="isVariantPriceMarkupChanged(index)" aria-hidden="true"></i>
                                                        </label>
                                                    </template>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 images" v-if="images.length">
                        <div class="card">
                            <div class="card-header">
                                <label
                                        data-container="body"
                                        data-toggle="hover"
                                        data-placement="right"
                                        data-content="Following images will be uploaded to your Shopify store. This tool allows you to add new images by adding the images URL below."
                                        title="Images"
                                >
                                    <h4 class="card-title">Images</h4>
                                    <div class="help-block" @click="openHelpModal('images')">
                                        <i class="fa fa-question-circle"></i>
                                        <div class="pulse-css"></div>
                                    </div>
                                </label>
                                <div class="edit-action">
                                    <input
                                            v-if="displayEditAllSecCheckbox"
                                            @change="editScrap('images')"
                                            v-model="editAllSecCheckbox.images"
                                            type="checkbox"
                                            class="edit-checkbox"
                                    >

                                    <template v-if="editLoading['images']">
                                        <div class="edit-loading">
                                            <div class="sk-three-bounce">
                                                <div class="sk-child sk-bounce1"></div>
                                                <div class="sk-child sk-bounce2"></div>
                                                <div class="sk-child sk-bounce3"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="images-block">
                                    <div class="image-item" v-for="(img,index) in images" >
                                        <label :for="`image-checked${index}`">
                                            <img
                                                    :src="img"
                                                    class="page_img"
                                                    @load="pageImageLoaded"
                                            />
                                        </label>
                                        <div :style="{display:pageImageLoad}" class="lds-spinner loading_small"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                                        <input
                                                v-model="checkedImages"
                                                type="checkbox"
                                                :value="img"
                                                class="form-check-input image-checkbox"
                                                :id="`image-checked${index}`"
                                        >
                                    </div>
                                </div>
                                <div>
                                    <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm mt-5 ml-0"
                                            data-toggle="modal"
                                            data-target="#add-image-modal"
                                            @click="showAddImageModal()"
                                    >
                                        Add Image
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php  if (Yii::$app->params['enableReview']): ?>
                        <div class="col-lg-12 reviews" v-if="reviews.length">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Reviews</h4>
                                    <div class="help-block" @click="openHelpModal('reviews')">
                                        <i class="fa fa-question-circle"></i>
                                        <div class="pulse-css"></div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="basic-form">
                                        <form>
                                            <div class="form-group">
                                                <!--<label class="radio-inline mr-3">
                                                  <input
                                                    value="0"
                                                    v-model.number="radioSelectReviews"
                                                    type="radio"
                                                    name="radioReviews"
                                                    id="allReviews"
                                                  >
                                                    <span class="checkmark"></span>
                                                    <label
                                                    data-container="body"
                                                    data-toggle="hover"
                                                    data-placement="right"
                                                    data-content="This tool allow you automatically to choose and to import all available reviews to your store."
                                                    title="All reviews"
                                                    for="allReviews"
                                                  >
                                                    All reviews
                                                  </label>
                                                </label>-->
                                                <label class="radio-inline mr-3">
                                                    <input
                                                            value="1"
                                                            v-model.number="radioSelectReviews"
                                                            type="radio"
                                                            name="radioReviews"
                                                            id="selectReviews"
                                                    >
                                                    <span class="checkmark"></span>
                                                    <label
                                                            data-container="body"
                                                            data-toggle="hover"
                                                            data-placement="right"
                                                            data-content="By using this tool you can choose which review you would like to import in your Shopify store."
                                                            title="Select reviews"
                                                            for="selectReviews"
                                                    >
                                                        Select reviews
                                                    </label>
                                                </label>
                                            </div>
                                        </form>
                                    </div>
                                    <div :style="{display:displayReviewList}" class="table-responsive" >
                                        <table class="table table-responsive-md">
                                            <thead>
                                            <tr>
                                                <th>
                                                    <input
                                                            v-model="allCheckedOrUncheckedReviews"
                                                            type="checkbox"
                                                            :value="0"
                                                    >
                                                </th>
                                                <th>User name</th>
                                                <th>Images</th>
                                                <th>Feedback</th>
                                                <th>Date</th>
                                                <th>Star</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr v-if="reviews.length" v-for="(tr,index) in reviews">
                                                <td>
                                                    <input
                                                            type="checkbox"
                                                            :value="index"
                                                            v-model="chooseReviews"
                                                    >
                                                </td>
                                                <td>
                                                    <input
                                                            type="text"
                                                            class="form-control form-control-md"
                                                            :value="tr.name"
                                                            v-model="reviews[index].name"
                                                    >
                                                </td>
                                                <td>
                                                    <div class="reviews-images">
                                                        <img
                                                                v-for="img of reviews[index].reviewImages"
                                                                class="reviews_img"
                                                                :src="img"
                                                                @click="displayLightBox(img)"
                                                        />
                                                    </div>
                                                </td>
                                                <td>
                                                 <textarea
                                                         class="form-control"
                                                         rows="3"
                                                         v-model="reviews[index].feedback"
                                                         cols="60"
                                                 >
                                                     {{tr.feedback}}
                                                 </textarea>
                                                </td>
                                                <td>
                                                    <input
                                                            type="date"
                                                            v-model="reviews[index].date"
                                                            class="review-date-time"
                                                    >
                                                </td>
                                                <td>
                                                    <star-rating
                                                            star-size="22"
                                                            :show-rating="false"
                                                            :increment="0.5"
                                                            v-model.number="reviews[index].star">
                                                    </star-rating>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif ?>
                    <div class="col-lg-12 do-submit">
                        <button
                                type="button"
                                class="btn btn-primary mt-2"
                                @click="doSubmit({publish: 1, imported_from: <?= Product::IMPORTED_FROM_DASHBOARD ?>})"
                        >
                            Publish to Shopify
                        </button>
                        <button
                                type="button"
                                class="btn btn-primary mt-2"
                                @click="doSubmit({publish: 0, imported_from: <?= Product::IMPORTED_FROM_DASHBOARD ?>})"
                        >
                            Save for preview
                        </button>
                    </div>

                </div>
                <div id="preloader-loading" :style="{display: loading ? 'block' : 'none'}">
                    <div class="loading-message">
                        {{loadingMessageList[loadingMessage]}}
                    </div>
                    <div class="sk-three-bounce">
                        <div class="sk-child sk-bounce1"></div>
                        <div class="sk-child sk-bounce2"></div>
                        <div class="sk-child sk-bounce3"></div>
                    </div>
                </div>
            </div>

            <!-- Modal -->
            <template class="app-content">
                <div class = 'shop-modal'>
                    <div
                            class="modal fade bd-example-modal-lg text-center"
                            data-keyboard="false"
                            data-backdrop="static"
                            id="myModal"
                            role="dialog"
                    >
                        <div class="modal-dialog modal-lg shop-modal">
                            <!-- Modal content-->
                            <div class="modal-content">
                                <div v-if="loading">
                                    <div class="loading">
                                        <img src="/images/loading.gif">
                                    </div>
                                </div>
                                <template v-else >
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>

                                        <div class="header_logo">
                                            <div class="header_logo_first"></div>

                                            <div class="header_logo_second">
                                                <p class="modal-title">Import this product to your shopify store</p>
                                            </div>
                                        </div>
                                        <div class="header-text">
                                            <p>
                                                Bellow you see the form with all available fields for product which you want
                                                import to shopify. You can edit all available fields and values using this form,
                                                and it will be saved on shopify. Check all fields and then click "Import" button,
                                                after that wait until loading will be finished, and check your product in shopify.
                                            </p>
                                        </div>

                                    </div>
                                    <div class="modal-body">
                                        <div >
                                            <div class="hasBorder">

                                                <div class="form-group ">
                                                    <div class="tooltip main-tooltip ">
                                                        <span class="tooltip_right_text">This will be your product title, you can edit it before import</span>
                                                        <label class="td-name">Title:</label>
                                                    </div>
                                                    <p-check
                                                            v-if="displayEditAllSecCheckbox"
                                                            @change="editScrap('title')"
                                                            class="p-switch p-outline"
                                                            color="silver"
                                                            v-model="editAllSecCheckbox.title">
                                                    </p-check>
                                                    <input
                                                            v-model="title"
                                                            type="text"
                                                            class="form-control inp_1"
                                                    >
                                                </div>
                                                <div class="row form-group price-container">
                                                    <div class="col-md-4">
                                                        <div class="form-group ">
                                                            <div class="tooltip main-tooltip ">
                                                                <span class="tooltip_right_text">The original price from source</span>
                                                                <label class="td-name">Price:</label>
                                                            </div>
                                                            <p-check
                                                                    v-if="displayEditAllSecCheckbox"
                                                                    @change="editScrap('price')"
                                                                    class="p-switch p-outline"
                                                                    color="silver"
                                                                    v-model="editAllSecCheckbox.price">
                                                            </p-check>

                                                            <input
                                                                    v-model.number="price"
                                                                    type="number"
                                                                    step="any"
                                                                    class="form-control inp_1"
                                                                    :disabled="priceReadonly"
                                                            >

                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group ">
                                                            <div class="tooltip main-tooltip ">
                                                                <span class="tooltip_right_text long_right_text">The price which will be saved on shopify, note that price could be different if you enabled different price for each variant</span>
                                                                <label class="td-name">Final price:</label>
                                                            </div>
                                                            <div class="loading-block">
                                                                <input
                                                                        v-model.number="finalPrice "
                                                                        type="number"
                                                                        step="any"
                                                                        class="form-control inp_1"
                                                                >
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group ">
                                                            <div class="tooltip main-tooltip ">
                                                        <span class="tooltip_right_text long_right_text">
                                                            This is the tool for helping of generating your dropshipping price,
                                                            using this you can automatically increase the price in shopify from source
                                                            price using calculations by fixed amount or by percentage
                                                        </span>
                                                                <label class="td-name">Price Policy</label>
                                                            </div>
                                                            <select class="form-control" v-model="priceSelected">
                                                                <option value="0"> By Percent</option>
                                                                <option value="1">By Amount</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div :style="{display:checkperCent}" class="form-group ">
                                                            <div class="tooltip main-tooltip ">
                                                                <span class="tooltip_right_text">Price will be added using this percentage</span>
                                                                <label class="td-name">Percent:</label>
                                                            </div>
                                                            <p-check
                                                                    v-if="displayEditAllSecCheckbox"
                                                                    @change="getEditInfoFromBaseClass('priceByPercent')"
                                                                    class="p-switch p-outline"
                                                                    color="silver"
                                                                    v-model="editAllSecCheckbox.priceByPercent">
                                                            </p-check>

                                                            <input
                                                                    v-model.number="priceByPercent"
                                                                    type="number"
                                                                    step="any"
                                                                    class="form-control inp_1"
                                                            >
                                                        </div>

                                                    </div>

                                                    <div class="col-md-4">
                                                        <div :style="{display:checkByAmount}" class="form-group ">
                                                            <div class="tooltip main-tooltip ">
                                                                <span class="tooltip_right_text">This amount will be added to original price</span>
                                                                <label class="td-name">By Amount:</label>
                                                            </div>
                                                            <p-check
                                                                    v-if="displayEditAllSecCheckbox"
                                                                    @change="getEditInfoFromBaseClass('priceByAmount')"
                                                                    class="p-switch p-outline"
                                                                    color="silver"
                                                                    v-model="editAllSecCheckbox.priceByAmount">
                                                            </p-check>

                                                            <input
                                                                    v-model.number="priceByAmount"
                                                                    type="number"
                                                                    step="any"
                                                                    class="form-control inp_1"
                                                            >
                                                        </div>

                                                    </div>

                                                </div>
                                                <div class="row form-group compare-at-price-container" id="compare_at_price">
                                                    <div class="col-md-4">
                                                        <div class="form-group ">
                                                            <div class="tooltip main-tooltip ">
                                                                <span class="tooltip_right_text long_right_text">The compare at price which will be saved on shopify, note that price could be different if you enabled different price for each variant</span>
                                                                <label class="td-name">Final compare At Price:</label>
                                                            </div>
                                                            <div class="loading-block">
                                                                <input
                                                                        v-model.number="finalCompareAtPrice"
                                                                        type="number"
                                                                        step="any"
                                                                        class="form-control inp_1"
                                                                >
                                                            </div>

                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="form-group ">
                                                            <div class="tooltip main-tooltip ">
                                                        <span class="tooltip_right_text long_right_text">
                                                            This is the tool for helping of generating your dropshipping price,
                                                            using this you can automatically increase the price in shopify from source
                                                            price using calculations by fixed amount or by percentage</span>
                                                                <label class="td-name">Compare At Price Policy</label>
                                                            </div>
                                                            <select class="form-control" v-model="compareAtPriceSelected">
                                                                <option value="0"> By Percent</option>
                                                                <option value="1">By Amount</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div :style="{display:checkCompareAtPricePerCent}" class="form-group ">
                                                            <div class="tooltip main-tooltip ">
                                                                <span class="tooltip_right_text">Price will be added using this percentage</span>
                                                                <label class="td-name">Percent:</label>
                                                            </div>
                                                            <p-check
                                                                    v-if="displayEditAllSecCheckbox"
                                                                    @change="getEditInfoFromBaseClass('compareAtPriceByPercent')"
                                                                    class="p-switch p-outline"
                                                                    color="silver"
                                                                    v-model="editAllSecCheckbox.compareAtPriceByPercent">
                                                            </p-check>

                                                            <input
                                                                    v-model.number="compareAtPriceByPercent"
                                                                    type="number"
                                                                    step="any"
                                                                    class="form-control inp_1"
                                                            >
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div :style="{display:checkCompareAtPriceByAmount}" class="form-group ">
                                                            <div class="tooltip main-tooltip ">
                                                                <span class="tooltip_right_text">This amount will be added to original price</span>
                                                                <label class="td-name">By Amount:</label>
                                                            </div>
                                                            <p-check
                                                                    v-if="displayEditAllSecCheckbox"
                                                                    @change="getEditInfoFromBaseClass('compareAtPriceByAmount')"
                                                                    class="p-switch p-outline"
                                                                    color="silver"
                                                                    v-model="editAllSecCheckbox.compareAtPriceByAmount">
                                                            </p-check>

                                                            <input
                                                                    v-model.number="compareAtPriceByAmount"
                                                                    type="number"
                                                                    step="any"
                                                                    class="form-control inp_1"
                                                            >
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class=" row form-group count_id">
                                                    <div class="product-id-container">
                                                        <div class="tooltip main-tooltip ">
                                                            <span class="tooltip_right_text long_right_text">This is unique id from source product, it will be used also as sku if you didn't enabled different sku for each variant</span>
                                                            <label class="td-name">Product Id:</label>
                                                        </div>
                                                        <input v-model="finalSKU" class="form-control">
                                                    </div>
                                                </div>
                                                <br>
                                                <div class="row form-group brand_weight">
                                                    <div>
                                                        <div class="tooltip main-tooltip ">
                                                            <span class="tooltip_right_text">The brand of product</span>
                                                            <label class="td-name">Brand:</label>
                                                        </div>
                                                        <p-check
                                                                v-if="displayEditAllSecCheckbox"
                                                                @change="editScrap('brand')"
                                                                class="p-switch p-outline"
                                                                color="silver"
                                                                v-model="editAllSecCheckbox.brand"
                                                        >
                                                        </p-check>
                                                        <input
                                                                v-model="brand"
                                                                type="text"
                                                                step="any"
                                                                class="form-control inp_1"
                                                        >
                                                    </div>
                                                    <div class="weight-container">
                                                        <div class="tooltip main-tooltip ">
                                                            <span class="tooltip_right_text">The weight of product</span>
                                                            <label class="td-name">Weight:</label>
                                                        </div>
                                                        <p-check
                                                                v-if="displayEditAllSecCheckbox"
                                                                @change="editScrap('weight')"
                                                                class="p-switch p-outline"
                                                                color="silver"
                                                                v-model="editAllSecCheckbox.weight">
                                                        </p-check>
                                                        <input
                                                                v-model.number="weight"
                                                                type="text"
                                                                step="any"
                                                                class="form-control inp_1"
                                                        >
                                                    </div>
                                                    <div>
                                                        <div class="tooltip main-tooltip ">
                                                            <span class="tooltip_right_text">The weight of product</span>
                                                            <label class="td-name">Weight type:</label>
                                                        </div>
                                                        <select v-model="selectedWeightUnit" class="form-control">
                                                            <option></option>
                                                            <option
                                                                    v-for="unit in weightUnitForHtml"
                                                                    :value="unit"
                                                            >
                                                                {{unit}}
                                                            </option>

                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="hasBorder">
                                            <div class="form-group description-block">
                                                <div class="tooltip main-tooltip ">
                                                    <span class="tooltip_right_text">Product description</span>
                                                    <label class="td-name">Description:</label>
                                                </div>
                                                <p-check
                                                        v-if="displayEditAllSecCheckbox"
                                                        @change="editScrap('description')"
                                                        class="p-switch p-outline"
                                                        color="silver"
                                                        v-model="editAllSecCheckbox.description">
                                                </p-check>
                                                <ckeditor
                                                        :editor="editor"
                                                        v-model="description"
                                                >
                                                </ckeditor>
                                            </div>
                                        </div>
                                        <div class="hasBorder">
                                            <div class="form-group ">
                                                <div class="tooltip main-tooltip ">
                                                    <span class="tooltip_right_text">Choose collection from your shopify store</span>
                                                    <label class="td-name">Collection</label>
                                                </div>
                                                <br><br>

                                                <select v-model="selectedCollection" class="form-control">
                                                    <option
                                                            v-for="collection of collectionsArray "
                                                            :value="collection.id"
                                                    >
                                                        {{collection.title}}
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div v-if="reviews.length" class="hasBorder">
                                            <div class="allReviewsList">
                                                <div class="row">
                                                    <div class="">
                                                        <ul class="review-variations-ul">
                                                            <li class="first-li">
                                                                <input
                                                                        value="0"
                                                                        v-model.number="radioSelectReviews"
                                                                        name="radioSelectReviews"
                                                                        type="radio"
                                                                        id="f-option"
                                                                >
                                                                <div class="tooltip main-tooltip ">
                                                                    <span class="tooltip_right_text different_checkbox">Import automatically all available reviews from first page</span>
                                                                    <label for="f-option">All reviews</label>
                                                                </div>
                                                                <div class="check"></div>
                                                            </li>

                                                            <li class="second-li">
                                                                <input
                                                                        value="1"
                                                                        v-model.number="radioSelectReviews"
                                                                        name="radioSelectReviews"
                                                                        type="radio"
                                                                        id="s-option">

                                                                <div class="tooltip main-tooltip ">
                                                                    <span class="tooltip_right_text different_checkbox">Select which reviews you want import</span>
                                                                    <label for="s-option">Select reviews</label>
                                                                </div>
                                                                <div class="check">
                                                                    <div class="inside"></div>
                                                                </div>
                                                            </li>

                                                        </ul>
                                                    </div>
                                                </div>
                                                <div :style="{display:displayReviewList}" class="form-group reviews_table_div">
                                                    <table id="reviews" class="table">
                                                        <tr>

                                                            <th>
                                                                <p-check
                                                                        class="p-switch p-outline"
                                                                        :value="0"
                                                                        color="silver"
                                                                        v-model="allCheckedOrUncheckedReviews">
                                                                </p-check>
                                                            </th>
                                                            <th class="td-name">User name</th>
                                                            <th class="td-name">Images</th>
                                                            <th class="td-name">Feedback</th>
                                                            <th class="td-name">Date</th>
                                                            <th class="td-name">Star</th>
                                                        </tr>
                                                        <template>
                                                            <tr v-if="reviews.length" v-for="(tr,index) in reviews">
                                                                <td>
                                                                    <p-check
                                                                            name="check"
                                                                            class="p-switch p-outline"
                                                                            :value="index"
                                                                            color="silver"
                                                                            v-model="chooseReviews">
                                                                    </p-check>
                                                                </td>
                                                                <td>
                                                                    <input type="text" v-model="reviews[index].name" :value="tr.name">
                                                                </td>
                                                                <td>
                                                                    <div class="reviews_images">
                                                                        <img
                                                                                v-for="img of reviews[index].reviewImages"
                                                                                class="reviews_img"
                                                                                :src="img"
                                                                                @click="displayLightBox(img)"
                                                                        />
                                                                    </div>
                                                                </td>
                                                                <td>
                                                         <textarea
                                                                 rows="3"
                                                                 v-model="reviews[index].feedback"
                                                                 cols="40">{{tr.feedback}}
                                                         </textarea>
                                                                </td>
                                                                <td>
                                                                    <date-picker
                                                                            v-model="reviews[index].date"
                                                                            :custom-formatter="formatDate"
                                                                            type="datetime"
                                                                            width="200"
                                                                            format="YYYY-MM-DD HH:mm"
                                                                            :lang="'en'"
                                                                            placeholder="Select Date Time">

                                                                    </date-picker>
                                                                </td>
                                                                <td>
                                                                    <star-rating
                                                                            star-size="22"
                                                                            :show-rating="false"
                                                                            :increment="0.5"
                                                                            v-model.number="reviews[index].star">
                                                                    </star-rating>
                                                                </td>
                                                            </tr>
                                                        </template>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="hasBorder " id="images">
                                            <div class="tooltip main-tooltip ">
                                                <span class="tooltip_right_text long_right_text">These images will be uploaded to your shopify store and will be available for this product, you can also add new images by adding their urls</span>
                                                <label class="td-name">Images</label>
                                            </div>
                                            <p-check
                                                    v-if="displayEditAllSecCheckbox"
                                                    @change="editScrap('images')"
                                                    class="p-switch p-outline"
                                                    color="silver"
                                                    v-model="editAllSecCheckbox.images">
                                            </p-check>


                                            <div id="allimg">
                                                <div v-if="images.length" v-for="img of images">
                                                    <img
                                                            :src="img"
                                                            class="page_img"
                                                            @load="pageImageLoaded"
                                                    />
                                                    <div :style="{display:pageImageLoad}" class="lds-spinner loading_small"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>
                                                    <br/>
                                                    <p-check
                                                            class="p-switch p-outline"
                                                            :value="img"
                                                            color="silver"
                                                            v-model="checkedImages">
                                                    </p-check>
                                                </div>

                                                <div>
                                                    <button @click="showAddImageModal()">Add image</button>
                                                </div>
                                            </div>

                                        </div>

                                        <div v-if="displayMaxVariantMessage" class="max-variant">
                                            <div class="max-variant-message">
                                                This product has more than 100 variants, in shopify you can import max 100 variants!
                                            </div>
                                        </div>

                                        <div v-if="thereIsVariants" class="form-group hasBorder ">
                                            <div class="row">
                                                <p-check
                                                        v-if="displayEditAllSecCheckbox"
                                                        @change="editScrap('allOptions')"
                                                        class="p-switch p-outline variant-switch"
                                                        color="silver"
                                                        v-model="editAllSecCheckbox.allOptions"
                                                >
                                                </p-check>

                                                <ul class="variant-variations-ul">
                                                    <li class="first-li">
                                                        <input
                                                                value="0"
                                                                v-model.number="radioSelectAllOptions"
                                                                type="radio"
                                                                id="f-option2"
                                                        >
                                                        <div class="tooltip main-tooltip ">
                                                            <span class="tooltip_right_text long_right_text different_checkbox">Import automatically all available variants, note that max count of variants is 100, so the rest will be skipped</span>
                                                            <label for="f-option2">All variants</label>
                                                        </div>
                                                        <div class="check"></div>
                                                    </li>

                                                    <li class="second-li">
                                                        <input
                                                                value="1"
                                                                v-model.number="radioSelectAllOptions"
                                                                type="radio"
                                                                id="s-option2"
                                                        >
                                                        <div class="tooltip main-tooltip ">
                                                            <span class="tooltip_right_text different_checkbox">Choose which variants you want import to your shopify store</span>
                                                            <label for="s-option2">Select variants</label>
                                                        </div>
                                                        <div class="check">
                                                            <div class="inside"></div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div :style="{display:displayVariationList}"
                                                 id="table_untitle"
                                                 class="form-group all_options_table_div">
                                                <table v-if="allOptions.length>1" class="table " id="all_options">

                                                    <tr v-for="(tr,index) in allOptions" v-if="index<1">
                                                        <th></th>
                                                        <th>
                                                            <div class="tooltip">
                                                                <span class="tooltip_right_text">Check / uncheck all variants</span>
                                                                <p-check
                                                                        data-placement="top"
                                                                        class="p-switch p-outline tooltip"
                                                                        color="silver"
                                                                        v-model="allCheckedOrUncheckedVariations"
                                                                >

                                                                </p-check>
                                                            </div>

                                                        </th>
                                                        <th v-for="(td,index) in tr.slice(0,-1)"
                                                            :style="[td.name.length>10 ? {'min-width':'200px'} : {'min-width':'120px'}]" @click="showSubscribeLinkModal(td.name in disabledImportType ? !disabledImportType[td.name] : !disabledImportType.forVariants)">
                                                            <div class="tooltip">

                                                                <span class="td-name">{{td.name=== 'CompareAtPrice' ? 'Compare At Price' : td.name}}</span>
                                                                <span class="tooltip_bottom_text">{{tooltipObject[td.name]?tooltipObject[td.name]:tooltipObject.forVariants}}</span>
                                                                <input type="checkbox"
                                                                       class="p-switch p-outline gago"
                                                                       :value="index"
                                                                       color="silver"
                                                                       :disabled="td.name in disabledImportType ? !disabledImportType[td.name] : !disabledImportType.forVariants"
                                                                       v-model="tdDisabledArr"
                                                                >
                                                            </div>
                                                        </th>
                                                    </tr>

                                                    <tr v-for="(tr,index) in allOptions" v-if="index>0">
                                                        <td>{{index}}</td>
                                                        <td style="min-width: 80px">
                                                            <p-check
                                                                    :disabled="!chooseVariationsItems.includes(index) && chooseVariationsItems.length>=numberMaxVariant ?true:false"
                                                                    class="p-switch p-outline"
                                                                    :value="index"
                                                                    color="silver"
                                                                    v-model="chooseVariationsItems">
                                                            </p-check>

                                                        </td>

                                                        <td v-for="(td,trIndex) in tr.slice(0,-1)">
                                                            <template v-if="allOptions[0][trIndex].name === 'Price'">
                                                                <input
                                                                        type="text"
                                                                        class="optionsInput"
                                                                        :disabled="tdDisabled(trIndex,'price')"
                                                                        :change="changeVariantsPrice(index,trIndex,td.name)"
                                                                        v-if="td.input"
                                                                        v-model="allOptions[index][trIndex].name"
                                                                >
                                                            </template>
                                                            <template v-else-if="allOptions[0][trIndex].name === 'CompareAtPrice'">
                                                                <input
                                                                        type="text"
                                                                        class="optionsInput"
                                                                        :disabled="tdDisabled(trIndex)"
                                                                        v-if="td.input"
                                                                        v-model="allOptions[index][trIndex].name"
                                                                >
                                                            </template>
                                                            <template v-else>
                                                                <img
                                                                        class="variant_img"
                                                                        :style="{opacity:tdDisabled(trIndex,1)}"
                                                                        @click="tdDisabled(trIndex,1) === 0.2 ?'': preferImage(td.name,index,trIndex)"
                                                                        v-if="td.type=='img'"
                                                                        :src="td.name"
                                                                        @load="variantImageLoaded"
                                                                >
                                                                <div
                                                                        v-if="td.type=='img'"
                                                                        :style="{display:displayVariantImageLoad}"
                                                                        class="lds-spinner loading_small"
                                                                >
                                                                    <div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>

                                                                </div>
                                                                <input
                                                                        type="text"
                                                                        class="optionsInput"
                                                                        :disabled="tdDisabled(trIndex)"
                                                                        @input="changeVariationsName(allOptions[0][trIndex].name,td.name,index,trIndex,event)"
                                                                        v-else-if="td.input"
                                                                        :value="allOptions[index][trIndex].name"
                                                                        @blur="changeRepeatVariantName(allOptions[0][trIndex].name,trIndex)"
                                                                        @focus="getFocusVariantInputValue(allOptions[0][trIndex].name,event)"
                                                                >

                                                                <span v-else>{{td.name}}</span>
                                                            </template>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div style="text-align: center;" class="form-group">
                                            <button @click="doSubmit" class="btn btn-danger" id="submit">
                                                Save product in shopify
                                            </button>
                                        </div>
                                        <br>
                                        <button id="modal_hide" type="button" class="btn btn-primary" data-dismiss="modal">Close
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- START preferImageModal -->
                <div class="modal fade" id="prefer-image-modal">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Choose Image</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <template v-for="src of allImagesMyModal">
                                    <img class="small-modal-image" @click="closePreferImageModal(src)" :src="src"/>
                                </template>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END preferImageModal -->

                <!-- START AddImageModal -->


                <div class="modal fade" id="add-image-modal">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add Image</h5>
                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <span>Please insert image url</span>
                                <br><br>
                                <input
                                        @focus = "displayMassageNotValidImage = 'none'"
                                        class = "form-control form-control-md"
                                        v-model = "addImageSrc"
                                >
                                <br>
                                <span class="warning-message-img-src" :style="{display:displayMassageNotValidImage}">Image source is not valid</span><br>
                                <button class="form-control btn-sm" @click="addImageFromModal()">Add</button>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END AddImageModal -->

                <!-- START lightBoxModal -->
                <div :style="{display:displayLightBoxDiv}">
                    <div class="light_box">
                        <div class="light_box_img_block">
                            <span @click="closeLightBox" class="light_box_close">&times;</span>
                            <img class="light_box_img" :src="lightBoxImgSrc">
                        </div>
                        <div id="light_box_caption"></div>
                    </div>

                </div>
                <!-- END lightBoxModal -->

                <div id="pageInfoModals" class="modal" role="dialog">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Hey!</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="message">
                                    {{pageInfoModalBody}}
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- HELP MODAL START  -->
                <div id="helpModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                        <span v-if="helpMessageKey === 'import_product'">
                            <?=HelpTextHelper::getHelpText('import_new_product','title')?>
                        </span>
                                    <span v-else-if="helpMessageKey === 'reg_price_markup'">
                            <?=HelpTextHelper::getHelpText('regular_price_markup','title')?>
                        </span>
                                    <span v-else-if="helpMessageKey === 'comp_price_markup'">
                            <?=HelpTextHelper::getHelpText('comp_price_markup','title')?>
                        </span>
                                    <span v-else-if="helpMessageKey === 'product_additional_details'">
                            <?=HelpTextHelper::getHelpText('product_additional_details','title')?>
                        </span>
                                    <span v-else-if="helpMessageKey === 'variants'">
                            <?=HelpTextHelper::getHelpText('variants','title')?>
                        </span>
                                    <span v-else-if="helpMessageKey === 'images'">
                            <?=HelpTextHelper::getHelpText('images','title')?>
                        </span>
                                    <span v-else-if="helpMessageKey === 'reviews'">
                            <?=HelpTextHelper::getHelpText('reviews','title')?>
                        </span>
                                    <span v-else-if="helpMessageKey === 'currency_converter'">
                            <?=HelpTextHelper::getHelpText('currency_converter','title') ?? 'Currency Convertor'?>
                        </span>
                                    <?php foreach ($trainingVideos as $video): ?>
                                        <span v-else-if="helpMessageKey === 'training_video_<?= $video->id ?>'">
                                <?= $video->title ?>
                            </span>
                                    <?php endforeach; ?>

                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div v-if="helpMessageKey === 'import_product'">
                                    <?=HelpTextHelper::getHelpText('import_new_product','text')?>
                                </div>
                                <div v-else-if="helpMessageKey === 'product_details'">
                                    <?=HelpTextHelper::getHelpText('product_details','text')?>
                                </div>
                                <div v-else-if="helpMessageKey === 'reg_price_markup'">
                                    <?=HelpTextHelper::getHelpText('regular_price_markup','text')?>
                                </div>
                                <div v-else-if="helpMessageKey === 'comp_price_markup'">
                                    <?=HelpTextHelper::getHelpText('comp_price_markup','text')?>
                                </div>
                                <div v-else-if="helpMessageKey === 'product_additional_details'">
                                    <?=HelpTextHelper::getHelpText('product_additional_details','text')?>
                                </div>
                                <div v-else-if="helpMessageKey === 'variants'">
                                    <?=HelpTextHelper::getHelpText('variants','text')?>
                                </div>
                                <div v-else-if="helpMessageKey === 'images'">
                                    <?=HelpTextHelper::getHelpText('images','text')?>
                                </div>
                                <div v-else-if="helpMessageKey === 'reviews'">
                                    <?=HelpTextHelper::getHelpText('reviews','text')?>
                                </div>
                                <?php foreach ($trainingVideos as $video): ?>
                                    <div v-else-if="helpMessageKey === 'training_video_<?= $video->id ?>'">
                                        <?php if (strpos($video->video_url, 'loom.com') !== false):?>
                                            <div style="position: relative; padding-bottom: 53.59375000000001%; height: 0;">
                                                <iframe src="<?= $video->video_url ?>" frameborder="0" webkitallowfullscreen
                                                        mozallowfullscreen allowfullscreen
                                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
                                            </div>
                                        <?php else:?>
                                            <div class="embed-responsive embed-responsive-16by9">
                                                <iframe id="embed-responsive-item" src="https://www.youtube.com/embed/<?= $video->youtubeId?>"
                                                        frameborder="0" allowfullscreen></iframe>
                                            </div>
                                        <?php endif?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  HELP MODAL END  -->

                <!--  Variant Price Markup Modal Start  -->
                <div id="variantPriceMarkupModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                        <span v-if="helpMessageKey === 'import_product'">
                            Set Variant Price Markup
                        </span>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                            </div>
                            <div class="modal-body">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Regular price markup</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="basic-form">
                                                <form>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-3">
                                                            <div class="form-group-header" >
                                                                <label
                                                                        data-container="body"
                                                                        data-toggle="hover"
                                                                        data-placement="right"
                                                                        data-content="<?= HelpTextHelper::getHelpText('price','text') ?>"
                                                                        title="<?= HelpTextHelper::getHelpText('price','title') ?>"
                                                                >
                                                                    Price
                                                                </label>
                                                            </div>
                                                            <input
                                                                    type="number"
                                                                    class="form-control form-control-md"
                                                                    placeholder="price"
                                                                    v-model.number="variant_price"
                                                                    :disabled="priceReadonly"
                                                            >
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label
                                                                    data-container="body"
                                                                    data-toggle="hover"
                                                                    data-placement="right"
                                                                    data-content="<?= HelpTextHelper::getHelpText('final_price','text') ?>"
                                                                    title="<?= HelpTextHelper::getHelpText('final_price','title') ?>"
                                                            >
                                                                Final price
                                                            </label>

                                                            <input
                                                                    type="number"
                                                                    class="form-control form-control-md"
                                                                    v-model.number="variant_final_price"
                                                                    @change="changeVariantFinalPrice"
                                                                    placeholder="final price"
                                                            >
                                                        </div>
                                                        <div class="form-group col-md-3">
                                                            <label
                                                                    data-container="body"
                                                                    data-toggle="hover"
                                                                    data-placement="right"
                                                                    data-content="<?= HelpTextHelper::getHelpText('price_policy','text') ?>"
                                                                    title="<?= HelpTextHelper::getHelpText('price_policy','title')  ?>"
                                                            >
                                                                Price Policy
                                                            </label>
                                                            <select v-model="variant_price_markup" class="form-control form-control-md">
                                                                <option value="0"> By Percent</option>
                                                                <option value="1">By Amount</option>
                                                            </select>
                                                        </div>
                                                        <div v-if="variant_price_markup === '0'" class="form-group col-md-3">
                                                            <div class="form-group-header" >
                                                                <label
                                                                        data-container="body"
                                                                        data-toggle="hover"
                                                                        data-placement="right"
                                                                        data-content="<?= HelpTextHelper::getHelpText('percent','text')  ?>"
                                                                        title="<?= HelpTextHelper::getHelpText('percent','title') ?>"
                                                                >
                                                                    Percent
                                                                </label>
                                                            </div>
                                                            <input
                                                                    type="number"
                                                                    class="form-control form-control-md"
                                                                    v-model.number="variant_price_by_percent"
                                                            >
                                                        </div>
                                                        <div v-else class="form-group col-md-3">
                                                            <label
                                                                    data-container="body"
                                                                    data-toggle="hover"
                                                                    data-placement="right"
                                                                    data-content="<?= HelpTextHelper::getHelpText('by_amount','text')  ?>"
                                                                    title="<?= HelpTextHelper::getHelpText('by_amount','title') ?>"
                                                            >
                                                                By Amount
                                                            </label>

                                                            <input
                                                                    type="number"
                                                                    class="form-control form-control-md"
                                                                    v-model.number="variant_price_by_amount"
                                                                    class="edit-checkbox"
                                                            >
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title">Compare at price markup</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="basic-form">
                                                <form>
                                                    <div class="form-row">
                                                        <div class="form-group col-md-4">
                                                            <label
                                                                    data-container="body"
                                                                    data-toggle="hover"
                                                                    data-placement="right"
                                                                    data-content="<?= HelpTextHelper::getHelpText('final_compare_at_price','text') ?>"
                                                                    title="<?= HelpTextHelper::getHelpText('final_compare_at_price','title') ?>"
                                                            >
                                                                Final compare At Price
                                                            </label>
                                                            <input
                                                                    type="number"
                                                                    class="form-control form-control-md"
                                                                    placeholder="final compare at price"
                                                                    v-model.number="variant_compare_at_price"
                                                                    @change="changeVariantCompareAtPrice"
                                                            >
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <label
                                                                    data-container="body"
                                                                    data-toggle="hover"
                                                                    data-placement="right"
                                                                    data-content="<?= HelpTextHelper::getHelpText('compare_at_price_policy','text') ?>"
                                                                    title="<?= HelpTextHelper::getHelpText('compare_at_price_policy','title') ?>"
                                                            >
                                                                Compare At Price Policy
                                                            </label>
                                                            <select v-model="variant_compare_at_price_markup" class="form-control form-control-md">
                                                                <option value="0"> By Percent</option>
                                                                <option value="1">By Amount</option>
                                                            </select>
                                                        </div>
                                                        <div v-if="variant_compare_at_price_markup === '0'" class="form-group col-md-4">
                                                            <div class="form-group-header" >
                                                                <label
                                                                        data-container="body"
                                                                        data-toggle="hover"
                                                                        data-placement="right"
                                                                        data-content="<?= HelpTextHelper::getHelpText('percent','text') ?>"
                                                                        title="<?= HelpTextHelper::getHelpText('percent','title')?>"
                                                                >
                                                                    Percent
                                                                </label>
                                                            </div>

                                                            <input
                                                                    type="number"
                                                                    class="form-control form-control-md"
                                                                    v-model.number="variant_compare_at_price_by_percent"
                                                            >
                                                        </div>
                                                        <div v-else class="form-group col-md-4">
                                                            <label
                                                                    data-container="body"
                                                                    data-toggle="hover"
                                                                    data-placement="right"
                                                                    data-content="<?= HelpTextHelper::getHelpText('by_amount','text') ?>"
                                                                    title="<?= HelpTextHelper::getHelpText('by_amount','title') ?>"
                                                            >
                                                                By Amount
                                                            </label>
                                                            <input
                                                                    type="number"
                                                                    class="form-control form-control-md"
                                                                    v-model.number="variant_compare_at_price_by_amount"
                                                            >
                                                        </div>
                                                    </div>

                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <label>
                                        Get from product markup
                                    </label>
                                    <input
                                            type="checkbox"
                                            v-model="variant_get_from_product_markup"
                                            :checked="variant_get_from_product_markup"
                                            class="edit-checkbox"
                                    />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button @click="variantMarkupSave" type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  Variant Price Markup Modal End  -->

                <!-- REQUEST SITE MODAL START  -->
                <div id="requestSiteModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog"
                     aria-labelledby="myLargeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <?= HelpTextHelper::getHelpText('requested_site', 'title') ?>
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php $form = ActiveForm::begin(
                                    [
                                        'action' => ['create-requested-site'],
                                        'enableClientScript' => false,
                                    ]
                                );?>
                                <p><?= HelpTextHelper::getHelpText('requested_site', 'text') ?></p>
                                <div class="row">
                                    <div class="form-group col-md-12 mb-3">
                                        <?= $form->field($requestedSiteModel, 'url', ['template' => "{label}\n{input}"])->textInput(['class' => 'form-control']) ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12 text-center">
                                        <?= Html::submitButton('Send', ['class' => 'btn btn-primary mb-2']) ?>
                                    </div>
                                </div>
                                <?php ActiveForm::end(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  REQUEST SITE MODAL END  -->
            </template>
        </div>

    </div>
<?php
//$this->registerJsFile('@web/js/importProduct/jquery-3.3.1.min.js', ['depends' => [AppAsset::class]]);

$this->registerJsFile('@web/js/importProduct/package/bootstrap.min.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/vue.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/ckeditorVue.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/ckeditor.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/star-rating.min.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/bootstrap-vue.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/crypto-js.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/dataTable.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/lightbox.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/moment.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/pretty-checkbox-vue.min.js', ['depends' => [AppAsset::class]]);

$this->registerJsFile('@web/js/importProduct/package/global.min.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/package/moment.min.js', ['depends' => [AppAsset::class]]);
//$this->registerJsFile('@web/js/importProduct/background.js', ['depends' => [AppAsset::class]]);
$this->registerJsFile('@web/js/importProduct/popup.js?v=24', ['depends' => [AppAsset::class]]);
$this->registerJsFile("https://code.jquery.com/ui/1.12.1/jquery-ui.js", ['depends' => [AppAsset::class]]);

?>