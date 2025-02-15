<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AlertCaptcha */

$this->title = 'Update Alert Captcha: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Alert Captchas', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="alert-captcha-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
