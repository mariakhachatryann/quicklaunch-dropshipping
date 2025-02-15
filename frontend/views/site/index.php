<?php
/** @var View $this
 * @var string $suggestReviewTitle
 * @var string $suggestReviewText
 * @var AvailableSite[] $sites
 * @var int $productsCount
 */

use common\models\AvailableSite;
use frontend\widgets\InstructionSteps;
use frontend\widgets\LimitPillsWidget;
use frontend\widgets\RecentProducts;
use frontend\widgets\MenuPillsWidget;
use yii\helpers\Html;
use yii\web\View;

$this->title = 'Shionimporter';

if ($suggestReviewTitle && $suggestReviewText) {
	$this->registerJsVar('suggestReviewTitle', $suggestReviewTitle);
	$this->registerJsVar('suggestReviewText', $suggestReviewText);
	$this->registerJs('suggestReview(suggestReviewTitle, suggestReviewText)');
}
    /*$this->registerJs('swal("Exciting News! ShionImporter Now Supports Cornoli.com", `
  <p>Expand your product selection and discover trendy new styles with <a href="https://cornoli.com/" target="_blank">Cornoli.com</a>, now integrated into ShionImporter! Explore their vast collection and seamlessly import products directly into your store.</p>

<p><strong>Here&#39;s what you can expect:</strong></p>

<ul>
	<li><strong>Fresh and On-Demand Products:</strong>&nbsp;Find the latest trends to keep your store stocked with what customers crave.</li>
	<li><strong>Effortless Integration:</strong>&nbsp;Use ShionImporter&#39;s one-click import to effortlessly add Cornoli.com products to your store in seconds.</li>
	<li><strong>Streamlined Dropshipping:</strong>&nbsp;Manage your entire Cornoli.com inventory and fulfillment process directly through ShionImporter.</li>
</ul>

<p><strong>Boost your dropshipping business today! Start exploring <a href="https://cornoli.com/" target="_blank">Cornoli.com</a> with ShionImporter.</strong></p>

    `, "warning")');*/
    //Yii::$app->session->set('update_show', true);

?>
<?= MenuPillsWidget::widget()?>
<div class="card">
    <div class="card-body p-4 ">
        <h4 class="card-title">START HERE</h4>
        <iframe width="100%" height="400px" src="https://www.youtube.com/embed/2gbGwIU3mEU?si=99ojG7QwMyBDiHYe" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
</div>

<?= InstructionSteps::widget(['sites' => $sites]) ?>
<?= LimitPillsWidget::widget(['productsCount' => $productsCount]) ?>
<?php //= RecentProducts::widget() ?>

<!--<input type="hidden" id="extension_t" value="--><?php //= Yii::$app->user->identity->access_token ?><!--">-->

<?\frontend\widgets\GraphicWidget::widget() ?>


