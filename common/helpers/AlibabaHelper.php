<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class AlibabaHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_ALIBABA;

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

            $mainDataJSONDom = $this->extractor($this->trimCustom($content), "window.detailData = ", '</script>');
            $mainDataJSON = json_decode($mainDataJSONDom, TRUE);
            if (!$mainDataJSON) {
                $mainDataJSONDom = $this->extractor($this->trimCustom($content), "window.detailData = ", 'window.detailData.sc');
                $mainDataJSON = json_decode($mainDataJSONDom, TRUE);
            }

            $productDataJson = ArrayHelper::getValue($mainDataJSON, 'globalData.product');
            $bredDataJson = ArrayHelper::getValue($mainDataJSON, 'globalData.seo.breadCrumb.pathList');

            if (!empty($mainDataJSON) && !empty($productDataJson)) {

                $Name = "";
                if (isset($productDataJson['subject'])) {
                    $Name = $productDataJson['subject'];
                }
                $mainDataArr['url'] = $url;
                $mainDataArr['productName'] = $Name;

                $sku = "";
                if (isset($productDataJson['productId'])) {
                    $sku = $productDataJson['productId'];
                }
                $mainDataArr['productID'] = $sku;

                $price = 0;
                $productPrices = ArrayHelper::getValue($productDataJson, 'price.productLadderPrices', []);

                if (!empty($productPrices)) {
                    foreach ($productPrices as $productPrice) {
                        if ($productPrice['price'] > $price) {
                            $price = $productPrice['price'];
                        }
                    }
                } else {
                    $price = ArrayHelper::getValue($productDataJson, 'price.productRangePrices.priceRangeHigh', 0);
                }
                $mainDataArr['price'] = $price;
                if (isset($productDataJson['mediaItems'])) {
                    foreach ($productDataJson['mediaItems'] as $productMedia) {
                        if (isset($productMedia['type']) && $productMedia['type'] == 'image') {
                            $mainDataArr['productimages'][] = $productMedia['imageUrl']['big'];
                        }
                    }
                }

                if ($productDataJson['productBasicProperties']) {
                    foreach ($productDataJson['productBasicProperties'] as $elemKey => $descElem) {
                        $mainDataArr['productdescription'][$elemKey][trim($descElem['attrName'])] = trim($descElem['attrValue']);
                    }
                }

                $bredCounter = 0;

                $mainDataArr['breadcrumb'] = [];
                if (!empty($bredDataJson)) {
                    foreach ($bredDataJson as $bred) {
                        $catUrl = ArrayHelper::getValue($bred, 'hrefObject.url');
                        $catName = ArrayHelper::getValue($bred, 'hrefObject.name');
                        $mainDataArr['breadcrumb'][$bredCounter] = array(
                            "name" => $this->trimCustom($catName),
                            "url" => $catUrl,
                        );

                        $bredCounter++;
                    }
                }
                $mainDataArr['options'] = [];
                if (isset($productDataJson['sku']) && isset($productDataJson['sku']['skuAttrs']) && !empty($productDataJson['sku']['skuAttrs'])) {
                    foreach ($productDataJson['sku']['skuAttrs'] as $skuAttr) {
                        if ($skuAttr['values']) {
                            $mainDataArr['options'][$skuAttr['name']] = ArrayHelper::getColumn($skuAttr['values'], 'name');
                        }
                    }
                }
                $relatedCOunter = 0;
                if (isset($productDataJson['sku']['skuInfoMap']) && $productDataJson['sku']['skuInfoMap']) {
                    foreach ($productDataJson['sku']['skuInfoMap'] as $strOptionsValuesIds => $varSku) {
                        $varImage = null;
                        if (isset($productDataJson['sku']) && isset($productDataJson['sku']['skuAttrs']) && !empty($productDataJson['sku']['skuAttrs'])) {
                            $optionsValuesIdsIdsArr = explode(';', $strOptionsValuesIds);
                            $variantOptions = [];
                            foreach ($optionsValuesIdsIdsArr as $optionValueId) {
                                if ($optionValueId) {
                                    $optionsIds = explode(':', $optionValueId);
                                    $optionValueId = $optionsIds[1];
                                    $optionId = $optionsIds[0];
                                    foreach ($productDataJson['sku']['skuAttrs'] as $skuAttr) {

                                        if ($skuAttr['values'] && $optionId == $skuAttr['id']) {
                                            foreach ($skuAttr['values'] as $skuAttrValue) {
                                                if ($skuAttrValue['id'] == $optionValueId) {
                                                    if (isset($skuAttrValue['originImage'])) {
                                                        $varImage = $skuAttrValue['originImage'];
                                                    }
                                                    $variantOptions[$skuAttr['name']] = $skuAttrValue['name'];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        $varQty = ArrayHelper::getValue($mainDataJSON['globalData'], 'inventory.skuInventory.' . $varSku['id'] . '.warehouseInventoryList.0.inventoryCount', 0);
                        $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $varSku['id'];
                        $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $varSku['id'];
                        $mainDataArr['variants'][$relatedCOunter]['typeID'] = $productDataJson['productId'];
                        $mainDataArr['variants'][$relatedCOunter]['variantName'] = implode(',', $variantOptions);
                        $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $varImage;
                        $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = preg_replace('/[^\\d.]+/', '', $mainDataArr['price']);
                        $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = $variantOptions;
                        $mainDataArr['variants'][$relatedCOunter]['inventory'] = $varQty < 0 ? 999 : $varQty;
                        $relatedCOunter++;
                    }
                } else {
                    $mainDataArr['stock'] = 999;
                }

                return $mainDataArr;
            }
        } else {
            echo 'URLNotOpen: ' . $url . $lb;
        }
    }
    
}