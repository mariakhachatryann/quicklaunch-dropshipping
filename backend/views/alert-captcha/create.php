<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AlertCaptcha */

$this->title = 'Create Alert Captcha';
$this->params['breadcrumbs'][] = ['label' => 'Alert Captchas', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alert-captcha-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
