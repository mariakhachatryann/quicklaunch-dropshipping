<?php

use common\helpers\HelpTextHelper;
use common\models\BulkImport;
use common\models\Video;
use frontend\assets\AppAsset;

/**
 * @var BulkImport $model
 * @var bool $allowBulkImport
 * @var Video[] $trainingVideos
 */

$this->title = 'Bulk Import From Category Page';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/importProduct/style.css', ['depends' => [AppAsset::class]]);

?>

<div class="review-settings">
    <div id="import-app">
        <div class="row scrap-url-block">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="section setting-section">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header justify-content-around">
                                        <h4 class="card-title" style="max-width:200px"><?= $this->title ?></h4> <br />
                                        <div class="help-block openHelpModal" data-id="bulk_import">
                                            <i class="fa fa-question-circle"></i>
                                            <div class="pulse-css"></div>
                                        </div>
                                        <?php foreach ($trainingVideos as $video): ?>
                                            <?php if (in_array($video->id, Video::IMPORT_MULTIPLE_VIDEO_IDS)):?>
                                                <div class="help-block openHelpModal"
                                                     data-id="training_video_<?= $video->id ?>">
                                                    <i class="fa fa-play"></i>
                                                    <div class="pulse-css"></div>
                                                </div>
                                            <?php endif;?>
                                        <?php endforeach; ?>

                                        <div class="col-md-7 ml-4 timeline-panel text-muted planChangeMessage">
                                            <p class="mb-0">
                                                Subscribe to higher plans to get more features!
                                                <a class="linkToPlans" href="/profile/subscribe">Check the list of the plans</a>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="input-group">
                                            <p><i>Please, use <a target="_blank"
                                                                 href="<?= Yii::$app->params['chromeExtensionUrl'] ?>">Google
                                                        Chrome extension</a>
                                                    for getting better performance during the importing process of
                                                    products from Shein.
                                                </i></p>
                                            <h6>Paste product category URL you want to import</h6>
                                            <div class="input-group mb-3">
                                                <input
                                                        id="cotegotyBulkImportLink"
                                                        type="text"
                                                        placeholder="https://us.shein.com/Women-Clothing-c-2030.html"
                                                        class="form-control border-0"
                                                >
                                                <button id="bulkImportCreate" class="btn btn-primary <?= !$allowBulkImport ? 'multipleImportDisabledButton' : '' ?> " disabled>Import</button>
                                            </div>
                                            <div class="w-100 justify-content-center d-none"
                                                 id="bulkImportLoading">
                                                <div class="edit-loading">
                                                    <div class="sk-three-bounce" style="background-color: transparent">
                                                        <div class="sk-child sk-bounce1"></div>
                                                        <div class="sk-child sk-bounce2"></div>
                                                        <div class="sk-child sk-bounce3"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- HELP MODAL START  -->
    <div id="helpModal" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span class="help_modal_item_title" data-id="bulk_import" style="display: none">
                            <?=HelpTextHelper::getHelpText('import_new_product','title')?>
                        </span>
                        <?php foreach ($trainingVideos as $video): ?>
                            <?php if (in_array($video->id, Video::IMPORT_MULTIPLE_VIDEO_IDS)):?>
                                <span class="help_modal_item_title" data-id="training_video_<?= $video->id ?>" style="display: none">
                                    <?= $video->title ?>
                                </span>
                            <?php endif;?>
                        <?php endforeach; ?>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="help_modal_item" data-id="bulk_import" style="display: none">
                        <?=HelpTextHelper::getHelpText('import_new_product','text')?>
                    </div>
                    <?php foreach ($trainingVideos as $video): ?>
                        <?php if (in_array($video->id, Video::IMPORT_MULTIPLE_VIDEO_IDS)):?>
                            <div class="help_modal_item" data-id="training_video_<?= $video->id ?>" style="display: none">
                                <?php if (strpos($video->video_url, 'loom.com') !== false):?>
                                    <div style="position: relative; padding-bottom: 53.59375000000001%; height: 0;">
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
                        <?php endif;?>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--  HELP MODAL END  -->
