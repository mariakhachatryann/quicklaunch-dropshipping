<?php
/* @var $posts \common\models\Post[]*/
/* @var $category \common\models\Category*/
/* @var $post_id */
$this->title = $category ? $category->title : 'Other';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card">
    <div class="card-body">
        <h4 class="card-title"><?= $category ? $category->title : 'Other';?></h4>
        <p class="m-0 subtitle"><?=$category->parentCategory->title ?? ''?></p>
        <?php if (!empty($posts)) :?>
            <?php foreach ($posts as $key => $post):?>
            <div class="accordion accordion-flush" id="accordionFlushCategory">
                <div class="accordion-item mb-3 cursor-pointer">
                    <div class="accordion-header mb-2" id="flush-headingOne">
                        <button class="accordion-button rounded-lg collapsed bg-primary text-white" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-<?= $post['id']?>" aria-expanded="false" aria-controls="flush-collapse-<?= $post['id']?>">
                            <span><?= $post->title ?></span>
                        </button>
                    </div>
                    <div id="flush-collapse-<?= $post['id']?>" class="accordion-collapse collapse" aria-labelledby="flush-heading" data-bs-parent="#accordionFlushCategory">
                        <?= $post->content ?>
                    </div>
                 </div>
            </div>
            <?php endforeach;?>
        <?php else:?>
            <div class="empty-tickets">
                <h6> No question yet </h6>
            </div>
        <?php endif;?>
    </div>
</div>