<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Credit */
/* @var $plans array */
/* @var $user_id integer */

$this->title = 'Create Credit';
$this->params['breadcrumbs'][] = ['label' => 'Credit', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="available-site-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'plans' => $plans,
        'user_id' => $user_id
    ]) ?>

</div>
