<?php

/* @var $this yii\web\View */
/* @var $content string */

use yii\bootstrap4\Html;

$this->beginPage();

?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= Html::encode($this->title) ?></title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon.png">
    <link href="/css/style.css" rel="stylesheet">
    <?php $this->head() ?>
    <?= $this->render('_ga')?>
</head>

<body class="h-100">
<?php $this->beginBody() ?>
<div class="authincation h-100">
    <div class="container h-100">
        <div class="row justify-content-center h-100 align-items-center">
            <div class="col-md-5">
                <div class="form-input-content text-center error-page">
                    <?=$content?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endBody() ?>
</body>
<!--**********************************
	Scripts
***********************************-->
<!-- Required vendors -->
<script src="/vendor/global/global.min.js"></script>
<script src="/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="/js/custom.min.js"></script>
<script src="/js/deznav-init.js"></script>

</html>
<?php $this->endPage() ?>