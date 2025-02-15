<?php

/* @var backend\models\ProductSearch $searchModel */

/* @var int $displayType */
/* @var boolean $allowDeleteMultipleProducts */
/* @var boolean $allowBulkMonitoring */
/* @var boolean $allowMonitorNow */
/* @var string $itemView */
/* @var yii\web\View $this */

use common\models\AvailableSite;
use common\models\Product;
use frontend\assets\AppAsset;
use frontend\widgets\MenuPillsWidget;
use kartik\daterange\DateRangePicker;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\ListView;
use yii\bootstrap4\LinkPager;

$this->title = 'Imported products';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if(Yii::$app->session->hasFlash('publishQueueSet')) :?>
    <div class="alert alert-primary solid alert-dismissible fade show">
        <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        <?= Yii::$app->session->getFlash('publishQueueSet')?>
        <button type="button" class="close h-100" data-dismiss="alert" aria-label="Close"><span><i class="mdi mdi-close"></i></span>
        </button>
    </div>
<?php endif;?>

<div id="accordion-one" class="accordion accordion-primary" style="margin-top: 15px">
	<div class="accordion__item">
		<div class="accordion__header rounded-lg" data-toggle="collapse" data-target="#default_collapseOne"
			 aria-expanded="true" style="width: max-content">
			<span class="accordion__header--text btn bg-primary mb-4 text-white">Filter <iconify-icon icon="material-symbols:filter-alt" class="mt-2"></iconify-icon></span>
			<span class="accordion__header--indicator"></span>
		</div>
		<div id="default_collapseOne" class="accordion__body collapse" data-parent="#accordion-one" style="">
			<div class="accordion__body--text">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="basic-form">
								<?php $form = ActiveForm::begin([
									'method' => 'get',
								]);
								?>
								<div class="row">
									<div class="form-group col-md-3 mb-3">
										<?= $form->field($searchModel, 'title')->textInput()->label('Name') ?>
									</div>
									<div class="form-group col-md-3 mb-3">
										<?= $form->field($searchModel, 'shopify_id')->textInput(['number'])->label('Id') ?>
									</div>
<!--									<div class="form-group col-md-3 mb-3">-->
<!--										--><?php //= $form->field($searchModel, 'is_deleted')->dropDownList(['No', 'Yes'],
//											[
//												'class'  => 'form-control',
//												'prompt' => 'All'
//											])
//										?>
<!--									</div>-->
									<div class="form-group col-md-3 mb-3">
										<?= $form->field($searchModel, 'site_id')->dropDownList(AvailableSite::getSitesDropdown(),
											[
												'class'  => 'form-select p-2',
												'prompt' => 'All'
											])->label('Site')
										?>
									</div>
									<div class="form-group col-md-3 mb-3">
										<?= $form->field($searchModel, 'sku')->textInput(['number'])->label('SKU') ?>
									</div>
                                    <div class="form-group col-md-3 mb-3">
                                        <?= $form->field($searchModel, 'monitoring_price')->dropDownList(['No', 'Yes'],
                                            [
                                                'class'  => 'form-select p-2',
                                                'prompt' => 'All'
                                            ])
                                        ?>
                                    </div>
									<div class="form-group col-md-3 mb-3">
										<?= $form->field($searchModel, 'monitoring_stock')->dropDownList(['No', 'Yes'],
											[
												'class'  => 'form-select p-2',
												'prompt' => 'All'
											])
										?>
									</div>
									<div class="form-group col-md-3 mb-3">
										<?= $form->field($searchModel, 'created_at')->widget(DateRangePicker::class, [
											'convertFormat'  => true,
											'startAttribute' => 'datetime_min',
											'endAttribute'   => 'datetime_max',
                                            'bsVersion' => '4',
											'pluginOptions'  => [
												'timePicker' => false,
												'timePickerIncrement' => 30,
												'locale' => [
													'format' => 'Y-m-d'
												]
											]
										]) ?>
									</div>
									<div class="form-group col-md-3 mb-3">
                                        <?= $form->field($searchModel, 'is_published')->dropDownList(['Draft', 'Published'],
                                            [
                                                'class'  => 'form-select p-2',
                                                'prompt' => 'All'
                                            ])->label('Status')
