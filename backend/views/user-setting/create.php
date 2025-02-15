<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UserSetting */

$this->title = 'Create User Setting';
$this->params['breadcrumbs'][] = ['label' => 'User Settings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-setting-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
