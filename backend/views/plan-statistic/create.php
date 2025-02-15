<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PlanStatistic */

$this->title = 'Create Plan Statistic';
$this->params['breadcrumbs'][] = ['label' => 'Plan Statistics', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="plan-statistic-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
