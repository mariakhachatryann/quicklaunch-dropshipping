<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 31.05.2019
 * Time: 17:15
 */

namespace frontend\controllers;


use common\services\ShopifyGraphQLService;
use common\models\{Currency, Feature, Plan, PlanChargeRequest, Product, User, UserSetting};
use frontend\helpers\PlanHelper;
use frontend\models\ProductPricingRuleSearch;
use Shopify\Auth\FileSessionStorage;
use Shopify\Context;
use Yii;
use yii\helpers\ArrayHelper;
use Shopify\Clients\Graphql as Client;

use yii\web\{HttpException, MethodNotAllowedHttpException, NotFoundHttpException, UploadedFile};

class ProfileController extends UserController
{
    public function actionSubscribe()
    {
        $user = Yii::$app->user->identity;

//        $shopifyService = new ShopifyGraphQLService($user->getShopifyApi());
//        $productData = [
//            'title' => 'tets',
//            'bodyHtml' => '<p>test</p>',
//        ];
//
//
//        $response = $shopifyService->createProduct($productData);
//        print_r($response);die;
        /* @var User $user*/
        $query = Plan::find()->orderBy('price');
        if (!$user->custom_plan_visible) {
            $query->andWhere(['is_custom' => 0]);
        }
        $plans = $query->all();
        $isCurrentFree = $user->isCurrentPlanFree();
        return $this->render('subscribe', compact('plans', 'isCurrentFree'));
    }

    /**
     * @return yii\web\Response
     * @throws HttpException
     * @throws MethodNotAllowedHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionSubscribePlan()
    {
        $user = Yii::$app->user->identity;
        /* @var $user User*/
        if ($user->plan_status == User::PLAN_STATUS_ACTIVE && ArrayHelper::getValue($user,'plan.price')!= 0) {
            $planChargeRequests = $user->getPlanChargeRequests()->orderBy('id')->andWhere(['status' => PlanChargeRequest::PLAN_ACTIVE])->one();
            if ($planChargeRequests) {
                $chargeId = $planChargeRequests->chargeId;
                try {
                    $mutation = <<<GRAPHQL
                        mutation {
                          appSubscriptionCancel(
                            id: "{$chargeId}",
                          ) {
                            userErrors {
                              field
                              message
                            }
                            appSubscription {
                              id
                              status
                            }
                          }
                        }
                        GRAPHQL;

                    $user->getShopifyApi()->query($mutation);
                }catch (\Exception $exception){
                    Yii::error([$exception->getMessage(), $user->id], 'ChargeCancelFailed');
                }
                $planChargeRequests->delete();
            }
        }
        $chosenPlanId = Yii::$app->request->post('planId');
        $plan = Plan::findOne($chosenPlanId);
        $result = $user->subscribe($plan);

        if (isset($result['url'])) {
            return $this->redirect($result['url']);
        }

        if ($result) {
            $user->sendEmail(
                Yii::$app->params['supportEmail'],
                'New plan subscription',
                $user->username.' has subscribed to plan '.$user->plan->name);
        }

        Yii::$app->session->setFlash('success', 'You have activated '.$plan->name.' plan');
        Yii::$app->session->setFlash('justActivated', true);

