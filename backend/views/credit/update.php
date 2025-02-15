<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Credit */
/* @var $user_id integer */
/* @var $plans array */

$this->title = 'Credit ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Credits', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="available-site-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'plans' => $plans,
        'user_id' => $user_id,
    ]) ?>

</div>
