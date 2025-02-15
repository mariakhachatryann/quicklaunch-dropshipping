<?php

use backend\assets\AppAsset;
use backend\models\Admin;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use backend\widgets\LeadWidget;

AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<?php $this->beginBody() ?>

<div class="wrapper">
    <header class="main-header">
        <a href="<?= \yii\helpers\Url::to(['site/index']) ?>" class="logo">
            <span class="logo-lg"><?= Html::encode(Yii::$app->name) ?></span>
        </a>
        <nav class="navbar navbar-static-top" role="navigation">
            <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>
            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <?php if (Yii::$app->user->isGuest): ?>
                        <li><?= Html::a('Login', ['/site/login']) ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>

    <aside class="main-sidebar">
        <section class="sidebar">
            <ul class="sidebar-menu" data-widget="tree">
                <li class="header">MAIN NAVIGATION</li>
                <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role_type != Admin::ROLE_CAPTCHA_SOLVER): ?>
                    <li><?= Html::a('<i class="fa fa-dashboard"></i> Home', ['/site/index']) ?></li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-users"></i> <span>Users</span></a>
                        <ul class="treeview-menu">
                            <li><?= Html::a('Users', ['/user/index']) ?></li>
                            <li><?= Html::a('Charges', ['/user-charge/index']) ?></li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-cubes"></i> <span>Plans</span></a>
                        <ul class="treeview-menu">
                            <li><?= Html::a('Sites', ['/available-site/index']) ?></li>
                            <li><?= Html::a('Uninstalls', ['/uninstall/index']) ?></li>
                            <li><?= Html::a('Plans', ['/plan/index']) ?></li>
                            <li><?= Html::a('Plan Features', ['/feature/index']) ?></li>
                            <li><?= Html::a('Promo Code', ['/promo-code/index']) ?></li>
                        </ul>
                    </li>
                    <?= LeadWidget::widget() ?>
                    <li><?= Html::a('<i class="fa fa-product-hunt"></i> Products', ['/product/index']) ?></li>
                    <li><?= Html::a('<i class="fa fa-bell"></i> Notifications', ['/notification/index']) ?></li>
                    <li><?= Html::a('<i class="fa fa-envelope"></i> Email Templates', ['/email-template/index']) ?></li>
                    <li><?= Html::a('<i class="fa fa-info-circle"></i> Help Texts', ['/help-texts/index']) ?></li>
                    <li class="treeview">
                        <a href="#"><i class="fa fa-tasks"></i> <span>Queues</span> <span class="caret"></span></a>
                        <ul class="treeview-menu">
                            <li><?= Html::a('Product Publish Queue', ['/product-publish-queue/index']) ?></li>
                            <li><?= Html::a('Monitoring Queue', ['/monitoring-queue/index']) ?></li>
                            <li><?= Html::a('Import Queue', ['/import-queues/index']) ?></li>
                            <li><?= Html::a('Failed Queue', ['/failed-queue/index']) ?></li>
                            <li><?= Html::a('Alert Captchas', ['/alert-captcha/index']) ?></li>
                            <li><?= Html::a('Captcha Solvers', ['/captcha-solver/index']) ?></li>
                        </ul>
                    </li>
                    <li><?= Html::a('<i class="fa fa-user"></i> Admin Users', ['/admin-user/index']) ?></li>
                    <li><?= Html::a('<i class="fa fa-user"></i> Captcha Solver Logs', ['/captcha-solver-log/index']) ?></li>
                    <li>
                        <?= Html::beginForm(['/site/logout'], 'post')
                        . Html::submitButton(
                            '<i class="fa fa-sign-out"></i> Logout (' . Yii::$app->user->identity->username . ')',
                            ['class' => 'btn btn-link logout']
                        )
                        . Html::endForm()
                        ?>
                    </li>

                <?php endif ?>

                <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->role_type == Admin::ROLE_CAPTCHA_SOLVER): ?>
                    <li><?= Html::a('<i class="fa fa-eye"></i> Monitoring Queue', ['/monitoring-queue/index']) ?></li>
                    <li><?= Html::a('<i class="fa fa-upload"></i> Import Queue', ['/import-queues/index']) ?></li>
                    <li><?= Html::a('<i class="fa fa-times-circle"></i> Failed Queue', ['/failed-queue/index']) ?></li>
                    <li><?= Html::a('<i class="fa fa-exclamation-circle"></i> Alert Captchas', ['/alert-captcha/index']) ?></li>
                    <li><?= Html::a('<i class="fa fa-users"></i> Captcha Solvers', ['/captcha-solver/index']) ?></li>

                    <li>
                        <?= Html::beginForm(['/site/logout'], 'post')
                        . Html::submitButton(
                            '<i class="fa fa-sign-out"></i> Logout (' . Yii::$app->user->identity->username . ')',
                            ['class' => 'btn btn-link logout']
                        )
                        . Html::endForm()
                        ?>
                    </li>
                <?php endif ?>
            </ul>
        </section>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <h1><?= Html::encode($this->title) ?></h1>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs'] ?? []]) ?>
        </section>

        <section class="content">
            <?= Alert::widget() ?>
            <?= $content ?>
        </section>
    </div>

    <footer class="main-footer">
        <div class="pull-right hidden-xs">
            <?= Yii::powered() ?>
        </div>
        <strong>&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></strong> All rights reserved.
    </footer>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
