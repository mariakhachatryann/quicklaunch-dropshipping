<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "promo_codes".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $code
 * @property int|null $plan_id
 * @property int|null $price
 * @property int|null $active_until
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Plan $plan
 * @property User $user
 * @property User[] $users
 */
class PromoCode extends \yii\db\ActiveRecord
{
    public $userName;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'promo_codes';
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
            [['user_id', 'plan_id', 'price', 'created_at', 'updated_at'], 'integer'],
            [['active_until'], 'safe'],
            [['code', 'userName'], 'string', 'max' => 255],
            [['code'], 'unique'],
            ['userName', 'validateUserAttribute'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::class, 'targetAttribute' => ['plan_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    public function validateUserAttribute($attribute)
    {
        $user =  User::findByUsername($this->userName);
        if (!$user) {
            $this->addError($attribute, 'User not found');
        }else {
            $this->user_id = $user->id;
        }

    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User',
            'code' => 'Code',
            'plan_id' => 'Plan',
            'price' => 'Price',
            'active_until' => 'Active Until',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plan::class, ['id' => 'plan_id']);
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

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['promo_code_id' => 'id']);
    }

    public static function getPromoByPlan(int $planId, string $promo, int $userId): ?self
    {
        return self::find()
            ->where(['plan_id' => $planId, 'code' => $promo])
            ->andWhere(['OR',
                ['IS', 'user_id', null],
                ['=', 'user_id', $userId]
            ])
            ->one();
    }
}
