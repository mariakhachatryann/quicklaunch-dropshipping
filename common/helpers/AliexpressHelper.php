<?php


namespace common\helpers;


use yii\helpers\ArrayHelper;

class AliexpressHelper extends BaseScrapHelper
{

    protected bool $convertToInt = false;


    public function getProduct(string $content, string $url): array
    {
        $aliData = $this->getDataFromContent($url, $content);

        $variants = $this->getVariants($aliData);
        $options = $this->getOptions($aliData);

        $descriptionItems = $this->getDescription($aliData['productdescription']);

        $onlyImages = ArrayHelper::getColumn($aliData['variants'], 'variantImages');
        $optionNames = array_keys($aliData['options']);

        $preparedData = [
            'title' => $aliData['productName'],
            'price' => $aliData['priceHigh'],
            'body_html' => $descriptionItems,
            'brand' => null,
            'vendor' => null,
            'images' => array_values(array_unique(array_merge(
                $aliData['productimages'], $onlyImages
            ))),
            'product_type' => $aliData['subcategory']??'',
            'productId' => $aliData['productID'],
            'stockCount' => $aliData['inventory'],
            'variants' => $variants,
            'options' => $options,
            'onlyImages' => $onlyImages,
            'nameQueue' => $optionNames,
            'onlyOptionName' => static::getOptionNames($onlyImages, $optionNames),
            'weight' => null,
            'weight_unit' => null,
            'productUrl' => $url,
            'variantsStockCount' => $aliData['inventory'],
            'reRequest' => null,
            'reviews' => [
                'reviews' => []
            ]
        ];

        return $preparedData;

    }

