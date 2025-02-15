<?php


namespace common\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%lead_images}}".
 *
 * @property int $id
 * @property string $name
 * @property int $lead_id
 * @property Lead $lead
 * @property int $created_at
 * @property int $updated_at
 *
 * @property LeadMessage[] $leadMessages
 * @property Subject $subject
 * @property User $user
 */

class LeadImage extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%lead_images}}';
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
            [['lead_id', 'name'], 'required'],
            ['name', 'image', 'extensions' => 'jpg, png, jpeg'],
            [['lead_id'], 'exist', 'skipOnError' => true, 'targetClass' => Lead::class, 'targetAttribute' => ['lead_id' => 'id']],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLead(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Lead::class, ['id' => 'lead_id']);
    }

}