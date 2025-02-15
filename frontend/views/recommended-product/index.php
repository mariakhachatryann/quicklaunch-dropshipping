<?php

use common\models\AvailableSite;
use common\models\Niche;
use common\models\ProductType;
use common\models\UserSetting;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel \backend\models\RecommendedProductSearch */

$this->title = 'Recommended Products';
$this->params['breadcrumbs'][] = $this->title;

$nicheIconMap = [
    'Technology' => 'material-symbols:laptop-mac-outline-sharp',
    'Fashion' => 'mdi:tshirt-crew',
    'Health & Fitness' => 'mdi:dumbbell',
    'Home Decor' => 'mdi:home-variant',
    'Travel' => 'mdi:airplane-takeoff',
    'Food & Drink' => 'mdi:food',
    'Finance' => 'mdi:currency-usd',
    'Gaming' => 'mdi:gamepad',
    'Beauty' => 'mdi:brush',
    'Education' => 'mdi:school',
];
?>
<div class="recommended-product-index">
    <div class="card position-relative overflow-hidden">
        <div class="shop-part d-flex w-100">
            <div class="shop-filters flex-shrink-0 border-end d-none d-lg-block">
                <ul class="list-group pt-2 border-bottom rounded-0">
                    <h6 class="my-3 mx-4 fw-semibold">Filter by Niche</h6>
                    <?php foreach (Niche::find()->all() as $niche): ?>
                        <div class="accordion accordion-flush" id="accordionFlushExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-<?= $niche['id']?>" aria-expanded="false" aria-controls="flush-collapse-<?= $niche['id']?>">
                                        <iconify-icon icon="<?= $nicheIconMap[$niche['name']] ?>" class="fs-5 mr-2"></iconify-icon>
                                        <?= $niche['name'] ?>
                                    </button>
                                </h2>
                                <div id="flush-collapse-<?= $niche['id']?>" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body py-0 ml-3">
                                        <?php foreach ($niche->categories as $category): ?>
                                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="?categoryId=<?= urlencode($category->id) ?>">
                                                <?= $category['name'] ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
