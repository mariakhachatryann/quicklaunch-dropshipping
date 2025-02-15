<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 04.07.2019
 * Time: 17:52
 */
?>
<li class="dropdown tasks-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-tasks"></i>
    </a>
    <ul class="dropdown-menu">
        <!--<li class="header">You have 9 tasks</li>-->
        <li>
            <!-- inner menu: contains the actual data -->
            <ul class="menu">
                <li><!-- Task item -->
                    <a href="#">
                        <h3>
                            Products (<?= number_format($productCount)."/".number_format($productLimit)?>)
                            <small class="pull-right"><?= $productPercent?>%</small>
                        </h3>
                        <div class="progress xs">
                            <div class="progress-bar progress-bar-aqua" style="width: <?= $productPercent?>%"
                                 role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                 aria-valuemax="100">
                                <span class="sr-only">20% Complete</span>
                            </div>
                        </div>
                    </a>
                </li>
                <!-- end task item -->
                <li><!-- Task item -->
                    <a href="#">
                        <h3>
                            Monitoring (<?= number_format($monitoringCount)."/".number_format($monitoringLimit)?>)
                            <small class="pull-right"><?= $monitoringPercent?>%</small>
                        </h3>
                        <div class="progress xs">
                            <div class="progress-bar progress-bar-green" style="width: <?= $monitoringPercent?>%"
                                 role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                 aria-valuemax="100">
                                <span class="sr-only">40% Complete</span>
                            </div>
                        </div>
                    </a>
                </li>
                <!-- end task item -->
                <li><!-- Task item -->
                    <a href="#">
                        <h3>
                            Reviews (<?= number_format($reviewsCount)."/".number_format($reviewsLimit)?>)
                            <small class="pull-right"><?= $reviewsPercent?>%</small>
                        </h3>
                        <div class="progress xs">
                            <div class="progress-bar progress-bar-red" style="width: <?= $reviewsPercent?>%"
                                 role="progressbar" aria-valuenow="20" aria-valuemin="0"
                                 aria-valuemax="100">
                                <span class="sr-only">60% Complete</span>
                            </div>
                        </div>
                    </a>
                </li>
                <!-- end task item -->
            </ul>
        </li>
        <li class="footer">
            <a href="/profile/subscribe">View all plans</a>
        </li>
    </ul>
</li>

