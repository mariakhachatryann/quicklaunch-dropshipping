<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\CaptchaSolverLog */

$this->title = 'Create Captcha Solver Log';
$this->params['breadcrumbs'][] = ['label' => 'Captcha Solver Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="captcha-solver-log-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
