<?php

namespace common\models;

use backend\models\LeadMessageImage;
use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%lead_messages}}".
 *
 * @property int $id
 * @property int $lead_id
 * @property int $user_id
 * @property string $message
 * @property string $image
 * @property int $created_at
 * @property int $updated_at
 * @property int $status
 * @property boolean $sender
 *
 * @property Lead $lead
 * @property User $user
 * @property LeadMessageImage[] $images
 */
class LeadMessage extends \yii\db\ActiveRecord
{

    const STATUS_UNREAD = 0;
    const STATUS_READ = 1;
    const STATUS_ANSWERED = 2;

    const SENDER_ADMIN = 0;
    const SENDER_USER = 1;

    public $prepare_message;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%lead_messages}}';
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
            [['lead_id', 'user_id', 'created_at', 'updated_at', 'status', 'sender'], 'integer'],
            [['message', 'prepare_message'], 'string'],
            ['image', 'image', 'extensions' => 'jpg, png, jpeg'],
            [['lead_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lead::class, 'targetAttribute' => ['lead_id' => 'id']],
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
            'lead_id' => 'Lead',
            'user_id' => 'User',
            'message' => 'Message',
            'image' => 'Image',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLead()
    {
        return $this->hasOne(Lead::class, ['id' => 'lead_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getImageUrl()
    {
        return '/backend/web/uploads/lead_message_images/'.$this->image;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImages(): \yii\db\ActiveQuery
    {
        return $this->hasMany(LeadMessageImage::class, ['lead_message_id' => 'id']);
    }
}
