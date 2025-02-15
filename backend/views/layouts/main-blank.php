<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;


AppAsset::register($this);
?>

<?php $this->beginPage(); ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="UTF-8"/>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head(); ?>
</head>
<body>
<?php $this->beginBody(); ?>

<div class="container">
    <?= $content ?>
</div>

<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
