<?php


namespace backend\models;


use common\models\Currency;
use common\models\Plan;
use common\models\User;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;

/**
 * Credit model
 *
 * @property integer $id
 * @property double $amount
 * @property integer $user_id
 * @property integer $plan_id
 * @property integer $status
 * @property string $error_message
 * @property User $user
 * @property Plan $plan
 * @property integer $created_at
 * @property integer $updated_at
 * @property string shopify_credit_id
 * @method creteCredit
 */

class Credit extends \yii\db\ActiveRecord
{

    const STATUS_SUCCESS = 1;
    const STATUS_ERROR = 2;
    const Status_PENDING = 0;

    const CREDIT_DESCRIPTION = 'credit';

    public static function tableName()
    {
        return '{{%credits}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['shopify_credit_id', 'status', 'error_message', 'amount'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::class, 'targetAttribute' => ['plan_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'amount' => 'Amount',
            'user_id' => 'User',
            'plan_id' => 'Plan',
            'status' => 'Status',
            'error_message' => 'Error Message',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function createCredit()
    {
        $client = $this->user->getShopifyApi();

        if ($client) {
            $data = [
                'amount' => $this->amount,
                'description' => self::CREDIT_DESCRIPTION,
                "currency"=> Currency::DEFAULT_CURRENCY
            ];

            if (strpos($this->user->username, 'sheinimporter') === 0) {
                $data['test'] = true;
            }

            return $client->getApplicationCreditManager()->create($data)->getId();
        }

        throw new Exception('client not found');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id']);
    }
}