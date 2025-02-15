<?php
/* @var $dataProvider*/
/* @var $searchModel*/

use common\helpers\HelpTextHelper;
use common\helpers\OrderHelper;
use frontend\assets\AppAsset;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Orders';
$this->params['breadcrumbs'][] = $this->title;

?>


<div class="card">
    <div class="card-body">
        <h3><?= HelpTextHelper::getHelpText('order_info', 'title')?></h3>
        <p><?= HelpTextHelper::getHelpText('order_info', 'text')?></p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="basic-form">

            <?php $form = ActiveForm::begin([
                'method' => 'get',
            ]); ?>
            <div class="row">
                <div class="form-group col-md-3 mb-3">
                    <?= $form->field($searchModel, 'ids')->input('text', ['class' => 'form-control'])->label('Id') ?>
                </div>
                <div class="form-group col-md-3 mb-3">
                    <?= $form->field($searchModel, 'financial_status')->dropDownList(OrderHelper::FINANCIAL_STATUSES, ['class' => 'form-select p-2']) ?>
                </div>
                <div class="form-group col-md-3 mb-3">
                    <?= $form->field($searchModel, 'fulfillment_status')->dropDownList(OrderHelper::FULFILLMENT_STATUSES, ['class' => 'form-select p-2']) ?>
                </div>
                <div class="form-group col-md-3 mb-3">
                    <?= $form->field($searchModel, 'status')->dropDownList(OrderHelper::STATUSES, ['class' => 'form-select p-2']) ?>
                </div>
                <div class="form-group col-md-3 mb-3">
                    <?= $form->field($searchModel, 'created_at_min')->input('date', ['class' => 'form-control']) ?>
                </div>
                <div class="form-group col-md-3 mb-3">
                    <?= $form->field($searchModel, 'created_at_max')->input('date', ['class' => 'form-control']) ?>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-md-3 mb-3">
                    <?= Html::button('Search', ['type' => 'submit', 'class' => 'btn btn-primary mb-2']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive border rounded p-3">
            <table class="table align-middle text-nowrap mb-0">
                <thead>
                <tr>
                    <th scope="col">
                        <div class="form-check">
                            <input class="form-check-input bulk-check" type="checkbox" value="" id="flexCheckDefault">
                        </div>
                    </th>
                    <th><strong>Order</strong></th>
                    <th><strong>Date</strong></th>
                    <th><strong>Financial Status</strong></th>
                    <th><strong>Fulfillment Status</strong></th>
                    <th><strong>Amount</strong></th>
                </tr>
                </thead>
                <tbody>
                <?= \yii\widgets\ListView::widget([
                    'dataProvider' => $dataProvider,
                    'itemView' => '_orderList',
                    'summary' => ''
                ]);
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

