<?php

namespace common\helpers;

use common\models\AvailableSite;
use common\models\ProductUrl;
use common\models\User;
use DOMDocument;
use DOMXPath;
use Yii;
use yii\helpers\ArrayHelper;

class SheinHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_SHEIN;


    public function getProduct(string $content, string $url): array
    {

        if (!$content) {
            Yii::error(compact('url'), 'SheinEmptyContent');
            return [];
        }
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
        if ($content) {

            $mainDataArr = array();
            $contentDom = new DOMDocument();
            @$contentDom->loadHTML($content);
            $xpath = new DOMXPath($contentDom);


            $mainDataJSON = $this->getInfoObject($content);


            if (!empty($mainDataJSON)) {

                $mainDataArr['url'] = $url;
                $mainDataArr['productName'] = trim(str_replace('SHEIN', '', $this->getTitle($xpath, $mainDataJSON)));


                $mainDataArr['productID'] = ArrayHelper::getValue($mainDataJSON, 'detail.goods_id');

                $price = ArrayHelper::getValue($mainDataJSON, 'detail.salePrice.usdAmount', 0);;

                $mainDataArr['price'] = $price;

                $mainDataArr['productimages'] = $this->getImageList($mainDataJSON, true);

                if (isset($mainDataJSON['detail']['productDetails'])) {
                    foreach ($mainDataJSON['detail']['productDetails'] as $detailItem) {
                        $mainDataArr['productdescription'][] = [trim($detailItem['attr_name']) => trim($detailItem['attr_value'])];
                    }
                }


                $bredCounter = 0;
                $mainDataArr['breadcrumb'] = [];

                if (isset($mainDataJSON['parentCats']['children'])) {
                    foreach ($mainDataJSON['parentCats']['children'] as $bred) {
                        if (isset($bred['children']) && !empty($bred['children'])) {
                            $catName = ArrayHelper::getValue($bred, 'children.0.cat_name');
                        } else {
                            $catName = ArrayHelper::getValue($bred, 'cat_name');

                        }
                        $mainDataArr['breadcrumb'][$bredCounter] = array(
                            "name" => $this->trimCustom($catName),
                            "url" => '',
                        );

                        $bredCounter++;
                    }
                }

                $mainDataArr['options'] = [];
                $relatedCOunter = 0;
                $repeatOptVals = [];

                if (isset($mainDataJSON['relation_color']) && !empty($mainDataJSON['relation_color'])) {
                    $currentSelectedSkuList = array_values(ArrayHelper::getValue($mainDataJSON, 'attrSizeList.sale_attr_list'));
                    $currentSelectedDetails = $mainDataJSON['detail'];
                    $currentSelectedVal = $currentSelectedDetails['mainSaleAttribute'][0]['attr_value_en'];
                    $optName = $currentSelectedDetails['mainSaleAttribute'][0]['attr_name_en'];
                    $mainDataArr['options'][$optName][] = $currentSelectedVal;
                    $repeatOptVals[$currentSelectedVal] = 1;

                    foreach ($currentSelectedSkuList[0]['sku_list'] as $sku_item) {

                        $skuId = $mainDataJSON['detail']['goods_sn'];
                        $fulfillName = [$optName => $currentSelectedVal];
                        $skuVal = '';
                        if (!empty($sku_item['sku_sale_attr'])) {
                            $skuId = $mainDataJSON['detail']['goods_sn'];
                            foreach ($sku_item['sku_sale_attr'] as $sku_attr_item) {
                                $skuId .= '_' . $sku_attr_item['attr_value_id'];
                                $skuVal = $sku_attr_item['attr_value_name_en'];
                                $fulfillName[$sku_attr_item['attr_name']] = $skuVal;
                                if ($skuVal && (!isset($skuVal, $mainDataArr['options'][$sku_attr_item['attr_name']]) || !in_array($skuVal, $mainDataArr['options'][$sku_attr_item['attr_name']]))) {
                                    $mainDataArr['options'][$sku_attr_item['attr_name']][] = $skuVal;
                                }
                            }
                        }

                        $skuQty = $sku_item['stock'];
                        $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $skuId;
                        $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $skuId;
                        $mainDataArr['variants'][$relatedCOunter]['typeID'] = $skuId;
                        $mainDataArr['variants'][$relatedCOunter]['variantName'] = !empty($fulfillName) ? implode(',', $fulfillName) : $currentSelectedVal;
                        $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $currentSelectedDetails['original_img'];
                        $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $sku_item['price']['salePrice']['amount'];
                        $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = !empty($fulfillName) ? $fulfillName : [$optName => $currentSelectedVal];
                        $mainDataArr['variants'][$relatedCOunter]['inventory'] = $skuQty;
                        $relatedCOunter++;
                    }

                    foreach ($mainDataJSON['relation_color'] as $optColor) {
                        $optName = ArrayHelper::getValue($optColor, 'mainSaleAttribute.0.attr_name_en');
                        $optVal = ArrayHelper::getValue($optColor, 'mainSaleAttribute.0.attr_value_en');
                        $optId = ArrayHelper::getValue($optColor, 'goods_sn');
                        $goodId = ArrayHelper::getValue($optColor, 'goods_id');
                        if (!isset($optVal, $mainDataArr['options'][$optName]) || !in_array($optVal, $mainDataArr['options'][$optName])) {
                            $mainDataArr['options'][$optName][] = $optVal;
                            $repeatOptVals[$optVal] = 1;
                        } elseif (isset($repeatOptVals[$optVal])) {
                            $repeatOptVals[$optVal]++;
                            $optVal = $optVal . '-' . $repeatOptVals[$optVal];
                            $mainDataArr['options'][$optName][] = $optVal;
                        }

                        $optContent = $this->getContent($goodId, $url, $mainDataArr['productID']);

                        if ($optContent) {
                            $optObject = $this->getInfoObject($optContent);
                            $skuList = $this->getSkuList($optObject, $url);
                            $imageList = $this->getImageList($optObject);
                            $mainDataArr['productimages'] = array_merge($mainDataArr['productimages'], $imageList);
                            foreach ($skuList[0]['sku_list'] as $sku_item) {
                                $fulfillName = [$optName => $optVal];
                                $skuId = $optId;
                                $skuVal = '';
                                $skuQty = $sku_item['stock'];

                                if (!empty($sku_item['sku_sale_attr'])) {
                                    foreach ($sku_item['sku_sale_attr'] as $sku_attr_item) {
                                        $skuId .= '_' . $sku_attr_item['attr_value_id'];
                                        $skuVal = $sku_attr_item['attr_value_name_en'];
                                        $fulfillName[$sku_attr_item['attr_name']] = $skuVal;
                                        if ($skuVal && (!isset($skuVal, $mainDataArr['options'][$sku_attr_item['attr_name']]) || !in_array($skuVal, $mainDataArr['options'][$sku_attr_item['attr_name']]))) {
                                            $mainDataArr['options'][$sku_attr_item['attr_name']][] = $skuVal;
                                        }
                                    }
                                }
                                $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $skuId;
                                $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $skuId;
                                $mainDataArr['variants'][$relatedCOunter]['typeID'] = $skuId;
                                $mainDataArr['variants'][$relatedCOunter]['variantName'] = implode(',', $fulfillName);
                                $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $optColor['original_img'];
                                $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $sku_item['price']['salePrice']['amount'];
                                $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = $fulfillName;
                                $mainDataArr['variants'][$relatedCOunter]['inventory'] = $skuQty;
                                $relatedCOunter++;
                            }
                        }

                    }
                } else {
                    $skuList = array_values(ArrayHelper::getValue($mainDataJSON, 'attrSizeList.sale_attr_list'));
                    $currentSelectId = $mainDataJSON['detail']['goods_sn'];

                    foreach ($skuList[0]['sku_list'] as $sku_item) {

                        if (!empty($sku_item['sku_sale_attr'])) {
                            $skuQty = $sku_item['stock'];
                            $skuId = $currentSelectId;
                            foreach ($sku_item['sku_sale_attr'] as $sku_attr_item) {
                                $skuId .= '_' . $sku_attr_item['attr_value_id'];
                                $skuVal = $sku_attr_item['attr_value_name_en'];
                                $fulfillName[$sku_attr_item['attr_name']] = $skuVal;
                                if ($skuVal && (!isset($skuVal, $mainDataArr['options'][$sku_attr_item['attr_name']]) || !in_array($skuVal, $mainDataArr['options'][$sku_attr_item['attr_name']]))) {
                                    $mainDataArr['options'][$sku_attr_item['attr_name']][] = $skuVal;
                                }
                            }
                            $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $skuId;
                            $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $skuId;
                            $mainDataArr['variants'][$relatedCOunter]['typeID'] = $skuId;
                            $mainDataArr['variants'][$relatedCOunter]['variantName'] = implode(',', $fulfillName);
                            $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $sku_item['price']['salePrice']['amount'];
                            $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = $fulfillName;
                            $mainDataArr['variants'][$relatedCOunter]['inventory'] = $skuQty;
                            $relatedCOunter++;
                        } else {
                            $mainDataArr['stock'] = ArrayHelper::getValue($sku_item, 'stock', 0);
                        }

                    }
                }

                return $mainDataArr;
            }
        }
    }

    public function getInfoObject($content)
    {
        $mainDataJSONDom = $this->extractor($this->trimCustom($content), "productIntroData: ", ', abt:');
        $mainDataJSON = json_decode($mainDataJSONDom, true);
        if (!$mainDataJSON) {
            $mainDataJSONDom = stripslashes($mainDataJSONDom);
            $mainDataJSON = json_decode($mainDataJSONDom, true);
        }
        return $mainDataJSON;
    }

    public function getSkuList($infoObject, $url)
    {
        $sizeList = ArrayHelper::getValue($infoObject, 'attrSizeList.sale_attr_list');

        return array_values($sizeList ?: []);
    }

    public function getImageList($infoObject, $main = false)
    {
        $imageList = [];
        if (isset($infoObject['goods_imgs'])) {

            if (isset($infoObject['goods_imgs']['main_image']['origin_image'])) {
                $imageList[] = $infoObject['goods_imgs']['main_image']['origin_image'];
            }
            if (!empty($infoObject['goods_imgs']['detail_image'])) {
                foreach ($infoObject['goods_imgs']['detail_image'] as $image) {
                    $imageList[] = ArrayHelper::getValue($image, 'origin_image');
                    if (count($imageList) == 2 && !$main) {
                        break;
                    }
                }
            }

        }

        return array_values($imageList);
    }


    public function getContent($goodId, $url, $currentGoodId)
    {
        $variantUrl = str_replace($currentGoodId, $goodId, $url);
        $model = new ProductUrl(['url' => $variantUrl]);

        $userId = Yii::$app->params['user_id'] ?? null;
        $user = $userId ? User::findOne($userId) : Yii::$app->user->identity;

        $model->setUser($user);

        return $model->getPageContent();
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