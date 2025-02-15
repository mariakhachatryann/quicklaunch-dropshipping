<?php

namespace common\models;

use Exception;
use GuzzleHttp\Client;
use Yii;
use yii\base\Model;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "plans".
 *
 * @property string $url
 *
 * @property string $scrappedProductUrl
 */
class ProductUrl extends Model
{

    const NODE_URL = 'http://localhost:4000/';
    const NODE_ACTION_SCRAP = 'scrap';
    const NODE_ACTION_REVIEWS = 'reviews';
    const NODE_ACTION_VARIANTS = 'product-variant';


    public $url;
    public $content;
    public $addSheinHeader;

    protected $_site;

    protected ?User $_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['url', 'content'], 'string'],
            [['url'], 'required'],
            ['url', 'filter', 'filter' => [$this, 'fixUrl']],
        ];
    }

    public function setUser(User $user)
    {
        $this->_user = $user;
    }

    public function fixUrl($url)
    {
        $domain = parse_url($url, PHP_URL_HOST);

        if ((strpos($url, AvailableSite::SITE_TRENDYOL) || strstr($url, AvailableSite::SITE_EBAY)) && strpos($url, '?')) {
            $url = substr($url, 0, strpos($url, '?'));
        } elseif (strpos($url, AvailableSite::SITE_BANGOOD) !== false) {
            if (strpos($url, '-m.') !== false || strpos($url, 'm.') !== false) {
                $url = str_replace(['-m.', 'm.'], ['.', ''], $url);
            }
        } elseif (strpos($url, AvailableSite::SITE_GEARBEST) !== false) {
            if (strpos($url, '/m-') !== false || strpos($url, '/m.') !== false) {
                $url = str_replace(['/m-', '/m.'], '/', $url);
            }
        } elseif (strpos($domain, 'm.') === 0) {
            $urlData = parse_url($this->url);
            $domain = $urlData['host'];
            $path = $urlData['path'];
            if ($domain == 'm.shein.com') {
                $domain = 'shein.com';
                $explodePath = explode('/', $path);
                $sub = 'www';
                $path = $explodePath[1];
                if (in_array(strlen($explodePath[1]), [2, 3])) {
                    $sub = $explodePath[1];
                    $path = $explodePath[2];
                }
                $this->url = "{$urlData['scheme']}://{$sub}.{$domain}/{$path}";
            }
            
            $url = $this->url;
            $url = str_replace('m.', 'www.', $url);
        }
    
        if (strpos($url, AvailableSite::SITE_SHEIN) !== false || strpos($url, AvailableSite::SITE_TEMU) !== false) {
            $url = explode('.html', $url)[0] . '.html';
            $url = str_replace('https://shein.com', 'https://www.shein.com', $url);
        }


        if (strpos($url, AvailableSite::SITE_SHEIN) !== false) {
            $url = str_replace('https://shein.com/us/', 'https://us.shein.com/', $url);
            $url = str_replace('https://shein.com/', 'https://www.shein.com/', $url);

        }

        if (strpos($url, '#') !== false) {
            $url = substr($url, 0, strpos($url, '#'));
        }

        return $url;
    }

    protected function getIsGlobalShein(): bool
    {
        $urlData = parse_url($this->url);
        $domain = $urlData['host'];
        $isGlobalShein = in_array($domain, ['www.shein.com', 'shein.com']);
        return $isGlobalShein;
    }

    public function getProductData()
    {
        if (!$this->url) {
            return null;
        }
        $site = $this->getSite();
        if (!$site) {
            return null;
        }

        if (!$this->content) {
            if (in_array($site->name, [AvailableSite::SITE_DHGATE])) {
                $pageContent = '';
            } else {
                $pageContent = $this->getPageContent();
            }
        } else {
            $pageContent = $this->content;
        }

        if ($site->import_by_queue && !$this->content) {
            $response = json_encode($pageContent);
        } else {
            $response = $this->getDataByContent($pageContent);
            if ($site->name == AvailableSite::SITE_SHEIN && !empty($this->_user)) {

                $response = json_decode($response, true);

                if (!empty($response['body_html'])) {
                    $sizeArray = $this->getSizeArray($pageContent);
                    $sizeTable = '';
                    if ($this->_user->userSetting->measurement == UserSetting::MEASUREMENT_IN && !empty($sizeArray['sizeInfoInch'])) {
                        $sizeTable = $this->makeTableFromSizeArray($sizeArray['sizeInfoInch'], 'sizeInfoInch');
                    } elseif (!empty($sizeArray['sizeInfo'])) {
                        $sizeTable = $this->makeTableFromSizeArray($sizeArray['sizeInfo'], 'sizeInfo');
                    }

                    $response['sizeTable'] = $sizeTable;
                }

                $response = json_encode($response);
            }
        }

        return $response;
    }

    public function getSite(): ?AvailableSite
    {
        if (!$this->_site) {
            $domain = parse_url($this->url, PHP_URL_HOST);
            if (strpos($domain, 'share.') !== false) {
                return null;
            }

            if (strpos($domain, 'shopee') !== false) {
                $siteName = 'shopee';
            } else {
                if (substr_count($domain, '.') > 1) {
                    $siteName = substr($domain, strpos($domain, ".") + 1);
                } else {
                    $siteName = $domain;
                }
                $siteName = explode('.', $siteName);
                $siteName = $siteName[0];
                if (!$siteName) {
                    return null;
                }
            }

            $site = AvailableSite::find()->where(['=', 'name', $siteName])->one();
            $this->_site = $site;
        }
       
        return $this->_site;
    }

    protected function getParsedVariantsFromData(array $content, $fromExtension): array
    {
        $variants = $content['variants'];
        $skuIndex = $this->getIndexForOption('SKU', $variants);
        $priceIndex = $this->getIndexForOption('Price', $variants);
        $compareAtPriceIndex = $this->getIndexForOption('CompareAtPrice', $variants);
        $qtyIndex = $this->getIndexForOption('Quantity', $variants);
        unset($variants[0]);
        $parsedVariants = [];
        if ($variants) {
            foreach ($variants as $variantItem) {
                $parsedVariants[] = [
                    'default_sku' => $variantItem[$skuIndex]['name'],
                    'sku' => $variantItem[$skuIndex]['name'],
                    'price' => $variantItem[$priceIndex]['name'],
                    'compare_at_price' => $fromExtension ? $variantItem[$compareAtPriceIndex]['name'] : $variantItem[$priceIndex]['name'],
                    'inventory_quantity' => $variantItem[$qtyIndex]['name'],
                ];
            }
        } else {
            $parsedVariants[] = [
                'default_sku' => $content['productId'],
                'sku' => $content['productId'],
                'price' => $content['price'],
                'compare_at_price' => $content['price'],
                'inventory_quantity' => $content['stockCount'],
            ];
        }


        return [
            'data' => ['variants' => $parsedVariants],
            'success' => 1,
        ];
    }

    public function getPageContent($includeReviews = null, $monitoring = false, $monitoringQueueId = null)
    {
        if (is_null($includeReviews)) {
            $includeReviews = $this->_user->userSetting->import_reviews;
        }

        $site = $this->getSite();
        if ($site && $site->import_by_queue) {

            $importQueue = $this->addUrlToImportQueue($this->url, $site, $this->_user->id, $includeReviews, $monitoring ? ImportQueue::TYPE_MONITORING : ImportQueue::TYPE_IMPORT, $monitoringQueueId);

            if ($importQueue->status == ImportQueue::STATUS_ERROR) {
                Yii::error($importQueue->attributes, 'ImportQueueInvalidUrl');
                return false;
            }
            for ($i = 1; $i < 100; $i++) {
                sleep(2);
                $content = $importQueue->getContent();
                if ($content) {
                    if (isset($content['status'])) {
                        if ($content['status'] == 0) {
                            $importQueue->refresh();
                            Yii::error([
                                'id' => $importQueue->id,
                                'url' => $importQueue->url,
                                'handler' => $importQueue->handler,
                                'country' => ImportQueue::COUNTRY_MAP[$importQueue->country] ?? $importQueue->country,
                                $content
                            ], 'ImportQueueError');
                            return false;
                        }
                    }
                    return $content;
                }
            }
            return false;
        }
        $client = new Client();

        $curlData = [
            CURLOPT_PROXY => Yii::$app->params['proxyManagerUrl'],
            CURLOPT_PROXYPORT => $this->getPort(),
            CURLOPT_SSL_VERIFYPEER => false
        ];

        $options = [
            'timeout' => 100,
            'curl' => $curlData,
        ];

        if ($this->addSheinHeader) {
            $options['headers'] = [
                'x-requested-with' => 'XMLHttpRequest'
            ];
        }
        $response = $client->get($this->url, $options);

        $contents = $response->getBody()->getContents();
        return $contents === '' ? false : $contents;

    }

    protected function getPort(): string
    {
        $urlData = parse_url($this->url);
        $domain = $urlData['host'];

        if (strpos($domain, 'co.uk')) {
            return '24004';
        }

        if (strpos($domain, 'eur.shein.com') !== false || strpos($domain, 'fr.shein.com')) {
            return '24007';
        }

        if (strpos($domain, 'nz.shein.com') !== false) {
            return '24009';
        }

        if (strpos($domain, 'de.shein.com') !== false) {
            return '24008';
        }

        if (strpos($domain, 'it.shein.com') !== false) {
            return '24010';
        }

        if (strpos($domain, 'shein') !== false || strpos($domain, 'amazon') !== false
            || strpos($domain, 'aliexpress') !== false) {
            $isGlobalShein = $this->getIsGlobalShein();
            return $isGlobalShein ? '24003' : '24005';
        }
        return '24005';
    }

    public function getDataByContent($content)
    {
        $site = $this->getSite();

        if (strpos($site->name, AvailableSite::SITE_SHEIN) !== false && Yii::$app->params['sheinScrapNode']) {
            return $this->sendPostToNode(static::NODE_ACTION_SCRAP, $content);
        }
        $siteHelper = $site->getHelperClass();

        return json_encode($siteHelper->getProduct($content, $this->url));
    }

    public function sendPostToNode(string $action, ?string $content = null)
    {
        $url = static::NODE_URL . $action;
        $response = '';
        if ($this->url) {
            $client = new Client();
            $data = [
                'url' => $this->url
            ];
            if ($content) {
                $data['html_content'] = $content;
            }
            try {
                $options = [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json'
                    ],
                    'json' => $data,
                ];
                $response = $client->post($url, $options)->getBody()->getContents();
            } catch (Exception $e) {
                Yii::error($e->getMessage(), 'nodeError');
                $response = json_encode([]);
            }
        }
        return $response;
    }

    public function getSizeArray($content)
    {
        $findString = 'productIntroData: ';

        $pos = strpos($content, $findString);
        $cutString = substr($content, $pos + strlen($findString));

        $lastPos = strpos($cutString, '</script>');

        $cutString = substr($cutString, 0, $lastPos);

        $openBrackets = 0;
        $closeBrackets = 0;

        for ($i = 0; $i < strlen($cutString); $i++) {
            if ($cutString[$i] == '{') {
                $openBrackets++;
            }
            if ($cutString[$i] == '}') {
                $closeBrackets++;
            }
            if ($openBrackets == $closeBrackets) {
                $cutString = substr($cutString, 0, ++$i);
                break;
            }
        }

        $cutString = json_decode($cutString, true);

        return $cutString['sizeInfoDes'] ?? null;
    }

    public function makeTableFromSizeArray($sizeArray, $className)
    {
        if (!$sizeArray) {
            return '';
        }

        $table = '<table class="sh-size-table ' . $className . '">';
        $table .= '<tr>';
        foreach ($sizeArray[0] as $name => $sizeColumn) {
            if (!strpos($name, '_')) {
                $table .= '<td>' . $name . '</td>';
            } else if ($name == 'attr_name') {
                $table .= '<td>' . $sizeColumn . '</td>';
            }
        }
        $table .= '</tr>';

        foreach ($sizeArray as $sizeInfoItem) {
            $table .= '<tr>';
            foreach ($sizeInfoItem as $name => $sizeColumn) {
                if (!strpos($name, '_')) {
                    $table .= '<td>' . $sizeColumn . '</td>';
                } else if ($name == 'attr_name') {
                    $table .= '<td>' . $sizeInfoItem['attr_value_name'] . '</td>';

                }
            }
            $table .= '</tr>';
        }
        $table .= '</table>';

        return $table;
    }

    public function getProductReviewData()
    {
        return $this->sendPostToNode(static::NODE_ACTION_REVIEWS);
    }

    public function getVariantDataByContent($content, $fromExtension)
    {
        $site = $this->getSite();
        if ($site->import_by_queue) {
            return json_encode($this->getParsedVariantsFromData($content, $fromExtension));
        }
        if ($site->name == AvailableSite::SITE_SHEIN && Yii::$app->params['sheinScrapNode']) {
            return $this->sendPostToNode(static::NODE_ACTION_VARIANTS, $content);
        }
        $siteHelper = $site->getHelperClass();
        return json_encode($siteHelper->getProductVariants($content, $this->url));
    }


    public function addUrlToImportQueue(string $url, AvailableSite $site, int $user_id, int $import_reviews, int $type = 0, ?int $monitoringQueueId = null): ImportQueue
    {
        return ImportQueue::createQueue($url, $site, $user_id, $import_reviews, $type, $monitoringQueueId);
    }

    protected function getIndexForOption(string $optionName, array $variants): int
    {
        foreach ($variants[0] as $index => $variantItem) {
            if ($variantItem['name'] == $optionName) {
                return $index;
            }
        }
    }

	public function importProductData($user)
	{
		$site = $this->getSite();
		if (!$site) {
			throw new NotFoundHttpException('Invalid product url!');
		}
		$availableSites = $user->plan->getPlanSites()->select(['site_id'])->column();
		if (!in_array($site->id, $availableSites)) {
			throw new ForbiddenHttpException('Site is not available in your plan!');
		}

		$this->setUser($user);
		$this->url = str_replace('-&-', '', $this->url);
		$productData = $this->getProductData();
		$productData = json_decode($productData, true);
		if (!empty($productData['reRequest'])) {
			$productData = $this->getProductData();
			$productData = json_decode($productData, true);
		}
		if (!empty($productData['productId'])) {
			$productData['existingId'] = $user->getProducts()->where(['sku' => $productData['productId']])->select(['id'])->scalar();
		}
		return $productData;
	}
}
