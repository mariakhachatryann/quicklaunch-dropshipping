<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\AlignFormatter;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class TemuHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_TEMU;

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
                $mainDataArr['productName'] = ArrayHelper::getValue($mainDataJSON, 'store.goods.goodsName');

                $mainDataArr['productimages'] = $this->getImageList($mainDataJSON);

                $bredCounter = 0;
                $mainDataArr['breadcrumb'] = [];
                if ($bredsArr = ArrayHelper::getValue($mainDataJSON, 'store.crumbList')) {
                    foreach ($bredsArr as $bred) {
                        $mainDataArr['breadcrumb'][$bredCounter] = array(
                            "name" => $bred['optName'],
                            "url" => $bred['linkUrl'],
                        );
                        $bredCounter++;
                    }
                }elseif ($bredsArr = ArrayHelper::getValue($mainDataJSON, 'store.crumbOptList')) {
                    foreach ($bredsArr as $bred) {
                        $mainDataArr['breadcrumb'][$bredCounter] = array(
                            "name" => $bred['optName'],
                            "url" => $bred['linkUrl'],
                        );
                        $bredCounter++;
                    }
                }




                if ($descriptionArr = ArrayHelper::getValue($mainDataJSON, 'store.goods.goodsProperty')) {
                    foreach ($descriptionArr as $key => $descriptionKeyNode) {
                        if (!empty($descriptionKeyNode['values'])) {
                            $mainDataArr['productdescription'][] = [trim($descriptionKeyNode['key']) => trim(implode(',', $descriptionKeyNode['values']))];
                        }
                    }
                }

                $price = 0;
                if ($priceArr = ArrayHelper::getValue($mainDataJSON, 'store.goods.salePriceRich')) {
                    foreach ($priceArr as $priceItem) {
                        if($priceItem['type'] == 'price'){
                            $price = preg_replace('/[^\\d.]+/', '', $priceItem['text']);
                            break;
                        }
                    }
                }
                $mainDataArr['price'] = $price;

                $mainDataArr['productID'] = ArrayHelper::getValue($mainDataJSON, 'store.goods.goodsId');
            

                $mainDataArr['options'] = [];

                $variantsArr = ArrayHelper::getValue($mainDataJSON, 'store.sku', []);

                $optionsValues = [];
                if ($variantsArr) {
                    $relatedCounter = 0;

                    foreach ($variantsArr as $variantArr) {

                        $fulfillName = [];
                        foreach ($variantArr['specs'] as  $optionNameArr) {
                            $optionValue = trim($optionNameArr['specValue']);
                            if (!isset($optionsValues[$optionValue])) {
                                $mainDataArr['options'][$optionNameArr['specKey']][] = $optionValue;
                                $optionsValues[$optionValue] = 1;
                            }
                            $fulfillName[$optionNameArr['specKey']] = $optionValue;
                        }

                        $skuQty = $variantArr['limitQuantity'];

                        $mainDataArr['variants'][$relatedCounter]['SKUId'] = $variantArr['skuId'];
                        $mainDataArr['variants'][$relatedCounter]['SKUId_old'] = $variantArr['skuId'];
                        $mainDataArr['variants'][$relatedCounter]['typeID'] = $variantArr['skuId'];
                        $mainDataArr['variants'][$relatedCounter]['variantName'] = implode(',', $fulfillName);
                        $mainDataArr['variants'][$relatedCounter]['variantImages'] = $variantArr['thumbUrl'] ?? '';
                        $mainDataArr['variants'][$relatedCounter]['skuPrice'] = isset($variantArr['linePriceRich']) ? $variantArr['linePriceRich'][1]['text'] : 0;
                        $mainDataArr['variants'][$relatedCounter]['fulfillName'] = $fulfillName;
                        $mainDataArr['variants'][$relatedCounter]['inventory'] = $skuQty;
                        $relatedCounter++;
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
        $mainDataJSONDom = $this->extractor($this->trimCustom($content), 'window.rawData=', ';document.dispatchEvent(new');

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
        if ($images = ArrayHelper::getValue($infoObject, 'store.goods.gallery')) {


            foreach ($images as $image) {
                $imageList[] = ArrayHelper::getValue($image, 'url');
            }
        }

        return $imageList;
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