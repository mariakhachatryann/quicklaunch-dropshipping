<?php

use yii\helpers\Url;

$faqItems = \common\models\FaqCategory::categoryPostsItems();

?>
<aside class="left-sidebar with-vertical">
    <div>
        <div>

            <div class="brand-logo d-flex align-items-center">
                <a href="/" class="d-flex text-nowrap logo-img">
                    <img class="logo-abbr" src="/images/logo.png" alt="">
                    <img class="brand-title brand-title-silver" src="/images/logo-text-silver.png" alt="">
                </a>
            </div>

            <nav class="sidebar-nav scroll-sidebar" data-simplebar>
                <ul class="sidebar-menu" id="sidebarnav">
                    <li>
                        <span class="sidebar-divider lg"></span>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="/" title="Home" id="get-url" aria-expanded="false">
                            <iconify-icon icon="solar:home-2-broken" class=""></iconify-icon>
                            <span class="hide-menu">Home</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= Url::toRoute('/products') ?>" title="Imported products" aria-expanded="false">
                            <iconify-icon icon="solar:import-broken" class=""></iconify-icon>
                            <span class="hide-menu">Imported Products</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= Url::toRoute('/store/niche-selection') ?>" title="Niche" aria-expanded="false">
                            <iconify-icon icon="material-symbols:category-outline" class=""></iconify-icon>
                            <span class="hide-menu">Niche Selection</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= Url::toRoute('/site/training-videos') ?>" title="Training Videos" aria-expanded="false">
                            <iconify-icon icon="solar:videocamera-outline" class=""></iconify-icon>
                            <span class="hide-menu">Training videos</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void()" aria-expanded="false" title="Lead">
                            <iconify-icon icon="solar:chat-round-line-duotone"></iconify-icon>
                            <span class="hide-menu">Support</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= Url::toRoute('/site/contact') ?>">
                                    <iconify-icon icon="solar:phone-linear"></iconify-icon>
                                    <span class="hide-menu">Contact us</span>
                                </a>
                            </li>
                            <li class="sidebar-item">
                                <a class="sidebar-link" href="<?= Url::toRoute('/site/tickets') ?>">
                                    <iconify-icon icon="solar:letter-opened-line-duotone"></iconify-icon>
                                    <span class="hide-menu">Opened tickets</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link has-arrow" href="javascript:void()" aria-expanded="false">
                            <iconify-icon icon="solar:question-circle-line-duotone" class=""></iconify-icon>
                            <span class="hide-menu">FAQ</span>
                        </a>
                        <ul aria-expanded="false" class="collapse first-level">
                            <?php foreach ($faqItems as $faqItem): ?>
                                <li class="sidebar-item">
                                    <?php if (!empty($faqItem['items'])): ?>
                                        <a class="sidebar-link" href="javascript:void()">
                                            <iconify-icon icon="material-symbols:info-outline"></iconify-icon>
                                            <span class="hide-menu"><?= $faqItem['label'] ?></span>
                                        </a>
                                        <ul aria-expanded="false">
                                            <?php foreach ($faqItem['items'] ?? [] as $item): ?>
                                                <li>
                                                    <a class="sidebar-link" href="<?= Url::toRoute($item['url']) ?>">
                                                        <iconify-icon icon="material-symbols:info-outline"></iconify-icon>
                                                        <?= $item['label'] ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>

                                    <?php else: ?>
                                        <a class="sidebar-link" href="<?= Url::toRoute($faqItem['url']) ?>">
                                            <iconify-icon icon="material-symbols:info-outline"></iconify-icon>
                                            <span class="hide-menu"><?= $faqItem['label'] ?></span>
                                        </a>
                                    <?php endif ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>

                    <li class="sidebar-item">
                        <a class="sidebar-link" href="https://jointherealworld.com/?a=mkwpkzrbtq" title="Learn Dropshipping" target="_blank" >
                            <iconify-icon icon="solar:book-2-linear"></iconify-icon>
                            <span class="hide-menu">Learn Dropshipping</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= Url::toRoute('/recommended-product/index') ?>" title="Recommended products">
                            <iconify-icon icon="solar:star-line-duotone"></iconify-icon>
                            <span class="hide-menu">Recommended Proucts</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= Url::toRoute('/orders') ?>" title="Orders">
                            <iconify-icon icon="solar:document-text-line-duotone"></iconify-icon>
                            <span class="hide-menu">Orders</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= Url::toRoute('/product-review') ?>" title="Reviews">
                            <iconify-icon icon="solar:star-circle-linear"></iconify-icon>
                            <span class="hide-menu">Reviews</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= Url::toRoute('/profile/settings') ?>" title="Settings">
                            <iconify-icon icon="solar:settings-outline"></iconify-icon>
                            <span class="hide-menu">Settings</span>
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link" href="<?= Url::toRoute('/profile/subscribe') ?>" title="Settings">
                            <iconify-icon icon="solar:dollar-outline"></iconify-icon>
                            <span class="hide-menu">Plans</span>
                        </a>
                    </li>
                    <li>
                        <span class="sidebar-divider lg"></span>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</aside>