<!--                        <li class="list-group-item border-0 p-0 mx-4 mb-2">-->
<!--                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="?nicheId=--><?php //= urlencode($niche->id) ?><!--">-->
<!--                                <iconify-icon icon="--><?php //= $nicheIconMap[$niche['name']] ?><!--" class="fs-5"></iconify-icon>-->
<!--                                --><?php //= $niche['name'] ?>
<!--                            </a>-->
<!--                        </li>-->
                    <?php endforeach; ?>
                </ul>
                <ul class="list-group pt-2 border-bottom rounded-0">
                    <h6 class="my-3 mx-4 fw-semibold">Sort By</h6>
                    <li class="list-group-item border-0 p-0 mx-4 mb-2">
                        <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                            <iconify-icon icon="mdi:sort-descending" class="fs-5"></iconify-icon>Newest
                        </a>
                    </li>
                    <li class="list-group-item border-0 p-0 mx-4 mb-2">
                        <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                            <iconify-icon icon="mdi:arrow-down-bold" class="fs-5"></iconify-icon>Price: High-Low
                        </a>
                    </li>
                    <li class="list-group-item border-0 p-0 mx-4 mb-2">
                        <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                            <iconify-icon icon="mdi:arrow-up-bold" class="fs-5"></iconify-icon>
                            Price: Low-High
                        </a>
                    </li>
                </ul>
                <div class="by-gender border-bottom rounded-0">
                    <h6 class="mt-4 mb-3 mx-4 fw-semibold">By Gender</h6>
                    <div class="pb-4 px-4">
                        <div class="form-check py-2 mb-0">
                            <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios1" value="option1" checked>
                            <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios1">
                                All
                            </label>
                        </div>
                        <div class="form-check py-2 mb-0">
                            <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios2" value="option1">
                            <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios2">
                                Men
                            </label>
                        </div>
                        <div class="form-check py-2 mb-0">
                            <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios3" value="option1">
                            <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios3">
                                Women
                            </label>
                        </div>
                        <div class="form-check py-2 mb-0">
                            <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios4" value="option1">
                            <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios4">
                                Kids
                            </label>
                        </div>
                    </div>
                </div>
                <div class="by-pricing border-bottom rounded-0">
                    <h6 class="mt-4 mb-3 mx-4 fw-semibold">By Pricing</h6>
                    <div class="pb-4 px-4">
                        <div class="form-check py-2 mb-0">
                            <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios5" value="option1" checked>
                            <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios5">
                                All
                            </label>
                        </div>
                        <div class="form-check py-2 mb-0">
                            <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios6" value="option1">
                            <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios6">
                                0-50
                            </label>
                        </div>
                        <div class="form-check py-2 mb-0">
                            <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios7" value="option1">
                            <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios7">
                                50-100
                            </label>
                        </div>
                        <div class="form-check py-2 mb-0">
                            <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios8" value="option1">
                            <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios8">
                                100-200
                            </label>
                        </div>
                        <div class="form-check py-2 mb-0">
                            <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios9" value="option1">
                            <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios9">
                                Over 200
                            </label>
                        </div>
                    </div>
                </div>
                <div class="by-colors border-bottom rounded-0">
                    <h6 class="mt-4 mb-3 mx-4 fw-semibold">By Colors</h6>
                    <div class="pb-4 px-4">
                        <ul class="list-unstyled d-flex flex-wrap align-items-center gap-2 mb-0">
                            <li class="shop-color-list">
                                <a class="shop-colors-item rounded-circle d-block shop-colors-1" href="javascript:void(0)"></a>
                            </li>
                            <li class="shop-color-list">
                                <a class="shop-colors-item rounded-circle d-block shop-colors-2" href="javascript:void(0)"></a>
                            </li>
                            <li class="shop-color-list">
                                <a class="shop-colors-item rounded-circle d-block shop-colors-3" href="javascript:void(0)"></a>
                            </li>
                            <li class="shop-color-list">
                                <a class="shop-colors-item rounded-circle d-block shop-colors-4" href="javascript:void(0)"></a>
                            </li>
                            <li class="shop-color-list">
                                <a class="shop-colors-item rounded-circle d-block shop-colors-5" href="javascript:void(0)"></a>
                            </li>
                            <li class="shop-color-list">
                                <a class="shop-colors-item rounded-circle d-block shop-colors-6" href="javascript:void(0)"></a>
                            </li>
                            <li class="shop-color-list">
                                <a class="shop-colors-item rounded-circle d-block shop-colors-7" href="javascript:void(0)"></a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="p-4">
                    <a href="/recommended-product" class="btn btn-primary w-100">Reset Filters</a>
                </div>
            </div>
            <div class="card-body p-4 pb-0">
                <div class="d-flex justify-content-between align-items-center gap-6 mb-4">
                    <a class="btn btn-primary d-lg-none d-flex" data-bs-toggle="offcanvas" href="#filtercategory" role="button" aria-controls="filtercategory">
                        <iconify-icon icon="solar:hamburger-menu-line-duotone" class="fs-6"></iconify-icon>
                    </a>
                    <h5 class="fs-5 mb-0 d-none d-lg-block">Products</h5>
                    <button class="btn me-1 mb-1 bg-primary text-white px-4 fs-4 cursor-pointer bulk-selection">
                        Bulk Selection
                    </button>

                </div>
                <div class="row">

                <?= ListView::widget([
                        'dataProvider' => $dataProvider,
                        'id' => 'product_list',
                        'itemView' => '_recommended_product_item',
                        'options' => [
                            'tag' => false,
                        ],
                        'itemOptions' => [
                            'tag' => false,
                        ],
                    ])?>

                </div>
            </div>
            <div class="offcanvas offcanvas-start" tabindex="-1" id="filtercategory" aria-labelledby="filtercategoryLabel">
                <div class="offcanvas-body shop-filters w-100 p-0">
                    <ul class="list-group pt-2 border-bottom rounded-0">
                        <h6 class="my-3 mx-4 fw-semibold">Filter by Category</h6>
                        <li class="list-group-item border-0 p-0 mx-4 mb-2">
                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                                <i class="ti ti-circles fs-5"></i>All
                            </a>
                        </li>
                        <li class="list-group-item border-0 p-0 mx-4 mb-2">
                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                                <i class="ti ti-hanger fs-5"></i>Fashion
                            </a>
                        </li>
                        <li class="list-group-item border-0 p-0 mx-4 mb-2">
                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                                <i class="ti ti-notebook fs-5"></i>
                                </i>Books
                            </a>
                        </li>
                        <li class="list-group-item border-0 p-0 mx-4 mb-2">
                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                                <i class="ti ti-mood-smile fs-5"></i>Toys
                            </a>
                        </li>
                        <li class="list-group-item border-0 p-0 mx-4 mb-2">
                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                                <i class="ti ti-device-laptop fs-5"></i>Electronics
                            </a>
                        </li>
                    </ul>
                    <ul class="list-group pt-2 border-bottom rounded-0">
                        <h6 class="my-3 mx-4 fw-semibold">Sort By</h6>
                        <li class="list-group-item border-0 p-0 mx-4 mb-2">
                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                                <iconify-icon icon="mdi:sort-descending" class="fs-5"></iconify-icon>Newest
                            </a>
                        </li>
                        <li class="list-group-item border-0 p-0 mx-4 mb-2">
                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                                <i class="ti ti-sort-ascending-2 fs-5"></i>Price: High-Low
                            </a>
                        </li>
                        <li class="list-group-item border-0 p-0 mx-4 mb-2">
                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                                <i class="ti ti-sort-descending-2 fs-5"></i>
                                </i>Price: Low-High
                            </a>
                        </li>
                        <li class="list-group-item border-0 p-0 mx-4 mb-2">
                            <a class="d-flex align-items-center gap-6 list-group-item-action text-dark px-3 py-6 rounded-1" href="javascript:void(0)">
                                <i class="ti ti-ad-2 fs-5"></i>Discounted
                            </a>
                        </li>
                    </ul>
                    <div class="by-gender border-bottom rounded-0">
                        <h6 class="mt-4 mb-3 mx-4 fw-semibold">By Gender</h6>
                        <div class="pb-4 px-4">
                            <div class="form-check py-2 mb-0">
                                <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios10" value="option1" checked>
                                <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios10">
                                    All
                                </label>
                            </div>
                            <div class="form-check py-2 mb-0">
                                <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios11" value="option1">
                                <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios11">
                                    Men
                                </label>
                            </div>
                            <div class="form-check py-2 mb-0">
                                <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios12" value="option1">
                                <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios12">
                                    Women
                                </label>
                            </div>
                            <div class="form-check py-2 mb-0">
                                <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios13" value="option1">
                                <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios13">
                                    Kids
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="by-pricing border-bottom rounded-0">
                        <h6 class="mt-4 mb-3 mx-4 fw-semibold">By Pricing</h6>
                        <div class="pb-4 px-4">
                            <div class="form-check py-2 mb-0">
                                <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios14" value="option1" checked>
                                <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios14">
                                    All
                                </label>
                            </div>
                            <div class="form-check py-2 mb-0">
                                <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios15" value="option1">
                                <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios15">
                                    0-50
                                </label>
                            </div>
                            <div class="form-check py-2 mb-0">
                                <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios16" value="option1">
                                <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios16">
                                    50-100
                                </label>
                            </div>
                            <div class="form-check py-2 mb-0">
                                <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios17" value="option1">
                                <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios17">
                                    100-200
                                </label>
                            </div>
                            <div class="form-check py-2 mb-0">
                                <input class="form-check-input p-2" type="radio" name="exampleRadios" id="exampleRadios18" value="option1">
                                <label class="form-check-label d-flex align-items-center ps-2" for="exampleRadios18">
                                    Over 200
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="by-colors border-bottom rounded-0">
                        <h6 class="mt-4 mb-3 mx-4 fw-semibold">By Colors</h6>
                        <div class="pb-4 px-4">
                            <ul class="list-unstyled d-flex flex-wrap align-items-center gap-2 mb-0">
                                <li class="shop-color-list">
                                    <a class="shop-colors-item rounded-circle d-block shop-colors-1" href="javascript:void(0)"></a>
                                </li>
                                <li class="shop-color-list">
                                    <a class="shop-colors-item rounded-circle d-block shop-colors-2" href="javascript:void(0)"></a>
                                </li>
                                <li class="shop-color-list">
                                    <a class="shop-colors-item rounded-circle d-block shop-colors-3" href="javascript:void(0)"></a>
                                </li>
                                <li class="shop-color-list">
                                    <a class="shop-colors-item rounded-circle d-block shop-colors-4" href="javascript:void(0)"></a>
                                </li>
                                <li class="shop-color-list">
                                    <a class="shop-colors-item rounded-circle d-block shop-colors-5" href="javascript:void(0)"></a>
                                </li>
                                <li class="shop-color-list">
                                    <a class="shop-colors-item rounded-circle d-block shop-colors-6" href="javascript:void(0)"></a>
                                </li>
                                <li class="shop-color-list">
                                    <a class="shop-colors-item rounded-circle d-block shop-colors-7" href="javascript:void(0)"></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="p-4">
                        <a href="javascript:void(0)" class="btn btn-primary w-100">Reset Filters</a>
                    </div>
                </div>
            </div>

    </div>
    <div class="modal fade" id="bulk-import-modal" tabindex="-1" aria-labelledby="bulk-import-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        Import Products
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12">
                        <div class="card-header px-0">
                            <h5 class="card-title">Default Pricing Rules</h5>
                        </div>
                        <?php $form = ActiveForm::begin([
                            'action' => Url::to(['recommended-product/bulk-import']),
                            'method' => 'post',
                        ]) ?>
                        <input id="bulkImportProductIds" name="bulkImportProductIds" class="d-none" />
                        <div class="px-0">
                            <div class="basic-form">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <?= $form->field($setting, 'price_markup')
                                            ->dropDownList(UserSetting::$priceMarkups, [
                                                'class' => 'form-select bulk-price-markup',
                                            ])
                                            ->label(null, [
                                                'data-toggle' => 'tooltip',
                                                'title' => "price-markup"
                                            ]) ?>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <?= $form->field($setting, 'compare_at_price_markup')
                                            ->dropDownList(UserSetting::$priceMarkups, [
                                                'class' => 'form-select bulk_compare_at_price_markup',
                                            ])
                                            ->label(null, [
                                                'data-toggle' => 'tooltip',
                                                'title' => "comparePriceMarkup"
                                            ]) ?>
                                    </div>
                                    <div class="form-group col-md-6 d-none">
                                        <div class="bulk-price-by-amount">
                                            <?= $form->field($setting, 'price_by_amount')->textInput([
                                                'type' => 'number',
                                                'min' => 0,
                                                'step' => 'any'
                                            ])->label(null, [
                                                'data-toggle' => 'tooltip',
                                                'title' => "price_by_amount"
                                            ]) ?>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="bulk_price_by_percent">
                                            <?= $form->field($setting, 'price_by_percent')->textInput([
                                                'type' => 'number',
                                                'min' => 0,
                                                'step' => 'any'
                                            ])->label(null, [
                                                'data-toggle' => 'tooltip',
                                                'title' => "price_by_percent"
                                            ]) ?>
                                        </div>
                                    </div>
                                    <div class="form-select col-md-6 d-none">
                                        <div class="bulk_compare_at_price_by_amount">
                                            <?= $form->field($setting, 'compare_at_price_by_amount')->textInput(['type' => 'number', 'min' => 0, 'step' => 'any'])->label(null, ['data-toggle' => 'tooltip', 'title' => '']) ?>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="bulk_compare_at_price_by_percent">
                                            <?= $form->field($setting, 'compare_at_price_by_percent')->textInput(['type' => 'number', 'min' => 0, 'step' => 'any'])->label(null, ['data-toggle' => 'tooltip', 'title' => "compare_at_price_by_amount"]) ?>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label>
                                            Collection
                                        </label>
                                        <select class="form-select" name="collection">
                                            <option value="">Select Shopify collection</option>
                                            <?php foreach ($collections as $collection): ?>
                                                <option value="<?= $collection['id'] ?>">
                                                    <?= $collection['title'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" >Close</button>
                        <button type="submit" class="btn btn-primary"
                                formaction="<?= Url::to(['recommended-product/bulk-import']) ?>">
                            Save for Preview
                        </button>
                        <button type="submit" class="btn btn-secondary"
                                formaction="<?= Url::to(['recommended-product/bulk-import', 'publish' => true]) ?>">
                            Publish to Shopify
                        </button>
                    </div>
                    <?php ActiveForm::end() ?>
                </div>

            </div>
        </div>
    </div>
</div>
