<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\AvailableSite */

$this->title = 'Create Available Site';
$this->params['breadcrumbs'][] = ['label' => 'Available Sites', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="available-site-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
