<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class BangoodHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_BANGOOD;

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
            'stockCount' => $aliData['variants'][0]['inventory'] ?? 999,
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

            $JSONDom = $xpath->query("//script[@type='application/ld+json']");
            $JSONRAW = "";
            $JSON = "";

            if ($JSONDom->length > 0) {


                $JSONRAW = $this->trimCustom($JSONDom->item(0)->nodeValue);
                $JSONRAW = substr($JSONRAW, 0, -1);

                $JSON = json_decode($JSONRAW, TRUE);


                $mainDataArr = array();
                $mainDataArr['options'] = array();

                $NameDom = $xpath->query("//h1/span");
                $Name = "";
                if ($NameDom->length > 0) {
                    $Name = $this->trimCustom($NameDom->item(0)->nodeValue);


                    $mainDataArr['url'] = $url;
                    $mainDataArr['productName'] = $Name;

                    $productIDDom = $xpath->query("//div[contains(@class,'reviewer-id')]");
                    $productID = "";
                    if ($productIDDom->length > 0) {
                        $productID = $this->trimCustom($productIDDom->item(0)->nodeValue);
                        $productID = preg_replace('/\D/', '', $productID);
                    }
                    $mainDataArr['productID'] = $productID;

                    $price = "";
                    if (isset($JSON['offers'][0]['price'])) {
                        $price = $JSON['offers'][0]['price'];
                    } elseif (isset($JSON['offers']['price'])) {
                        $price = $JSON['offers']['price'];
                    }
                    $mainDataArr['price'] = $price;

                    $imagesDom = $xpath->query("//div[@class='main-viewed-wrap imgList-slide']//ul//li");
                    $imageSingl = "";
                    $images = "";
                    if ($imagesDom->length > 0) {
                        for ($img = 0; $img < $imagesDom->length; $img++) {
                            $imageSingl = $this->trimCustom($imagesDom->item(0)->getAttribute("data-large"));
                            $images = $this->trimCustom($imagesDom->item($img)->getAttribute("data-large"));
                            $mainDataArr['productimages'][$img] = $images;
                        }
                    }

                    $colorsDom = $xpath->query("//div[@class='product-block product-block-img']//a[@class='imgtag']");
                    $colors = "";
                    $colorsID = "";
                    $colorsIDArr = array();
                    $colorsImgArr = array();
                    if ($colorsDom->length > 0) {
                        for ($colr = 0; $colr < $colorsDom->length; $colr++) {

                            $colors = $this->trimCustom($colorsDom->item($colr)->getAttribute("title"));
                            $colorsID = $this->trimCustom($colorsDom->item($colr)->getAttribute("data-value-id"));
                            $colorsImg = $this->trimCustom($colorsDom->item($colr)->getAttribute("data-large"));
                            $colorsIDArr[$colorsID] = $colors;
                            $colorsImgArr[$colorsID] = $colorsImg;
                            $mainDataArr['options']['Color'][$colr] = $colors;
                        }
                    }


                    $sizesDom = $xpath->query("//div[@class='product-block product-block-text']//a");
                    $sizes = "";
                    $valueID = "";
                    $sizesIDArr = array();
                    if ($sizesDom->length > 0) {
                        for ($sz = 0; $sz < $sizesDom->length; $sz++) {
                            $sizes = $this->trimCustom($sizesDom->item($sz)->getAttribute("title"));
                            $valueID = $this->trimCustom($sizesDom->item($sz)->getAttribute("data-value-id"));
                            $sizesIDArr[$valueID] = $sizes;
                            $mainDataArr['options']['Size'][$sz] = $sizes;
                        }
                    }

                    $breadcrumbDom = $xpath->query("//div[@typeof='BreadcrumbList']//ul//li//a");
                    $catUrl = "";
                    $catName = "";
                    $cateId = "";
                    if ($breadcrumbDom->length > 0) {
                        for ($bred = 0; $bred < $breadcrumbDom->length; $bred++) {
                            $catUrl = $this->trimCustom($breadcrumbDom->item($bred)->getAttribute("href"));
                            $cateId = $this->trimCustom($breadcrumbDom->item($bred)->getAttribute("dpid"));
                            $catName = $this->trimCustom($breadcrumbDom->item($bred)->nodeValue);
                            $mainDataArr['breadcrumb'][$bred] = array(
                                "cateId" => $cateId,
                                "name" => $catName,
                                "url" => $catUrl,);
                        }
                    }

                    $productDescDom = $xpath->query("//div[@id='J-specification']//li");
                    $productDescArr = array();
                    $productRAW = "";
                    $productKey = "";
                    $productValue = "";

                    if ($productDescDom->length > 0) {
                        for ($prod = 0; $prod < $productDescDom->length; $prod++) {

                            $productRAW = $this->trimCustom($productDescDom->item($prod)->nodeValue);
                            $productRAWParts = explode(": ", $productRAW);

                            $productKey = $productRAWParts[0];
                            $productValue = $productRAWParts[1];

                            $mainDataArr['productdescription'][$prod][$productKey] = $productValue;
                        }
                    }
                    $mainSKUArr = array();
                    $colorImagesArr = array();
                    if (!empty($sizesIDArr) && (!empty($colorsIDArr))) {
                        foreach ($sizesIDArr as $sizesID => $sizesName) {

                            foreach ($colorsIDArr as $colorsIDNew => $colorsName) {

                                $mainSKU = $productID . "-" . $colorsIDNew . "-" . $sizesID;
                                $mainSKUData = $colorsName . "," . $sizesName;

                                $mainSKUArr[$mainSKU] = $mainSKUData;
                                $colorImagesArr[$mainSKU] = $colorsImgArr[$colorsIDNew];
                            }
                        }
                    } elseif (empty($sizesIDArr) && (!empty($colorsIDArr))) {
                        foreach ($colorsIDArr as $colorsIDNew => $colorsName) {

                            $mainSKU = $productID . "-" . $colorsIDNew;
                            $mainSKUData = $colorsName;
                            $colorImagesArr[$mainSKU] = $colorsImgArr[$colorsIDNew];
                            $mainSKUArr[$mainSKU] = $mainSKUData;
                        }
                    } elseif (!empty($sizesIDArr) && (empty($colorsIDArr))) {

                        foreach ($sizesIDArr as $sizesID => $sizesName) {

                            $mainSKU = $productID . "-" . $sizesID;
                            $mainSKUData = $sizesName;

                            $mainSKUArr[$mainSKU] = $mainSKUData;
                        }
                    } else {

                        $mainSKU = $productID;
                        $mainSKUData = "";

                        $mainSKUArr[$mainSKU] = $mainSKUData;
                    }


                    $variantsSKU = "";


                    $variantsArr = array();

                    if (isset($JSON['offers'])) {
                        foreach ($JSON['offers'] as $offersArr) {
                            if (is_array($offersArr)) {
                                if (isset($offersArr['sku'])) {
                                    $variantsSKU = $offersArr['sku'];
                                }
                                $variantsArr[$variantsSKU] = $offersArr;
                            } else {
                                $variantsSKU = $JSON['offers']['sku'];
                                $variantsArr[$variantsSKU] = $JSON['offers'];
                            }
                        }
                    }

                    $relatedCOunter = 0;

                    foreach ($variantsArr as $mainSKUID => $variantArr) {
                        foreach ($mainSKUArr as $skuId => $mainSKURawData) {
                            $match = false;
                            $explodeMainSKUID = explode('-', $mainSKUID);
                            $explodeSkuId = explode('-', $skuId);
                            $countMatches = 0;
                            foreach ($explodeMainSKUID as $mainSkuIdPart) {
                                $clearSkuPart = preg_replace('/[^\\d.]+/', '', $mainSkuIdPart);
                                foreach ($explodeSkuId as $skuIdPart) {
                                    if ($clearSkuPart == $skuIdPart) {
                                        $countMatches++;
                                    }
                                }
                                if ($countMatches == count($explodeSkuId)) {
                                    $match = true;
                                    break;
                                }
                            }
                            if ($match) {
                                $mainSKURawDataParts = explode(",", $mainSKURawData);

                                $variantColor = "";
                                $variantSizes = "";

                                if (isset($mainSKURawDataParts[0]) && isset($mainDataArr['options']['Color'])) {
                                    $variantColor = $mainSKURawDataParts[0];
                                } else {
                                    $variantSizes = $mainSKURawDataParts[0];

                                }

                                if (isset($mainSKURawDataParts[1])) {
                                    $variantSizes = $mainSKURawDataParts[1];
                                }
                                $skuUrl = $variantArr['url'];
                                $skuPrice = $variantArr['price'];
                                if (isset($colorImagesArr[$skuId])) {
                                    $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $colorImagesArr[$skuId];
                                }
                                $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $mainSKUID;
                                $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $mainSKUID;
                                $mainDataArr['variants'][$relatedCOunter]['typeID'] = $mainSKUID;
                                $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $skuPrice;
                                if (isset($variantArr['availability']) && $variantArr['availability'] == 'http://schema.org/InStock') {
                                    $mainDataArr['variants'][$relatedCOunter]['inventory'] = 999;
                                }


                                if (!empty($variantColor) && (!empty($variantSizes))) {
                                    $mainDataArr['variants'][$relatedCOunter]['variantName'] = $mainSKURawData;
                                    $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                                        "Color" => $variantColor,
                                        "Size" => $variantSizes,
                                    );
                                } elseif (empty($variantColor) && (!empty($variantSizes))) {
                                    $mainDataArr['variants'][$relatedCOunter]['variantName'] = $mainSKURawData;
                                    $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                                        "Size" => $variantSizes,
                                    );
                                } elseif (!empty($variantColor) && (empty($variantSizes))) {
                                    $mainDataArr['variants'][$relatedCOunter]['variantName'] = $mainSKURawData;
                                    $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                                        "Color" => $variantColor,
                                    );
                                }
                                $relatedCOunter++;
                                break;
                            }
                        }
                    }

                    return $mainDataArr;
                }
            }
        } else {
            echo 'URLNotOpen: ' . $url . $lb;
        }
    }
    
}