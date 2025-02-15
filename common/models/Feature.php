<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "features".
 *
 * @property int $id
 * @property string $name
 * @property string|null $icon
 * @property string|null $description
 * @property string|null $setting_key
 * @property int|null $sort
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Feature extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'features';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['sort', 'created_at', 'updated_at'], 'integer'],
            [['name', 'icon', 'description', 'setting_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'icon' => 'Icon',
            'description' => 'Description',
            'setting_key' => 'Setting Key',
            'sort' => 'Sort',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getAllFeatures()
    {
        return Feature::find()->orderBy(['sort' => SORT_ASC])->all();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanFeatures()
    {
        return $this->hasMany(PlanFeature::class, ['feature_id' => 'id']);
    }

    public static function getPlanSettingkeys($user)
    {
        if ($user->plan) {
            return $user->plan->getFeatures()
                ->where(['NOT', ['setting_key' => null]])
                ->select(['setting_key'])
                ->indexBy('setting_key')
                ->column();
        }
        return [];

    }

    public static function getFeauturesDescriptions()
    {
        return Feature::find()->select(['description', 'setting_key'])
            ->where(['NOT', ['setting_key' => null]])
            ->indexBy('setting_key')->column();

    }
}
