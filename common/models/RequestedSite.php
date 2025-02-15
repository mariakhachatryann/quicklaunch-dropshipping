<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "requested_sites".
 *
 * @property int $id
 * @property string|null $url
 * @property int|null $user_id
 * @property int|null $status
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Users $user
 */
class RequestedSite extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_IMPLEMENTED = 2;
    const STATUS_DECLINED = 3;

    const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_IN_PROGRESS => 'In progress',
        self::STATUS_IMPLEMENTED => 'Implemented',
        self::STATUS_DECLINED => 'Declined',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'requested_sites';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['url'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'user_id' => 'User ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::class, ['id' => 'user_id']);
    }
}
