<?php
/* @var $plans \common\models\Plan[]*/
/* @var $isCurrentFree bool*/

use common\models\Feature;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use frontend\widgets\MenuPillsWidget;
use common\helpers\HelpTextHelper;
use common\models\Plan;

$this->title = 'Plans';
$this->params['breadcrumbs'][] = $this->title;
$colors = ['danger','info', 'primary'];

?>

<div class="row">
    <?php foreach ($plans as $key=>$plan) : if ($plan->price == 0) continue;?>
        <div class="col-md-4 ">
            <div class="card" style="height: calc(100% - 30px);">
                <div class="card-body mb-0">
                    <h5 class="card-title text-center"><?= $plan->name ?> </h5>

                    <p class="card-text  text-center "><?= $plan->description?></p>
                    <span>Available sites</span>

                    <ul class="list-icons">
                        <?php foreach ($plan->sites as $site):?>
                            <li class="d-flex">
                                <iconify-icon icon="material-symbols:check-small" style="font-size:20px;"></iconify-icon>
                                <span class="card-text"><?= $site->name?></span>
                            </li>
                        <?php endforeach;?>
                    </ul>
                    <br />

                    <ul class="list-icons">
                        <li>
                            <iconify-icon icon="material-symbols:check-small" style="font-size:20px;"></iconify-icon>
                            Trial days: <?=number_format($plan->trial_days)?> days
                        </li>
                        <li>
                            <iconify-icon icon="material-symbols:check-small" style="font-size:20px;"></iconify-icon>
                            Product limit: <?=number_format($plan->product_limit)?> product
                        </li>
                        <li>
                            <iconify-icon icon="material-symbols:check-small" style="font-size:20px;"></iconify-icon>
                            Monitoring limit: <?=number_format($plan->monitoring_limit)?> products
                        </li>
                        <?php  if (Yii::$app->params['enableReview']): ?>
                        <li>
                            <iconify-icon icon="material-symbols:check-small" style="font-size:20px;"></iconify-icon>
                            Review limit: <?=number_format($plan->review_limit)?> reviews
                        </li>
                        <?php endif ?>
                        <?php foreach (Feature::getAllFeatures() as $feature) :?>
                            <li>
                                <?php if (in_array($feature->id, $plan->featuresIds)):?>
                                    <iconify-icon icon="material-symbols:check-small" style="font-size:20px;"></iconify-icon>
                                    <i class="<?= $feature->icon ?>"></i>
                                    <?= $feature->name?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach;?>
                    </ul>
                    
                 
                  
                </div>

                <div class="price text-center">
                    <h1 class="text-center d-inline-flex align-items-center">
                        <?php if ($plan->old_price):?>
                        <del><iconify-icon icon="hugeicons:dollar-02"></iconify-icon>
                            <?= $plan->old_price ?>
                        </del>
                           <br>
                            <?= $plan->price ?>
                        <?php else:?>
                            <iconify-icon icon="hugeicons:dollar-02"></iconify-icon>
                            <?= $plan->price ?>
                        <?php endif?>
                    </h1>
                    <small class="d-block mt-2">per month</small>
                </div>
                <div class="card-footer text-center bg-transparent border-0">
                    <?php if (Yii::$app->user->identity->plan_id == $plan->id &&
                        Yii::$app->user->identity->plan_status == \common\models\Plan::PLAN_ACTIVE):?>
                        <?php if (Yii::$app->user->identity->cancelled_plan == 1) :?>
                            <?= Html::submitButton('Canceled', ['class' => 'btn btn-primary ', 'style' => "background: <?= $colors[$key] ?> , border-color: #ffffff"]) ?>
                        <?php else:?>
                            <form method="POST" action="<?= Url::toRoute('profile/cancel-plan') ?>" >
                                <button type="submit" class="btn bg-primary subscribe-plan-cancel"
                                        data-title = "<?= HelpTextHelper::getHelpText('plan_subscription_cancel', 'title')?>"
                                        data-text = "<?= HelpTextHelper::getHelpText('plan_subscription_cancel', 'text')?>"
                                        style="border: 1px solid silver; color: #fff"

                                >
                                    Cancel
                                </button>
                            </form>
                        <?php endif;?>

                    <?php else:?>
                        <form method="POST" action="<?= Url::toRoute('profile/subscribe-plan') ?>">
                            <input type="hidden" value="<?= $plan->id?>" name="planId">
                            <input type="hidden" value="<?= $isCurrentFree?>" name="isCurrentFree">
                            <input type="hidden" name="debug" value="<?=Yii::$app->params['debug']?>">

                            <button type="submit" class="btn bg-primary subscribe-plan"
                                    data-title="<?= HelpTextHelper::getHelpText('plan_subscribe', 'title')?>"
                                    data-text = "<?= HelpTextHelper::getHelpText('plan_subscribe', 'text')?>"
                                style="border: 1px solid silver; color: #fff"
                            >
                                Subscribe
                            </button>
                        </form>
                    <?php endif;?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>