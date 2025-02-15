<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductReviewImage */

$this->title = 'Update Product Review Image: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Product Review Images', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="product-review-image-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
