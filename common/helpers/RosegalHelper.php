<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class RosegalHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_ROSEGAL;

    public function getProduct(string $content, string $url): array
    {
        $aliData = $this->getDataFromContent($url, $content);
        $variants = $this->getVariants($aliData);
        $options = $this->getOptions($aliData);

        $descriptionItems = $this->getDescription($aliData['productdescription']);

        $onlyImages = ArrayHelper::getColumn($aliData['variants'], 'variantImages');
        $optionNames = array_keys($aliData['options']);

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

    function getDataFromContent(string $url, string $content): array
    {
        $urlParts = explode("_", str_replace(".html", "", $url));
        $id = end($urlParts);

        $lb = (PHP_SAPI == 'cli') ? "\n" : "<br>";
        set_time_limit(0);

        if ($content) {
            $doc = new DOMDocument();
            @$doc->loadHTML($content);
            $xpathDetailPage = new DOMXPath($doc);

            $mainDataArr = array();

            $JSONDom = $xpathDetailPage->query("//script[@type='application/ld+json'][2 ]");
            $JSONRAW = "";
            $JSON = "";
            if ($JSONDom->length > 0) {

                $JSONRAW = $this->trimCustom($JSONDom->item(0)->nodeValue);

                $JSON = json_decode($JSONRAW, TRUE);

                $Name = "";
                if (isset($JSON['name'])) {
                    $Name = $JSON['name'];
                }

                $mainDataArr['url'] = $url;
                $mainDataArr['productName'] = $Name;


                $sku = "";
                if (isset($JSON['sku'])) {
                    $sku = $JSON['sku'];
                }

                $mainDataArr['sku'] = $sku;

                $description = "";
                if (isset($JSON['description'])) {
                    $description = $JSON['description'];
                }

                $mainDataArr['productID'] = $id;

                $rating = "";
                if (isset($JSON['aggregateRating']['ratingValue'])) {
                    $rating = $JSON['aggregateRating']['ratingValue'];
                }
                $mainDataArr['rating'] = $rating;

                $reviewCount = "";
                if (isset($JSON['aggregateRating']['reviewCount'])) {
                    $reviewCount = $JSON['aggregateRating']['reviewCount'];
                }
                $mainDataArr['ratings'] = $reviewCount;

                $price = "";
                if (isset($JSON['offers']['price'])) {
                    $price = $JSON['offers']['price'];
                }
                $mainDataArr['price'] = $price;


                $imagesDom = $xpathDetailPage->query("//div[@id='goods_thumb_content']/ul//li");
                $imagesRaw = "";
                $images = "";
                if ($imagesDom->length > 0) {
                    for ($img = 0; $img < $imagesDom->length; $img++) {
                        $images = $this->trimCustom($imagesDom->item($img)->getAttribute("data-bigimg"));

                        $mainDataArr['productimages'][$img] = $images;
                    }
                }

                $productDescDom = $xpathDetailPage->query("//div[@class='xxkkk20']//strong");
                if ($productDescDom->length > 0) {
                    for ($prod = 0; $prod < $productDescDom->length; $prod++) {
                        $htmlProdCounter = $prod + 1;

                        $productKeyDom = $xpathDetailPage->query("//div[@class='xxkkk20']//strong[" . $htmlProdCounter . "]");
                        $productKey = "";
                        if ($productKeyDom->length > 0) {
                            $productKey = $this->trimCustom($productKeyDom->item(0)->nodeValue);
                            $productKey = str_replace(":", "", $productKey);
                        }

                        $productValueDom = $xpathDetailPage->query("//div[@class='xxkkk20']//strong[" . $htmlProdCounter . "]/following-sibling::text()");
                        $productValue = "";
                        if ($productValueDom->length > 0) {
                            $productValue = $this->trimCustom($productValueDom->item(0)->nodeValue);
                        }
                        $mainDataArr['productdescription'][$prod][$productKey] = $productValue;
                    }
                }

                $breadcrumbDom = $xpathDetailPage->query("//script[@type='application/ld+json'][1]");
                $breadcrumbJSON = "";

                if ($breadcrumbDom->length > 0) {
                    $breadcrumbJSON = $this->trimCustom($breadcrumbDom->item(0)->nodeValue);
                    $breadcrumbJSON = json_decode($breadcrumbJSON, TRUE);

                    $bredCounter = 0;
                    $breadcrumbCount = 0;
                    $breadcrumbCount = count($breadcrumbJSON['itemListElement']);
                    $breadcrumbCount = $breadcrumbCount - 1;

                    foreach ($breadcrumbJSON['itemListElement'] as $itemListElementArr) {

                        $catUrl = $itemListElementArr['item']['@id'];
                        $catName = $itemListElementArr['item']['name'];

                        $mainDataArr['breadcrumb'][$bredCounter] = array(
                            "name" => $catName,
                            "url" => $catUrl,);

                        $bredCounter++;
                    }
                }


                $productDescTextDom = $xpathDetailPage->query("//div[@class='xxkkk20']");
                $productDescText = "";
                if ($productDescTextDom->length > 0) {

//        $productDescText = $this->trimCustom($productDescTextDom->item(0)->nodeValue);
                    $productDescText = $this->getInnerHTML($productDescTextDom);
                }

                $mainDataArr['productdescriptiontext'] = $productDescText;

                $colorsDom = $xpathDetailPage->query("//div[@data-type='Color']//a[contains(@class,'itemAttr')]");
                $colors = "";
                if ($colorsDom->length > 0) {
                    for ($colr = 0; $colr < $colorsDom->length; $colr++) {
                        $colors = $this->trimCustom($colorsDom->item($colr)->getAttribute("title"));
                        $mainDataArr['options']['Color'][$colr] = $colors;
                    }
                }

                $sizesDom = $xpathDetailPage->query("//div[@data-type='Size']//a[contains(@class,'itemAttr')]");
                $sizes = "";
                $stock = "";
                $valueID = "";
                if ($sizesDom->length > 0) {
                    for ($sz = 0; $sz < $sizesDom->length; $sz++) {
                        $sizes = $this->trimCustom($sizesDom->item($sz)->getAttribute("title"));

                        $mainDataArr['options']['Size'][$sz] = $sizes;
                    }
                }

                $variationsMainJSONDom = $this->extractor($this->trimCustom($content), "var all_same_goods_list = ", "}];");

                $variationsMainJSON = json_decode($variationsMainJSONDom . "}]", TRUE);
                $relatedCOunter = 0;
                if (!empty($variationsMainJSON)) {
                    foreach ($variationsMainJSON as $variationsDataArr) {

                        $SKUId = "";
                        if (isset($variationsDataArr['sku'])) {
                            $SKUId = $variationsDataArr['sku'];
                        }

                        $typeID = "";
                        if (isset($variationsDataArr['goods_id'])) {
                            $typeID = $variationsDataArr['goods_id'];
                        }

                        $varColor = "";
                        if (isset($variationsDataArr['Color'])) {
                            $varColor = $variationsDataArr['Color'];
                        }

                        $varSize = "";
                        if (isset($variationsDataArr['Size'])) {
                            $varSize = $variationsDataArr['Size'];
                        }

                        $prdt_image = "";
                        if (isset($variationsDataArr['goods_img'])) {
                            $prdt_image = $variationsDataArr['goods_img'];
                        }

                        $prdt_price = "";
                        if (isset($variationsDataArr['shop_price'])) {
                            $prdt_price = $variationsDataArr['shop_price'];
                        }

                        $prdt_stock = "";
                        if (isset($variationsDataArr['goods_number'])) {
                            $prdt_stock = $variationsDataArr['goods_number'];
                        }

                        if (empty($varSize)) {

                            $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $SKUId;
                            $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $SKUId;
                            $mainDataArr['variants'][$relatedCOunter]['typeID'] = $typeID;
                            $mainDataArr['variants'][$relatedCOunter]['variantName'] = $varColor;
                            $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $prdt_image;
                            $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $prdt_price;

                            $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                                "Color" => $varColor,
                            );
                            $mainDataArr['variants'][$relatedCOunter]['inventory'] = $prdt_stock;
                            $relatedCOunter++;
                        } else {

                            $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $SKUId;
                            $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $SKUId;
                            $mainDataArr['variants'][$relatedCOunter]['typeID'] = $typeID;
                            $mainDataArr['variants'][$relatedCOunter]['variantName'] = $varColor . "," . $varSize;
                            $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $prdt_image;
                            $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $prdt_price;

                            $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                                "Color" => $varColor,
                                "Size" => $varSize,
                            );
                            $mainDataArr['variants'][$relatedCOunter]['inventory'] = $prdt_stock;
                            $relatedCOunter++;
                        }
                    }
                }
                return $mainDataArr;
            }
        } else {
            echo 'URLNotOpen: ' . $url . $lb;
        }
    }


    /**
     * $XPath->query ( '//p[@class="row"]/span' )->nodeValue;
     * return a string without html tags.
     *
     * So this function will solve this problem
     * and return a string with html tags
     *
     * @param DomXpathArray $domXpathArray
     * @param int $loopCounter
     * @return string
     */
    public function getInnerHTML($domXpathArray, $loopCounter = 0)
    {
        //extract data with tags
        $counter = 0;
        $innerHTML = '';
        foreach ($domXpathArray as $tag) {

            $children = $tag->childNodes;
            foreach ($children as $child) {
                $tmpDoc = new DOMDocument();
                $tmpDoc->appendChild($tmpDoc->importNode($child, true));
                $innerHTML .= trim($tmpDoc->saveHTML());
            }
            $counter++;
            if ($loopCounter > 0 && $counter >= $loopCounter)
                break;
        }

        return $innerHTML;
    }
}