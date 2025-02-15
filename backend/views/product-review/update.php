<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductReview */

$this->title = 'Update Product Review: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Product Reviews', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="product-review-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
