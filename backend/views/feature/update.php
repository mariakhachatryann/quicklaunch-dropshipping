<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Feature */

$this->title = 'Update Feature: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Features', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="feature-update">

    <?= Html::a('Sort features',['/feature/sort'],['class' => 'btn btn-primary'])?>

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>



</div>
