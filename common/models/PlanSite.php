<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "plan_sites".
 *
 * @property int $id
 * @property int|null $plan_id
 * @property int|null $site_id
 *
 * @property Plans $plan
 * @property Sites $site
 */
class PlanSite extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan_sites';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_id', 'site_id'], 'integer'],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plans::class, 'targetAttribute' => ['plan_id' => 'id']],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => Sites::class, 'targetAttribute' => ['site_id' => 'id']],
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
            'site_id' => 'Site ID',
        ];
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

    /**
     * Gets query for [[Site]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Sites::class, ['id' => 'site_id']);
    }
}
