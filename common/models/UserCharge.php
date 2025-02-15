<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_charges}}".
 *
 * @property int $id
 * @property int $charge_id
 * @property int $user_id
 * @property string $name
 * @property int $api_client_id
 * @property float|null $price
 * @property string $status
 * @property string|null $billing_on
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $test
 * @property string|null $activated_on
 * @property string|null $canceled_on
 * @property int|null $trial_days
 * @property float|null $capped_amount
 * @property string|null $trial_ends_a_on
 * @property float|null $balance_used
 * @property float|null $balance_remaining
 * @property int|null $risk_level
 */
class UserCharge extends \yii\db\ActiveRecord
{
	const STATUS_ACTIVE = 'Active';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_charges}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['charge_id','user_id', 'api_client_id', 'test', 'trial_days', 'risk_level'], 'integer'],
            [['price', 'capped_amount', 'balance_used', 'balance_remaining'], 'number'],
            [['billing_on', 'created_at', 'updated_at', 'activated_on', 'canceled_on', 'trial_ends_a_on'], 'safe'],
            [['name', 'status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'charge_id' => 'Charge ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'api_client_id' => 'Api Client ID',
            'price' => 'Price',
            'status' => 'Status',
            'billing_on' => 'Billing On',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'test' => 'Test',
            'activated_on' => 'Activated On',
            'canceled_on' => 'Canceled On',
            'trial_days' => 'Trial Days',
            'capped_amount' => 'Capped Amount',
            'trial_ends_a_on' => 'Trial Ends A On',
            'balance_used' => 'Balance Used',
            'balance_remaining' => 'Balance Remaining',
            'risk_level' => 'Risk Level',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getChargeId()
    {
        return $this->charge_id;
    }
}
