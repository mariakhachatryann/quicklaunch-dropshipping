<?php
/* @var $posts \common\models\Post[]*/
/* @var $category \common\models\Category*/
$this->title = 'Posts';
$this->params['breadcrumbs'][] = $this->title;
?>

    <div class="card">
        <div class="card-body">
            <?php if (!empty($posts)) :?>
                <?php foreach ($posts as $key => $post):?>
                    <div class="accordion accordion-flush" id="accordionFlushPost">
                        <div class="accordion-item mb-3 cursor-pointer">
                            <div class="accordion-header mb-2" id="flush-headingOne">
                                <button class="accordion-button rounded-lg collapsed bg-primary text-white" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-<?= $post['id']?>" aria-expanded="false" aria-controls="flush-collapse-<?= $post['id']?>">
                                    <span><?= $post->title ?></span>
                                </button>
                            </div>
                            <div id="flush-collapse-<?= $post['id']?>" class="accordion-collapse collapse" aria-labelledby="flush-heading" data-bs-parent="#accordionFlushPost">
<!--                                <div class="accordion__body--text">-->
                                    <?= $post->content ?>
<!--                                </div>-->
                            </div>
                        </div>
                    </div>

                <?php endforeach;?>
            <?php else:?>
                <div class="empty-tickets">
                    <h4> No question yet </h4>
                </div>
            <?php endif;?>
        </div>
    </div>




