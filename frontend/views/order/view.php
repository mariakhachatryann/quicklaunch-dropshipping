<?php
/* @var array $orderData*/
$this->title = 'Order #'.(1000 + $orderData['number']);
$this->params['breadcrumbs'][] = ['label' => 'Order', 'url' => ['/order']];
$this->params['breadcrumbs'][] = $this->title;

$date = $orderData['date'];
$total = $orderData['total_price'];
$address = $orderData['address'];
$customer = $orderData['customer'];
$products = $orderData['lineItems'];
$customer_first_name = $customer['customer_firstName'] ?? '';
$customer_last_name = $customer['customer_lastName'] ?? '';
$customer_email = $customer['customer_email'] ?? '';
$address_phone = $customer['address_phone'] ?? '';
$address_address1 = $address['address_address1'] ?? '';
$address_address2 = $address['address_address2'] ?? '';
$address_city = $address['address_city'] ?? '';
$address_province = $address['address_province'] ?? '';
$address_provinceCode = $address['address_provinceCode'] ?? '';
$address_country = $address['address_country'] ?? '';
$address_countryCode = $address['address_countryCode'] ?? '';
$address_zip = $address['address_zip'] ?? '';
?>
<div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <?php foreach ($products as $product) :?>
                    <div class="row">
                        <div class="card">
                            <div class="card-header inline">
                                <h1 class="card-title"><?=$this->title?> </h1>
                                <h6 class="font-weight-light"><?= $date->format(\common\models\Product::DATE_DISPLAY_FORMAT)?> | <?= $product['product_id']?></h6>

                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <tbody>
                                        <tr>
                                            <td class=""><h6><?= $product['title']?></h6></td>
                                            <td class="nowrap p-0"><h6>$ <?= floatval($product['price'])?> x <?= $product['quantity']?></h6></td>
                                            <td class="p-3"><h6 class=" mr-0 strong">$ <?= $product['total']?></h6></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-3"></div>
                                    <div class="col-3"></div>
                                    <div class="col-3">
                                        <?php if ($product['product_src_url']):?>
                                            <a href="<?=$product['product_src_url']?>" target="_blank" class="btn btn-primary p-2">Make order</a>
                                        <?php endif?>
                                    </div>
                                    <div class="col-3">
                                        <a href="<?=Yii::$app->user->identity->getShopUrl().'/admin/orders/' . $orderData['id']?>" target="_blank" class="btn btn-success p-2">View order</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="col-sm-4">
                <div class="row">
                        <div class="card col">
                            <div class="card-header d-block">
                                <h2 class="card-title"> Customer </h2>
                            </div>
                            <div class="card-body p-3">
                                <h5 class="strong">CONTACT INFORMATION</h5>
                                <div>  <?= $customer_first_name.' '. $customer_last_name ?></div>
                                <div>  <?= $customer_email ?> </div>
                                <div>  <?= $address_phone ?> </div>
                                <hr>
                                <h5 class="strong">SHIPPING ADDRESS</h5>
                                <div>   <?= $customer_first_name.' '. $customer_last_name?></div>
                                <div>   Address 1 : <?= $address_address1 ?></div>
                                <div>   <?php if($address_address2!=''): ?> Address 2 : <?= $address_address2?> <?php endif ?></div>
                                <div>   City : <?= $address_city ?></div>
                                <div>   <?php if($address_province!=''): ?> Province : <?= $address_province?> <?php endif ?></div>
                                <div>   <?php if($address_provinceCode!=''): ?>Province Code : <?= $address_provinceCode?> <?php endif ?></div>
                                <div>   Country : <?= $address_country ?></div>
                                <div>   Country Code: <?= $address_countryCode?></div>
                                <div>   Zip : <?= $address_zip?></div>
                                <hr>
                                <h4 class="strong">BILLING ADDRESS</h4>
                                <div>Same as shipping address</div>
                            </div>
                        </div>
                </div>
                <div class="row">
                    <div class="card col">
                        <div class="card-header d-block">
                            <h1 class="card-title"> Details</h1>
                        </div>
                        <div class="card-body">
                                <div class="row justify-content-between" >
                                <div class="col-3 text-nowrap text-center"><h5>Subtotal</h5></div>
                                <div class="col-3 text-nowrap text-center"><h5>Discount</h5></div>
                                <div class="col-3 text-nowrap text-center"><h5>Total</h5></div>
                                </div>
                                <div class="row justify-content-between" >
                                    <div class="col-3 text-nowrap  text-center">$<?= $orderData['subTotal'] ?></div>
                                    <div class="col-3 text-nowrap text-center">$<?= $orderData['totalDiscounts'] ?></div>
                                    <div class="col-3 text-nowrap text-center"><?= $orderData['total_price'] ?><br></div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>

