<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class TomtopHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_TOMTOP;

    public function getProduct(string $content, string $url): array
    {
        $aliData = $this->getDataFromContent($url, $content);
        $variants = $this->getVariants($aliData);
        $options = $this->getOptions($aliData);
        $onlyImages = ArrayHelper::getColumn($aliData['variants'] ?? [], 'variantImages');
        $optionNames = isset($aliData['options']) ? array_keys($aliData['options']) : [];
        $descriptionItems = [];

        if (isset($aliData['productdescription'])) {
            $descriptionItems = is_string($aliData['productdescription']) ? $this->getDescriptionFromString($aliData['productdescription']) : $this->getDescription($aliData['productdescription']);
        }

        $productTypeIndex = count($aliData['breadcrumb']) - 1;

        $preparedData = [
            'title' => $aliData['productName'],
            'price' => $aliData['price'],
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

    public function getDataFromContent(string $url, string $content): array
    {
        $lb = (PHP_SAPI == 'cli') ? "\n" : "<br>";
        set_time_limit(0);
        explode("_", str_replace(".html", "", $url));


        if (!empty($content)) {

            $doc = new DOMDocument();
            @$doc->loadHTML($content);
            $xpathDetailPage = new DOMXPath($doc);


            $mainDataArr = array();

            $NameDom = $xpathDetailPage->query("//span[@itemprop='name']");
            $Name = "";
            if ($NameDom->length > 0) {

                $Name = $this->trimCustom($NameDom->item(0)->nodeValue);
                $mainDataArr['url'] = $url;
                $mainDataArr['productName'] = $Name;

                $skuDom = $xpathDetailPage->query("//span[@itemprop='mpn']");
                $sku = "";
                if ($skuDom->length > 0) {
                    $sku = $this->trimCustom($skuDom->item(0)->nodeValue);
                    $sku = str_replace("Item#: ", "", $sku);
                }
                $mainDataArr['productID'] = $sku;

                $ratingDom = $xpathDetailPage->query("//span[@itemprop='ratingValue']");
                $rating = "";
                if ($ratingDom->length > 0) {
                    $rating = $this->trimCustom($ratingDom->item(0)->nodeValue);
                }
                $mainDataArr['rating'] = $rating;

                $reviewCountDom = $xpathDetailPage->query("//span[@itemprop='reviewCount']");
                $reviewCount = "";
                if ($reviewCountDom->length > 0) {
                    $reviewCount = $this->trimCustom($reviewCountDom->item(0)->nodeValue);
                }
                $mainDataArr['ratings'] = $reviewCount;

                $priceDom = $xpathDetailPage->query("//p[@id='detailPrice']");
                $price = "";

                if ($priceDom->length > 0) {
                    $price = $this->trimCustom($priceDom->item(0)->nodeValue);
                }
                $mainDataArr['price'] = $price;

                $imagesDom = $xpathDetailPage->query("//div[@id='showCaseSmallPic']//ul//li/a");
                $imagesRaw = "";
                $images = "";
                if ($imagesDom->length > 0) {
                    for ($img = 0; $img < $imagesDom->length; $img++) {
                        $images = $this->trimCustom($imagesDom->item($img)->getAttribute("href"));

                        $mainDataArr['productimages'][$img] = $images;
                    }
                }

                $productDescDom = $xpathDetailPage->query("//div[@id='description']//strong[2]/following-sibling::br/following-sibling::text()");
                $productDescHtml = "";
                if ($productDescDom->length > 0) {

                    for ($prod = 0; $prod < $productDescDom->length; $prod++) {
                        $htmlProdCounter = $prod + 1;

                        $productKeyDom = $xpathDetailPage->query("//div[@id='description']//strong[2]/following-sibling::br[" . $htmlProdCounter . "]/following-sibling::text()");
                        $productKeyRaw = "";
                        if ($productKeyDom->length > 0) {
                            $productKeyRaw = $this->trimCustom($productKeyDom->item(0)->nodeValue);

                            if (!empty($productKeyRaw)) {

                                $productKeyParts = explode(": ", $productKeyRaw);

                                $productKey = "";
                                if (isset($productKeyParts[0])) {
                                    $productKey = $productKeyParts[0];
                                }

                                $productValue = "";
                                if (isset($productKeyParts[1])) {
                                    $productValue = $productKeyParts[1];
                                }

                                $mainDataArr['productdescription'][$prod][$productKey] = $productValue;
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

                        $catUrl = $itemListElementArr['item'][0]['@id'];
                        $catName = $itemListElementArr['item'][0]['name'];

                        $mainDataArr['breadcrumb'][$bredCounter] = array(
                            "name" => $catName,
                            "url" => $catUrl,);

                        $bredCounter++;
                    }
                }

                $productDescTextDom = $xpathDetailPage->query("//div[@id='description']");
                $productDescText = "";
                if ($productDescTextDom->length > 0) {
                    $productDescText = $this->trimCustom($productDescTextDom->item(0)->nodeValue);
                }

                $mainDataArr['productdescription'] = $productDescText;

                $variationsMainJSONDom = $this->extractor($this->trimCustom($content), "var mainContent = ", "}];");

                $variationsMainJSON = json_decode($variationsMainJSONDom . "}]", TRUE);

                $relatedCOunter = 0;
                if (!empty($variationsMainJSON)) {
                    foreach ($variationsMainJSON as $variationsDataArr) {

                        $SKUId = "";
                        if (isset($variationsDataArr['sku'])) {
                            $SKUId = $variationsDataArr['sku'];
                        }

                        $typeID = "";
                        if (isset($variationsDataArr['url'])) {
                            $typeID = $variationsDataArr['url'];
                        }

                        $prdt_image = "";
                        if (isset($variationsDataArr['imgList'][0]['imgUrl'])) {
                            $prdt_image = "https://img.tttcdn.com/product/xy/2000/2000/" . $variationsDataArr['imgList'][0]['imgUrl'];
                        }

                        $prdt_price = "";
                        if (isset($variationsDataArr['whouse'])) {
                            foreach ($variationsDataArr['whouse'] as $whousekey => $whouseValue) {
                                if (isset($whouseValue['nowprice'])) {
                                    $prdt_price = $whouseValue['nowprice'];
                                }
                            }
                        }

                        $prdt_stock = "";
                        if (isset($variationsDataArr['whouse'])) {
                            foreach ($variationsDataArr['whouse'] as $whouseStokkey => $whouseStokValue) {
                                if (isset($whouseStokValue['qty'])) {
                                    $prdt_stock = $whouseStokValue['qty'];
                                    if($prdt_stock < 0 && $whouseStokValue['oversold']){
                                        $prdt_stock = 999;
                                    }
                                }
                            }
                        }
                        $mainDataArr['options'] = [];
                        if (isset($variationsDataArr['attributeMap'])) {

                            $variantAttributes = [];
                            foreach ($variationsDataArr['attributeMap'] as $attributeName => $attribute) {
                                if (!isset($mainDataArr['options']) || !in_array($attributeName, $mainDataArr['options'])) {
                                    $mainDataArr['options'][$attributeName] = [];
                                    $variantAttributes[$attributeName] = [];
                                }
                                if (isset($mainDataArr['options'][$attributeName]) && !in_array($attribute['value'], $mainDataArr['options'][$attributeName])) {
                                    $mainDataArr['options'][$attributeName][] = $attribute['value'];
                                    $variantAttributes[$attributeName] = $attribute['value'];
                                }
                            }
                            if ($variantAttributes) {
                                $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $SKUId;
                                $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $SKUId;
                                $mainDataArr['variants'][$relatedCOunter]['typeID'] = $typeID;
                                $mainDataArr['variants'][$relatedCOunter]['variantName'] = implode(',', array_values($variantAttributes));
                                $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $prdt_image;
                                $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $prdt_price;

                                $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = $variantAttributes;
                                $mainDataArr['variants'][$relatedCOunter]['inventory'] = $prdt_stock;
                                $relatedCOunter++;
                            }

                        }
                    }
                }


                return $mainDataArr;
            }
        } else {
            echo 'URLNotOpen: ' . $url . $lb;
        }
    }

    public function getDescriptionFromString(string $description): array
    {
        return [
            [
                'attr_name' => 'Description',
                'attr_value' => str_replace('Description:', '', $description)
            ]
        ];
    }

}