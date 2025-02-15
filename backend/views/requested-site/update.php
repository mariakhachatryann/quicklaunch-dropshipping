<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\RequestedSite */

$this->title = 'Update Requested Site: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Requested Sites', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="requested-site-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
