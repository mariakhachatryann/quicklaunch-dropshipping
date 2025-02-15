<?php
/* @var $products \common\models\Product*/

use common\models\Product;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;
?>
<?php if (!empty($products)) :?>

<div class="row">
    <div class="col-xl-12 col-lg-12 col-xxl-12 col-sm-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Last added products</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive recentOrderTable">
                <table class="table verticle-middle table-responsive-md">
                    <tbody>
                    <?php foreach ($products as $product) :?>
                        <tr>
                            <td><?= $product->id ?></td>
                            <td><?= StringHelper::truncateWords($product->title, 10)?></td>
                            <td><?= date(Product::DATE_DISPLAY_FORMAT, $product->created_at)?></td>
                            <td class="pl-2 pr-2">
                                <div class="text-center mb-2">
                                    <?=$this->render('@frontend/views/product/_action_buttons',['model' => $product])?>
                                </div>
                            </td>
                        </tr>

                    <?php endforeach;?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<?php endif;?>