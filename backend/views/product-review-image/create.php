<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductReviewImage */

$this->title = 'Create Product Review Image';
$this->params['breadcrumbs'][] = ['label' => 'Product Review Images', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-review-image-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
