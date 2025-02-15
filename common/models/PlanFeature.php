<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "plan_features".
 *
 * @property int $id
 * @property int|null $plan_id
 * @property int|null $feature_id
 *
 * @property Features $feature
 * @property Plans $plan
 */
class PlanFeature extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan_features';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_id', 'feature_id'], 'integer'],
            [['feature_id'], 'exist', 'skipOnError' => true, 'targetClass' => Features::class, 'targetAttribute' => ['feature_id' => 'id']],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plans::class, 'targetAttribute' => ['plan_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_id' => 'Plan ID',
            'feature_id' => 'Feature ID',
        ];
    }

    /**
     * Gets query for [[Feature]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFeature()
    {
        return $this->hasOne(Features::class, ['id' => 'feature_id']);
    }

    /**
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plans::class, ['id' => 'plan_id']);
    }
}
