<?php

namespace common\helpers;

use common\models\AvailableSite;
use common\models\ProductUrl;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class PrettyLittleThing extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_PRETTY_LITTLE_THING;


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

            $mainDataJSON = $this->getInfoObject($content);

            if (!empty($mainDataJSON)) {
                $mainDataArr['url'] = $url;

                $bredCounter = 0;
                $bredDom = $xpath->query('//ul[@class="breadcrumb"]//li//a');
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

                $productDescDom = $xpath->query('//div[@class="target-description"]');

                if ($productDescDom->length > 0) {
                    $descChildNodes = $productDescDom->item(0)->childNodes;
                    foreach ($descChildNodes as $descKey => $descChildNode) {
                        $descPropValue = trim($descChildNode->nodeValue);
                        if ($descPropValue) {
                            $mainDataArr['productdescription'][] = [$descKey => $descPropValue];
                        }
                    }
                }
                $mainDataArr['productimages'] = [];
                $mainDataArr['options'] = [];
                $relatedCOunter = 0;
                $repeatOptVals = [];
                foreach ($mainDataJSON as $key => $mainDataProps) {
                    if (is_array($mainDataProps)) {

                        $productImages = ArrayHelper::getValue($mainDataProps, 'gallery.main', []);
                        $mainDataArr['productimages'] = array_merge($mainDataArr['productimages'], $productImages);
                        $color = $mainDataProps['colour'];
                        if (!isset($repeatOptVals[$color])) {
                            $mainDataArr['options']['Color'][] = $color;
                            $repeatOptVals[$color] = 1;
                        } else {
                            $repeatOptVals[$color]++;
                            $color = $mainDataProps['colour'] . '-' . $repeatOptVals[$mainDataProps['colour']];
                            $mainDataArr['options']['Color'][] = $color;
                        }
                        if (isset($mainDataProps['subProducts'])) {

                            $mainDataArr['productName'] = ArrayHelper::getValue($mainDataProps, 'name');

                            $mainDataArr['productID'] = ArrayHelper::getValue($mainDataProps, 'product_id');

                            $price = ArrayHelper::getValue($mainDataProps, 'price', 0);
                            $price = preg_replace('/[^\\d.]+/', '', $price);
                            $mainDataArr['price'] = $price;

                            foreach ($mainDataProps['subProducts'] as $subProduct) {

                                if (isset($subProduct['size'])) {

                                    if (!isset($mainDataArr['options']['Size']) || !in_array($subProduct['size'], $mainDataArr['options']['Size'])) {
                                        $mainDataArr['options']['Size'][] = $subProduct['size'];
                                    }

                                    $skuId = stripcslashes($subProduct['sku']);
                                    $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $skuId;
                                    $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $skuId;
                                    $mainDataArr['variants'][$relatedCOunter]['typeID'] = $skuId;
                                    $mainDataArr['variants'][$relatedCOunter]['variantName'] = $color . ',' . $subProduct['size'];
                                    $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $productImages[0];
                                    $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $price;
                                    $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = ['Color' => $color, 'Size' => $subProduct['size']];
                                    $mainDataArr['variants'][$relatedCOunter]['inventory'] = intval($subProduct['stock']);
                                    $relatedCOunter++;
                                }
                            }
                        } else {
                            $itemId = ArrayHelper::getValue($mainDataProps, 'product_id');
                            $subProductDetailsJson = $this->getContent($itemId, $url);

                            $price = ArrayHelper::getValue($mainDataProps, 'price', 0);
                            $price = preg_replace('/[^\\d.]+/', '', $price);

                            if ($subProductDetailsJson && $productImages) {

                                $subProductDetails = json_decode($subProductDetailsJson, true);
                                foreach ($subProductDetails['subProducts'] as $subProduct) {

                                    if (isset($subProduct['size'])) {

                                        if (!isset($mainDataArr['options']['Size']) || !in_array($subProduct['size'], $mainDataArr['options']['Size'])) {
                                            $mainDataArr['options']['Size'][] = $subProduct['size'];
                                        }

                                        $skuId = stripcslashes($subProduct['sku']);
                                        $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $skuId;
                                        $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $skuId;
                                        $mainDataArr['variants'][$relatedCOunter]['typeID'] = $skuId;
                                        $mainDataArr['variants'][$relatedCOunter]['variantName'] = $color . ',' . $subProduct['size'];
                                        $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $productImages[0];
                                        $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $price;
                                        $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = ['Color' => $color, 'Size' => $subProduct['size']];
                                        $mainDataArr['variants'][$relatedCOunter]['inventory'] = intval($subProduct['stock']);;
                                        $relatedCOunter++;
                                    }
                                }
                            }
                        }

                    }

                }
                if ($bredDom->length) {
                    foreach ($bredDom as $bred) {
                        $catUrl = $bred->getAttribute('href');
                        $catName = $bred->nodeValue;
                        if (trim($catName) != trim($mainDataArr['productName'])) {
                            $mainDataArr['breadcrumb'][$bredCounter] = array(
                                "name" => $this->trimCustom($catName),
                                "url" => $catUrl,
                            );
                        }
                        $bredCounter++;
                    }
                }

                return $mainDataArr;
            }
        } else {
            echo 'URLNotOpen: ' . $url . $lb;
        }
    }

    public function getInfoObject($content)
    {
        $mainDataJSONDom = $this->extractor($this->trimCustom($content), 'var pltProductData = JSON.parse("', '");');
        $mainDataJSONDom = stripslashes($mainDataJSONDom);;
        return json_decode($mainDataJSONDom, true);
    }

    public function getSkuList($infoObject, $url)
    {
        $sizeList = ArrayHelper::getValue($infoObject, 'attrSizeList.sale_attr_list');

        return array_values($sizeList);
    }

    public function getImageList($infoObject, $main = false)
    {
        $imageList = [];
        if (isset($infoObject['goods_imgs'])) {

            if (isset($infoObject['goods_imgs']['main_image']['origin_image'])) {
                $imageList[] = $infoObject['goods_imgs']['main_image']['origin_image'];
            }

            foreach ($infoObject['goods_imgs']['detail_image'] as $image) {
                $imageList[] = ArrayHelper::getValue($image, 'origin_image');
                if (count($imageList) == 2 && !$main) {
                    break;
                }
            }
        }

        return array_values($imageList);
    }


    public function getContent($goodId, $url)
    {

        $urldData = parse_url($url);
        $variantUrl = 'https://' . $urldData['host'] . '/pltcatalog/product/getsizes/id/' . $goodId;
        $model = new ProductUrl(['url' => $variantUrl]);
        if ($model->validate()) {
            sleep(rand(1, 3));
            return $model->getPageContent();
        }

        return null;

    }

    public function getTitle($xpath, $infoObject)
    {
        if (isset($infoObject['detail']['goods_url_name'])) {
            return $infoObject['detail']['goods_url_name'];
        } else {
            $titleNode = $xpath->query('//h1[@class="product-intro__head-name"]');
            if ($titleNode->length) {
                return $titleNode->nodeValue;
            }
        }

    }


}