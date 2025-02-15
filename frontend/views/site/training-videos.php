<?php
/* @var $videos common\models\Video[]*/
$this->title = 'Training videos';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="card card-body">
    <h4 class="mb-4"><?=$this->title?></h4>

    <?php if (!empty($videos)) :?>
        <?php foreach ($videos as $key => $video):?>
            <div class="accordion accordion-flush" id="accordionFlushVideo">
                <div class="accordion-item mb-3 cursor-pointer">
                    <div class="accordion-header mb-2" id="flush-headingOne">
                        <button class="accordion-button rounded-lg collapsed bg-primary text-white" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse-<?= $video['id']?>" aria-expanded="false" aria-controls="flush-collapse-<?= $video['id']?>">
                            <span><?= $video->title ?></span>
                        </button>
                    </div>
                    <div id="flush-collapse-<?= $video['id']?>" class="accordion-collapse collapse" aria-labelledby="flush-heading" data-bs-parent="#accordionFlushVideo">
                        <?= $video->description ?>
                        <?php if (strpos($video->video_url, 'loom.com') !== false):?>
                            <div class="position-relative" style="padding-bottom: 53.59375000000001%; height: 0;">
                                <iframe src="<?= $video->video_url ?>" frameborder="0" webkitallowfullscreen
                                        mozallowfullscreen allowfullscreen
                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;"></iframe>
                            </div>
                        <?php else:?>
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe id="embed-responsive-item" src="https://www.youtube.com/embed/<?= $video->youtubeId?>"
                                        frameborder="0" allowfullscreen></iframe>
                            </div>
                        <?php endif?>
                    </div>
                </div>
            </div>

        <?php endforeach;?>
    <?php else:?>
        <div class="empty-tickets">
            <h4> No Videos yet </h4>
        </div>
    <?php endif;?>
</div>
