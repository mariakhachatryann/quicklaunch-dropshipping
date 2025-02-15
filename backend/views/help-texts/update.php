<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\HelpTexts */

$this->title = 'Update Help Texts: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Help Texts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="help-texts-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
