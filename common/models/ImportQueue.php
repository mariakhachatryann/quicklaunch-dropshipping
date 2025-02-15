<?php

namespace common\models;

use Yii;
use yii\base\BaseObject;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\validators\UrlValidator;

/**
 * This is the model class for table "import_queues".
 *
 * @property int $id
 * @property int|null $site_id
 * @property int|null $user_id
 * @property string|null $url
 * @property string|null $processing_ip
 * @property int|null $status
 * @property int $country
 * @property int $monitoring_queue_id
 * @property int $handler
 * @property int $fail_count
 * @property int $type
 * @property int|null $created_at
 * @property boolean $import_reviews
 * @property int|null $updated_at
 *
 * @property AvailableSite $site
 * @property User $user
 * @property MonitoringQueue $monitoringQueue
 * @property AlertCaptcha[] $alertCaptchas
 */
class ImportQueue extends ActiveRecord
{

    const TYPE_MONITORING = 0;
    const TYPE_IMPORT = 1;

    const TYPE_MAP = [
        self::TYPE_MONITORING => 'Monitoring',
        self::TYPE_IMPORT => 'Import',
    ];

    const STATUS_PENDING = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_SUCCESSFUL = 2;
    const STATUS_ERROR = 3;

    const VERSION = 4;

    const COUNTRY_ANY = -1;
    
    const COUNTRY_US = 0;
    const COUNTRY_GLOBAL = 1;
    const COUNTRY_ES = 2;
    const COUNTRY_DE = 3;
    const COUNTRY_FR = 4;
    const COUNTRY_EUR = 5;

    const COUNTRY_OTHER = 6;
    const COUNTRY_TEMU = 7;
    const COUNTRY_BELLE_WHOLESALE = 8;
    const COUNTRY_TRENDYOL = 9;
    const COUNTRY_BR = 10;
    const COUNTRY_CA = 11;
    const COUNTRY_ZA = 12;
    const COUNTRY_AMAZON = 13;
    const COUNTRY_ALIEXPRESS = 14;
    const COUNTRY_GB = 15;
    const COUNTRY_AU = 16;

    const MAX_FAIL_COUNT = 1;

    const COUNTRY_MAP = [
        self::COUNTRY_US => 'us',
        self::COUNTRY_GLOBAL => 'www',
        self::COUNTRY_ES => 'es',
        self::COUNTRY_DE => 'de',
        self::COUNTRY_FR => 'fr',
        self::COUNTRY_EUR => 'eur',
        self::COUNTRY_BR => 'br',
        self::COUNTRY_AU => 'au',
        self::COUNTRY_CA => 'ca',
        self::COUNTRY_ZA => 'za',
        self::COUNTRY_OTHER => 'other',
        self::COUNTRY_TEMU => 'temu',
        self::COUNTRY_TRENDYOL => 'trendyol',
        self::COUNTRY_BELLE_WHOLESALE => 'bellewholesale',
        self::COUNTRY_AMAZON => 'amazon',
        self::COUNTRY_ALIEXPRESS => 'aliexpress',
        self::COUNTRY_GB => 'gb',
    ];

    const STATUSES = [
        self::STATUS_PENDING => 'Pending',
        self::STATUS_PROCESSING => 'Processing',
        self::STATUS_SUCCESSFUL => 'Successful',
        self::STATUS_ERROR => 'Error',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'import_queues';
    }

