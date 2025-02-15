<?php

namespace common\helpers;

use common\models\AvailableSite;
use common\models\ProductUrl;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class EfourWholesalehHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_E_FOUR_WHOLESALE;


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

            $variantsJson = $this->extractor($this->trimCustom($content), 'variants: ', ', }; </scr');
            $variantsArr = json_decode($variantsJson, true);
            $productMainJson = $this->extractor($this->trimCustom($content), 'var meta =', ';');
            $productMainArr = json_decode($productMainJson, true);

            if (!empty($variantsArr) && !empty($productMainArr)) {
                $mainDataArr['url'] = $url;

                $mainDataArr['breadcrumb'][] = array(
                    "name" => ArrayHelper::getValue($productMainArr, 'product.type'),
                    "url" => $url,
                );

                $mainDataArr['productID'] = ArrayHelper::getValue($productMainArr, 'product.id');

                $titleNodes = $xpath->query('//meta[@property="og:title"]');
                $mainDataArr['productName'] = $titleNodes->item(0)->getAttribute('content');

                $descriptionNodes = $xpath->query('//meta[@name="description"]');
                $mainDataArr['productdescription'][] = [$descriptionNodes->item(0)->getAttribute('content')];


                $mainDataArr['productimages'] = [];
                $imagesJson = $this->extractor($this->trimCustom($content), 'images: ', '],') . ']';
                $mainDataArr['productimages'] = json_decode($imagesJson, true);


                $mainDataArr['options'] = [];
                $optionsJson = $this->extractor($this->trimCustom($content), 'options:', '],') . ']';
                $optionsArr = json_decode($optionsJson, true);
                foreach ($optionsArr as $option) {
                    $mainDataArr['options'][$option['name']] = $option['values'];
                }

                $relatedCOunter = 0;
                $optionNames = array_keys($mainDataArr['options']);
                foreach ($variantsArr as $key => $variant) {
                    $varPrice = $variant['price'] / 100;
                    if (!isset($mainDataArr['price'])) {
                        $mainDataArr['price'] = $varPrice;

                    }
                    $skuId = stripcslashes($variant['sku']);
                    $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $skuId;
                    $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $skuId;
                    $mainDataArr['variants'][$relatedCOunter]['typeID'] = $skuId;
                    $mainDataArr['variants'][$relatedCOunter]['variantName'] = implode(',', $variant['options']);
                    $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $variant['featured_media']['preview_image']['src'];
                    $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $varPrice;
                    $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array_combine($optionNames, $variant['options']);
                    $mainDataArr['variants'][$relatedCOunter]['inventory'] = $variant['available'] ? 999 : 0;
                    $relatedCOunter++;
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