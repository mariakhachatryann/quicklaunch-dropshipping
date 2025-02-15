<?php

namespace common\models;

use common\helpers\AlibabaHelper;
use common\helpers\AliexpressHelper;
use common\helpers\AmazonHelper;
use common\helpers\BangoodHelper;
use common\helpers\DearLoveHelper;
use common\helpers\DhgateHelper;
use common\helpers\EbayHelper;
use common\helpers\EfourWholesalehHelper;
use common\helpers\EmmaclothHelper;
use common\helpers\EverythingFivePoundsHelper;
use common\helpers\FashionnovaHelper;
use common\helpers\GearbestHelper;
use common\helpers\KohlsHelper;
use common\helpers\LazadaHelper;
use common\helpers\MiniintheboxHelper;
use common\helpers\ModeSheHelper;
use common\helpers\PrettyLittleThing;
use common\helpers\RosegalHelper;
use common\helpers\SheinHelper;
use common\helpers\TemuHelper;
use common\helpers\TomtopHelper;
use common\helpers\TrendyolHelper;
use Yii;

/**
 * This is the model class for table "sites".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $url
 * @property int|null $import_by_queue
 * @property string|null $color
 * @property int|null $scrap_internal
 * @property int|null $monitor_available
 * @property int|null $import_by_extension
 * @property string|null $logo
 * @property int|null $is_new
 * @property int|null $has_reviews
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class AvailableSite extends \yii\db\ActiveRecord
{

    const SCRAP_EXTERNAL = 0;
    const SCRAP_INTERNAL = 1;

    const SCRAP_TYPES = [
        self::SCRAP_INTERNAL => 'Internal',
        self::SCRAP_EXTERNAL => 'External'
    ];

    const EXTENSION_SITES = [
        self::SITE_SHEIN,
        self::SITE_ALIEXPRESS,
        self::SITE_EMMACLOTH,
        self::SITE_PRETTY_LITTLE_THING,
        self::SITE_FASHIONNOVA,
        self::SITE_CHICME,
        self::SITE_BELLEWHOLESALE,
        self::SITE_TRENDYOL
    ];

    const SITE_ALIEXPRESS = 'aliexpress';
    const SITE_DHGATE = 'dhgate';
    const SITE_EMMACLOTH = 'emmacloth';
    const SITE_SHEIN = 'shein';
    const SITE_DEAR_LOVER = 'dear-lover';
    const SITE_PRETTY_LITTLE_THING = 'prettylittlething';
    const SITE_E_FOUR_WHOLESALE = 'e4wholesale';
    const SITE_LAZADA = 'lazada';
    const SITE_AMAZON = 'amazon';
    const SITE_GEARBEST = 'gearbest';
    const SITE_EVERYTHING_FIVE_POUNDS = 'everything5pounds';
    const SITE_FASHIONNOVA = 'fashionnova';
    const SITE_CHICME = 'chicme';
    const SITE_BELLEWHOLESALE = 'bellewholesale';
    const SITE_TRENDYOL = 'trendyol';
    const SITE_TEMU = 'temu';
    const SITE_ALIBABA = 'alibaba';
    const SITE_BANGOOD = 'banggood';
    const SITE_EBAY = 'ebay';
    const SITE_MINIINTHEBOX = 'miniinthebox';
    const SITE_ROSEGAL = 'rosegal';
    const SITE_TOMTOP = 'tomtop';
    const SITE_KOHLS = 'kohls';
    const SITE_MODESHE = 'modeshe';

    const SITES_HELPERS_MAP = [
        self::SITE_SHEIN => SheinHelper::class,
        self::SITE_DEAR_LOVER => DearLoveHelper::class,
        self::SITE_ALIEXPRESS => AliexpressHelper::class,
        self::SITE_AMAZON => AmazonHelper::class,
        self::SITE_GEARBEST => GearbestHelper::class,
        self::SITE_EVERYTHING_FIVE_POUNDS => EverythingFivePoundsHelper::class,
        self::SITE_FASHIONNOVA => FashionnovaHelper::class,
        self::SITE_TRENDYOL => TrendyolHelper::class,
        self::SITE_TEMU => TemuHelper::class,
        self::SITE_ALIBABA => AlibabaHelper::class,
        self::SITE_BANGOOD => BangoodHelper::class,
        self::SITE_EBAY => EbayHelper::class,
        self::SITE_DHGATE => DhgateHelper::class,
        self::SITE_EMMACLOTH => EmmaclothHelper::class,
        self::SITE_LAZADA => LazadaHelper::class,
        self::SITE_MINIINTHEBOX => MiniintheboxHelper::class,
        self::SITE_ROSEGAL => RosegalHelper::class,
        self::SITE_TOMTOP => TomtopHelper::class,
        self::SITE_KOHLS => KohlsHelper::class,
        self::SITE_MODESHE => ModeSheHelper::class,
        self::SITE_PRETTY_LITTLE_THING => PrettyLittleThing::class,
        self::SITE_E_FOUR_WHOLESALE => EfourWholesalehHelper::class,
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sites';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['import_by_queue', 'scrap_internal', 'monitor_available', 'import_by_extension', 'is_new', 'has_reviews', 'created_at', 'updated_at'], 'integer'],
            [['name', 'url', 'color', 'logo'], 'string', 'max' => 255],
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
            'url' => 'Url',
            'import_by_queue' => 'Import By Queue',
            'color' => 'Color',
            'scrap_internal' => 'Scrap Internal',
            'monitor_available' => 'Monitor Available',
            'import_by_extension' => 'Import By Extension',
            'logo' => 'Logo',
            'is_new' => 'Is New',
            'has_reviews' => 'Has Reviews',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public static function getSitesDropdown(): array
    {
        return self::find()->select(['name', 'id'])->indexBy('id')->column();
    }
}
