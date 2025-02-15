<?php

use frontend\widgets\RateWidget;
use yii\helpers\{Html, StringHelper, Url};

/* @var common\models\ProductReview $model*/
/* @var backend\models\ProductReviewSearch $searchModel*/

?>
<tr>
    <?php if (empty($searchModel->product_id)):?>
    <td>
        <div class="d-flex align-items-center" >
            <?php if ($model->product) :?>
                    <div style="margin-left: 3px;width: 70px; height: 70px; overflow: hidden" class="rounded mr-3 d-none d-xl-inline-block">
                        <img src="<?= $model->product->productImage ?>" style="width:80px; height: 100px">
                    </div>
                <div >
                    <div class="row">
                        <p style="line-height: 1.2; font-weight: 600; margin: 0"><?= Html::a(StringHelper::truncateWords($model->product->title ,4),
                                Yii::$app->user->identity->shopUrl."/products/".$model->product->handle,
                                ['target'=>'_blank', 'class' => '']);
                            ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="col-md-5">
                    <div style="margin-left: 3px;width: 70px; height: 70px; overflow: hidden" class="img-fluid rounded mr-3 d-none d-xl-inline-block">
                        <p> Product deleted</p>
                    </div>
                </div>
            <?php endif ?>

        </div>
    </td>
    <?php endif?>
    <td><?= $model->reviewer_name?></td>
    <td><?= $model->getStatusName()?></td>
    <td>
        <div class="row">
            <?php if ($model->productReviewImages): ?>
                <div class="col-md-5">
                    <?php foreach ($model->productReviewImages as $img): ?>
                        <div style="width: 70px; height: 70px; overflow: hidden" class="img-fluid rounded mr-3 d-none d-xl-inline-block">
                            <img src="<?= $img->image_url?>" alt="" class="img-fluid rounded mr-3 d-none d-xl-inline-block" style="width:80px; height: 100px" >
                        </div>
                        <?php break;?>
                    <?php endforeach?>
                </div>
            <?php endif; ?>
            <div class="col-md-7">
                <div class="row align-items-center mb-2">
                    <div class="d-flex">
                        <?= RateWidget::widget(['rate' => $model->rate]) ?>
                    </div>
                </div>
                <div class="row" style="font-size: 14px;">
                    <p class="mb-0">
                        <?= StringHelper::truncate($model->review, 25); ?>
                    </p>
                </div>
            </div>
    </td>
    <td>
        <small><?= date(\common\models\Product::DATE_DISPLAY_FORMAT, $model->date);?></small>
    </td>
    <td>
        <div class="d-flex">
            <a href="<?= Url::toRoute(['/product-review/update', 'id' => $model->id]) ?>"
               class="btn btn-primary light btn-sm ml-1 mt-1">Edit
            </a>
            <a href="<?= Url::toRoute(['/product-review/delete', 'id' => $model->id]) ?>"
               data-confirm="Are you sure you want remove this review ?" class="btn btn-danger light  btn-sm ml-1 mt-1">Delete
            </a>
            <a href="<?= Url::toRoute(['/product-review/publish', 'id' => $model->id, 'product_id' => $model->product_id]) ?>"
               class="btn btn-success light  btn-sm ml-1 mt-1"><?= $model->getIsPublished() ? 'Unpublish' : 'Publish' ?>
            </a>
        </div>
    </td>
</tr>
