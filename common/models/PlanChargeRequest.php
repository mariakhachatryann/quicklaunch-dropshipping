<?php

namespace common\models;

use Shopify\Exception\HttpRequestException;
use Shopify\Exception\MissingArgumentException;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception;

/**
 * This is the model class for table "{{%plan_charge_requests}}".
 *
 * @property int $id
 * @property int $chargeId
 * @property int $user_id
 * @property int $plan_id
 * @property int $status
 * @property int $created_at
 * @property int $updated_at
 * @property int $activated_on
 * @property string $response_data
 *
 * @property Plan $plan
 * @property User $user
 */
class PlanChargeRequest extends \yii\db\ActiveRecord
{
    const PLAN_PENDING = 0;
    const PLAN_ACTIVE = 1;
    const PLAN_DECLINED = 2;
    const PLAN_IN_QUEUE = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%plan_charge_requests}}';
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['chargeId', 'user_id', 'plan_id', 'status','created_at', 'updated_at', 'activated_on'], 'integer'],
            [['response_data'], 'string'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::class, 'targetAttribute' => ['plan_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'chargeId' => 'Charge ID',
            'user_id' => 'User ID',
            'plan_id' => 'Plan ID',
            'status' => 'Status',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @throws Exception
     * @throws HttpRequestException
     * @throws MissingArgumentException
     */
    public function charge()
    {
        $plan = $this->plan;
        $client = $this->user->getShopifyApi();
        $debug = Yii::$app->request->post('debug') ?? 2;

        $chargeData = [
            'name' => $plan->name,
            'return_url' => User::getDashboardUrl($debug) . '/charge',
        ];

        if (strpos($this->user->username, 'sheinimporter') === 0) {
            $chargeData['test'] = true;
        }

        $mutation = <<<GRAPHQL
        mutation {
          appSubscriptionCreate(
            name: "{$chargeData['name']}",
            returnUrl: "{$chargeData['return_url']}",
            lineItems: [{
                plan: {
                    appRecurringPricingDetails: {
                        price: {
                            amount: 10.00,
                            currencyCode: USD
                        }
                    }
                }
            }]
          ) {
            userErrors {
              field
              message
            }
            confirmationUrl
            appSubscription {
              id
            }
          }
        }
        GRAPHQL;

        $response = $client->query($mutation);
        $responseData = json_decode($response->getBody(), true);
        $charge = $responseData['data']['appSubscriptionCreate'] ?? null;
        $errors = $responseData['data']['appSubscriptionCreate']['userErrors'] ?? [];

        if (empty($charge['confirmationUrl'])) {
            $this->response_data = serialize($errors);
            $this->save();
            return false;
        }

        $this->chargeId = $charge['id'];
        $this->response_data = serialize($charge);
        $this->save();
        return $charge['confirmationUrl'];
    }


    public function getChargeRequest()
    {
        $client = $this->user->getShopifyApi();

        $query = <<<'GRAPHQL'
        query ($id: ID!) {
            appSubscription(id: $id) {
                status
            }
        }
        GRAPHQL;

        $response = $client->query($query, ['id' => $this->chargeId]);
        $status = $response['data']['appSubscription']['status'];

        if ($status === 'ACTIVE') {
            $this->status = self::PLAN_ACTIVE;
            $this->activated_on = time();
            $this->save();

            $this->user->plan_id = $this->plan_id;
            $this->user->plan_status = User::PLAN_STATUS_ACTIVE;
            $this->user->save();

            $this->user->enableFeatures();
            $this->user->correctMonitoringLimitMismatch();

            return true;
        }

        $this->status = self::PLAN_DECLINED;
        $this->save();
        return false;
    }
}
