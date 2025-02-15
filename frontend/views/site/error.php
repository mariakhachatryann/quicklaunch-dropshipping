<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>
<h4><i class="fa fa-exclamation-triangle text-danger"></i> <?= $name ?></h4>
<p><?= nl2br(Html::encode($message)) ?></p>
<div>
    <a class="btn btn-primary" href="/">Back to Home</a>
</div>