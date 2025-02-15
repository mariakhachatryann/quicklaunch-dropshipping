<?php
/* @var \common\models\Product $model*/
$data = json_decode($model->product_data, true);
use frontend\widgets\RateWidget;
use yii\helpers\Html;
use yii\helpers\Url; ?>

<div class="card">
    <div class="card-body">
        <div class="row m-b-30">
            <div class="col-md-5 col-xxl-12">
                <div class="new-arrival-product mb-4 mb-xxl-4 mb-md-0">
                    <div class="new-arrivals-img-contnent overflow-hidden">
                        <a href="<?=$model->handleUrl?>">
                            <img class="img-fluid" src="<?=$model->getProductImage()?>" alt="">
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-7 col-xxl-12">
                <div class="new-arrival-content position-relative">
                    <h4><a href="<?=$model->handleUrl?>"><?=$model->title?></a></h4>
                    <p class="price"><?=$model->getDisplayPrice()?></p>
<!--                    <p>Availability: <span class="item"> In stock <i class="fa fa-check-circle text-success"></i></span></p>
-->                    <p>SKU: <span class="item"><?=$model->sku?></span> </p>
                    <p>Variants count: <span class="item"><?=$model->count_variants?></span> </p>
                    <p class="text-content"></p>
                    <?php  if (Yii::$app->params['enableReview']): ?>
                        <div class="comment-review star-rating text-right product-list">
<!--                            --><?php //=Html::a(RateWidget::widget(['rate' => $model->rate]), $model->getReviewsUrl(), ['class' => 'text-nowrap'])?>
                            <span class="review-text">(<?=$model->reviewsCount?> reviews) / </span>
<!--                            <a class="product-review" href="--><?php //=$model->getReviewsUrl()?><!--">Write a review?</a>-->
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="text-left mb-4 ml-5">
        <?=$this->render('_action_buttons', compact('model'))?>
    </div>
    </div>
