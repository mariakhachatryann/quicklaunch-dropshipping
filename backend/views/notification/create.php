<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model \common\models\Notification*/

$this->title = 'Create Notification';
$this->params['breadcrumbs'][] = ['label' => 'Notifications', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', compact('model')) ?>

</div>
