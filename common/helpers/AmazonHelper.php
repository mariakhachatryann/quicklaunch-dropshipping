<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class AmazonHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_AMAZON;

    public function getProduct(string $content, string $url): array
    {
        $aliData = $this->getDataFromContent($url, $content);

        $variants = $this->getVariants($aliData);
        $options = $this->getOptions($aliData);

        $descriptionItems = $this->getDescription($aliData['productdescription'] ?? []);

        $onlyImages = [];
        if (!empty($aliData['variants'])) {
            $onlyImages = ArrayHelper::getColumn($aliData['variants'], 'variantImages');
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

            $mainDataJSONDom = $this->extractor($this->trimCustom($content), "var obj = jQuery.parseJSON('", "');");
            $mainDataJSONDom = stripslashes($mainDataJSONDom);;

            $mainDataJSON = json_decode($mainDataJSONDom, TRUE);

            if (!empty($mainDataJSON)) {

                $Name = "";
                if (isset($mainDataJSON['title'])) {
                    $Name = $mainDataJSON['title'];
                }
                $mainDataArr['url'] = $url;
                $mainDataArr['productName'] = $Name;

                $sku = "";
                if (isset($mainDataJSON['mediaAsin'])) {
                    $sku = $mainDataJSON['mediaAsin'];
                }
                $mainDataArr['productID'] = $sku;

                $price = 0;
                $priceNodes = $this->getPriceNodes($xpath);

                if ($priceNodes) {
                    foreach ($priceNodes as $priceNode) {
                        if ($priceNode->getAttribute('value')) {
                            $price = preg_replace('/[^\\d.]+/', '', $priceNode->getAttribute('value'));
                        } else {
                            if (strpos($price, ',')) {
                                $price = str_replace(',', '.', $price);
                            }
                            $price = preg_replace('/[^\\d.]+/', '', $priceNode->nodeValue);
                        }
                    }
                }

                $mainDataArr['price'] = $price;
                if (isset($mainDataJSON['colorImages']) && !empty($mainDataJSON['colorImages'])) {
                    foreach ($mainDataJSON['colorImages'] as $swatchImagesArr) {
                        $colorImages = ArrayHelper::getColumn($swatchImagesArr, 'large');
                        if (!empty($colorImages)) {
                            foreach ($colorImages as $colorImage) {
                                $mainDataArr['productimages'][] = $colorImage;
                            }
                        }
                    }
                } else {
                    $imagesNodes = $xpath->query('//div[@id="altImages"]//ul//li//img');
                    foreach ($imagesNodes as $imagesNode) {
                        $img = $imagesNode->getAttribute('src');

                        if (strpos($img, '._') && !strpos($img, '.gif')) {
                            $imgParts = explode('._', $img);
                            $img = $imgParts[0] . '.jpg';
                            $mainDataArr['productimages'][] = $img;
                        }

                    }
                }

                $productDescDom = $xpath->query('//div[@id="detailBullets_feature_div"]/ul/li/span[@class="a-list-item"]');

                if ($productDescDom->length > 0) {
                    for ($prod = 0; $prod < $productDescDom->length; $prod++) {
                        $descElem = explode(':', $productDescDom->item($prod)->nodeValue);
                        $mainDataArr['productdescription'][$prod][trim($descElem[0])] = trim($descElem[1]);
                    }
                } else {
                    $productDescDomKeys = $xpath->query('//table[@class="a-keyvalue prodDetTable"]/tr/th');
                    $productDescDomValues = $xpath->query('//table[@class="a-keyvalue prodDetTable"]/tr/td');
                    if ($productDescDomKeys->length && $productDescDomValues->length) {
                        foreach ($productDescDomKeys as $elemKey => $keyValue) {
                            if ($keyValue->nodeValue != 'Customer Reviews') {
                                $mainDataArr['productdescription'][$elemKey][trim($keyValue->nodeValue)] = $productDescDomValues->item($elemKey)->nodeValue;
                            }

                        }
                    }
                }

                $bredCounter = 0;
                $bredDom = $xpath->query('//div[@id="wayfinding-breadcrumbs_feature_div"]/ul/li/span/a');
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

                $optionsValuesJSON = trim($this->extractor($this->trimCustom($content), '"variationValues" : ', '"asinVariationValues" : '));
                $optionsValuesArr = json_decode(trim($optionsValuesJSON, ','), true);

                if(!$optionsValuesArr){
                    $optionsValuesJSON = trim($this->extractor($this->trimCustom($content), '"variationValues" : ', '"selectedVariationValues" : '));
                    $optionsValuesArr = json_decode(trim($optionsValuesJSON, ','), true);
                }

                $optionsNamesJSON = trim($this->extractor($this->trimCustom($content), '"variationDisplayLabels" : ', '"dimensionHierarchyData"'));
                $optionsNamesArr = json_decode(trim($optionsNamesJSON, ','), true);
                if (!$optionsNamesArr) {
                    $optionsNamesJSON = trim($this->extractor($this->trimCustom($content), '"variationDisplayLabels" : ', '};'));
                    $optionsNamesArr = json_decode($optionsNamesJSON, true);
                }
                $mainDataArr['options'] = [];
                if ($optionsNamesArr) {

                    foreach ($optionsNamesArr as $optionNameKey => $optionName) {
                        $mainDataArr['options'][$optionName] = $optionsValuesArr[$optionNameKey];
                    }
                }

                $variantsOptionsJSON = trim($this->extractor($this->trimCustom($content), '"dimensionValuesDisplayData" :', '"pwASINs"'));
                $variantsOptionsArr = json_decode(trim($variantsOptionsJSON, ','), true);
                $relatedCOunter = 0;
                $mainDataArr['variants'] = [];
                $outOfStockDom = $xpath->query('//div[@id="exports_desktop_outOfStock_buybox_message_feature_div"]');
                if (!$outOfStockDom->length) {
                    $outOfStockDom = $xpath->query('//div[@id="outOfStock"]');
                }
                $currencyCodes = array("$", "£", "€", "¥");
                if ($variantsOptionsArr) {

                    foreach ($variantsOptionsArr as $SKUId => $variantOptions) {
                        $variantColorImage = '';
                        $nodeSelectors = ['//div[@id="variation_size_name"]/ul/li[@data-defaultasin="' . trim($SKUId) . '"]', '//div[@id="variation_color_name"]/ul/li[@data-defaultasin="' . trim($SKUId) . '"]'];

                        foreach ($nodeSelectors as $nodeSelector) {
                            $variantNodes = $xpath->query($nodeSelector);
                            $varPrice = 0;
                            if ($variantNodes->length) {
                                foreach ($variantNodes as $variantNode) {
                                    foreach ($currencyCodes as $code) {
                                        $nodeValue = $variantNode->nodeValue;
                                        if (strpos($nodeValue, $code)) {
                                            $varPrice = explode($code, $nodeValue)[1];
                                            if ($varPrice > 0 && $mainDataArr['price'] == 0) {
                                                $mainDataArr['price'] = $varPrice;
                                            }
                                            break;
                                        }
                                    }
                                }
                            } else {
                                $varPrice = $mainDataArr['price'];
                            }
                            if ($varPrice) {
                                break;
                            }
                        }

                        if (isset($mainDataJSON['colorImages']) && $mainDataJSON['colorImages']) {

                            $variantColorName = implode(' ', $variantOptions);
                            if (strpos($variantColorName, '/')) {
                                $variantColorName = str_replace('/', '\/', $variantColorName);
                            }

                            if (isset($mainDataJSON['colorImages'][$variantColorName])) {
                                $variantColorImages = ArrayHelper::getValue($mainDataJSON['colorImages'], $variantColorName);
                                if ($variantColorImages) {
                                    foreach ($variantColorImages as $varColorImage) {
                                        $variantColorImageType = ArrayHelper::getValue($varColorImage, 'variant');
                                        if ($variantColorImageType == 'MAIN') {
                                            $variantColorImage = ArrayHelper::getValue($varColorImage, 'large');
                                            break;
                                        }
                                    }
                                }
                            } else if (isset($optionsNamesArr['color_name'])) {

                                foreach ($variantOptions as $variantOption) {
                                    if (strpos($variantOption, '/') && !isset($mainDataJSON['colorImages'][$variantOption])) {
                                        $variantOption = str_replace('/', '\/', $variantOption);
                                    }
                                    if (isset($mainDataJSON['colorImages'][$variantOption])) {
                                        $variantColorImages = ArrayHelper::getValue($mainDataJSON['colorImages'], $variantOption);
                                        if ($variantColorImages) {
                                            foreach ($variantColorImages as $varColorImage) {
                                                $variantColorImageType = ArrayHelper::getValue($varColorImage, 'variant');
                                                if ($variantColorImageType == 'MAIN') {
                                                    $variantColorImage = ArrayHelper::getValue($varColorImage, 'large');
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $SKUId;
                        $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $SKUId;
                        $mainDataArr['variants'][$relatedCOunter]['typeID'] = $mainDataJSON['mediaAsin'];
                        $mainDataArr['variants'][$relatedCOunter]['variantName'] = implode(',', $variantOptions);
                        $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $variantColorImage;
                        $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = preg_replace('/[^\\d.]+/', '', $varPrice);
                        $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array_combine($optionsNamesArr, $variantOptions);
                        $mainDataArr['variants'][$relatedCOunter]['inventory'] = !$outOfStockDom->length ? 999 : 0;
                        $relatedCOunter++;
                    }
                } else {
                    $mainDataArr['stock'] = !$outOfStockDom->length ? 999 : 0;
                }

                return $mainDataArr;
            }
        } else {
            echo 'URLNotOpen: ' . $url . $lb;
        }
    }

    public function getPriceNodes($xpath)
    {
        $priceSelectorsArr = ['//span[@class="a-price a-text-price a-size-medium apexPriceToPay"]/span[@class="a-offscreen"]', '//span[@id="price"]', '//span[@id="price_inside_buybox"]', '//span[@id="priceblock_ourprice"]', '//input[@id="twister-plus-price-data-price"]', '//span[@class="a-price aok-align-center priceToPay"]/span[@class="a-offscreen"]'];
        foreach ($priceSelectorsArr as $priceSelector) {
            $priceNodes = $xpath->query($priceSelector);
            if ($priceNodes->length) {
                return $priceNodes;
            }
        }
    }



 
}