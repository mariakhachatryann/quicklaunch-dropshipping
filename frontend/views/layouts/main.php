<?php

use common\models\User;
use common\models\UserSetting;
use yii\bootstrap4\Breadcrumbs;
use yii\helpers\Html;
use frontend\controllers\ProductController;
use common\models\Product;
use frontend\widgets\CustomBreadcrumb;
use common\widgets\Alert;
use yii\helpers\Url;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $content string */

frontend\assets\AppAsset::register($this);
/* @var User $user*/
$user = Yii::$app->user->identity;
//if ($user && $user->userSetting) {
//    $this->registerJs(
//        'localStorage.setItem("theme", "'.array_search($user->userSetting->site_theme, UserSetting::$siteThemes).'");',
//        View::POS_BEGIN
//    );
//}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
        <?= $this->render('_ga')?>
    </head>
    
    <body class="link-sidebar">
        <?php $this->beginBody() ?>
            <div class="preloader">
                <div class="sk-three-bounce">
                    <div class="sk-child sk-bounce1"></div>
                    <div class="sk-child sk-bounce2"></div>
                    <div class="sk-child sk-bounce3"></div>
                </div>
            </div>
            <div id="main-wrapper">
                <a href="<?= Url::toRoute('/site/contact') ?>" style="position: fixed; z-index: 9999999; right:2%; bottom: 5%"
                   data-container="body"
                   data-toggle="tooltip"
                   data-placement="left"
                   data-content="Support"
                   title="Support"
                >
                    <iconify-icon icon="solar:chat-round-bold" style="font-size: 40px"></iconify-icon>
                </a>

            <?= $this->render('_left_navbar') ?>

                <div class="page-wrapper">
                    <?= $this->render('_header') ?>

                    <div class="body-wrapper">
                        <div class="container-fluid">
                            <div class="page-titles">
                                <?= CustomBreadcrumb::widget([
                                    'links' => $this->params['breadcrumbs'] ?? [],
                                ]) ?>
                            </div>
                            <?= Alert::widget() ?>
                            <input type="hidden" value="<?= Yii::$app->params['chromeExtensionUrl'] ?>" id="extensionLink">
                            <?= $content ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="dark-transparent sidebartoggler"></div>
        <?php $this->endBody() ?>
    </body>

</html>
<?php $this->endPage() ?>
