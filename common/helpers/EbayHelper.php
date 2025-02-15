<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class EbayHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_EBAY;

    public function getProduct(string $content, string $url): array
    {

        $aliData = $this->getDataFromContent($url, $content);

        $variants = $this->getVariants($aliData);
        $options = $this->getOptions($aliData);

        $descriptionItems = $this->getDescription($aliData['productdescription'] ?? []);

        $onlyImages = [];
        if (!empty($aliData['variants'])) {
            $onlyImages = array_filter(ArrayHelper::getColumn($aliData['variants'], 'variantImages'));
        }

        $optionNames = isset($aliData['options']) ? array_keys($aliData['options']) : [];

        $productTypeIndex = count($aliData['breadcrumb']) - 1;

        $preparedData = [
            'title' => $this->removeEmojisFromString($aliData['productName']),
            'price' => $aliData['price'],
            'body_html' => $descriptionItems,
            'brand' => null,
            'vendor' => null,
            'images' => array_values(array_unique(array_merge(
                $aliData['productimages'] ?? [], $onlyImages
            ))),
            'product_type' => $aliData['breadcrumb'][$productTypeIndex]['name'] ?? '',
            'productId' => $aliData['productID'],
            'stockCount' => $aliData['variants'][0]['inventory'] ?? $aliData['stock'],
            'variants' => $variants,
            'options' => $options,
            'onlyImages' => $onlyImages,
            'nameQueue' => $optionNames,
            'onlyOptionName' => self::getOptionNames($onlyImages, $optionNames),
            'weight' => null,
            'weight_unit' => null,
            'productUrl' => $url,
            'variantsStockCount' => $aliData['variants'][0]['inventory'] ?? 0,
            'reRequest' => null,
            'reviews' => [
                'reviews' => []
            ]
        ];

        return $preparedData;
    }


    public function getDataFromContent(string $url, string $content): array
    {

        $lb = (PHP_SAPI == 'cli') ? "\n" : "<br>";
        set_time_limit(0);


        if ($content) {
            $mainDataArr = array();
            $contentDom = new DOMDocument();
            @$contentDom->loadHTML($content);
            $xpath = new DOMXPath($contentDom);

            $Name = "";
            $titleNodes = $this->getTitleNodes($xpath);
            foreach ($titleNodes as $titleNode) {
                $Name = $titleNode->nodeValue;
            }
            $mainDataArr['url'] = $url;
            $mainDataArr['productName'] = trim(str_replace('Details about', '', $Name));

            $sku = "";

            if ($path = parse_url($url)['path']) {
                $sku = str_replace(['/itm/', '/p/'], '', $path);
            }
            $mainDataArr['productID'] = $sku;


            $price = 0;
            $priceNodes = $this->getPriceNodes($xpath);
            if ($priceNodes) {
                foreach ($priceNodes as $priceNode) {
                    if ($priceNode->getAttribute('content')) {
                        $price = $priceNode->getAttribute('content');
                    } else {
                        $price = $priceNode->nodeValue;
                        if (strpos($price, ',')) {
                            $price = str_replace(',', '.', $price);
                        }
                    }


                    $price = preg_replace('/[^\\d.]+/', '', $price);

                }
            }

            $mainDataArr['price'] = $price;

            $imagesNodes = $this->getImagesNodes($xpath);
            if ($imagesNodes->length) {
                foreach ($imagesNodes as $imagesNode) {
                    $src = $imagesNode->getAttribute('src');
                    $src = str_replace(['s-l64', 's-l300', 's-l6400'], 's-l640', $src);

                    $mainDataArr['productimages'][] = $src;
                }
            }

            $productDescKeysNodes = $this->getDescKeyNodes($xpath);
            $productDescValueNodes = $this->getDescValueNodes($xpath);

            if ($productDescValueNodes && $productDescValueNodes->length && $productDescKeysNodes && $productDescKeysNodes->length) {
                for ($prod = 0; $prod < $productDescValueNodes->length; $prod++) {
                    $mainDataArr['productdescription'][$prod][trim($productDescKeysNodes->item($prod)->nodeValue)] = trim($productDescValueNodes->item($prod)->nodeValue);
                }
            }

            $bredCounter = 0;
            $bredDom = $this->getBredNodes($xpath);
            $mainDataArr['breadcrumb'] = [];

            if ($bredDom->length) {
                foreach ($bredDom as $bred) {
                    $catUrl = $bred->getAttribute('href');
                    $catName = $bred->nodeValue;
                    $mainDataArr['breadcrumb'][$bredCounter] = array(
                        "name" => $this->trimCustom($catName),
                        "url" => $catUrl,
                    );

                    $bredCounter++;
                }
            }

            $mainDataArr['options'] = [];
            $mainDataArr['variants'] = [];

            if (strpos($this->trimCustom($content), '"itemVariationsMap":') !== false) {
                $allContent = explode('"itemVariationsMap":', $this->trimCustom($content))[1];

                $pos = -1;
                $positions = [];
                while (($pos = strpos($allContent, '{', $pos + 1)) !== false) {
                    $positions[$pos] = 'open';
                }
                $pos = -1;
                while (($pos = strpos($allContent, '}', $pos + 1)) !== false) {
                    $positions[$pos] = 'close';
                }
                ksort($positions);
                $openCount = 0;
                $closeCount = 0;
                $closeIndex = 0;
                foreach ($positions as $position => $type) {
                    switch ($type) {
                        case 'open':
                            ++$openCount;
                            break;
                        case 'close':
                            ++$closeCount;
                            break;
                    }
                    if ($openCount == $closeCount) {
                        $closeIndex = $position;
                        break;
                    }
                }
                $allContent = substr($allContent, 0, $closeIndex + 1);

                $variantsDataJSONDom = '{' . $this->extractor($this->trimCustom($content), '"menuItemMap":{', '}}') . '}}';
                $optionsNamesDataJSONDom = $this->extractor($this->trimCustom($content), '"menuModels":', ',"menuItemMap":');
                $variantsDataJSON = json_decode($variantsDataJSONDom, TRUE);
                $variantsMapDataJSON = json_decode("[$allContent]", true);
                $optionsNamesDataJSON = json_decode($optionsNamesDataJSONDom, TRUE);

                if ($variantsMapDataJSON && $variantsDataJSON) {
                    $relatedCOunter = 0;
                    foreach ($variantsMapDataJSON[0] as $variantId => $variant) {
                        $variantImage = null;
                        $variantOptionValuesMap = ArrayHelper::getValue($variant, 'traitValuesMap');
                        foreach ($variantOptionValuesMap as $optionName => &$variantOptionValue) {
                            $variantData = $variantsDataJSON[$variantOptionValue];
                            $variantOptionValuesMap[$optionName] = $variantData['displayName'];
                            if (!isset($mainDataArr['options'][$optionName]) || !in_array($variantData['displayName'], $mainDataArr['options'][$optionName])) {
                                $mainDataArr['options'][$optionName][] = $variantData['displayName'];
                            }
                            if (isset($variantData['thumbnailIndex']) && $variantData['thumbnailIndex'] && isset($mainDataArr['productimages'][$variantData['thumbnailIndex']])) {
                                $variantImage = $mainDataArr['productimages'][$variantData['thumbnailIndex']];
                            }
                        }
                        $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $variantId;
                        $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $variantId;
                        $mainDataArr['variants'][$relatedCOunter]['typeID'] = $variantId;
                        $mainDataArr['variants'][$relatedCOunter]['variantName'] = implode(',', $variantOptionValuesMap);
                        $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $variantImage;
                        $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = ArrayHelper::getValue($variant, 'contentPrice');
                        $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = $variantOptionValuesMap;
                        $mainDataArr['variants'][$relatedCOunter]['inventory'] = ArrayHelper::getValue($variant, 'quantity');
                        $relatedCOunter++;
                    }
                }
            } else {
                $mainDataArr['stock'] = 999;
            }

            return $mainDataArr;

        } else {
            echo 'URLNotOpen: ' . $url . $lb;
        }
    }

    public function getPriceNodes($xpath)
    {
        $priceSelectorsArr = ['//span[@id="prcIsum"]', '//span[@itemprop="price"]', '//div[@class="display-price"]', '//span[@id="mm-saleDscPrc"]'];

        foreach ($priceSelectorsArr as $priceSelector) {
            $priceNodes = $xpath->query($priceSelector);
            if ($priceNodes->length) {
                return $priceNodes;
            }
        }
    }

    public function getTitleNodes($xpath)
    {
        $titleSelectorsArr = ['//h1[@id="itemTitle"]', '//h1[@class="product-title"]', '//h1[@class="x-item-title__mainTitle"]//span'];
        foreach ($titleSelectorsArr as $titleSelector) {
            $titleNodes = $xpath->query($titleSelector);
            if ($titleNodes->length) {
                return $titleNodes;
            }
        }
    }

    public function getImagesNodes($xpath)
    {
        $imageSelectorsArr = ['//div[@class="thumbPicturePanel stock-img-exist"]//img', '//div[@id="vi_main_img_fs"]//img', '//td[@class="tdThumb"]/div/img', '//img[@id="icImg"]','//div[@id="PicturePanel"]//img'];
        foreach ($imageSelectorsArr as $imageSelector) {
            $imageNodes = $xpath->query($imageSelector);
            if ($imageNodes->length) {
                return $imageNodes;
            }
        }
    }

    public function getDescKeyNodes($xpath)
    {
        $descSelectorsArr = [
            '//div[@class="ux-layout-section__item ux-layout-section__item--table-view"]/div[@class="ux-layout-section__row"]/div[@class="ux-labels-values__labels"]',
            '//div[@class="s-name"]',
            '//div[@class="ux-layout-section-evo__row"]//div[@class="ux-labels-values__labels-content"]'
        ];
        foreach ($descSelectorsArr as $descSelector) {
            $descKeyNodes = $xpath->query($descSelector);
            if ($descKeyNodes->length) {
                return $descKeyNodes;
            }
        }
        return  [];
    }

    public function getDescValueNodes($xpath)
    {
        $descSelectorsArr = [
            '//div[@class="ux-layout-section__item ux-layout-section__item--table-view"]/div[@class="ux-layout-section__row"]/div[@class="ux-labels-values__values"]',
            '//div[@class="s-value"]',
            '//div[@class="ux-layout-section-evo__row"]//div[@class="ux-labels-values__values-content"]'
        ];
        foreach ($descSelectorsArr as $descSelector) {
            $descValueNodes = $xpath->query($descSelector);
            if ($descValueNodes->length) {
                return $descValueNodes;
            }
        }
        return  [];


    }

    public function getBredNodes($xpath)
    {
        $bredSelectorsArr = [
            '//nav[@class="breadcrumbs"]//li//a',
            '//nav[@class="breadcrumbs breadcrumb--overflow"]//li//a',
            '//li[@id="vi-VR-brumb-lnkLst"]/ul/li/a',
        ];
        foreach ($bredSelectorsArr as $bredSelector) {
            $bredValueNodes = $xpath->query($bredSelector);

            if ($bredValueNodes->length) {
                return $bredValueNodes;
            }
        }
    }
}