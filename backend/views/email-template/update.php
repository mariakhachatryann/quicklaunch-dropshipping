<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\EmailTemplate */

$this->title = 'Update Email Template: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Email Templates', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="email-template-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
