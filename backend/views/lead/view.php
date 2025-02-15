<?php

use common\models\Lead;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Lead */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Leads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="lead-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Answer', ['lead-message/create', 'lead_id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>

        <?= Html::a('Close ticket', ['close', 'id' => $model->id], [
            'class' => 'btn btn-warning',
            'data' => [
                'confirm' => 'Are you sure you want to close this ticket?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'subject_id',
                'value'     => function ($model) {
                    return  $model->subject_id ? $model->subject->title : 'Other';
                }
            ],
            [
                'label' => 'User',
                'attribute' => 'user_id',
                'format' => 'raw',
                'value' => function($model) {
                    return Html::a($model->user->username, Url::toRoute(['/user/view', 'id' => $model->user_id]),['target' => '_blank']);
                },
            ],

            'message:ntext',
			[
				'label' => 'Additional Data',
				'attribute' => 'additional_data',
				'format' => 'raw',
				'value' => function (Lead $model) {
					return Html::a($model->additional_data, $model->additional_data, ['target' => '_blank']);
				}
			],
            [
                'attribute' => 'image',
                'format' => 'raw',
                'value'     => function ($model) {
                    return Html::img($model->imageUrl, ['style' => 'width:150px', 'class' => 'leadImageSmall']);
                }
            ],
            [
                'attribute' => 'status',
                'value'     => function ($model) {
                    return  \common\models\Lead::LEAD_STATUSES[$model->status];
                }
            ],
            [
                'attribute' => 'created_at',
                'value'     => function ($model) {
                    return date('Y-m-d H:i:s', $model->created_at);
                }
            ],
            [
                'attribute' => 'updated_at',
                'value'     => function ($model) {
                    return date('Y-m-d H:i:s', $model->created_at);
                }
            ],
        ],
    ]) ?>

    <?php if (!empty($model->leadImages)) :?>
        <?php foreach ($model->leadImages as $image):?>
            <div style="display: inline-block; margin-top: 20px">
                <?= Html::img( '/backend/web/uploads/lead_images/'.$image->name, ['style' => 'width:250px', 'class' => 'leadImageSmall', 'margin-left' => '15px'])?>
            </div>
        <?php endforeach;?>
    <?php endif;?>

	<h2>User Details</h2>
	<?= $this->render('/user/_userDetailView', ['model' => $model->user]) ?>

	<div class="lead-messages">
        <?php if (!empty($model->leadMessages)) :?>
            <?php foreach ($model->leadMessages as $message):?>
            <div class="lead-message <?= !$message->sender ? 'admin' : 'user' ?>">
                <div class="lead-message-username">
                    <b><?= $message->sender ? ucfirst($message->user->username) : 'Admin' ?></b>
                </div>
                <div class="lead-message-text">
                    <?= $message->message?>

                </div>
                <div class="lead-message-text">
                    <?= Html::img($message->imageUrl, ['class' => 'leadImageSmall', 'style' => 'width:150px'])?>
                </div>

                <?php if ($message->images) : ?>
                <?php foreach($message->images as $image) : ?>
                <div class="lead-message-text">
                    <?= Html::img($image->name, [ 'class' => 'leadImageSmall', 'style' => 'width:150px'])?>
                </div>
                <?php endforeach; ?>
                <?php endif?>
                <div class="lead-message-text reply-message">
                    <?= date('d M Y, H:i', $message->created_at) ?>
                    <div class="reply-button">
                        <?= Html::a('Reply to this message', ['lead-message/create', 'lead_id' => $model->id, 'message_id' => $message->id], ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>
            </div>

            <?php endforeach;?>
        <?php endif;?>

        <div class="leadImageZoom" style="display:none">
            <img class="leadImageZoomPreview" alt="">
        </div>


    </div>

</div>
