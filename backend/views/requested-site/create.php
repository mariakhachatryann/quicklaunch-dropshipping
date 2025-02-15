<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\RequestedSite */

$this->title = 'Create Requested Site';
$this->params['breadcrumbs'][] = ['label' => 'Requested Sites', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="requested-site-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
