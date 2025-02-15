<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\HelpTexts */

$this->title = 'Create Help Texts';
$this->params['breadcrumbs'][] = ['label' => 'Help Texts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="help-texts-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
