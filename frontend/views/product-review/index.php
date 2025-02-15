<?php

use yii\bootstrap4\LinkPager;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;
use common\models\ProductReview;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Product Reviews';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="basic-form">
                <?php $form = ActiveForm::begin([
                    'method' => 'get',
                ]); ?>
                <div class="row">
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($searchModel, 'id')->input('text', ['class' => 'form-control'])->label('Id') ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($searchModel, 'product_title')->input('text', ['class' => 'form-control'])->label('Product Title') ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($searchModel, 'review')->input('text', ['class' => 'form-control'])->label('Review') ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($searchModel, 'reviewer_name')->input('text', ['class' => 'form-control'])->label('Reviewer Name') ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($searchModel, 'rate')->input('number', ['class' => 'form-control'])->label('Rate') ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($searchModel, 'created_at_min')->input('date', ['class' => 'form-control']) ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($searchModel, 'created_at_max')->input('date', ['class' => 'form-control']) ?>
                    </div>
                    <div class="form-group col-md-3 mb-3">
                        <?= $form->field($searchModel, 'status')->dropDownList(ProductReview::STATUSES, ['class' => 'form-select p-2']) ?>
                    </div>
                    <div class="form-group col-md-3 mb-3 mt-4">
                        <?= Html::button('Search', ['type' => 'submit', 'class' => 'btn btn-primary mb-2']) ?>
                    </div>
                </div>


                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>


    <!--<div class="col-xl-12">
        <div class="card">
            <div class="card-body px-4 py-3 py-md-2">
                <div class="row align-items-center">
                    <div class="col-sm-12 col-md-7">
                        <ul class="nav nav-pills review-tab">
                            <li class="nav-item">
                                <a href="<?/*= Url::toRoute(['/product-review'])*/?>" class="nav-link active px-2 px-lg-3" >All Reviews</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?/*= Url::toRoute(['/product-review/index', 'status' => ProductReview::STATUS_PUBLISHED])*/?>" class="nav-link px-2 px-lg-3">Published</a>
                            </li>
                            <li class="nav-item">
                                <a href="<?/*= Url::toRoute(['/product-review/index', 'status' => ProductReview::STATUS_PENDING])*/?>" class="nav-link px-2 px-lg-3">Pending</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-sm-12 col-md-5 text-md-right mt-md-0 mt-4">
                        <a href="javascript:void(0);" class="btn btn-primary rounded mr-1 btn-sm px-4">Publish</a>
                        <a href="javascript:void(0);" class="btn btn-danger rounded btn-sm px-4">Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>-->
<div class="card card-body">
    <div class="table-responsive">
        <table class="table table-responsive-lg">
            <thead>
                <tr>
                    <?php if (empty($searchModel->product_id)):?>
                        <th>Product</th>
                    <?php endif?>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Review</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="font-size">
            <?= ListView::widget([
                'dataProvider' => $dataProvider,
                'itemView' => '_review',
                'layout' => "{summary}\n{items}",
                'viewParams' => compact('searchModel')
            ]);
            ?>
            </tbody>
        </table>
    </div>
<div class="justify-content-center">
    <?=LinkPager::widget(['pagination' => $dataProvider->pagination])?>
</div>
