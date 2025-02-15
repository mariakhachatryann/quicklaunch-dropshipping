<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PromoCode */

$this->title = 'Create Promo Code';
$this->params['breadcrumbs'][] = ['label' => 'Promo Codes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="promo-code-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