    /**
     * @param string $url
     * @param int $siteId
     * @param int $user_id
     * @return bool
     */
    public static function createQueue(string $url, AvailableSite $site, int $user_id, int $import_reviews, int $type = 0, ?int $monitoringQueueId = null): self
    {
        $url = rtrim($url, '#');
        $model = new self();
        $model->site_id = $site->id;
        $model->user_id = $user_id;
        $model->type = $type;
        $model->import_reviews = $import_reviews;
        $model->monitoring_queue_id = $monitoringQueueId;

        if (strpos($url, AvailableSite::SITE_SHEIN) !== false) {
            $url = str_replace('https://shein.com/us/', 'https://us.shein.com/', $url);
            $url = str_replace('https://shein.com/', 'https://www.shein.com/', $url);
        }

        $model->url = $url;
        $model->country = $site->name == AvailableSite::SITE_SHEIN ? self::COUNTRY_US : self::COUNTRY_OTHER;
        if ($site->name == AvailableSite::SITE_SHEIN) {
            if (strpos($url,  'shein.co.uk')) {
                $model->country = self::COUNTRY_GB;
            } else {
                foreach (self::COUNTRY_MAP as $countryNumber => $country) {
                    if (strpos($url, $country . '.shein')) {
                        $model->country = $countryNumber;
                    }
                }
            }
            
        } elseif (in_array($site->name, [AvailableSite::SITE_TEMU])) {
            $model->country = self::COUNTRY_TEMU;
        } elseif (in_array($site->name, [AvailableSite::SITE_TRENDYOL])) {
            $model->country = self::COUNTRY_TRENDYOL;
        } elseif (in_array($site->name, [AvailableSite::SITE_BELLEWHOLESALE, AvailableSite::SITE_CHICME])) {
            $model->country = self::COUNTRY_BELLE_WHOLESALE;
        }  elseif ($site->name == AvailableSite::SITE_AMAZON) {
            $model->country = self::COUNTRY_AMAZON;
        } elseif ($site->name == AvailableSite::SITE_ALIEXPRESS) {
            $model->country = self::COUNTRY_ALIEXPRESS;
        }
        
        $validator = new UrlValidator();
        if (!$validator->validate($model->url)) {
            $model->status = ImportQueue::STATUS_ERROR;
        }

        $model->save();
        return $model;
    }

    public function getContent(): ?array
    {
        $key = $this->getCacheKey();
        return Yii::$app->cache->get($key) ?: null;
    }

    public function getCacheKey(): string
    {
        return 'import_queue_' . $this->id;
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['site_id', 'user_id', 'country', 'handler', 'status', 'created_at', 'updated_at', 'fail_count', 'type', 'monitoring_queue_id'], 'integer'],
            [['import_reviews'], 'boolean'],
            [['url'], 'string', 'max' => 700],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            [['site_id'], 'exist', 'skipOnError' => true, 'targetClass' => AvailableSite::class, 'targetAttribute' => ['site_id' => 'id']],
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
            'site_id' => 'Site',
            'user_id' => 'User',
            'url' => 'Url',
            'fail_count' => 'Fail Count',
            'status' => 'Status',
            'type' => 'Type',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'import_reviews' => 'Import Reviews',
            'monitoring_queue_id' => 'Monitoring Queue Id',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * Gets query for [[AvailableSite]].
     *
     * @return ActiveQuery
     */
    public function getSite(): ActiveQuery
    {
        return $this->hasOne(AvailableSite::class, ['id' => 'site_id']);
    }

    /**
     * Gets query for [[MonitoringQueue]].
     *
     * @return ActiveQuery
     */
    public function getMonitoringQueue(): ActiveQuery
    {
        return $this->hasOne(MonitoringQueue::class, ['id' => 'monitoring_queue_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    /**
     * Gets query for [[AlertCaptcha]].
     *
     * @return ActiveQuery
     */
    public function getAlertCaptchas(): ActiveQuery
    {
        return $this->hasMany(AlertCaptcha::class, ['import_queue_id' => 'id']);
    }

    public function updateData(array $data): bool
    {
        $key = $this->getCacheKey();
        return Yii::$app->cache->set($key, $data, 3600);
    }

    public function getLogData(): array
    {
        $logData = $this->attributes;
        $logData['created_at'] = date('Y-m-d H:i:s', $logData['created_at']);
        $logData['updated_at'] = date('Y-m-d H:i:s', $logData['updated_at']);
        $logData['captchaCount'] = count($this->alertCaptchas);
        return $logData;
    }
}
