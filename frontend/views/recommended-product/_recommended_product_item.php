<?php

use frontend\models\RecommendedProduct;
use yii\helpers\Url;

/* @var RecommendedProduct $model*/
?>

<div class="col-sm-6 col-xxl-4">
    <div class="card overflow-hidden rounded-2 border product-card" data-product-id="<?= $model->id ?>">
        <div class="card" style="height: 300px;">
            <a href="<?= $model->url ?>" class="hover-img d-block overflow-hidden">
                <img src="<?= $model->image ?>" class="card-img-top rounded-0 img-fluid w-100" alt="matdash-img" style="object-fit: cover;">
            </a>
        </div>

        <div class="card-body pt-3 p-4">
            <h6 class="fw-semibold fs-4">
                <?= mb_strimwidth($model->title, 0, 25, '...') ?>
            </h6>
            <div class="d-flex align-items-center justify-content-center">
                <span class="fw-semibold fs-4 mb-0"><?= $model->site->name ?? '' ?></span>
            </div>
            <div class="text-center mt-4">
                <a
                        href="<?= $model->url . '?shionRecommendedProductImport=true' ?>"
                        target="_blank"
                        class="btn btn-primary"
                        style="font-weight: bold"
                >
                    GET THIS PRODUCT
                </a>
            </div>
        </div>
        <div class="selection-indicator"></div>

    </div>
</div>