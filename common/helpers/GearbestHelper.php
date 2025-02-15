<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\AlignFormatter;
use yii\helpers\ArrayHelper;

class GearbestHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_GEARBEST;

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

            $schemaJsonDom = $xpath->query('//script[@type="application/ld+json"]');
            $productSchemaJson = json_decode($schemaJsonDom[0]->nodeValue);
            if ($productSchemaJson) {

                $mainDataArr['url'] = $url;
                $mainDataArr['productName'] = $productSchemaJson->name;
                $mainDataArr['productID'] = $productSchemaJson->sku;
                $mainDataArr['price'] = $productSchemaJson->offers->price;
                $productStock = $productSchemaJson->offers->availability == 'http://schema.org/InStock' ? 999 : 0;
                $imagesDom = $xpath->query('//span[@id="js-goodsThumbnail"]//img');

                if ($imagesDom->length) {
                    foreach ($imagesDom as $imageDom) {
                        $mainDataArr['productimages'][] = $imageDom->getAttribute('data-origin-src');

                    }
                }


                $productDescDom = $xpath->query('//div[@class="product_pz_info product_pz_style2"]//td');

                if ($productDescDom->length > 0) {
                    for ($prod = 0; $prod < $productDescDom->length; $prod++) {
                        $descElems = array_filter(explode(' ', $productDescDom->item($prod)->nodeValue));

                        foreach ($descElems as $key => $descElem) {
                            if (strpos($descElem, ':') !== false) {
                                $mainDataArr['productdescription'][] = [trim($descElem) => trim($descElems[($key + 1)])];

                            }
                        }
                    }
                } else {
                    $productDescDom = $xpath->query('//section[@class="platformGoodsDesc js-platformGoodsDesc"]//p[@class="textDescContent"]');
                    if ($productDescDom->length) {
                        $description = str_replace('Specifications:', '', $productDescDom->item(0)->nodeValue);
                        $mainDataArr['productdescription'][]['Specifications'] = $description;
                    }
                }

                $bredCounter = 0;
                $bredDom = $xpath->query('//a[@class="cGoodsCrumb_itemLink"]');
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

                $mainDataArr['options'] = [];
                $optionsDom = $xpath->query('//li[@class="goodsIntro_attrItem goodsIntro_attrRow"]');
                if ($optionsDom) {
                    foreach ($optionsDom as $optionDom) {
                        $tmpDoc = new DOMDocument();
                        $tmpDoc->appendChild($tmpDoc->importNode($optionDom, true));
                        $optionXpath = new DOMXPath($tmpDoc);
                        $optionNameDom = $optionXpath->query('//label');
                        $optionValuesDom = $optionXpath->query('//a');
                        if ($optionNameDom->length && $optionValuesDom->length) {
                            $optionName = trim($optionNameDom[0]->nodeValue, ':');
                            foreach ($optionValuesDom as $optionValueDom) {
                                if ($optionValueDom->getAttribute('data-attr')) {
                                    $mainDataArr['options'][$optionName][] = trim($optionValueDom->getAttribute('data-attr'));
                                }
                            }
                        }
                    }
                }
                $optionsNames = array_keys($mainDataArr['options']);
                $combinations = $this->combinations(array_values($mainDataArr['options']));

                $mainDataArr['variants'] = [];
                $relatedCOunter = 0;
                if (!empty($combinations)) {
                    foreach ($combinations as $combination) {
                        $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $mainDataArr['productID'] . '-' . $relatedCOunter;
                        $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $mainDataArr['productID'] . '-' . $relatedCOunter;;
                        $mainDataArr['variants'][$relatedCOunter]['typeID'] = $mainDataArr['productID'] . '-' . $relatedCOunter;;
                        $mainDataArr['variants'][$relatedCOunter]['variantName'] = implode(',', $combination);
                        $mainDataArr['variants'][$relatedCOunter]['variantImages'] = '';
                        $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $mainDataArr['price'];
                        $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array_combine($optionsNames, $combination);
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

    public function combinations($arrays, $i = 0)
    {
        if (!isset($arrays[$i])) {
            return array();
        }
        $result = array();
        if ($i == count($arrays) - 1) {
            foreach ($arrays[$i] as $v) {
                $result[] = [$v];
            }
            return $result;
        }

        $tmp = $this->combinations($arrays, $i + 1);
        $result = array();
        foreach ($arrays[$i] as $v) {
            foreach ($tmp as $t) {
                $result[] = is_array($t) ?
                    array_merge(array($v), $t) :
                    array($v, $t);
            }
        }

        return $result;
    }
}