    protected function getDataFromContent(string $url, string $content): array
    {
        if (strpos($content, 'window._dida_config_._init_data_=') !== false) {
            return $this->getDataFromContentNew($url, $content);
        }
        $script = substr($content, strpos($content, 'window.runParams ='));
        $script = substr($script, 0, strpos($script, 'csrfToken:'));
        $script = str_replace('data:', '"data":', $script);
        $script = substr($script, strpos($script, '=') + 1);

        $script = rtrim($script);
        $script = substr($script, 0, -1);
        $script .= "}";

        $o = json_decode($script);

        if (!$o) {
            $dataJson = $this->extractor($content, 'window._dida_config_._init_data_=', '</script>');
            $dataJson = str_replace('data:', '"data":', $dataJson);
            $o = json_decode($dataJson);
        }

        if (empty($o->data->titleModule->subject)) {
            return [];
        }

        $finalArr = [];
        $finalArr['options'] = [];
        $finalArr['variants'] = [];

        $finalArr['storeName'] = $o->data->storeModule->storeName;
        $finalArr['storeURL'] = 'https:' . $o->data->storeModule->storeURL;
        $finalArr['storeNumber'] = $o->data->storeModule->storeNum;
        $finalArr['productName'] = $o->data->titleModule->subject;

        if (isset($o->data->priceModule->maxActivityAmount->value)) {

            if ($o->data->priceModule->minActivityAmount->value == 0.01) {
                $finalArr['priceHigh'] = round($o->data->priceModule->maxAmount->value, 2);
                $finalArr['priceLow'] = round($o->data->priceModule->minAmount->value, 2);
            } else {
                $finalArr['priceHigh'] = round($o->data->priceModule->maxActivityAmount->value, 2);
                $finalArr['priceLow'] = round($o->data->priceModule->minActivityAmount->value, 2);
            }

        } else {
            $finalArr['priceHigh'] = round($o->data->priceModule->maxAmount->value, 2);
            $finalArr['priceLow'] = round($o->data->priceModule->minAmount->value, 2);
        }

        $finalArr['productID'] = $o->data->pageModule->productId;
        $finalArr['url'] = $o->data->pageModule->itemDetailUrl;
        $finalArr['productimages'] = $o->data->imageModule->imagePathList;
        $finalArr['productdescription'] = array();
        $productdescriptiontext = '';
        $ogTitle = explode('|', $o->data->pageModule->ogTitle);
        // preg_match_all('/.* (.*)% OFF/', $discount, $matches);
        // $discount = $matches[1];
        $oDescription = $o->data->specsModule->props;
        $oVariants = $o->data->skuModule->productSKUPropertyList ?? [];
        $oVariantsPrices = $o->data->skuModule->skuPriceList;
        $finalArr['breadcrumb'] = $o->data->crossLinkModule->breadCrumbPathList;
        $finalArr['ratings'] = $o->data->titleModule->feedbackRating->totalValidNum;
        $finalArr['rating'] = $o->data->titleModule->feedbackRating->averageStar;
        $finalArr['SellerAdminSeq'] = $o->data->feedbackModule->sellerAdminSeq;
        $finalArr['orders'] = $o->data->titleModule->tradeCount;
        $finalArr['storeRating'] = substr($o->data->storeModule->positiveRate, 0, -1);
        $finalArr['wishcount'] = $o->data->actionModule->itemWishedCount;
        $finalArr['category'] = $finalArr['breadcrumb'][2]->name ?? null;
        $finalArr['subcategory'] = $finalArr['breadcrumb'][3]->name ?? null;


        // has us shipping
        $finalArr['has_us_shipping'] = 0;
        if (isset($o->data->skuModule->productSKUPropertyList)) {
            foreach ($o->data->skuModule->productSKUPropertyList as $key => $value) {
                if ($value->skuPropertyName == 'Ships From') {
                    foreach ($value->skuPropertyValues as $key2 => $value2) {
                        if ($value2->propertyValueDisplayName == 'United States') {
                            $finalArr['has_us_shipping'] = 1;
                            break;
                        }
                    }
                }
            }
        }

        // locate video
        $finalArr['has_video'] = 0;
        $finalArr['video_id'] = '0';
        if (isset($o->data->imageModule->videoId)) {
            $finalArr['has_video'] = 1; // has video
            $finalArr['video_id'] = $o->data->imageModule->videoId; // video id
        }


        foreach ($oDescription as $spec) {
            $finalArr['productdescription'][] = array($spec->attrName => $spec->attrValue);
            $productdescriptiontext .= '<strong>' . $spec->attrName . ': </strong>' . $spec->attrValue . '</br>';
        }
        $finalArr['productdescriptiontext'] = $productdescriptiontext;
        if (isset($oVariants) && $oVariants) {
            $variantTypeIds = array();
            $variantImages = array();
            $variantOptionTypes = array();

            foreach ($oVariants as $variants) {
                //$finalArr['variants'][$variants->skuPropertyName] = array();
                $vorder = 1;
                $varStore = [];
                foreach ($variants->skuPropertyValues as $variant) {

                    if ($variant->propertyValueId == 0) {
                        $variantOptionTypes[(int)$variant->propertyValueIdLong] = $variants->skuPropertyName;

                    } else {
                        $variantOptionTypes[(int)$variant->propertyValueId] = $variants->skuPropertyName;

                    }

                    if (isset($variant->skuPropertyImageSummPath) && $variant->skuPropertyImageSummPath) {
                        $variantimage = explode('_', $variant->skuPropertyImageSummPath);
                        $variantimage[count($variantimage) - 1] = '640x640.jpg';
                        $variantimage = implode('_', $variantimage);
                        if ($variant->propertyValueId == 0) {
                            $variantImages[(int)$variant->propertyValueIdLong] = $variantimage;
                        } else {
                            $variantImages[(int)$variant->propertyValueId] = $variantimage;

                        }
                    }

                    $findit = false;
                    foreach ($varStore as $skey => $svalue) {
                        if ($variant->propertyValueDisplayName == $skey) {
                            $findit = true;
                            break;
                        }
                    }

                    if ($findit) {
                        if (isset($varStore[$variant->propertyValueDisplayName])) {
                            $varStore[$variant->propertyValueDisplayName][0] = $varStore[$variant->propertyValueDisplayName][0] + 1;
                            $var_value = $variant->propertyValueDisplayName . ' ' . $varStore[$variant->propertyValueDisplayName][0];
                        }
                    } else {
                        $varStore[$variant->propertyValueDisplayName][] = 1;
                        $var_value = $variant->propertyValueDisplayName;
                    }

                    $finalArr['options'][$variants->skuPropertyName][] = $var_value;

                    if ($variant->propertyValueId == 0) {
                        $variantTypeIds[(int)$variant->propertyValueIdLong] = $var_value;
                    } else {
                        $variantTypeIds[(int)$variant->propertyValueId] = $var_value;

                    }
                    $finalArr['options_order'][$variants->skuPropertyName][] = $variant->propertyValueId == 0 ? $variant->propertyValueIdLong : $variant->propertyValueId;

                }

            }


            foreach ($oVariantsPrices as $variantPrices) {

                $propIds = explode(',', $variantPrices->skuPropIds);
                $propNames = array();
                $propImages = array();
                $fulfillNames = array();

                foreach ($propIds as $propId) {

                    if (isset($variantTypeIds[(int)trim($propId)])) {
                        $propNames[] = $variantTypeIds[(int)trim($propId)];
                    }

                    if (isset($variantImages[(int)trim($propId)]))
                        $propImages[] = $variantImages[(int)trim($propId)];

                    // find order of option based on sku

                    foreach ($finalArr['options_order'] as $opt_key => $opt_value) {
                        foreach ($opt_value as $opt_key2 => $opt_value2) {
                            if ($opt_value2 == $propId) {
                                $opt_found = $opt_key2 + 1;
                                break;
                            }
                        }
                    }

                    $fulfillNames[$variantOptionTypes[(int)trim($propId)]] = $variantTypeIds[(int)trim($propId)];  // select variant by order

                }
                $propNames = implode(',', $propNames);
                $propImages = implode(', ', $propImages);

                //  $skuPrice = round($variantPrices->skuVal->skuAmount->value * (1 - $discount[0] / 100),2);
                $skuPrice = $variantPrices->skuVal->skuAmount->value;
                $finalArr['variants'][] = array('SKUId' => strval($variantPrices->skuAttr), 'SKUId_old' => strval($variantPrices->skuId), 'typeID' => strval($variantPrices->skuPropIds), 'variantName' => $propNames, 'fulfillName' => $fulfillNames, 'variantImages' => $propImages, 'skuPrice' => $skuPrice, 'available' => $variantPrices->skuVal->availQuantity > 0 ? 1 : 0, 'inventory' => $variantPrices->skuVal->availQuantity);
            }
        } else {  // product has 1 variant
            $finalArr['SKUId'] = strval($o->data->skuModule->skuPriceList[0]->skuAttr);
            $finalArr['SKUId_old'] = strval($o->data->skuModule->skuPriceList[0]->skuId);
            $finalArr['skuPrice'] = $o->data->skuModule->skuPriceList[0]->skuVal->skuAmount->value;

        }

        $finalArr['inventory'] = $o->data->quantityModule->totalAvailQuantity;

        return $finalArr;
    }
    protected function getDataFromContentNew(string $url, string $content): array
    {

        $mainDataJson = $this->extractor($content, 'window._dida_config_._init_data_=', '</script>');
        $mainDataJson = str_replace('data:', '"data":', $mainDataJson);
        $mainDataArr = ArrayHelper::getValue(json_decode($mainDataJson, true), 'data.data');

        if (!$mainDataArr) {
            return [];
        }

        $finalArr = [];
        $finalArr['options'] = [];
        $finalArr['variants'] = [];

        $finalArr['storeName'] = ArrayHelper::getValue($mainDataArr, 'headerInfo_2442.fields._for_header_info.storeModule.storeName');
        $finalArr['storeURL'] = 'https:' . ArrayHelper::getValue($mainDataArr, 'headerInfo_2442.fields._for_header_info.storeModule.storeURL');
        $finalArr['storeNumber'] = ArrayHelper::getValue($mainDataArr, 'headerInfo_2442.fields._for_header_info.storeModule.storeNum');
        $finalArr['productName'] = ArrayHelper::getValue($mainDataArr, 'titleBanner_2440.fields.subject');
        $finalArr['priceHigh'] = round(ArrayHelper::getValue($mainDataArr, 'price_2256.fields.maxActivityAmount.value'), 2);
        $finalArr['priceLow'] = round(ArrayHelper::getValue($mainDataArr, 'price_2256.fields.minActivityAmount.value'), 2);
        $finalArr['productID'] = ArrayHelper::getValue($mainDataArr, 'headerInfo_2442.fields._for_header_info.storeModule.productId');
        $finalArr['url'] = $url;
        $finalArr['productimages'] = ArrayHelper::getValue($mainDataArr, 'imageView_2247.fields.imageList');
        $finalArr['breadcrumb'] = [];

        $finalArr['productdescription'] = [];
       
        $storeDescriptionArr = ArrayHelper::getValue($mainDataArr, 'specsInfo_2263.fields.specs');

        if($storeDescriptionArr){
            foreach ($storeDescriptionArr as $spec) {
                $finalArr['productdescription'][] = [$spec['attrName'] => $spec['attrValue']];
            }
        }


        $oVariants = ArrayHelper::getValue($mainDataArr, 'sku_2257.fields.propertyList');
        $oVariantsPrices = ArrayHelper::getValue($mainDataArr, 'sku_2257.fields.skuList');



        // has us shipping




        if (isset($oVariants) && $oVariants) {
            $variantTypeIds = array();
            $variantImages = array();
            $variantOptionTypes = array();

            foreach ($oVariants as $variants) {
                //$finalArr['variants'][$variants->skuPropertyName] = array();
                $vorder = 1;
                $varStore = [];
                foreach ($variants['skuPropertyValues'] as $variant) {

                    if ($variant['propertyValueId'] == 0) {
                        $variantOptionTypes[(int)$variant['propertyValueIdLong']] = $variants['skuPropertyName'];

                    } else {
                        $variantOptionTypes[(int)$variant['propertyValueId']] = $variants['skuPropertyName'];

                    }

                    if (isset($variant['skuPropertyImageSummPath']) && $variant['skuPropertyImageSummPath']) {
                        $variantimage = explode('_', $variant['skuPropertyImageSummPath']);
                        $variantimage[count($variantimage) - 1] = '640x640.jpg';
                        $variantimage = implode('_', $variantimage);
                        if (!$variant['propertyValueId']) {
                            $variantImages[(int)$variant['propertyValueIdLong']] = $variantimage;
                        } else {
                            $variantImages[(int)$variant['propertyValueId']] = $variantimage;

                        }
                    }

                    $findit = false;
                    foreach ($varStore as $skey => $svalue) {
                        if ($variant['propertyValueDisplayName'] == $skey) {
                            $findit = true;
                            break;
                        }
                    }

                    if ($findit) {
                        if (isset($varStore[$variant['propertyValueDisplayName']])) {
                            $varStore[$variant['propertyValueDisplayName']][0] = $varStore[$variant['propertyValueDisplayName']][0] + 1;
                            $var_value = $variant['propertyValueDisplayName'] . ' ' . $varStore[$variant['propertyValueDisplayName']][0];
                        }
                    } else {
                        $varStore[$variant['propertyValueDisplayName']][] = 1;
                        $var_value = $variant['propertyValueDisplayName'];
                    }

                    $finalArr['options'][$variants['skuPropertyName']][] = $var_value;

                    if (!$variant['propertyValueId']) {
                        $variantTypeIds[(int)$variant['propertyValueIdLong']] = $var_value;
                    } else {
                        $variantTypeIds[(int)$variant['propertyValueId']] = $var_value;

                    }
                    $finalArr['options_order'][$variants['skuPropertyName']][] = !$variant['propertyValueId'] ? $variant['propertyValueIdLong'] : $variant['propertyValueId'];

                }

            }


            foreach ($oVariantsPrices as $variantPrices) {

                $propIds = explode(',', $variantPrices['skuPropIds']);
                $propNames = array();
                $propImages = array();
                $fulfillNames = array();

                foreach ($propIds as $propId) {

                    if (isset($variantTypeIds[(int)trim($propId)])) {
                        $propNames[] = $variantTypeIds[(int)trim($propId)];
                    }

                    if (isset($variantImages[(int)trim($propId)]))
                        $propImages[] = $variantImages[(int)trim($propId)];

                    // find order of option based on sku

                    foreach ($finalArr['options_order'] as $opt_key => $opt_value) {
                        foreach ($opt_value as $opt_key2 => $opt_value2) {
                            if ($opt_value2 == $propId) {
                                $opt_found = $opt_key2 + 1;
                                break;
                            }
                        }
                    }

                    $fulfillNames[$variantOptionTypes[(int)trim($propId)]] = $variantTypeIds[(int)trim($propId)];  // select variant by order

                }
                $propNames = implode(',', $propNames);
                $propImages = implode(', ', $propImages);

                //  $skuPrice = round($variantPrices->skuVal->skuAmount->value * (1 - $discount[0] / 100),2);
                $skuPrice = $variantPrices['skuVal']['skuAmount']['value'];
                $finalArr['variants'][] = array('SKUId' => strval($variantPrices['skuAttr']), 'SKUId_old' => strval($variantPrices['skuId']), 'typeID' => strval($variantPrices['skuPropIds']), 'variantName' => $propNames, 'fulfillName' => $fulfillNames, 'variantImages' => $propImages, 'skuPrice' => $skuPrice, 'available' => $variantPrices['skuVal']['availQuantity'] > 0 ? 1 : 0, 'inventory' => $variantPrices['skuVal']['availQuantity']);
            }
        } else {  // product has 1 variant
            $finalArr['SKUId'] = $finalArr['productID'];
            $finalArr['SKUId_old'] = $finalArr['productID'];
            $finalArr['skuPrice'] = $finalArr['priceHigh'];

        }

        $finalArr['inventory'] = ArrayHelper::getValue($mainDataArr, 'quantity_2259.fields.totalAvailQuantity');

        return $finalArr;
    }
}