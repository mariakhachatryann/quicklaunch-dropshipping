<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notifications".
 *
 * @property int $id
 * @property string|null $subject
 * @property string|null $text
 * @property int|null $date
 * @property int|null $notification_type
 * @property int|null $user_id
 * @property string|null $url
 * @property string|null $additional_data
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property User $user
 */
class Notification extends \yii\db\ActiveRecord
{
    const INFO_NOTIFICATION = 0;
    const DANGER_NOTIFICATION = 1;
    const SUCCESS_NOTIFICATION = 2;
    const WARNING_NOTIFICATION = 3;

    public static $notificationTypes = [
        self::INFO_NOTIFICATION => 'info',
        self::DANGER_NOTIFICATION => 'danger',
        self::SUCCESS_NOTIFICATION => 'success',
        self::WARNING_NOTIFICATION => 'warning',
    ];

    public static $notificationTypeStyles = [
        self::INFO_NOTIFICATION => 'fa fa-info text-blue',
        self::DANGER_NOTIFICATION => 'fa fa-ban text-red',
        self::SUCCESS_NOTIFICATION => 'fa fa-check text-green',
        self::WARNING_NOTIFICATION => 'fa fa-warning text-yellow',
    ];



    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['text', 'additional_data'], 'string'],
            [['date', 'notification_type', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['subject', 'url'], 'string', 'max' => 255],
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
            'subject' => 'Subject',
            'text' => 'Text',
            'date' => 'Date',
            'notification_type' => 'Notification Type',
            'user_id' => 'User ID',
            'url' => 'Url',
            'additional_data' => 'Additional Data',
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
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public static function notificationDivStyle($type)
    {
        return self::$notificationTypes[$type] ?? '';
    }
}