//                                        ?>
                                        <?php /*echo DateRangePicker::widget([
							'model' => $searchModel,
							'attribute' => 'created_at',
							'convertFormat' => true,
							'startAttribute' => 'datetime_min',
							'endAttribute' => 'datetime_max',
							'pluginOptions'=> [
								'timePicker' => true,
								'timePickerIncrement' => 30,
								'locale' => [
									'format' => 'Y-m-d h:i A'
								]
							]
						]);*/
										?>
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
				</div>
			</div>
		</div>

	</div>
</div>

<?php
if ($displayType != Product::DISPLAY_TABLE_STYLE):?>
    <?= ListView::widget([
        'dataProvider' => $dataProvider,
        'id' => 'product_list',
        'layout' => "{summary}\n<div class=\"row\">{items}</div>\n{pager}",
        'itemView' => $displayType == '_product_table_item',
        'options' => [
            'tag' => 'div',
            'class' => 'row'
        ],
        'itemOptions' => [
            'tag' => 'div',
            'class' => $displayType == Product::DISPLAY_lIST_STYLE ? 'col-lg-12 col-xl-6' : 'col-xl-3 col-lg-6 col-md-6 col-sm-6',
        ],
        'pager' => ['class' => 'frontend\widgets\Bootstrap4LinkPager'],
    ])?>

<?php else:?>
    <div class="modal fade" id="subscribeModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>

                </div>
                <div class="modal-body">
                    <h4><strong>This features is not available in Your plan</strong></h4>
                    <h4><strong>You can change Your plan <a href="<?= Url::to(['/profile/subscribe']) ?>">here</a></strong>
                    </h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="subscribeModal" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>

                </div>
                <div class="modal-body">
                    <h4><strong>Your Monitoring Limit Is Expired</strong></h4>
                    <h4><strong>You can change Your plan <a href="<?= Url::to(['/profile/subscribe']) ?>">here</a></strong>
                    </h4>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-center my-4">
                <h4 class="card-title"><?=$this->title?></h4>
                <div class="d-flex tableHeadControl">
                    <div>
                        <button class="btn btn-primary productBulkMonitoring mr-3"
                            <?php if (!$allowBulkMonitoring || !$allowMonitorNow):?>
                                data-disabled="true"
                            <?php endif?>>
                            Product Bulk Monitoring
                        </button>
                    </div>
                    <div>
                        <?php if ($allowDeleteMultipleProducts):?>
                            <span class="clickagain" style="display: none">Click Again For Deleting</span>
                        <?php endif?>
                        <button class="btn btn-danger deleteMultipleProducts"
                            <?php if (!$allowDeleteMultipleProducts):?>
                                data-disabled="true"
                            <?php endif?>
                        >Delete Multiple Products</button>
                    </div>
                </div>

            </div>
<!--            <div class="card-body">-->
<!--                <div class="d-flex justify-content-center">-->
<!--                    <div class="loading" style="display:none">-->
<!--                        <div class="sk-three-bounce" style="background-color:#2f363e">-->
<!--                            <div class="sk-child sk-bounce1"></div>-->
<!--                            <div class="sk-child sk-bounce2"></div>-->
<!--                            <div class="sk-child sk-bounce3"></div>-->
<!--                        </div>-->
<!--                    </div>-->
<!--                </div>-->

                <div class="product-list">
                    <div class="table-responsive border rounded p-3">
                        <table class="table align-middle text-nowrap mb-0">
                            <thead>
                            <tr>
                                <th scope="col">
                                    <div class="form-check">
                                        <input class="form-check-input bulk-check" type="checkbox" value="" id="flexCheckDefault">
                                    </div>
                                </th>
                                <th><strong>Name</strong></th>
                                <th><strong>Actions</strong></th>
                                <th><strong>Id</strong></th>
                                <th><strong>SKU</strong></th>
                                <th><strong>Price</strong></th>
                                <th><strong>Added</strong></th>
                                <th><strong>Status</strong></th>
                                <th><strong>Monitoring Price</strong></th>
                                <th><strong>Monitoring Stock</strong></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?= ListView::widget([
                                'dataProvider' => $dataProvider,
                                'id' => 'product_list',
                                'layout' => "{summary}\n{items}",
                                'itemView' => '_product_table_item',
                                'summaryOptions' => ['class' => 'col-sm-12'],
                                'options' => [
                                    'tag' => 'div',
                                    'class' => 'row'
                                ],
                                'itemOptions' => [
                                    'tag' => 'tr',
                                ],
                            ]);
                            ?>
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <div class="p-4">
        <?=LinkPager::widget(['pagination' => $dataProvider->pagination])?>
    </div>
<?php endif;?>


