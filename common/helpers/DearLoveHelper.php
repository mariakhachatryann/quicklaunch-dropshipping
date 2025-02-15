<?php

namespace common\helpers;

use common\models\AvailableSite;
use common\models\ProductUrl;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class DearLoveHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_DEAR_LOVER;


    public function getProduct(string $content, string $url): array
    {


        $aliData = $this->getDataFromContent($url, $content);
        if (!$aliData) {
            return [
                'variants' => []
            ];
        }
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
                $mainDataArr['productName'] = trim($this->getTitle($xpath));

                $mainDataArr['productimages'] = $this->getImageList($xpath);

                $bredCounter = 0;
                $mainDataArr['breadcrumb'] = [];
                $bradNodes = $xpath->query('//div[@id="body_box"]/div[@class="crumb"]//div//a');

                if ($bradNodes->length) {
                    foreach ($bradNodes as $bred) {
                        $mainDataArr['breadcrumb'][$bredCounter] = array(
                            "name" => $bred->nodeValue,
                            "url" => $bred->getAttribute('href'),
                        );

                        $bredCounter++;
                    }
                }


                $descriptionKeysNodes = $xpath->query('//table[@id="specifications"]//strong');
                $descriptionValuesNodes = $xpath->query('//table[@id="specifications"]//td');

                if ($descriptionKeysNodes->length && $descriptionValuesNodes) {
                    foreach ($descriptionKeysNodes as $key => $descriptionKeyNode) {
                        if ($descriptionValuesNodes->item($key + 1)) {
                            $mainDataArr['productdescription'][] = [trim($descriptionKeyNode->nodeValue) => trim($descriptionValuesNodes->item($key + 1)->nodeValue)];
                        }
                    }
                }

                $price = 0;
                $priceNode = $xpath->query('//span[@class="goods_price notranslate"]')->length ?
                    $xpath->query('//span[@class="goods_price notranslate"]') :
                    $xpath->query('//strong[@class="goods_price"]');
                if ($priceNode->length) {
                    $price = preg_replace('/[^\\d.]+/', '', $priceNode->item(0)->nodeValue);
                }
                $mainDataArr['price'] = $price;

                $mainDataArr['productID'] = ArrayHelper::getValue($mainDataJSON, '0.goods_id');


                $mainDataArr['options'] = [];


                $colorNodes = $xpath->query('//dd[@class="group_codeno_box"]/a');
                $optionsValues = [];
                if ($colorNodes->length) {
                    $relatedCounter = 0;
                    foreach ($colorNodes as $colorNode) {
                        $variantImageNode = $colorNode->getELementsByTagName('img');
                        $variantImage = null;
                        $colorValue = null;
                        if ($variantImageNode->length) {
                            $variantImage = $variantImageNode->item(0)->getAttribute('data-src');
                            if (strpos($variantImage, 'https://') === false) {
                                $variantImage = str_replace('//', 'https://', $variantImage);
                            }
                            if (strpos($variantImage, '?')) {
                                $to_pos = strpos($variantImage, '?'); // to must be after from
                                $variantImage = substr($variantImage, 0, $to_pos);
                            }
                            $variantTitle = $variantImageNode->item(0)->getAttribute('title');
                            $colorValue = explode(' ', $variantTitle)[0];
                            if (isset($optionsValues[$colorValue])) {
                                $optionsValues[$colorValue]++;
                                $colorValue = $colorValue . '-' . $optionsValues[$colorValue];
                            }


                        }
                        foreach ($mainDataJSON as $сolorVariant) {

                            if (!isset($mainDataArr['productID'])) {
                                $mainDataArr['productID'] = $сolorVariant['goods_id'];
                            }
                            $variantNameArr = explode('<br />', $сolorVariant['sku_value']);
                            $fulfillName = [];
                            foreach ($variantNameArr as $optionKey => $optionName) {
                                $optionNameArr = explode(':', $optionName);
                                $optionValue = trim($optionNameArr[1]);
                                if ($optionKey == 0 && $colorValue) {
                                    $optionValue = $colorValue;
                                }
                                if (!isset($optionsValues[$optionValue])) {
                                    $mainDataArr['options'][$optionNameArr[0]][] = $optionValue;
                                    $optionsValues[$optionValue] = 1;
                                }
                                $fulfillName[$optionNameArr[0]] = $optionValue;
                            }

                            $skuQty = $сolorVariant['stock_nums'];;
                            $mainDataArr['variants'][$relatedCounter]['SKUId'] = $сolorVariant['sku_code'];
                            $mainDataArr['variants'][$relatedCounter]['SKUId_old'] = $сolorVariant['sku_code'];;
                            $mainDataArr['variants'][$relatedCounter]['typeID'] = $сolorVariant['sku_code'];;
                            $mainDataArr['variants'][$relatedCounter]['variantName'] = implode(',', $fulfillName);
                            $mainDataArr['variants'][$relatedCounter]['variantImages'] = $variantImage;
                            $mainDataArr['variants'][$relatedCounter]['skuPrice'] = $сolorVariant['price'];
                            $mainDataArr['variants'][$relatedCounter]['fulfillName'] = $fulfillName;
                            $mainDataArr['variants'][$relatedCounter]['inventory'] = $skuQty;
                            $relatedCounter++;
                        }


                    }
                }
                return $mainDataArr;
            }
        }
        return [];
    }


    public function getInfoObject($content)
    {
        $mainDataJSONDom = $this->extractor($this->trimCustom($content), "skulist_str='", "';");
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

        return array_values($sizeList);
    }

    public function getImageList($xpath)
    {
        $imageList = [];
        $imagesSelectorsList  = ['//ul[@id="goodsimagelist"]//li//img','//ul[@class="viewimg swiper-wrapper small_goodsimagelist_sizetype"]//li//img'];
        foreach ($imagesSelectorsList as $imagesSelector) {
            $imagesNodes = $xpath->query($imagesSelector);
            if ($imagesNodes->length) {
                foreach ($imagesNodes as $imageNode) {
                    $image = $imageNode->getAttribute('data-src');
                    if (strpos($image, 'https://') === false) {
                        $image = str_replace('//', 'https://', $image);
                    }
                    if (strpos($image, '?')) {
                        $to_pos = strpos($image, '?'); // to must be after from
                        $image = substr($image, 0, $to_pos);
                    }
                    if (!in_array($image, $imageList)) {
                        $imageList[] = $image;

                    }
                }
                break;
            }
        }


        return $imageList;
    }


    public function getContent($url)
    {
        $model = new ProductUrl(['url' => $url]);
        sleep(rand(1, 7));
        return $model->getPageContent();
    }

    public function getTitle($xpath)
    {
        $titleNode = $xpath->query('//h1[@class="product_detail_h1"]');
        if ($titleNode->length) {
            return $titleNode->item(0)->nodeValue;
        }
        return '';

    }


}