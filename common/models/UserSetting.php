<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_settings}}".
 *
 * @property int $id
 * @property int $user_id
 * @property int $price_markup
 * @property int $compare_at_price_markup
 * @property int $review_limit_per_page
 * @property double $price_by_percent
 * @property double $price_by_amount
 * @property double $compare_at_price_by_amount
 * @property double $compare_at_price_by_percent
 * @property int $sku_import_type
 * @property int $price_import_type
 * @property int $stock_count_import_type
 * @property int $image_import_type
 * @property int $date_format
 * @property int $enable_add_reviews
 * @property int $reviews_auto_publish
 * @property int $product_currency_convertor
 * @property int $enable_add_review_images
 * @property int $measurement
 * @property int $variant_price_markup
 * @property int $site_theme
 * @property string $review_text_color
 * @property integer $review_fontsize
 * @property integer $custom_pricing_rules
 * @property boolean $use_default_currency
 * @property integer $currency_id
 * @property integer $default_currency_id
 * @property bool $import_reviews
 * @property bool $multiple_import
 * @property Currency $currency
 * @property Currency $defaultCurrency
 * @property string logo
 * @property string store_name
 *
 *
 * @property User $user
 */
class UserSetting extends \yii\db\ActiveRecord
{
    const SCENARIO_DEFAULT_CURRENCY = 'createDefaultCurrency';

    const BY_PERCENT = 0;
    const BY_AMOUNT = 1;

    const THEME_DARK = 'dark';
    const THEME_LIGHT = 'light';

    const SAME_FOR_EACH_VARIANT = 0;
    const DIFFERENT_FOR_EACH_VARIANT = 1;

    const MEASUREMENT_IN = 0;
    const MEASUREMENT_CM = 1;

    const MEASUREMENTS = [
        self::MEASUREMENT_IN => 'IN',
        self::MEASUREMENT_CM => 'CM',
    ];

    public static $priceMarkups = [
        self::BY_PERCENT => 'By Percent',
        self::BY_AMOUNT => 'By Amount',
    ];

    public static $siteThemes = [
        self::THEME_DARK => 0,
        self::THEME_LIGHT => 1,
    ];

    const _2px = 1;
    const _4px = 2;
    const _6px = 3;
    const _8px = 4;
    const _10px = 5;

    public static $fontSizeChanges = [
        self::_2px => '2px',
        self::_4px => '4px',
        self::_6px => '6px',
        self::_8px => '8px',
        self::_10px => '10px',
    ];

    public static $dateFormats = [
        0 => 'Y-m-d',
        1 => 'D/M/Y',
        2 => 'Y/M/D',
        3 => 'M/D/Y',
        4 => 'Y-m-d H:i',
        5 => 'd.m.Y',
        6 => 'm d Y',

    ];

    public static $jsDateFormats = [
        0 => 'YYYY-MM-DD',
        1 => 'DD/MM/YYYY',
        2 => 'YYYY/MM/DD',
        3 => 'MM/DD/YYYY',
        4 => 'YYYY-MM-DD HH:mm',
        5 => 'DD.MM.YYYY',
        6 => 'MMM DD YYYY'

    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_settings}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['import_reviews', 'variant_price_markup', 'user_id', 'date_format', 'enable_add_reviews', 'enable_add_review_images', 'review_fontsize', 'change_variants_option_name', 'product_currency_convertor', 'custom_pricing_rules', 'use_default_currency', 'currency_id', 'default_currency_id', 'reviews_auto_publish', 'site_theme', 'multiple_import'], 'integer'],
            [['review_text_color', 'store_name'], 'string'],
            [['logo'], 'file'],
            [['price_markup', 'compare_at_price_markup'], 'in', 'range' => [static::BY_AMOUNT, static::BY_PERCENT]],
            [['site_theme'], 'in', 'range' => static::$siteThemes],
            [['measurement'], 'in', 'range' => array_keys(self::MEASUREMENTS)],
            [['sku_import_type', 'price_import_type', 'stock_count_import_type', 'image_import_type'], 'in',
                'range' => [static::SAME_FOR_EACH_VARIANT, static::DIFFERENT_FOR_EACH_VARIANT]],
            [['price_by_percent', 'price_by_amount', 'compare_at_price_by_amount',
                'compare_at_price_by_percent', 'price_markup','compare_at_price_markup', 'review_limit_per_page'], 'number'],
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
            'user_id' => 'User ID',
            'price_markup' => 'Price Markup',
            'price_amount' => 'Price Amount',
            'price_percentage' => 'Price Percentage',
            'custom_pricing_rules' => 'Custom Pricing Rules',
            'product_currency_convertor' => 'Product Currency Convertor',
            'reviews_auto_publish' => 'Reviews Auto Publish',
            'review_limit_per_page' => 'Reviews Limit Per Page',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'currency_id']);

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultCurrency(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Currency::class, ['id' => 'default_currency_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function dateFormat($js = false)
    {

        return $js ? self::$jsDateFormats[$this->date_format] : self::$dateFormats[$this->date_format];
    }
}
