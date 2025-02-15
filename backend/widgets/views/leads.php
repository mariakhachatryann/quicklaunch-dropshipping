<?php
?>

<li class="treeview">

    <a href="#">
        <i class="fa fa-question-circle"></i>
        <span>Help Center</span>
        <span class="caret"></span>
    </a>
    <ul class="treeview-menu">
        <li>
            <a href="<?= \yii\helpers\Url::toRoute(['/post/index']) ?>">
                FAQ
            </a>
        </li>
        <li>
            <a href="<?= \yii\helpers\Url::toRoute(['/category/index']) ?>">
                Categories
            </a>
        </li>
        <li>
            <a href="<?= \yii\helpers\Url::toRoute(['/video/index']) ?>">
                Videos
            </a>
        </li>
        <li>
            <a href="<?= \yii\helpers\Url::toRoute(['/subject/index']) ?>">
                Subjects
            </a>
        </li>
        <li>
            <a href="<?= \yii\helpers\Url::toRoute(['/lead/index']) ?>" <?php if ($unreadLeadsCount) : ?> class="new-ticket" <?php endif; ?>>
                Tickets
            </a>
        </li>
        <li>
            <a href="<?= \yii\helpers\Url::toRoute(['/requested-site/index']) ?>">
                Requested Sites
            </a>
        </li>
    </ul>
</li>



