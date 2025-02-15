<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $categories common\models\Category[] */
/* @var $niche */
?>

<div class="card card-body">
<!--    <h1 class="mb-2">Select Categories to Add to Your Shopify Store</h1>-->

    <div class="categories-list">
<!--        <h5>Available Categories of --><?php //= $niche ?><!--:</h5>-->

        <?php $form = ActiveForm::begin([
            'action' => ['recommended-product/find-product-by-category'],
        ]); ?>

<!--        <div class="form-group">-->
<!--            <div class="d-flex flex-wrap align-items-center justify-content-start">-->
<!--                --><?php //foreach ($categories as $index => $category): ?>
<!--                    <div class="d-flex align-items-center mr-5 mb-2">-->
<!--                        --><?php //= Html::checkbox("categories[$index][id]", false, [
//                            'value' => $category->id,
//                            'class' => 'form-check-input m-2 position-relative',
//                            'id' => "category-{$index}-id"
//                        ]) ?>
<!--                        <label for="category---><?php //= $index ?><!---id" class="mb-0">--><?php //= $category->name ?><!--</label>-->
<!--                    </div>-->
<!--                --><?php //endforeach; ?>
<!--            </div>-->
<!--        </div>-->

        <div id="new-categories">
            <h5>Your preferred category of <?= $niche ?></h5>

            <div class="new-category">
                <?= Html::input('text', 'category', '', [
                    'class' => 'form-control mb-2 mr-2 ',
                    'placeholder' => 'Enter new category name'
                ]) ?>
            </div>
        </div>



        <div class="form-group">
            <?= Html::submitButton('Find products', ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>