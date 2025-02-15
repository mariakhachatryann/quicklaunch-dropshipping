<?php

use common\helpers\CalculationHelper;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

?>

<div class="row text-center">
    <div class="col-xl-3 col-lg-6 col-sm-6 col-xxl-4">
        <div class="widget-stat card">
            <div class="card-body p-4 "> <br />
                <?php if (Yii::$app->user->identity->plan_status) : ?>
                    <h4 class="card-title">Current plan - <?= ArrayHelper::getValue(Yii::$app->user->identity, 'plan.name'); ?></h4>
                  
                <?php else: ?>
                    <h4 class="card-title">Choose plan</h4>
                    <p class="text-danger"> Please, choose your plan</p>
                <?php endif; ?>

                <p>
                    <?= Html::a(
                        'See all plans <iconify-icon icon="solar:arrow-right-linear" class="align-middle"></iconify-icon>',
                        Url::to(['/profile/subscribe']),
                        ['class' => 'btn btn-primary btn-sm align-items-center']
                    ) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-sm-6 col-xxl-4">
        <div class="widget-stat card">
            <div class="card-body p-4">
                <h4 class="card-title">Products</h4>
                <h3><?= number_format($productsCount)?> / <?= number_format($productsLimit) ?></h3>
                <div class="progress mb-2">
                    <div class="progress-bar progress-animated bg-red" style="width: <?= CalculationHelper::getPercent($productsCount, $productsLimit)?>%"></div>
                </div>
                <small><?= CalculationHelper::getPercent($productsCount, $productsLimit)?> %</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-sm-6 col-xxl-4">
        <div class="widget-stat card">
            <div class="card-body p-4">
                <h4 class="card-title">Product Monitoring</h4>
                <h3><?= number_format($monitoringCount)?> / <?= number_format($monitoringLimit) ?></h3>
                <div class="progress mb-2">
                    <div class="progress-bar progress-animated bg-primary" style="width: <?= CalculationHelper::getPercent($monitoringCount, $monitoringLimit)?>%"></div>
                </div>
                <small><?= CalculationHelper::getPercent($monitoringCount, $monitoringLimit)?> %</small>
            </div>
        </div>
    </div>

    <?php if(Yii::$app->params['enableReview']): ?>
    <div class="col-xl-3 col-lg-6 col-sm-6 col-xxl-3">
        <div class="widget-stat card">
            <div class="card-body p-4">
                <h4 class="card-title">Product Reviews</h4>
                <h3><?= number_format($reviews) ?> / <?= number_format($reviewsLimit) ?></h3>
                <div class="progress mb-2">
                    <div class="progress-bar progress-animated bg-warning" style="width: <?= CalculationHelper::getPercent($reviews, $reviewsLimit)?>%"></div>
                </div>
                <small><?= CalculationHelper::getPercent($reviews, $reviewsLimit)?> %</small>
            </div>
        </div>
    </div>

    <?php endif ?>


    <!-- ./col -->
</div>
