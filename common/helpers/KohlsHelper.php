<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class KohlsHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_KOHLS;

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
            'stockCount' => $aliData['stock'] ?? $aliData['variants'][0]['inventory'],
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

            $mainDataJSONDom = $this->extractor($this->trimCustom($content), "var productV2JsonData = ", "}};");
            $mainDataJSON = json_decode($mainDataJSONDom . "}}", TRUE);
            if (!$mainDataJSON) {
                $mainDataJSONDom = $this->extractor($this->trimCustom($content), "var productV2JsonData = ", "};");
                $mainDataJSON = json_decode($mainDataJSONDom . "}", TRUE);
            }

            if (!empty($mainDataJSON)) {

                $Name = "";
                if (isset($mainDataJSON['productTitle'])) {
                    $Name = $mainDataJSON['productTitle'];
                }
                $mainDataArr['url'] = $url;
                $mainDataArr['productName'] = $Name;

                $sku = "";
                if (isset($mainDataJSON['webID'])) {
                    $sku = $mainDataJSON['webID'];
                }
                $mainDataArr['productID'] = $sku;

                $price = 0;
                if (isset($mainDataJSON['price']['salePrice']['minPrice'])) {
                    $price = $mainDataJSON['price']['salePrice']['minPrice'];
                } else {
                    if (is_array($mainDataJSON['price']['lowestApplicablePrice'])) {
                        $price = $mainDataJSON['price']['lowestApplicablePrice']['minPrice'];
                    } else {
                        $price = $mainDataJSON['price']['lowestApplicablePrice'];
                    }
                }
                $mainDataArr['price'] = $price;

                $rating = "";
                if (isset($mainDataJSON['avgRating'])) {
                    $rating = $mainDataJSON['avgRating'];
                }
                $mainDataArr['rating'] = $rating;

                $reviewCount = "";
                if (isset($mainDataJSON['ratingCount'])) {
                    $reviewCount = $mainDataJSON['ratingCount'];
                }
                $mainDataArr['ratings'] = $reviewCount;

                $swatchImages = "";
                $swatchImagesRaw = "";
                $imgCounter = 0;
                if (isset($mainDataJSON['swatchImages'])) {
                    foreach ($mainDataJSON['swatchImages'] as $swatchImagesArr) {

                        if (isset($swatchImagesArr['URL'])) {
                            $swatchImagesRaw = $swatchImagesArr['URL'];
                            $swatchImagesRaw = strtok($swatchImagesRaw, "?");
                            $swatchImages = str_replace("_sw", "", $swatchImagesRaw);
                            $swatchImages = $swatchImages . "?wid=1200&hei=1200&op_sharpen=1";

                            $mainDataArr['productimages'][$imgCounter] = $swatchImages;
                            $imgCounter++;
                        }
                    }
                }

                $altImages = "";
                $altImagesRaw = "";

                if (isset($mainDataJSON['altImages'])) {
                    foreach ($mainDataJSON['altImages'] as $altImagesArr) {

                        if (isset($altImagesArr['url'])) {
                            $altImagesRaw = $altImagesArr['url'];

                            $altImagesRaw = strtok($altImagesRaw, "?");
                            $altImages = $altImagesRaw . "?wid=1200&hei=1200&op_sharpen=1";

                            $mainDataArr['productimages'][$imgCounter] = $altImages;
                            $imgCounter++;
                        }
                    }
                } elseif (isset($mainDataJSON['images'])) {
                    foreach ($mainDataJSON['images'] as $image) {

                        if (isset($image['url'])) {
                            $imageRaw = $image['url'];

                            $imageRaw = strtok($imageRaw, "?");
                            $altImage = $imageRaw . "?wid=1200&hei=1200&op_sharpen=1";

                            $mainDataArr['productimages'][$imgCounter] = $altImage;
                            $imgCounter++;
                        }
                    }
                }
                $productDescHtml = "";
                if (isset($mainDataJSON['description']['longDescription'])) {
                    $productDescHtml = $mainDataJSON['description']['longDescription'];

                    $docDesc = new DOMDocument();
                    @$docDesc->loadHTML($productDescHtml);
                    $xpathDesc = new DOMXPath($docDesc);

                    $productDescDom = $xpathDesc->query("//ul");
                    $productDesc = "";
                    if ($productDescDom->length > 0) {
                        for ($prod = 0; $prod < $productDescDom->length; $prod++) {

                            $htmlProdCounter = $prod + 1;

                            $productKeyDom = $xpathDesc->query("//ul[" . $htmlProdCounter . "]/preceding::p");
                            if ($productKeyDom->length > 0) {
                                for ($ky = 0; $ky < $productKeyDom->length; $ky++) {
                                    $productKey = $this->trimCustom($productKeyDom->item($ky)->nodeValue);
                                }
                            }

                            $productValueDom = $xpathDesc->query("//ul[" . $htmlProdCounter . "]//li");
                            $productValue = "";
                            if ($productValueDom->length > 0) {
                                for ($val = 0; $val < $productValueDom->length; $val++) {
                                    $productValueRaw = $this->trimCustom($productValueDom->item($val)->nodeValue);
                                    $productValue .= $productValueRaw . ", ";
                                }
                                $productValue = substr($productValue, 0, -2);
                            }
                            if (isset($productKey) && isset($productValue)) {
                                $mainDataArr['productdescription'][$prod][$productKey] = $productValue;
                            }
                        }
                    }

                    $mainDataArr['productdescriptiontext'] = $productDescHtml;
                }

                $bredCounter = 0;
                if (isset($mainDataJSON['breadcrumbs'])) {
                    foreach ($mainDataJSON['breadcrumbs'] as $breadcrumbsArr) {
                        $catUrl = $breadcrumbsArr['seoURL'];
                        $catName = $breadcrumbsArr['name'];

                        $mainDataArr['breadcrumb'][$bredCounter] = array(
                            "name" => $catName,
                            "url" => $catUrl,);

                        $bredCounter++;
                    }
                }


                $relatedCOunter = 0;
                $prdt_stock = 0;
                $variantsTitles = [];

                if (isset($mainDataJSON['SKUS'])) {
                    foreach ($mainDataJSON['SKUS'] as $variationsDataArr) {
                        $variantOptions = [];
                        $SKUId = "";
                        if (isset($variationsDataArr['skuCode'])) {
                            $SKUId = $variationsDataArr['skuCode'];
                        }

                        $typeID = $sku;
                        $mainDataArr['options'] = [];
                        if (isset($variationsDataArr['color'])) {
                            $color = $variationsDataArr['color'];
                            if (!isset($mainDataArr['options']['Color']) || !in_array($color, $mainDataArr['options']['Color'])) {
                                $mainDataArr['options']['Color'][] = $color;
                            }
                            $variantOptions[] = $variationsDataArr['color'];
                        }

                        if (isset($variationsDataArr['size'])) {
                            $size = $variationsDataArr['size'];
                            if (!isset($mainDataArr['options']['size']) || !in_array($size, $mainDataArr['options']['size'])) {
                                $mainDataArr['options']['size'][] = $size;
                            }
                            $variantOptions[] = $size;
                        }

                        $prdt_image = "";
                        $prdt_imageRaw = "";
                        if (isset($variationsDataArr['images'][0]['url'])) {
                            $prdt_imageRaw = $variationsDataArr['images'][0]['url'];
                            $prdt_imageRaw = strtok($prdt_imageRaw, "?");
                            $prdt_image = str_replace("_sw", "", $prdt_imageRaw);
                            $prdt_image = $prdt_image . "?wid=1200&hei=1200&op_sharpen=1";
                        }


                        $prdt_price = 0;
                        if (isset($variationsDataArr['price']['salePrice']['minPrice'])) {
                            $prdt_price = $variationsDataArr['price']['salePrice']['minPrice'];
                        } else {
                            if (is_array($variationsDataArr['price']['lowestApplicablePrice'])) {
                                $prdt_price = $variationsDataArr['price']['lowestApplicablePrice']['minPrice'];
                            } else {
                                $prdt_price = $variationsDataArr['price']['lowestApplicablePrice'];
                            }
                        }

                        if (isset($variationsDataArr['itemMaxAvailableCount'])) {
                            $prdt_stock += $variationsDataArr['itemMaxAvailableCount'];
                        }

                        $variantTitle = trim(implode(',', $variantOptions), ',');
                        if (!in_array($variantTitle, $variantsTitles) && isset($mainDataArr['options']) && !empty($mainDataArr['options'])) {
                            $variantsTitles[] = $variantTitle;
                            $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $SKUId;
                            $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $SKUId;
                            $mainDataArr['variants'][$relatedCOunter]['typeID'] = $typeID;
                            $mainDataArr['variants'][$relatedCOunter]['variantName'] = $variantTitle;
                            $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $prdt_image;
                            $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $prdt_price;
                            $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array_combine(array_keys($mainDataArr['options']), $variantOptions);
                            $mainDataArr['variants'][$relatedCOunter]['inventory'] = $variationsDataArr['itemMaxAvailableCount'];
                            $relatedCOunter++;
                        }

                    }
                }
                $mainDataArr['stock'] = $prdt_stock;
                if (empty($mainDataArr['productdescription'])) {
                    $mainDataArr['productdescription'] = [
                        [
                            'description' => strip_tags($mainDataArr['productdescriptiontext'])
                        ]
                    ];
                }
                return $mainDataArr;
            }
        } else {
            echo 'URLNotOpen: ' . $url . $lb;
        }
    }

}