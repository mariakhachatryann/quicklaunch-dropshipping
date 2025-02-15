<?php

namespace common\models;

use common\helpers\ShopifyClient;
use frontend\models\api\Notification;
use frontend\models\api\ProductData;
use frontend\models\ProductPricingRule;
use Shopify\ApiVersion;
use Shopify\Auth\FileSessionStorage;
use Shopify\Clients\Graphql as Client;
use Shopify\Context;
use Shopify\Exception\HttpRequestException;
use Shopify\Exception\MissingArgumentException;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\IdentityInterface;
use InvalidArgumentException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;

/**
 * User model
 *
 * @property integer $id
 * * @property string $username
 * * @property string $full_name
 * * @property string $password_hash
 * * @property string $password_reset_token
 * * @property string $verification_token
 * * @property string $email
 * * @property string $auth_key
 * * @property integer $status
 * * @property integer $created_at
 * * @property integer $updated_at
 * * @property string $password write-only password
 * * @property integer $plan_id
 * * @property integer $promo_code_id
 * * @property boolean $plan_status
 * * @property boolean $cancelled_plan
 * * @property boolean $videos_checked
 * * @property string $access_token
 * * @property string $shopify_details
 * * @property string $country_code
 * * @property integer $fail_count
 * * @property integer $custom_plan_visible
 * * @property int $review_suggest_count
 * * @property int $review_suggested_at
 * * @property int $has_left_review
 * * @property int $left_review_at
 * * @property bool $is_manual_plan
 * * @property array $limits
 * *
 * * @property string $shopUrl
 * * @property Plan $plan
 * * @property PromoCode $promoCode
 * * @property Product[] $products
 * * @property BulkImport[] $bukImports
 * * @property ProductReview[] $productsReviews
 * * @property UserSetting $userSetting
 * * @property PlanChargeRequest[] $planChargeRequests
 * * @property MultipleImport[] $multipleImports
// * * @property Client $shopifyApi
 * * @property CancelledPlan[] $cancelledPlans
 * * @property UserCharge[] $userCharges
 * * @property ProductPricingRule[] $productPricingRules
 * * @property BulkMonitoring[] $bulkMonitorings
// * * @property \Slince\Shopify\Model\OnlineStore\ScriptTag $script
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    const PLAN_STATUS_INACTIVE = 0;
    const PLAN_STATUS_ACTIVE = 1;
    const PLAN_STATUS_CANCELED = 2;

    const NoNotifications = 'No Notifications';

    const COOKIE_APP_INSTALLED = 'app_installed';

    const VIDEOS_CHECKED = 1;
    const VIDEOS_NOT_CHECKED = 0;
    const FAIL_COUNT_LIMIT = 3;

    const SUGGEST_REVIEW_LIMIT = 10;
    const SUGGEST_REVIEW_INTERVAL = 1;

    const MANUALLY_UPGRADED = 1;
    const NOT_MANUALLY_UPGRADED = 0;

    public $password;

    public static $planStatuses = [
        '0' => 'Inactive',
        '1' => 'Active'
    ];


    public function planStatusName()
    {
        return self::$planStatuses[$this->plan_status];
    }


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->status == User::STATUS_ACTIVE) {
                $details = $this->getShopDetails();
                $this->email = $details['contactEmail'];
//                $this->fullName = $details->getShopOwner();
                $this->country_code = $details['billingAddress']['countryCode'];
                $this->shopify_details = json_encode($this->getShopifyDetails($details));
            }
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'full_name'], 'string'],
            ['email', 'email'],
            ['password', 'trim'],
            [['username'], 'unique'],
            [['plan_id', 'promo_code_id', 'fail_count', 'review_suggest_count'], 'integer'],
            ['left_review_at', 'safe'],
            ['left_review_at', 'required', 'when' => function (self $model) {
                return $model->has_left_review;
            }, 'whenClient' => "function() { 
				return $('#user-has_left_review').prop('checked')
			}"],
            [['plan_status', 'cancelled_plan',  'videos_checked', 'custom_plan_visible', 'has_left_review', 'is_manual_plan'], 'boolean'],
            [['access_token','country_code'], 'string'],
            ['shopify_details', 'safe'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlan(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanChargeRequests()
    {
        return $this->hasMany(PlanChargeRequest::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPromoCode(): \yii\db\ActiveQuery
    {
        return $this->hasOne(PromoCode::class, ['id' => 'promo_code_id']);
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Product::class, ['user_id' => 'id']);
    }

    public function getMonitoringProducts()
    {
        return $this->getProducts()->andWhere(['OR',
            ['monitoring_stock' => Product::MONITORING_ENABLE],
            ['monitoring_price' => Product::MONITORING_ENABLE]
        ]);
    }

    public function getProductsReviews()
    {
        //return $this->hasMany(ProductReview::class, ['product_id' => 'shopify_id'])->via('products');
        return $this->hasMany(ProductReview::class, ['user_id' => 'id']);
    }

    public function getUserSetting()
    {
        return $this->hasOne(UserSetting::class, ['user_id' => 'id']);
    }


    public function getLeads()
    {
        return $this->hasMany(Lead::class, ['user_id' => 'id']);
    }

    public function getNotifications()
    {
        $notifications = Notification::find()->where(['OR',
            ['user_id' => Yii::$app->user->id],
            ['IS', 'user_id', null]
        ])->orderBy('id DESC')->limit(100);
        return $notifications;
    }
    public function lastTenNotifications()
    {
        return $this->getUserNotifications()->orderBy(['id' => SORT_DESC])->limit(10)->all();
    }

    public function getUserNotifications(): ActiveQuery
    {
        return $this->hasMany(Notification::class, ['user_id' => 'id']);
    }
    public function getUserSeenNotifications()
    {
        return $this->hasMany(UserNotification::class, ['user_id' => 'id']);
    }

    public function getSeenNotifications()
    {
        return $this->hasMany(Notification::class, ['id' => 'notification_id'])
            ->via('userSeenNotifications');
    }

    public function getNewNotifications()
    {
        $readNotificationQuery = $this->getSeenNotifications()->select('id');
        return $this->getNotifications()->andWhere(['NOT IN', 'id', $readNotificationQuery])->orderBy(['id'=> SORT_ASC ])->all();
    }

    public function getNewNotificationIds()
    {
        $readenNotificationIds = $this->getSeenNotifications()->select('id')->column();
        return $this->getNotifications()->andWhere(['NOT IN', 'id', $readenNotificationIds])->select('id')->column();
    }

    public static function getDomain($shop)
    {
        if (strpos($shop, '.myshopify.com') === false ) {
            return $shop.'.myshopify.com';
        }
        return $shop;
    }

    public static function redirectLoginUrl($shop)
    {
        $shop = str_replace('.myshopify.com', '', $shop);
        if (!$shop) {
            throw new InvalidArgumentException('Shop argument is missing');
        }
        $debug = Yii::$app->request->post('debug', Yii::$app->params['debug']);
        $shopDomain = User::getDomain($shop);

        $shopifyClient = new ShopifyClient($shopDomain, '', Yii::$app->params['apiKey'], Yii::$app->params['apiSecretKey']);
        $redirectUrl = User::getDashboardUrl($debug);
        $scope = 'write_products, write_orders,  write_inventory'. (Yii::$app->params['enableReview'] ? ', write_script_tags' : '');
        return [
            'status' => 1,
            'url' => $shopifyClient->getAuthorizeUrl($scope, $redirectUrl),
        ];
    }

    public static function getDashboardUrl($debug)
    {
        $debug = intval($debug);
        $redirectUrls = [
            0 => 'dashboardUrl',
            1 => 'newDashboardUrl',
            2 => 'newTestDashboardUrl'
        ];

        return Url::toRoute(Yii::$app->params[$redirectUrls[$debug]], true);
    }

    /**
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public static function getAccessToken($shop, $code)
    {
        $shop = str_replace('.myshopify.com', '', $shop);
        $shopDomain = User::getDomain($shop);
        $shopifyClient = new ShopifyClient($shopDomain, '', Yii::$app->params['apiKey'], Yii::$app->params['apiSecretKey']);
        $access_token = ArrayHelper::getValue($shopifyClient->getAccessToken($code), 'access_token');
        if (!$access_token) {
            throw new NotFoundHttpException();
        }

        $user = User::findOne(['username' => $shop]);

        if (!$user) {
            $user = new User();
            $user->username = $shop;
        }

        $user->access_token = $access_token;
        try {
            if(Yii::$app->params['enableReview']){
               $user->addJsFiles();
            }
        } catch (\Exception $e) {
            Yii::error([$e->getMessage(), $shop], 'JsSetupError');
        }
        if ($user->isNewRecord || $user->status == User::STATUS_DELETED) {
            $user->generateAuthKey();
            $user->status = User::STATUS_ACTIVE;
            $isNewRecord = $user->isNewRecord;
            if ($user->save()) {
                if ($isNewRecord || !$user->userSetting) {
                    $userSettings = new UserSetting();
                    $userSettings->user_id = $user->id;
                    $userSettings->save();
                }
//                PlanHelper::subscribeUserToFreePlan($user);
                EmailTemplate::sendByKey(EmailTemplate::USER_INSTALL, $user);
                try {
                    $user->addWebhooks();
                } catch (\Exception $e) {
                }
            }
        } else {
            if (!$user->userSetting) { /* @todo create common method for saving user setting */
                $userSettings = new UserSetting();
                $userSettings->user_id = $user->id;
                $userSettings->save();
            }
            $user->save();
        }



        return [
            'status' => 1,
            'accessToken' => $access_token
        ];
    }

    /**
     * @throws MissingArgumentException
     */
    public function addJsFiles()
    {
        $client = $this->getShopifyApi();

        $query = <<<QUERY
            {
              scriptTags(first: 10) {
                edges {
                  node {
                    id
                    src
                    createdAt
                    updatedAt
                  }
                }
              }
            }
        QUERY;

        $response = $client->query($query);
        $responseBody = $response->getBody();
        $responseData = json_decode($responseBody, true);
        // Shopify GraphQL endpoint
        if (!$responseData) {
            $scripts = [
                Url::to('/js/vue-rate.js', true),
                Url::to('/js/sheinimporter.js?v=12', true),
                'https://cdn.jsdelivr.net/npm/vue@2.6.10/dist/vue.min.js',
            ];
            foreach ($scripts as $script) {
                $client->getScriptTagManager()->create(['event' => 'onload', 'src' => $script]);
            }
        }

    }

    /**
     * @throws MissingArgumentException
     */
    public function getShopifyApi()
    {
        $domain = User::getDomain($this->username);
        $accessToken = $this->access_token;

        Context::initialize(
            Yii::$app->params['apiKey'],
            Yii::$app->params['apiSecretKey'],
            ['read_orders,write_orders,read_products,write_products'],
            $domain,
            new FileSessionStorage('/tmp/php_sessions'),
            ApiVersion::LATEST,
            true,
            false
        );
        return new Client($domain, $accessToken);
    }

    public static function verifyWebhook()
    {
        if (isset($_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'])) {
            $hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
            $data = file_get_contents('php://input');


            $calculated_hmac = base64_encode(hash_hmac('sha256', $data, Yii::$app->params['apiSecretKey'], true));
            return $hmac_header == $calculated_hmac;
        }
        return false;
    }

    public function isCurrentPlanFree(): bool
    {
        return ($this->plan->price ?? 0) == 0;
    }


    public function deleteWebhooks()
    {
        $needRegenerate = false;
        $client = $this->getShopifyApi();
        $query = <<<GRAPHQL
                query {
                  webhookSubscriptions(first: 50) {
                    edges {
                      node {
                        id
                        callbackUrl
                      }
                    }
                  }
                }
            GRAPHQL;

        $response = $client->query($query);
        $webhooks = $response['body']['data']['webhookSubscriptions']['edges'] ?? [];

        foreach ($webhooks as $webhook) {
            if (strpos($webhook['node']['callbackUrl'], '/admin') !== false) {
                $deleteMutation = <<<GRAPHQL
                mutation webhookSubscriptionDelete(\$id: ID!) {
                  webhookSubscriptionDelete(id: \$id) {
                    deletedWebhookSubscriptionId
                    userErrors {
                      field
                      message
                    }
                  }
                }
            GRAPHQL;

                $client->query($deleteMutation, ['id' => $webhook['node']['id']]);
                $needRegenerate = true;
            }
        }

        if ($needRegenerate) {
            $this->addWebhooks();
        }
    }

    /**
     * @throws MissingArgumentException
     * @throws HttpRequestException
     */
    public function addWebhooks()
    {
        $this->addWebhookUninstall();
//        $this->addProductDeleteWebhook();
    }

    /**
     * @throws MissingArgumentException
     * @throws HttpRequestException
     */
    public function addWebhookUninstall(): void
    {
        $client = $this->getShopifyApi();
        $mutation = <<<GRAPHQL
                mutation webhookSubscriptionCreate(\$topic: WebhookSubscriptionTopic!, \$callbackUrl: URL!, \$format: WebhookSubscriptionFormat!) {
                  webhookSubscriptionCreate(topic: \$topic, webhookSubscription: {callbackUrl: \$callbackUrl, format: \$format}) {
                    webhookSubscription {
                      id
                      topic
                    }
                    userErrors {
                      field
                      message
                    }
                  }
                }
            GRAPHQL;

        $variables = [
            'topic' => 'APP_UNINSTALLED',
            'callbackUrl' => Url::toRoute('webhook/uninstall', true),
            'format' => 'JSON',
        ];

        $client->query($mutation, $variables);
    }

    public function sendEmail($to, $subject, $textBody, $replyTo = null)
    {
        $from = [Yii::$app->params['noReplyEmail'] => Yii::$app->params['appName']];

        try {
            $mailer = \Yii::$app->mailer->compose()
                ->setTo($to)
                ->setFrom($from)
                ->setSubject($subject)
                ->setHtmlBody($textBody);
            if ($replyTo) {
                $mailer->setReplyTo($replyTo);
            }
            return $mailer->send();
        } catch (\Swift_SwiftException $exception) {
            Yii::error($exception->getMessage(), 'email errors');
            return false;
        }
    }

    public function getLimits()
    {
        $productLimit = $this->plan ? $this->plan->product_limit : 0;
        $productCount = $this->getProducts()->count();
        $productPercent = $productLimit ? round($productCount * 100 / $productLimit) : 0;
        $productRemainsLimit = $productLimit ? $productLimit - $productCount : 0;

        $monitoringLimit = $this->plan ? $this->plan->monitoring_limit : 0;
        if ($this->email == 'nik5iron@gmail.com') {
            $monitoringLimit = 1500;
        }
        $monitoringCount = $this->getProducts()->andWhere(['OR',
            ['monitoring_stock' => 1],
            ['monitoring_price' => 1],
        ])->count();
        $monitoringPercent = $monitoringLimit ? round($monitoringCount * 100 / $monitoringLimit) : 0;
        $monitoringRemainsLimit = $monitoringLimit ? $monitoringLimit - $monitoringCount : 0;

        $reviewsLimit = $this->plan ? $this->plan->review_limit : 0;
        $reviewsCount = $this->getProductsReviews()->count();
        $reviewsPercent = $reviewsLimit ? round($reviewsCount * 100 / $reviewsLimit) : 0;
        $reviewsRemainsLimit = $reviewsLimit ? $reviewsLimit - $reviewsCount : 0;

        return compact('productLimit', 'productCount', 'productPercent', 'productRemainsLimit',
            'monitoringLimit', 'monitoringCount', 'monitoringPercent', 'monitoringRemainsLimit', 'reviewsLimit',
            'reviewsCount', 'reviewsPercent', 'reviewsRemainsLimit');
    }

    public function hasCachedDailyLimit(): ?bool
    {
        $key = 'daily_limit_' . $this->id;
        return Yii::$app->cache->get($key);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getNiches()
    {
        return $this->hasMany(Niche::class, ['id' => 'niche_id'])->viaTable('user_niche', ['user_id' => 'id']);
    }

    public function subscribe(?Plan $plan)
    {
        $user = $this;

        if (!$plan) {
            throw new NotFoundHttpException('Plan not found');
        }

        if ($user->plan_id == $plan->id) {
            throw new MethodNotAllowedHttpException('You already have that plan');
        }

        /*if ($user->plan_id && $user->plan->price > $plan->price) {
            throw new MethodNotAllowedHttpException('You are not allowed to choose that plan');
        }*/
        // @todo add queue for cancelling user plan on expiring date

        if ($plan->price == 0) {
            $user->plan_status = User::PLAN_STATUS_ACTIVE;
            $user->plan_id = $plan->id;
            $user->save();
            $user->enableFeatures();
            Product::updateAll([
                'monitoring_stock' => Product::MONITORING_DISABLE,
                'monitoring_price' => Product::MONITORING_DISABLE
            ], ['user_id' => $user->id]);
            return ['status' => 1, 'message' => 'Subscribed'];
        }
        $planChargeRequest = new PlanChargeRequest([
            'user_id' => $user->id,
            'plan_id' => $plan->id
        ]);

        $confirmation_url = $planChargeRequest->charge();
        if ($confirmation_url) {
            return [
                'status' => 1,
                'url' => $confirmation_url
            ];
        }
        throw new HttpException(404,'Charge has not been set!');

    }

    public function enableFeatures()
    {
        return true;
        $settingKeys = $this->plan->getFeatures()
            ->select('setting_key')
            ->andWhere(['IS NOT', 'setting_key', null])
            ->column();
        $settings = $this->userSetting;
        $allFeatures = Feature::find()
            ->select('setting_key')
            ->andWhere(['IS NOT', 'setting_key', null])
            ->column();
        $notActiveSettingKeys = array_diff($allFeatures, $settingKeys);

        foreach ($notActiveSettingKeys as $notActiveSettingKey) {
            $settings->$notActiveSettingKey = 0;
        }

        foreach ($settingKeys as $settingKey) {
            $settings->$settingKey = 1;
        }
        $settings->save();

    }

    public static function getUsersWithPlans(): ArrayDataProvider
    {
        $queryUserByTypes = User::find()
            ->alias('u')
            ->select([
                new Expression('COUNT(*) as total'),
                'plan_id',
                Plan::tableName() . '.name'
            ])
            ->joinWith('plan')
            ->groupBy('plan_id')->indexBy('plan_id')->asArray();

        $queryUserByTypesToday = (clone $queryUserByTypes)
            ->andWhere(['>', 'u.created_at', strtotime(date('Y-m-d'))]);

        $allData = $queryUserByTypes->all();
        $todaysData = $queryUserByTypesToday->all();

        foreach ($allData as $planId => &$data) {
            $data['today'] = $todaysData[$planId]['total'] ?? 0;
        }

        return new ArrayDataProvider([
            'allModels' => $allData
        ]);
    }

    public function getShopUrl()
    {
        return "https://{$this->username}.myshopify.com";
    }

    public function getStatus()
    {
        if ($this->status == self::STATUS_DELETED) {
            return 'Deleted';
        }
        if ($this->status == self::STATUS_ACTIVE) {
            return  'Active';
        }
        return 'Inactive';
    }

    public function getUserCharges()
    {
        return $this->hasMany(UserCharge::class, ['user_id' => 'id']);
    }

    public static function getUsers()
    {
        return self::find()->select(['username', 'id'])->indexBy('id')->column();
    }

    protected function getShopifyDetails($details)
    {
        $shopifyDetails = [
            'name' => $details['name'],
            'email' => $details['contactEmail'],
            'domain' => $details['primaryDomain']['host'],
            'createdAt' => $details['createdAt'],
            'province' => $details['billingAddress']['province'],
            'country' => $details['billingAddress']['country'],
            'address1' => $details['billingAddress']['address1'],
            'zip' => $details['billingAddress']['zip'],
            'city' => $details['billingAddress']['city'],
//            'source' => $details->getSource(),
            'phone' => $details['billingAddress']['phone'],
//            'updatedAt' =>$details['billingAddress']['updatedAt'],
//            'customerEmail' => $details->getCustomerEmail(),
            'latitude' => $details['billingAddress']['latitude'],
            'longitude' => $details['billingAddress']['longitude'],
//            'primaryLocationId' => $details->getPrimaryLocationId(),
//            'primaryLocale' => $details->getPrimaryLocale(),
            'address2' => $details['billingAddress']['address2'],
            'countryCode' => $details['billingAddress']['countryCode'],
            'countryName' => $details['billingAddress']['country'],
            'currency' => $details['currencyCode'],
//            'timezone' => $details->getTimezone(),
            'ianaTimezone' => $details['ianaTimezone'],
//            'shopOwner' => $details->getShopOwner(),
            'moneyFormat' => $details['currencyFormats']['moneyFormat'],
            'moneyWithCurrencyFormat' => $details['currencyFormats']['moneyWithCurrencyFormat'],
            'weightUnit' => $details['weightUnit'],
//            'provinceCode' => $details->getProvinceCode(),
//            'taxShipping' => $details->getTaxShipping(),
//            'countyTaxes' => $details->getCountyTaxes(),
            'planDisplayName' => $details['plan']['displayName'],
//            'planName' => $details->getPlanName(),
            'myshopifyDomain' => $details['myshopifyDomain'],
//            'googleAppsDomain' => $details->getGoogleAppsDomain(),
//            'googleAppsLoginEnabled' => $details->getGoogleAppsLoginEnabled(), 'moneyInEmailsFormat' => $details->getMoneyInEmailsFormat(),
        ];
        return $shopifyDetails;
    }

    protected function getShopDetails()
    {
        $client = $this->getShopifyApi();
        $query = <<<GRAPHQL
            query {
                shop {
                    id
                    name
                    primaryDomain {
                        host
                        sslEnabled
                        url
                    }
                    currencyCode
                    currencyFormats {
                        moneyFormat
                        moneyWithCurrencyFormat
                    }
                    customerAccounts
                    contactEmail
                    createdAt
                    description
                    billingAddress {
                        address1
                        address2
                        zip
                        country
                        city
                        phone
                        province
                        longitude
                        latitude
                        countryCode
                    }
                    updatedAt
                    email
                    plan {
                        displayName
                    }
                    myshopifyDomain
                    ianaTimezone
                    weightUnit
                }
            }
        GRAPHQL;

        $response = $client->query($query);
        return json_decode($response->getBody(), true)['data']['shop'];
    }

    public static function getCurrentUserId()
    {
        return Yii::$app->user->id;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBulkImports(): ActiveQuery
    {
        return $this->hasMany(BulkImport::class, ['user_id' => 'id']);
    }

    /**
     * @return ProductData
     */
    public function getProductDataModel()
    {
        $client = $this->getShopifyApi();
        return new ProductData(['client' => $client]);
    }

    public function saveSuggestReviewDetails()
    {
        $this->review_suggest_count++;
        $this->review_suggested_at = time();
        $this->save(false);
    }
}
