<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class LazadaHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_LAZADA;

    /**
     * @param string $content
     * @param string $url
     * @return array
     */
    public function getProduct(string $content, string $url): array
    {
        $aliData = $this->getDataFromContent($url, $content);
        $variants = $this->getVariants($aliData);
        $options = $this->getOptions($aliData);

        $descriptionItems = $this->getDescription($aliData['productdescription']);

        $onlyImages = [];
        if (!empty($aliData['variants'])) {
            $onlyImages = ArrayHelper::getColumn($aliData['variants'], 'variantImages');
        }

        $optionNames = isset($aliData['options']) ? array_keys($aliData['options']) : [];

        $productTypeIndex = count($aliData['breadcrumb']) - 1;

        $preparedData = [
            'title' => $this->removeEmojisFromString($aliData['productName']),
            'price' => $aliData['priceHigh'],
            'body_html' => $descriptionItems,
            'brand' => null,
            'vendor' => null,
            'images' => array_values(array_unique(array_merge(
                $aliData['productimages'], $onlyImages
            ))),
            'product_type' => $aliData['breadcrumb'][$productTypeIndex]['name'] ?? '',
            'productId' => $aliData['productID'],
            'stockCount' => $aliData['variants'][0]['inventory'] ?? 0,
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

    /**
     * @param string $url
     * @return array
     */
    public function getDataFromContent(string $url, string $content): array
    {

        $lb = (PHP_SAPI == 'cli') ? "\n" : "<br>";
        set_time_limit(5);

        if ($content) {

            $doc = new DOMDocument();
            @$doc->loadHTML($content);
            $xpathDetailPage = new DOMXPath($doc);

            $mainDataArr = [];

            $JSONDom = $xpathDetailPage->query("//script[@type='application/ld+json']");
            $JSONRAW = "";
            $JSON = "";
            if ($JSONDom->length > 0) {


                $JSONRAW = $this->trimCustom($JSONDom->item(0)->nodeValue);


                $JSON = json_decode($JSONRAW, TRUE);

                $Name = "";
                if (isset($JSON['name'])) {
                    $Name = $JSON['name'];
                }

                $mainDataArr['url'] = $url;
                $mainDataArr['productName'] = $Name;


                $mpn = "";
                if (isset($JSON['mpn'])) {
                    $mpn = $JSON['mpn'];
                }
                $mainDataArr['productID'] = $mpn;


                $storeNameDom = $xpathDetailPage->query("//div[@class='seller-name__detail']/a");
                $storeName = "";
                $storeURL = "";
                if ($storeNameDom->length > 0) {
                    $storeName = $this->trimCustom($storeNameDom->item(0)->nodeValue);
                    $storeURL = "https:" . $this->trimCustom($storeNameDom->item(0)->getAttribute('href'));
                    $storeURL = strtok($storeURL, "?");
                    $mainDataArr['storeName'] = $storeName;
                    $mainDataArr['storeURL'] = $storeURL;
                }


                $category = "";
                if (isset($JSON['category'])) {
                    $category = $JSON['category'];
                }

                $sku = "";
                if (isset($JSON['sku'])) {
                    $sku = $JSON['sku'];
                }

                $brand_name = "";
                if (isset($JSON['brand']['name'])) {
                    $brand_name = $JSON['brand']['name'];
                }

                $brand_url = "";
                if (isset($JSON['brand']['url'])) {
                    $brand_url = $JSON['brand']['url'];
                }

                $rating = "";
                if (isset($JSON['aggregateRating']['ratingValue'])) {
                    $rating = $JSON['aggregateRating']['ratingValue'];
                }
                $mainDataArr['rating'] = $rating;

                $reviewCount = "";
                if (isset($JSON['aggregateRating']['ratingCount'])) {
                    $reviewCount = $JSON['aggregateRating']['ratingCount'];
                }
                $mainDataArr['ratings'] = $reviewCount;

                $lowPrice = "";
                if (isset($JSON['offers'][0]['lowPrice'])) {
                    $lowPrice = $JSON['offers'][0]['lowPrice'];
                } elseif (isset($JSON['offers']['lowPrice'])) {
                    $lowPrice = $JSON['offers']['lowPrice'];
                }
                $mainDataArr['priceLow'] = $lowPrice;

                $highPrice = "";
                if (isset($JSON['offers'][0]['highPrice'])) {
                    $highPrice = $JSON['offers'][0]['highPrice'];
                } elseif (isset($JSON['offers']['highPrice'])) {
                    $highPrice = $JSON['offers']['highPrice'];
                }
                $mainDataArr['priceHigh'] = $highPrice;

                $main_image = "";
                if (isset($JSON['image'])) {
                    $main_image = "https:" . $JSON['image'];
                }

                $description = "";
                if (isset($JSON['description'])) {
                    $description = $JSON['description'];
                    $mainDataArr['productdescription'][][] = $description;
                }


                $imagesDom = $xpathDetailPage->query("//div[@class='next-slick-track']//div[@lazada_pdp_gallery_tpp_track='gallery']//img");
                $imagesRaw = "";
                $images = "";
                if ($imagesDom->length > 0) {
                    for ($img = 0; $img < $imagesDom->length; $img++) {

                        $imagesRaw = $this->trimCustom($imagesDom->item($img)->getAttribute("src"));

                        if (strstr($imagesRaw, "http")) {
                            $images = str_replace("_120x120q80.jpg_", "_720x720q80.jpg_", $imagesRaw);
                        } else {
                            $imagesRaw = "https:" . $imagesRaw;
                            $images = str_replace("_120x120q80.jpg_", "_720x720q80.jpg_", $imagesRaw);
                        }

                        $mainDataArr['productimages'][$img] = $images;
                    }
                }


                $sizesIDArr = array();
                $colorsIDArr = array();
                $variationsRawArr = array();
                $mainDataArr['options'] = [];
                $completeJSONDom = $this->extractor($this->trimCustom($content), "var __moduleData__ = ", "}};");


                $completeJSON = json_decode($completeJSONDom . "}}", TRUE);

                if (isset($completeJSON['data']['root']['fields']['productOption'])) {
                    $variationsMainJSON = $completeJSON['data']['root']['fields']['productOption'];

                    $variationsSkuBaseJSON = $variationsMainJSON['skuBase']['properties'];


                    $colors = "";
                    $colorsID = "";
                    $colrCounter = 0;

                    $sizes = "";
                    $sizeID = "";
                    $szCounter = 0;
                    foreach ($variationsSkuBaseJSON as $variationsSkuBaseArr) {
                        $variationsName = $variationsSkuBaseArr['name'];

                        if ((stristr($variationsName, "Color")) || (stristr($variationsName, "Colour"))) {

                            $colorsPID = $variationsSkuBaseArr['pid'];

                            if (isset($variationsSkuBaseArr['values'])) {
                                foreach ($variationsSkuBaseArr['values'] as $colorsVarArr) {
                                    $colors = $colorsVarArr['name'];
                                    $colorsID = $colorsVarArr['vid'];


                                    $colorsIDArr[$colorsPID][$colorsID] = $colors;
                                    $mainDataArr['options']['Color'][$colrCounter] = $colors;
                                    $colrCounter++;
                                }
                            }
                        }

                        if (stristr($variationsName, "Size")) {
                            $sizePID = $variationsSkuBaseArr['pid'];

                            if (isset($variationsSkuBaseArr['values'][0]['value'])) {
                                foreach ($variationsSkuBaseArr['values'][0]['value'] as $sizesVarArr) {
                                    $sizes = $sizesVarArr['name'];
                                    $sizeID = $sizesVarArr['vid'];


                                    $sizesIDArr[$sizePID][$sizeID] = $sizes;
                                    $mainDataArr['options']['Size'][$szCounter] = $sizes;
                                    $szCounter++;
                                }
                            }
                        }
                    }


                    $breadcrumbDom = $xpathDetailPage->query("//script[@type='application/ld+json'][2]");
                    $breadcrumbJSON = "";

                    if ($breadcrumbDom->length > 0) {
                        $breadcrumbJSON = $this->trimCustom($breadcrumbDom->item(0)->nodeValue);
                        $breadcrumbJSON = json_decode($breadcrumbJSON, TRUE);
                        $bredCounter = 0;
                        $breadcrumbCount = 0;
                        $breadcrumbCount = count($breadcrumbJSON['itemListElement']);
                        $breadcrumbCount = $breadcrumbCount - 1;


                        foreach ($breadcrumbJSON['itemListElement'] as $itemListElementArr) {
                            if ($bredCounter == $breadcrumbCount) {
                                continue;
                            }
                            $catUrl = $itemListElementArr['item'];
                            $catName = $itemListElementArr['name'];

                            $mainDataArr['breadcrumb'][$bredCounter] = array(
                                "name" => $catName,
                                "url" => $catUrl,);

                            $bredCounter++;
                        }
                    }


                    if (!empty($colorsIDArr) && (!empty($sizesIDArr))) {

                        $varCounter = 1;
                        foreach ($colorsIDArr as $colorsMainPID => $colorsMainArr) {

                            foreach ($colorsMainArr as $colorsRawID => $colorsRawName) {

                                foreach ($sizesIDArr as $sizesMainPID => $sizesMainArr) {

                                    foreach ($sizesMainArr as $sizeRawID => $sizeRawName) {

                                        $checkPropPath = "";
                                        $checkPropPath1 = "";
                                        if (isset($variationsMainJSON['skuBase']['skus'])) {
                                            $variationsSkusAllJSON = $variationsMainJSON['skuBase']['skus'];

                                            $checkPropPath = $colorsMainPID . ":" . $colorsRawID . ";" . $sizesMainPID . ":" . $sizeRawID;
                                            $checkPropPath1 = $sizesMainPID . ":" . $sizeRawID . ";" . $colorsMainPID . ":" . $colorsRawID;
                                            foreach ($variationsSkusAllJSON as $variationsSkusJSON) {

                                                $skuIdVar = $variationsSkusJSON['skuId'];

                                                $propPath = $variationsSkusJSON['propPath'];


                                                if ($checkPropPath == $propPath) {

                                                    $variationsRawArr[$varCounter]['varColor'] = $colorsRawName;
                                                    $variationsRawArr[$varCounter]['varColorID'] = $colorsRawID;
                                                    $variationsRawArr[$varCounter]['varSize'] = $sizeRawName;
                                                    $variationsRawArr[$varCounter]['varSizeID'] = $sizeRawID;
                                                    $variationsRawArr[$varCounter]['varSkuId'] = $skuIdVar;
                                                    $varCounter++;
                                                } elseif ($checkPropPath1 == $propPath) {


                                                    $variationsRawArr[$varCounter]['varColor'] = $colorsRawName;
                                                    $variationsRawArr[$varCounter]['varColorID'] = $colorsRawID;
                                                    $variationsRawArr[$varCounter]['varSize'] = $sizeRawName;
                                                    $variationsRawArr[$varCounter]['varSizeID'] = $sizeRawID;
                                                    $variationsRawArr[$varCounter]['varSkuId'] = $skuIdVar;
                                                    $varCounter++;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $relatedCOunter = 0;
                        foreach ($variationsRawArr as $variationsStoreArr) {

                            $varColor = $variationsStoreArr['varColor'];
                            $varColorID = $variationsStoreArr['varColorID'];
                            $varSize = $variationsStoreArr['varSize'];
                            $varSizeID = $variationsStoreArr['varSizeID'];
                            $varSkuId = $variationsStoreArr['varSkuId'];

                            if (isset($variationsMainJSON['skuBase']['skus'])) {
                                $variationsSkusAllInfoJSON = $completeJSON['data']['root']['fields']['skuInfos'];

                                if (key_exists($varSkuId, $variationsSkusAllInfoJSON)) {

                                    $variantsData = $variationsSkusAllInfoJSON[$varSkuId];

                                    $prdt_image = $variantsData['image'];
                                    if (!strstr($prdt_image, "http")) {
                                        $prdt_image = "https:" . $prdt_image;
                                    }
                                    $prdt_price = $variantsData['price']['salePrice']['value'];
                                    $prdt_stock = $variantsData['stock'];


                                    $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $varSkuId;
                                    $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $varSkuId;
                                    $mainDataArr['variants'][$relatedCOunter]['typeID'] = $varColorID . "," . $varSizeID;
                                    $mainDataArr['variants'][$relatedCOunter]['variantName'] = $varColor . "," . $varSize;
                                    $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $prdt_image;
                                    $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $prdt_price;

                                    $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                                        "Color" => $varColor,
                                        "Size" => $varSize,
                                    );
                                    $mainDataArr['variants'][$relatedCOunter]['inventory'] = $prdt_stock;

                                    $relatedCOunter++;
                                }
                            }
                        }
                    }

                    if (!empty($colorsIDArr) && (empty($sizesIDArr))) {

                        $varCounter = 1;
                        foreach ($colorsIDArr as $colorsMainPID => $colorsMainArr) {

                            foreach ($colorsMainArr as $colorsRawID => $colorsRawName) {


                                $checkPropPath = "";
                                $checkPropPath1 = "";
                                if (isset($variationsMainJSON['skuBase']['skus'])) {
                                    $variationsSkusAllJSON = $variationsMainJSON['skuBase']['skus'];

                                    $checkPropPath = $colorsMainPID . ":" . $colorsRawID;
                                    foreach ($variationsSkusAllJSON as $variationsSkusJSON) {

                                        $skuIdVar = $variationsSkusJSON['skuId'];

                                        $propPath = $variationsSkusJSON['propPath'];


                                        if ($checkPropPath == $propPath) {

                                            $variationsRawArr[$varCounter]['varColor'] = $colorsRawName;
                                            $variationsRawArr[$varCounter]['varColorID'] = $colorsRawID;
                                            $variationsRawArr[$varCounter]['varSkuId'] = $skuIdVar;
                                            $varCounter++;
                                        }
                                    }
                                }
                            }
                        }


                        $relatedCOunter = 0;
                        foreach ($variationsRawArr as $variationsStoreArr) {

                            $varColor = $variationsStoreArr['varColor'];
                            $varColorID = $variationsStoreArr['varColorID'];
                            $varSkuId = $variationsStoreArr['varSkuId'];

                            if (isset($variationsMainJSON['skuBase']['skus'])) {
                                $variationsSkusAllInfoJSON = $completeJSON['data']['root']['fields']['skuInfos'];

                                if (key_exists($varSkuId, $variationsSkusAllInfoJSON)) {

                                    $variantsData = $variationsSkusAllInfoJSON[$varSkuId];

                                    $prdt_image = $variantsData['image'];
                                    if (!strstr($prdt_image, "http")) {
                                        $prdt_image = "https:" . $prdt_image;
                                    }
                                    $prdt_price = $variantsData['price']['salePrice']['value'];
                                    $prdt_stock = $variantsData['stock'];


                                    $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $varSkuId;
                                    $mainDataArr['variants'][$relatedCOunter]['typeID'] = $varColorID;
                                    $mainDataArr['variants'][$relatedCOunter]['variantName'] = $varColor;
                                    $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $prdt_image;
                                    $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $prdt_price;

                                    $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                                        "Color" => $varColor,
                                    );
                                    $mainDataArr['variants'][$relatedCOunter]['inventory'] = $prdt_stock;

                                    $relatedCOunter++;
                                }
                            }
                        }
                    }

                    if (empty($colorsIDArr) && (empty($sizesIDArr))) {

                        $varCounter = 1;
                        if (isset($variationsMainJSON['skuBase']['skus'])) {
                            $variationsSkusAllJSON = $variationsMainJSON['skuBase']['skus'];

                            foreach ($variationsSkusAllJSON as $variationsSkusJSON) {

                                $skuIdVar = $variationsSkusJSON['skuId'];

                                $variationsRawArr[$varCounter]['varSkuId'] = $skuIdVar;
                                $varCounter++;
                            }
                        }

                        $relatedCOunter = 0;
                        foreach ($variationsRawArr as $variationsStoreArr) {
                            $varSkuId = $variationsStoreArr['varSkuId'];

                            if (isset($variationsMainJSON['skuBase']['skus'])) {
                                $variationsSkusAllInfoJSON = $completeJSON['data']['root']['fields']['skuInfos'];

                                if (key_exists($varSkuId, $variationsSkusAllInfoJSON)) {

                                    $variantsData = $variationsSkusAllInfoJSON[$varSkuId];

                                    $prdt_image = $variantsData['image'];
                                    if (!strstr($prdt_image, "http")) {
                                        $prdt_image = "https:" . $prdt_image;
                                    }
                                    $prdt_price = $variantsData['price']['salePrice']['value'];
                                    $prdt_stock = $variantsData['stock'];


                                    $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $varSkuId;
                                    $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $prdt_image;
                                    $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $prdt_price;

                                    $mainDataArr['variants'][$relatedCOunter]['inventory'] = $prdt_stock;

                                    $relatedCOunter++;
                                }
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

