<?php

use yii\helpers\Html;
use common\models\User;

$date = $model['date'];
$total = $model['total_price'];
$address = $model['address'];
$products = $model['lineItems'];
$class = $key == 0 ? '' : 'collapsed-box';
$openIcon = $key == 0 ? 'minus' : 'plus';

?>
<div class="box box-primary <?=$class?>">
    <div class="box-header with-border">
        <h3 class="box-title"><?= $date->format('Y-m-d')?> Order# <?=$model['number']?></h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-<?=$openIcon?>"></i>
            </button>
        </div>
        <!-- /.box-tools -->
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <section class="invoice">
            <!-- title row -->
            <div class="row">
                <div class="col-xs-12">
                    <h2 class="page-header">
                        <i class="fa fa-globe"> Order <?= $model['id']?></i>
                        <small class="pull-right">Date: <?= $date->format('Y-m-d H:s')?></small>
                    </h2>
                </div>
                <!-- /.col -->
            </div>
            <!-- info row -->
            <div class="row invoice-info">
                <div class="col-sm-4 invoice-col">
                    <address>
                        <strong>Address</strong><br>
                        Firstname : <?= $address['address_firstName']?><br>
                        Lastname : <?= $address['address_lastName']?><br>
                        Company : <?= $address['address_company']?><br>
                        Address 1 : <?= $address['address_address1']?><br>
                        Address 2 : <?= $address['address_address2']?><br>
                        City : <?= $address['address_city']?><br>
                        Province : <?= $address['address_province']?><br>
                        Country : <?= $address['address_country']?><br>
                        Zip : <?= $address['address_zip']?><br>
                        Phone : <?= $address['address_phone']?><br>
                        Name : <?= $address['address_name']?><br>
                        Province Code : <?= $address['address_provinceCode']?><br>
                        Country Code : <?= $address['address_countryCode']?><br>
                        Country Name : <?= $address['address_countryName']?><br>
                    </address>
                </div>
                <!-- /.col -->
                <div class="col-sm-4 invoice-col">
                    <address>
                        <strong>John Doe</strong><br>
                        795 Folsom Ave, Suite 600<br>
                        San Francisco, CA 94107<br>
                        Phone: (555) 539-1037<br>
                        Email: john.doe@example.com
                    </address>
                </div>
                <!-- /.col -->
                <div class="col-sm-4 invoice-col">
                    <b>Invoice #007612</b><br>
                    <br>
                    <b>Order ID:</b> 4F3S8J<br>
                    <b>Payment Due:</b> 2/22/2014<br>
                    <b>Account:</b> 968-34567
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->

            <!-- Table row -->
            <div class="row">
                <div class="col-xs-12 table-responsive">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>Id</th>
                            <th>Product</th>
                            <th>Product Source</th>
                            <th>Price</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $product):?>
                            <tr>
                                <td><?= $product['product_id']?></td>
                                <td>
                                    <?= Html::a($product['title'], Yii::$app->user->identity->shopUrl.'/admin/products/'.$product['product_id'], ['target' => '_blank'])?>
                                </td>
                                <td>
                                    <?php if (!empty($product['product_src_url'])):?>
                                        <?= Html::a('Order', $product['product_src_url'], ['target' => '_blank','class' => "btn btn-primary"])?>
                                    <?php endif; ?>
                                </td>
                                <td><?= $product['price']?></td>
                            </tr>
                        <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->

            <div class="row">
                <!-- accepted payments column -->
                <div class="col-xs-6">

                </div>
                <!-- /.col -->
                <div class="col-xs-6">
                    <p class="lead">Amount Due 2/22/2014</p>

                    <div class="table-responsive">
                        <table class="table">
                            <tr>
                                <th style="width:50%">Subtotal:</th>
                                <td>$0</td>
                            </tr>
                            <tr>
                                <th>Tax</th>
                                <td>$0</td>
                            </tr>
                            <tr>
                                <th>Shipping:</th>
                                <td>$0</td>
                            </tr>
                            <tr>
                                <th>Total:</th>
                                <td><?= $total?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->

        </section>
    </div>
    <!-- /.box-body -->
</div>

