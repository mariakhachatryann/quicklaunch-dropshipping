<?php
use frontend\widgets\RateWidget;
use yii\bootstrap4\Html;
use yii\helpers\Url;

/* @var common\models\Product $model*/
?>

    <div class="card">
        <div class="card-body">
            <div class="new-arrival-product">
                <div class="new-arrivals-img-contnent overflow-hidden">
                    <a href="<?=$model->handleUrl?>">
                        <img class="img-fluid" src="<?=$model->productImage?>" alt="">
                    </a>
                </div>
                <div class="new-arrival-content text-center mt-3">
                    <h4><a href="<?=$model->handleUrl?>"><?=$model->title?></a></h4>
                    <?php  if (Yii::$app->params['enableReview']): ?>
<!--                    --><?php //= Html::a(RateWidget::widget(['rate' => $model->rate]), $model->getReviewsUrl(), ['class' => 'text-nowrap'])?>
                    <?php endif; ?>
                    <div><span class="price"><?=$model->displayPrice?></span></div>
                </div>
            </div>
        </div>
        <div class="text-center mb-4">
            <?=$this->render('_action_buttons', compact('model'))?>
        </div>
    </div>
