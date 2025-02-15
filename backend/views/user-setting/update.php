<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UserSetting */

$this->title = 'Update User Setting: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'User Settings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="user-setting-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
