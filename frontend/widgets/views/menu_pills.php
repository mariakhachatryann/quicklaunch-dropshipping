<?php
use yii\helpers\Url;

$homePage = (Yii::$app->controller->id == 'site' && Yii::$app->controller->action->id == 'index') ? true : false;
$col = $homePage ? 4 : 3;
?>

<div class="row">
   <?php if (!$homePage):?>
    <div class="col-lg-4 col-md-6">
        <div class="card text-bg-success">
            <div class="card-body">
            <a href="<?= Url::to(['/']) ?>"  class="text-decoration-none text-reset hover:text-reset">
                <div class="d-flex align-items-center gap-6">
                    <iconify-icon icon="solar:home-bold-duotone" style="font-size: 40px;"></iconify-icon>
                    <div class="text-white">
                        <h5 class="text-white mb-1">
                            Home
                        </h5>
                        <small class="text-white mb-0 opacity-75">Follow steps for using our application</small>
                    </div>
                        </div>
                    </a>
                </div>
        </div>
    </div>

 <?php endif;?>
    <div class="col-lg-4 col-md-6">
        <div class="card text-bg-danger">
            <div class="card-body">
                <a href="<?= Url::to(['/site/training-videos']) ?>" class="text-decoration-none text-reset hover:text-reset">
                    <div class="d-flex align-items-center gap-6">
                        <iconify-icon icon="solar:videocamera-broken" style="font-size: 40px;"></iconify-icon>
                        <div>
                            <h5 class="text-white mb-1">
                                Training videos
                            </h5>
                            <small class="text-white mb-0 opacity-75">Check training videos for using our application effectively</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card text-bg-primary">
            <div class="card-body">
                <a href="<?= Url::to(['/site/category', 'id' => $firstFaqId]) ?>"  class="text-decoration-none text-reset hover:text-reset">
                    <div class="d-flex align-items-center gap-6">
                        <iconify-icon icon="solar:info-circle-linear" style="font-size: 40px;"></iconify-icon>
                        <div>
                            <h5 class="text-white mb-1">
                                FAQ
                            </h5>
                            <small>Here you can find getting started guide and answers to common questions</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card text-bg-secondary">
            <div class="card-body">
                <a href="<?= Url::to(['/site/contact']) ?>"  class="text-decoration-none text-reset hover:text-rese">
                    <div class="d-flex align-items-center gap-6">
                        <iconify-icon icon="solar:letter-linear" style="font-size: 40px;"></iconify-icon>
                        <div>
                            <h5 class="text-white mb-1">
                                Contact us
                            </h5>
                            <small>If you still have questions, <br> let us know</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>