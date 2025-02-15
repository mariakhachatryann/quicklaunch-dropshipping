<?php

use common\models\Lead;
use common\models\User;
use yii\helpers\Url;

$faqFirst = \common\models\Category::categoryPostsFirstItem();
?>
<?php //= \frontend\widgets\TicketsWidget::widget()?>

<header class="topbar">
    <div class="with-vertical">
        <nav class="navbar navbar-expand-lg p-0">
            <ul class="navbar-nav">
                <li class="nav-item nav-icon-hover-bg rounded-circle d-flex">
                    <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                        <iconify-icon icon="solar:hamburger-menu-line-duotone" class="fs-6"></iconify-icon>
                    </a>
                </li>
                <li class="nav-item d-none d-xl-flex rounded-circle">

                    <div class="input-group search-area ml-auto d-inline-flex">
                        <input type="text" class="form-control" id="search-post" placeholder="Search here" value="<?= Yii::$app->request->get('keyword')?>" />
                        <button class="input-group-text search-button" type="button">
                            <iconify-icon icon="solar:magnifer-linear" class="fs-2"></iconify-icon>
                        </button>

                    </div>
                    <div id="search-result"></div>

                </li>
            </ul>
            <a class="navbar-toggler p-0 border-0 nav-icon-hover-bg rounded-circle" href="javascript:void(0)" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <iconify-icon icon="solar:menu-dots-bold-duotone" class="fs-6"></iconify-icon>
            </a>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="d-flex align-items-center justify-content-between">
                    <ul class="navbar-nav flex-row mx-auto ms-lg-auto align-items-center justify-content-center">
                        <li class="nav-item dropdown">
                            <a href="javascript:void(0)" class="nav-link nav-icon-hover-bg rounded-circle d-flex d-lg-none align-items-center justify-content-center" data-bs-toggle="offcanvas" data-bs-target="#mobilenavbar" aria-controls="offcanvasWithBothOptions">
                                <iconify-icon icon="solar:sort-line-duotone" class="fs-6"></iconify-icon>
                            </a>
                        </li>

                        <li class="nav-item d-block d-xl-none">
                            <a class="nav-link nav-icon-hover-bg rounded-circle" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                <iconify-icon icon="solar:magnifer-line-duotone" class="fs-6"></iconify-icon>
                            </a>
                        </li>

                        <li class="nav-item d-block">
                            <a class="nav-link nav-icon-hover-bg rounded-circle"  href="<?= Url::toRoute('/profile/settings') ?>" data-bs-target="#exampleModal">
                                <iconify-icon icon="solar:settings-linear" class="fs-6"></iconify-icon>
                            </a>
                        </li>

                        <?php iF($faqFirst): ?>
                            <li class="nav-item d-block">
                                <a class="nav-link nav-icon-hover-bg rounded-circle"  href="<?= Url::toRoute('/site/faq-category/'.$faqFirst->id) ?>" data-bs-target="#exampleModal">
                                    <iconify-icon icon="solar:question-circle-linear" class="fs-6"></iconify-icon>
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item dropdown nav-icon-hover-bg rounded-circle">
                            <?= \frontend\widgets\NotificationsWidget::widget() ?>
                        </li>

                        <li class="nav-item dropdown nav-icon-hover-bg rounded-circle p-2 w-auto">
                            <a href="javascript:;" data-toggle="dropdown" class="text-reset text-decoration-none">
                                <span><?= Yii::$app->user->identity->username ?><i class="fa fa-caret-down ml-3" aria-hidden="true"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a href="/site/logout" data-method="post" class="dropdown-item ai-icon">
                                    <svg id="icon-logout" xmlns="http://www.w3.org/2000/svg" class="" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                    <span class="ml-2">Logout </span>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
</header>

