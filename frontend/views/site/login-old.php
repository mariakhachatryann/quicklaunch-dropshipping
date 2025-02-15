<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'Sign In';
?>

<div class="container">
    <div class="d-flex justify-content-center h-100">
        <div class="card">
            <div class="card-header">
                <h3>Insert your shopify store name</h3>
                <div class="d-flex justify-content-end social_icon">
                    <span><i class="fab fa-facebook-square"></i></span>
                    <span><i class="fab fa-google-plus-square"></i></span>
                    <span><i class="fab fa-twitter-square"></i></span>
                </div>
            </div>
            <div class="card-body">
                <?php ActiveForm::begin(['id' => 'login-form'])?>
                    <div class="input-group form-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">https://www.</span>
                        </div>
                        <input type="text" required class="form-control login-input" name="shop" placeholder="Shop name">
                        <input type="hidden" name="debug" value="<?=Yii::$app->params['debug']?>">
                        <div class="input-group-prepend">
                            <span class="input-group-text">.myshopify.com</span>
                        </div>

                    </div>
                    <div class="form-group text-center login-button">
                        <input type="submit" value="Login" class="btn login_btn">
                    </div>
                <?php ActiveForm::end()?>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-center links">
                    Don't have an account?<a href="#">Sign Up</a>
                </div>
                <div class="d-flex justify-content-center">
                    <a href="#">Forgot your password?</a>
                </div>
                <div class="d-flex justify-content-center">
                    <?= Html::a('Privacy policy', 'privacy-policy',['target' => '_blank'])?>
                </div>
            </div>
        </div>
    </div>
</div>