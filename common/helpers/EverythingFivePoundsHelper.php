<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\AlignFormatter;
use yii\helpers\ArrayHelper;

class EverythingFivePoundsHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_EVERYTHING_FIVE_POUNDS;

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


    public function getDataFromContent(string $url, string $content): array
    {

        $lb = (PHP_SAPI == 'cli') ? "\n" : "<br>";
        set_time_limit(0);


        if ($content) {

            $mainDataArr = array();
            $contentDom = new DOMDocument();
            @$contentDom->loadHTML($content);
            $xpath = new DOMXPath($contentDom);

            $meta = $xpath->query('//meta[@property="og:type"]');
            if ($meta->length) {

                $mainDataArr['url'] = $url;
                $mainDataArr['productName'] = $this->getProductName($xpath);
                $mainDataArr['productID'] = $this->getProductSku($xpath);
                $mainDataArr['price'] = $this->getProductPrice($xpath);
                $productStock = $this->getProductStock($xpath);
                $mainDataArr['productimages'] = $this->getProductImages($xpath);
                $mainDataArr['productdescription'] = $this->getProductDescription($xpath);
                $mainDataArr['breadcrumb'] = $this->getProductBreadcrumb($xpath);


                $mainDataArr['options'] = [];
                $colorsDom = $xpath->query('//ul[@id="colour-list"]//img');
                $optionImages = [];
                if ($colorsDom) {
                    foreach ($colorsDom as $colorNode) {
                        $mainDataArr['options']['Color'][] = trim($colorNode->getAttribute('title'));
                        $optionImages[trim($colorNode->getAttribute('title'))] = str_replace(['marker__', '30x30'], ['', '400x600'], trim($colorNode->getAttribute('data-original')));
                    }
                }
                $sizesDom = $xpath->query('//select[@id="Size"]//option');
                if ($sizesDom) {
                    foreach ($sizesDom as $sizeNode) {
                        if ($sizeNode->getAttribute('value')) {
                            $mainDataArr['options']['Size'][] = trim($sizeNode->nodeValue);
                        }
                    }
                }


                $optionsNames = array_keys($mainDataArr['options']);
                $combinations = $this->combinations(array_values($mainDataArr['options']), 0, $optionImages);

                $mainDataArr['variants'] = [];
                $relatedCOunter = 0;
                if (!empty($combinations)) {

                    foreach ($combinations as $combination) {
                       
                        $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $mainDataArr['productID'] . '-' . $relatedCOunter;
                        $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $mainDataArr['productID'] . '-' . $relatedCOunter;;
                        $mainDataArr['variants'][$relatedCOunter]['typeID'] = $mainDataArr['productID'] . '-' . $relatedCOunter;;
                        $mainDataArr['variants'][$relatedCOunter]['variantName'] = implode(',', $combination['value']);
                        $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $combination['img'] ?? '';
                        $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $mainDataArr['price'];
                        $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array_combine($optionsNames, $combination['value']);
                        $mainDataArr['variants'][$relatedCOunter]['inventory'] = $productStock;
                        $relatedCOunter++;
                    }
                }
                
                return $mainDataArr;
            }
        } else {
            echo 'URLNotOpen: ' . $url . $lb;
        }
    }

    public function getProductName($xpath)
    {
        $productNameDom = $xpath->query('//h1[@class="product-name"]');
        if ($productNameDom->length) {
            foreach ($productNameDom as $productNameNode) {
                if ($productNameNode->nodeValue) {
                    return $productNameNode->nodeValue;
                }
            }
        }
    }

    public function getProductSku($xpath)
    {
        $productSkuDom = $xpath->query('//input[@name="productCodePost"]');

        if ($productSkuDom->length) {
            foreach ($productSkuDom as $productSkuNode) {
                if ($productSkuNode->getAttribute('value')) {
                    return $productSkuNode->getAttribute('value');
                }
            }
        }else {
            $productSkuDom = $xpath->query('//input[@name="sku"]');
            if ($productSkuDom->length) {
                foreach ($productSkuDom as $productSkuNode) {
                    if ($productSkuNode->getAttribute('value')) {
                        return $productSkuNode->getAttribute('value');
                    }
                }
            }
        }
    }

    public function getProductPrice($xpath)
    {
        $productPriceDom = $xpath->query('//input[@name="unitPrice"]');

        if ($productPriceDom->length) {
            foreach ($productPriceDom as $productPriceNode) {
                if ($productPriceNode->getAttribute('value')) {
                    return $productPriceNode->getAttribute('value');
                }
            }
        }else{
            $productSkuDom = $xpath->query('//meta[@itemprop="price"]');
            if ($productSkuDom->length) {
                foreach ($productSkuDom as $productSkuNode) {
                    if ($productSkuNode->getAttribute('content')) {
                        return $productSkuNode->getAttribute('content');
                    }
                }
            }
        }
    }

    public function getProductStock($xpath)
    {
        $productStockDom = $xpath->query('//input[@id="productStockLevel"]');

        if ($productStockDom->length) {
            foreach ($productStockDom as $productStockNode) {
                if ($productStockNode->getAttribute('value')) {
                    return $productStockNode->getAttribute('value') == 'inStock' ? 999 : 0;
                }
            }
        }
        return 0;
    }

    public function getProductImages($xpath)
    {
        $productImagesDom = $xpath->query('//ul[@id="tumb-image"]//li//img');
        $images = [];
        if ($productImagesDom->length) {
            foreach ($productImagesDom as $productImageNode) {
                if ($productImageNode->getAttribute('data-original')) {
                    $img = $productImageNode->getAttribute('data-original');
                    $images[] = str_replace('100x150', '400x600', $img);
                }
            }
        }
        return $images;
    }

    public function getProductDescription($xpath)
    {
        $productDescDom = $xpath->query('//div[@class="productDescriptionText"]//div');
        $description = [];
        if ($productDescDom->length) {
            foreach ($productDescDom as $descItemNode) {
                $itemValue = $descItemNode->nodeValue;
                if ($itemValue && strpos($itemValue, ':')) {
                    $explodeText = explode(':', $itemValue);
                    $description[] = [trim($explodeText[0]) => trim($explodeText[1])];
                } else if (trim($itemValue) && preg_match("/[a-z]/i", $itemValue)) {
                    $description[] = [trim($itemValue) => ''];

                }
            }
        }
        return $description;
    }

    public function getProductBreadcrumb($xpath)
    {
        $bredCounter = 0;
        $bredDom = $xpath->query('//div[@id="breadcrumb"]//a');
        $breadcrumb = [];
        if ($bredDom->length) {
            foreach ($bredDom as $bred) {
                $catUrl = $bred->getAttribute('href');
                $catName = $bred->nodeValue;
                $breadcrumb[$bredCounter] = array(
                    "name" => $this->trimCustom($catName),
                    "url" => $catUrl,
                );

                $bredCounter++;
            }
        }
        if ($breadcrumb) {
            array_pop($breadcrumb);
        }
        return $breadcrumb;
    }

    public function combinations($arrays, $i = 0, $images)
    {
        if (!isset($arrays[$i])) {
            return array();
        }

        $result = array();
        if ($i == count($arrays) - 1) {

            foreach ($arrays[$i] as $v) {

                if(isset($images[$v])){
                    $result[] = ['value' => count($arrays) > 1 ? $v : array($v), 'img' => $images[$v]];
                }else{
                    $result[] = ['value' => count($arrays) > 1 ? $v : array($v)];
                }
            }
            return $result;
        }

        $tmp = $this->combinations($arrays, $i + 1, $images);
        $result = array();
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                if(isset($images[$v])){
                    $result[] = ['value'=>is_array($t) ?
                        array_merge(array($v), array($t['value'])) :
                        array($v, $t), 'img'=>$images[$v]];
                }elseif(isset($images[$t])){
                    $result[] = ['value'=>is_array($t) ?
                        array_merge(array($v), array($t['value'])) :
                        array($v, $t), 'img'=>$images[$t]];
                }else{
                    $result[] = ['value'=>is_array($t) ?
                        array_merge(array($v), array($t['value'])) :
                        array($v, $t)];
                }

                   
            }
        }

        return $result;
    }
}