        return $this->redirect(['/profile/subscribe']);
    }

    public function actionCharge($charge_id)
    {
        $user = Yii::$app->user->identity;
        /* @var $user User*/
        $result = $user->getCharge($charge_id);
        Yii::$app->session->setFlash('success', $result['message']);

        return $this->redirect(['profile/subscribe']);
    }

    public function actionCancelPlan()
    {
        /* @var User $user */
        $user = Yii::$app->user->identity;

        /* @var PlanChargeRequest $planChargeRequest */
        $planChargeRequest = $user->getPlanChargeRequests()->orderBy('id')->andWhere(['status' => PlanChargeRequest::PLAN_ACTIVE])->one();
        if (!$planChargeRequest) {
            $planChargeRequest = $user->getUserCharges()->where(['status' => 'active'])->one();
        }
        if ($planChargeRequest) {
            $client = $user->getShopifyApi();
            $planChargeRequest->status = PlanChargeRequest::PLAN_DECLINED;
            $planChargeRequest->save();
            $chargeId = $planChargeRequest->chargeId;

            $mutation = <<<GRAPHQL
                mutation {
                  appSubscriptionCancel(
                    id: "{$chargeId}",
                  ) {
                    userErrors {
                      field
                      message
                    }
                    appSubscription {
                      id
                      status
                    }
                  }
                }
                GRAPHQL;

            $client->query($mutation);


        }
        $user->plan_id = null;
        $user->plan_status = 0;
        $user->save();
        Yii::$app->session->setFlash('success', 'You have deactivated your plan');

        $user->sendEmail(
            Yii::$app->params['supportEmail'],
            'Plan cancellation',
            $user->username.' has cancelled plan');

        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionSettings()
    {
        $user = Yii::$app->user->identity;
//        $productModel = $user->getProductDataModel();
//        $productModel->title = 'title variant';
//        print_r($productModel->createShopifyProduct());die;

      /* @var User $user */
        $setting = $user->userSetting;
        $client = $user->getShopifyApi();
        if (Yii::$app->request->isPost) {
            $setting->load(Yii::$app->request->post());
            $setting->logo = UploadedFile::getInstance($setting, 'logo');

            if ($setting->validate()) {
                if ($setting->logo) {
                    $filePath = 'uploads/' . uniqid() . '_' . $setting->logo->getBaseName() . '.' . $setting->logo->extension;
                    if ($setting->logo->saveAs($filePath)) {

//                        $mutation = <<<GQL
//                            mutation {
//                                stagedUploadsCreate(input: {
//                                    resource: FILE,
//                                    filename: "{$setting->logo->getBaseName()}",
//                                    mimeType: "{$setting->logo->type}",
//                                    httpMethod: POST
//                                }) {
//                                    stagedTargets {
//                                        url
//                                        resourceUrl
//                                        parameters {
//                                            name
//                                            value
//                                        }
//                                    }
//                                }
//                            }
//                            GQL;
//
//                        $response = $client->query($mutation);
//                        $responseBody = $response->getBody();
//                        $responseData = json_decode($responseBody, true);
//                        print_r($responseData);
//                        die;
//
                        $setting->logo = $filePath;

                    }
                }

                $setting->save();
                return $this->redirect(Yii::$app->request->referrer);
            }
        }

//        $mutation = <<<GQL
//            mutation {
//                shopUpdate(input: {
//                    name: "gtdsd"
//                }) {
//                    shop {
//                        id
//                        name
//                    }
//                    userErrors {
//                        field
//                        message
//                    }
//                }
//            }
//        GQL;
//
//        $response = $client->query($mutation);
//        $responseBody = $response->getBody();
//        $responseData = json_decode($responseBody, true);
//
//        print_r($responseData);
//        die;
        $feauturesDescriptions = Feature::getFeauturesDescriptions();
        $planFeatures = Feature::getPlanSettingkeys($user);
        $availableFeatures = [];

        foreach (array_keys($feauturesDescriptions) as $settingKey) {
            $availableFeatures[$settingKey] = isset($planFeatures[$settingKey]);
            if (isset($setting->$settingKey)) {
                $setting->$settingKey = $availableFeatures[$settingKey] ? $setting->$settingKey : 0;
            }
        }

        $setting->save();
        $currencies = Currency::getDb()->cache(function ($db) {
            return Currency::find()->select(['CONCAT(code, " (", name, ")") AS label', 'id'])
                ->indexBy('id')->column();
        }, 1000);
        $searchModel = new ProductPricingRuleSearch();
        $searchModel->user_id = $user->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('settings', compact('setting', 'feauturesDescriptions', 'availableFeatures', 'searchModel', 'dataProvider', 'currencies'));
    }

    public function actionChangeSiteTheme()
    {
        $user = Yii::$app->user->identity;
        /* @var User $user */
        $setting = $user->userSetting;
        $theme = Yii::$app->request->post('theme');
        if ($setting->site_theme == UserSetting::$siteThemes[$theme]) {
            return true;
        }
        $setting->site_theme = UserSetting::$siteThemes[$theme];
        return $setting->save();
    }

}