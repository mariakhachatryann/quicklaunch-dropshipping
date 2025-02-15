<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "plans".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $description
 * @property int|null $price
 * @property int|null $trial_days
 * @property int|null $product_limit
 * @property int|null $monitoring_limit
 * @property int|null $review_limit
 * @property int|null $is_custom
 * @property string|null $color
 * @property float|null $old_price
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 */
class Plan extends \yii\db\ActiveRecord
{
    public $featureIds;
    public $siteIds;

    const PLAN_ACTIVE = 1;
    const PLAN_INACTIVE = 0;

    const BASIC_PLAN_ID = 5;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plans';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['price', 'trial_days', 'product_limit', 'monitoring_limit', 'review_limit', 'is_custom', 'created_at', 'updated_at'], 'integer'],
            [['old_price'], 'number'],
            [['name', 'description', 'color'], 'string', 'max' => 255],
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
            'description' => 'Description',
            'price' => 'Price',
            'trial_days' => 'Trial Days',
            'product_limit' => 'Product Limit',
            'monitoring_limit' => 'Monitoring Limit',
            'review_limit' => 'Review Limit',
            'is_custom' => 'Is Custom',
            'color' => 'Color',
            'old_price' => 'Old Price',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[PromoCodes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPromoCodes()
    {
        return $this->hasMany(PromoCode::class, ['plan_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['plan_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanSites()
    {
        return $this->hasMany(PlanSite::class, ['plan_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSites()
    {
        return $this->hasMany(AvailableSite::class, ['id' => 'site_id'])->via('planSites');
    }

    public function getSiteNames()
    {
        return implode(', ',$this->getSites()->select(['name'])->column());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPlanFeatures()
    {
        return $this->hasMany(PlanFeature::class, ['plan_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeatures()
    {
        return $this->hasMany(Feature::class, ['id' => 'feature_id'])->via('planFeatures')->orderBy('sort');
    }
    public static function getFreePlan(): ?self
    {
        return self::find()->where(['price' => 0])->one();
    }

    public static function getBasicPlan(): ?self
    {
        return self::find()->where(['!=', 'price', 0])->orderBy('price')->one();
    }

    public function getFeaturesIds()
    {
        return $this->hasMany(Feature::class, ['id' => 'feature_id'])->via('planFeatures')
            ->orderBy('sort')->select('id')->indexBy('id')->column();
    }

    public static function getPlans()
    {
        return self::find()->select(['name','id'])->indexBy('id')->column();
    }

}
