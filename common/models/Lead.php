<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "leads".
 *
 * @property int $id
 * @property int|null $subject_id
 * @property string|null $message
 * @property string|null $image
 * @property int|null $user_id
 * @property int|null $status
 * @property string|null $additional_data
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Subject $subject
 * @property User $user
 */
class Lead extends \yii\db\ActiveRecord
{
    const UNREAD_LEAD = 0;
    const READ_LEAD = 1;
    const ANSWERED_LEAD = 2;
    const CLOSED = 3;


    const LEAD_STATUSES = [
        self::UNREAD_LEAD => 'New',
        self::READ_LEAD => 'Read',
        self::ANSWERED_LEAD => 'Answeared',
        self::CLOSED => 'Closed'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'leads';
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
            [['subject_id', 'user_id', 'status', 'created_at', 'updated_at'], 'integer'],
            [['message', 'image', 'additional_data'], 'string'],
            [['subject_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subject::class, 'targetAttribute' => ['subject_id' => 'id']],
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
            'subject_id' => 'Subject ID',
            'message' => 'Message',
            'image' => 'Image',
            'user_id' => 'User ID',
            'status' => 'Status',
            'additional_data' => 'Product URL',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Subject]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubject()
    {
        return $this->hasOne(Subject::class, ['id' => 'subject_id']);
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
     * @return \yii\db\ActiveQuery
     */
    public function getLeadMessages()
    {
        return $this->hasMany(LeadMessage::class, ['lead_id' => 'id']);
    }
}
