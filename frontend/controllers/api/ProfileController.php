<?php
/**
 * Created by PhpStorm.
 * User: FS-Asus001
 * Date: 25.04.2019
 * Time: 16:55
 */

namespace frontend\controllers\api;

use backend\helpers\CurrencyHelper;
use common\helpers\ShopifyApiException;
use common\helpers\ShopifyClient;
use common\models\Feature;
use common\models\Plan;
use common\models\PlanChargeRequest;
use common\models\User;
use InvalidArgumentException;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;

class ProfileController extends ApiController
{


    /**
     * @return mixed
     */
    public function actionSubscribe()
    {
        $user = Yii::$app->user->identity;

        $chosenPlanId = Yii::$app->request->post('plan_id');
        $plan = Plan::findOne($chosenPlanId);
        return $user->subscribe($plan);
    }

    public function actionGetCharge($chargeId)
    {
        /* @var User $user */
        $user = Yii::$app->user->identity;

        return $user->getCharge($chargeId);

    }

    public function actionGetAllPlans()
    {
        
        return Plan::find()->all();
    }

    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        /* @var User $user */
        $shopifyClient = $user->getShopifyApi();

        $collectionsQuery = <<<GQL
            {
                collections(first: 250) {
                    edges {
                        node {
                            id
                            title
                        }
                    }
                }
            }
            GQL;

        $collectionsResponse = $shopifyClient->query($collectionsQuery);
        $responseData = json_decode($collectionsResponse->getBody(), true);

        $collections = [];

        if (isset($responseData['data']['collections']['edges'])) {
            foreach ($responseData['data']['collections']['edges'] as $collectionEdge) {
                $collections[] = [
                    'id' => $collectionEdge['node']['id'],
                    'title' => $collectionEdge['node']['title'],
                ];
            }
        }
        $plan = $user->plan;
        $availableSites = [];
        if ($plan) {
            $availableSites = $plan->getSites()->select(['url', 'name'])->indexBy('name')->column();
        }

        $limits = $user->getLimits();

        $featuresDescriptions = Feature::getFeauturesDescriptions();
        $planFeatures = Feature::getPlanSettingkeys($user);
        $availableFeatures = [];

        foreach (array_keys($featuresDescriptions) as $settingKey) {
            $availableFeatures[$settingKey] = isset($planFeatures[$settingKey]);
        }

        $customPricingRules = [];
        if ($user->userSetting->custom_pricing_rules) {
            $customPricingRules = ArrayHelper::toArray($user->productPricingRules);
        }

        $currencyRate = 1;
        if ($availableFeatures['product_currency_convertor'] && $user->userSetting->use_default_currency) {
            $currencyRate = CurrencyHelper::convert($user->userSetting->defaultCurrency->code, $user->userSetting->currency->code);
            $fixed = $currencyRate < 0.01 ? 4 : ($currencyRate < 0.1 ? 3 : 2);
            $currencyRate = number_format($currencyRate, $fixed, '.', '');
        }

        $userData = [
            'userId' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'plan_id' => $user->plan_id,
            'planName' => $user->plan->name ?? null,
            'plan_status' => $user->plan_status,
            'settings' => array_merge($user->userSetting->attributes, ['currency_rate' => $currencyRate]),
            'available_features' => $availableFeatures,
            'productLimit' => $limits['productLimit'],
            'productCount' => $limits['productCount'],
            'productRemainsLimit' => $limits['productRemainsLimit'],
            'variantsLimit' => $limits['monitoringLimit'],
            'variantsCount' => $limits['monitoringCount'],
            'monitoringRemainsLimit' => $limits['monitoringRemainsLimit'],
            'reviewsLimit' => $limits['reviewsLimit'],
            'reviewsCount' => $limits['reviewsCount'],
            'reviewsRemainsLimit' => $limits['reviewsRemainsLimit'],
            'availableSites' => $availableSites,
            'collections' => $collections,
            'productPricingRules' => $customPricingRules,
        ];

        return $userData;
    }

    public function actionCancelPlan()
    {
        /* @var User $user */
        $user = Yii::$app->user->identity;
        /* @var PlanChargeRequest $planChargeRequests */
        $planChargeRequests = $user->getPlanChargeRequests()->orderBy('id')->andWhere(['status' => PlanChargeRequest::PLAN_ACTIVE])->one();

        if ($planChargeRequests) {
            $chargeId = $planChargeRequests->chargeId;
            $user->getShopifyApi()->getRecurringApplicationChargeManager()->cancel($chargeId);
            $planChargeRequests->delete();

        }
        $user->plan_id = null;
        $user->plan_status = 0;
        $user->save();

        return [
            'status' => 1,
            'message' => 'You have deactivated your plan'
        ];

    }

    public function actionUpdateSettings()
    {
        $user = Yii::$app->user->identity;
        /* @var $user User*/
        $setting = $user->userSetting;
        if ($setting->load(Yii::$app->request->bodyParams,'') && $setting->save()) {

            return $setting->price_markup;
        }

        return [
            'status' => 0,
            'message' =>$setting->getErrors(),
        ];
    }
}