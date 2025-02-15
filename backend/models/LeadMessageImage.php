<?php


namespace backend\models;

use common\models\Lead;
use common\models\LeadMessage;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%lead_images}}".
 *
 * @property int $id
 * @property string $name
 * @property string $imageUrl
 * @property int $lead_message_id
 * @property Lead $lead
 * @property int $created_at
 * @property int $updated_at
 *
 */

class LeadMessageImage extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%lead_message_images}}';
    }

    /**
     * @return string[]
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['lead_message_id', 'name'], 'required'],
            ['name', 'image', 'extensions' => 'jpg, png, jpeg'],
            [['lead_message_id'], 'exist', 'skipOnError' => true, 'targetClass' => LeadMessage::class, 'targetAttribute' => ['lead_message_id' => 'id']],
        ];
    }

    public function getImageUrl()
    {
        return '/backend/web/uploads/lead_message_images/'. $this->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeadMessage(): \yii\db\ActiveQuery
    {
        return $this->hasOne(LeadMessage::class, ['id' => 'lead_id']);
    }

}