<?php
use yii\bootstrap\ActiveForm;
/* @var \yii\web\View $this*/
/* @var string $buttonName*/
$this->title = 'Login to ShionImporter dashboard by connecting your Shopify store';
?>
<div class="authincation h-100">
    <div class="container h-100">
        <div class="row justify-content-center h-100 align-items-center">
            <div class="card card body p-5">
                    <div class="row no-gutters">
                        <div class="col-xl-12">
<!--                            <div class="card card-body auth-form">-->
                                <h4 class="text-center mb-4">Connect Shionimporter with your Shopify store</h4>
                                
                                <?php if (Yii::$app->request->userIP == '127.0.0.1'):?>
                                    <?php ActiveForm::begin(['id' => 'login-form']) ?>
                                    <div class="input-group mb-3  input-info">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">https://</span>
                                        </div>
                                        <input type="text" required class="form-control login-input" name="shop" placeholder="Put your Shopify subdomain name">
                                        <input type="hidden" name="debug" value="<?=Yii::$app->params['debug']?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">.myshopify.com</span>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary btn-block login_btn"><?=$buttonName?></button>
                                    </div>
                                    <?php ActiveForm::end()?>

                                <?php endif?>
                                    <div class="input-group mb-3  input-info">
                                        <ul class="text-primary list-unstyled">
                                            <li>1. Login to Shopify admin dashboard</li>
                                            <li>2. Click "Apps" from the side menu</li>
                                            <li>3. Click the "Shionimporter" app from the list.</li>
                                        </ul>
                                    </div>
                                    <div class="text-center">
                                        <a href="https://apps.shopify.com/shein-importer" class="btn btn-primary btn-block login_btn"><?=$buttonName?></a>
                                    </div>
                               

                                <div class="new-account mt-3">
                                    <p>Don't have an Shopify account? <a class="text-primary" target="_blank" href="https://www.shopify.com/">Sign up</a></p>
                                </div>
                            </div>
<!--                        </div>-->
                </div>
            </div>
        </div>
    </div>
</div>