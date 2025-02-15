<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

?>

<div class="row">
    <div class="col-xl-12 col-lg-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Instruction steps</h4>
                <div id="DZ_W_TimeLine1" class="widget-timeline style-1 ps">
                    <ul class="timeline">
                        <li>
                            <div class="timeline-badge <?= $color = Yii::$app->user->identity->plan_id ? 'success' : 'dark' ?> ">
                            </div>
                            <div class="timeline-panel text-muted">
                                <h3 class="mb-0">1. Choose Your plan </h3>
                                <p class="mb-0">Choose Your plan and subscribe to be able to use our application</p>
                                <p>
                                    <?= Html::a('Subscribe', ['/profile/subscribe'], ['class' => 'btn btn-outline-warning btn-sm', 'target' => '_blank']) ?>
                                </p>
                            </div>
                        </li>

                        <li>
                            <div class="timeline-badge <?= $color = Yii::$app->user->identity->videos_checked ? 'success' : 'dark' ?>">
                            </div>
                            <div class="timeline-panel text-muted">
                                <h3 class="mb-0">2. FAQ</h3>
                                <p class="mb-0"> Here you can find getting started guide and answers to common questions</p>
                                <p>
                                    <?= Html::a('View', ['/site/category', 'id' => $firstFaqId], ['class' => 'btn btn-outline-warning btn-sm', 'target' => '_blank']) ?>
                                </p>
                            </div>
                        </li>

                        <li>
                            <div class="timeline-badge dark">
                            </div>
                            <div class="timeline-panel text-muted">
                                <h3 class="mb-0">3. Install Google Chrome extension</h3>
                                <p class="mb-0">Install <?= Html::a('Google Chrome extension', Yii::$app->params['chromeExtensionUrl'], ['target' => '_blank'])?> for getting better performance during the importing process of products from Shein</p>
                                <p>
                                    <?= Html::a('Install', Yii::$app->params['chromeExtensionUrl'], ['class' => 'btn btn-outline-success btn-sm', 'target' => '_blank']) ?>
                                    <?= Html::a('See training video', '/site/category/1/29', ['class' => 'btn btn-outline-warning btn-sm', 'target' => '_blank']) ?>
                                </p>
                            </div>
                        </li>

                        <li>
                            <div class="timeline-badge <?= $color = !empty(Yii::$app->user->identity->getProducts()->count()) ? 'success' : 'dark' ?> ">
                            </div>
                            <div class="timeline-panel text-muted">
                                <h3 class="mb-0">4. Import Products</h3>
                                <p class="mb-0">
                                    Import products to your Shopify store from the following sites
                                </p>
                                <?php foreach ($sites as $site): ?>
                                    <?= Html::a($site->name, $site->url, ['class' => 'btn btn-outline btn-sm mb-2 mt-1', 'target' => '_blank', 'style' => 'background-color:' . $site->color . ';color:white']) ?>
                                <?php endforeach; ?>

                            </div>
                        </li>
                    </ul>
<!--                    <div class="ps__rail-y" style="top: 0px; height: 370px; right: 0px;">-->
<!--                        <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 250px;"></div>-->
<!--                    </div>-->
                </div>
            </div>
        </div>
    </div>
</div>
