<?php

namespace common\helpers;

use common\models\AvailableSite;
use common\models\ProductUrl;
use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class ModeSheHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_MODESHE;

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
            'price' => $aliData['price'],
            'body_html' => $descriptionItems,
            'brand' => null,
            'vendor' => null,
            'images' => array_values(array_unique(array_merge(
                $aliData['productimages'], $onlyImages
            ))),
            'product_type' => $aliData['subcategory'] ?? 'tba',
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

        $DetailPageHTML = $this->getContent($url);
        if (empty($DetailPageHTML['ERR'])) {


            $doc = new DOMDocument();
            @$doc->loadHTML($DetailPageHTML['EXE']);
            $xpathDetailPage = new DOMXPath($doc);


            $mainDataArr = array();

            $JSONDom = $xpathDetailPage->query("//script[@type='application/ld+json']");
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


                $id = "";
                if (isset($JSON['productID'])) {
                    $id = $JSON['productID'];
                }

                $sku = "";
                if (isset($JSON['sku'])) {
                    $sku = $JSON['sku'];
                }

                $mainDataArr['productID'] = $id;

                $price = "";
                if (isset($JSON['offers']['price'])) {
                    $price = $JSON['offers']['price'];
                }
                $mainDataArr['price'] = $price;
                $mainDataArr['productimages'] = [];

                $imagesObj = $xpathDetailPage->query("//div[@class='row theiaStickySidebar']//img");
                $imgCounter = 0;
                $singImage = '';
                if ($imagesObj->length > 0) {

                    for ($i = 0; $i < $imagesObj->length; $i++) {

                        $imgSrcRaw = $imagesObj->item($i)->getAttribute("src");
                        if (stristr($imgSrcRaw, "data:image/")) {
                            continue;
                        }
                        if ($i == 0) {
                            $singImage = "https:" . $imgSrcRaw;
                        }

                        $mainDataArr['productimages'][$imgCounter] = "https:" . $imgSrcRaw;
                        $imgCounter++;
                    }
                }
                if (empty($mainDataArr['productimages'])) {
                    $imagesObj = $xpathDetailPage->query("//div[@class='row theiaStickySidebar']//div");
                    if ($imagesObj->length) {
                        if ($imageBgset = $imagesObj->item(0)) {

                        }
                    }
                }
                $mainDataArr['productdescription'] = [];

                $descKeyValObj = $xpathDetailPage->query("//div[@id='tab_pr_deskl']//div[contains(@class,'sp-tab-content')]//div[@class='product-intro__description-table-item']");
                if ($descKeyValObj->length > 0) {
                    for ($d = 0; $d < $descKeyValObj->length; $d++) {

                        $htmlDescCounter = $d + 1;

                        $descKeyValNewObj = $xpathDetailPage->query("//div[@id='tab_pr_deskl']//div[contains(@class,'sp-tab-content')]//div[@class='product-intro__description-table-item'][$htmlDescCounter]");

                        $descKeyValRaw = $this->getInnerHTML($descKeyValObj);
                        $prod = 0;
                        if (!empty($descKeyValRaw)) {

                            $descParts = explode('<div class="key">', $descKeyValRaw);
                            foreach ($descParts as $descValue) {
                                if (!empty($descValue)) {

                                    $descValue = $this->trimCustom(strip_tags($descValue));
                                    $descValue = str_replace("&nbsp;", "", $descValue);
                                    $singleDescParts = explode(": ", strip_tags($descValue));
                                    $descKey = "";
                                    if (isset($singleDescParts[0])) {
                                        $descKey = $singleDescParts[0];
                                    }
                                    $descVal = "";
                                    if (isset($singleDescParts[1])) {
                                        $descVal = $this->trimCustom($singleDescParts[1]);
                                    }


                                    $mainDataArr['productdescription'][$prod][$descKey] = $descVal;

                                    $prod++;
                                }
                            }

                            $mainDataArr['productdescriptiontext'] = $descKeyValRaw;
                        }
                    }
                } elseif ($xpathDetailPage->query("//div[@id='tab_pr_deskl']//div[contains(@class,'sp-tab-content')]/p")->length > 0) {
                    $descKeyValObj = $xpathDetailPage->query("//div[@id='tab_pr_deskl']//div[contains(@class,'sp-tab-content')]/p");

                    $path = "//div[@id='tab_pr_deskl']//div[contains(@class,'sp-tab-content')]/p";
                    if ($descKeyValObj->length < 2) {
                        $path = "//div[@id='tab_pr_deskl']//div[contains(@class,'sp-tab-content')]/strong";
                        $descKeyValObj = $xpathDetailPage->query($path);
                    }
                    if ($descKeyValObj->length > 0) {
                        for ($d = 0; $d < $descKeyValObj->length; $d++) {

                            $htmlDescCounter = $d + 1;

                            $descKeyValNewObj = $xpathDetailPage->query($path . "[$htmlDescCounter]");

                            $descKeyValRaw = $this->getInnerHTML($descKeyValNewObj);

                            if (stristr($descKeyValRaw, "Category: ") || (stristr($descKeyValRaw, "Color: "))) {
                                $prod = 0;

                                if (!empty($descKeyValRaw)) {

                                    $descParts = explode('<br', $descKeyValRaw);

                                    foreach ($descParts as $descValue) {
                                        if (!empty($descValue) && strpos($descValue, ':')) {
                                            $descValue = $this->trimCustom(strip_tags($descValue));
                                            $descValue = str_replace("&nbsp;", "", $descValue);
                                            $singleDescParts = explode(":", strip_tags($descValue));
                                            $descKey = "";
                                            if (isset($singleDescParts[0])) {
                                                $descKey = $singleDescParts[0];
                                                $descKey = str_replace(array('data-mce-fragment="1"', '>'), "", $descKey);
                                            }
                                            $descVal = "";
                                            if (isset($singleDescParts[1])) {
                                                $descVal = $this->trimCustom($singleDescParts[1]);
                                            }
                                            if ($descKey && $descVal) {
                                                $mainDataArr['productdescription'][$prod][$descKey] = $descVal;
                                                $prod++;
                                            }
                                        }
                                    }
                                    $mainDataArr['productdescriptiontext'] = $descKeyValRaw;
                                }
                            } else {
                                if (trim(strip_tags($descKeyValRaw), '&nbsp;')) {
                                    $mainDataArr['productdescription'][][''] = trim(strip_tags($descKeyValRaw), '&bull;');
                                }


                            }
                        }
                    }
                } else {
                    $descDom = $xpathDetailPage->query("//div[@id='shopify-section-pr_description']");
                    if ($descDom->length) {
                        $mainDataArr['productdescription'][0] = $descDom->nodeValue;
                    }
                }
                $breadcrumbDom = $xpathDetailPage->query("//nav[@class='sp-breadcrumb']//a");
                $breadcrumbName = "";
                $breadcrumbUrl = "";
                $bredCounter = 0;
                if ($breadcrumbDom->length > 0) {
                    for ($bre = 0; $bre < $breadcrumbDom->length; $bre++) {
                        $breadcrumbName = $this->trimCustom($breadcrumbDom->item($bre)->nodeValue);
                        $breadcrumbUrl = $this->trimCustom($breadcrumbDom->item($bre)->getAttribute("href"));

                        $mainDataArr['breadcrumb'][$bredCounter] = array(
                            "name" => $breadcrumbName,
                            "url" => $breadcrumbUrl,);
                        $bredCounter++;
                    }
                }
                $mainDataArr['options'] = [];
                $colorsDom = $xpathDetailPage->query("//div[@data-opname='color']//ul//li");
                $colors = "";
                if ($colorsDom->length > 0) {
                    for ($colr = 0; $colr < $colorsDom->length; $colr++) {
                        $colors = $this->trimCustom($colorsDom->item($colr)->nodeValue);

                        $mainDataArr['options']['Color'][$colr] = $colors;
                    }
                }

                $sizesDom = $xpathDetailPage->query("//div[@data-opname='size']//ul//li");
                $sizes = "";
                if ($sizesDom->length > 0) {
                    for ($sz = 0; $sz < $sizesDom->length; $sz++) {
                        $sizes = $this->trimCustom($sizesDom->item($sz)->nodeValue);
                        $mainDataArr['options']['Size'][$sz] = $sizes;
                    }
                }


                $variationsRawDom = $this->extractor($DetailPageHTML['EXE'], "var meta = ", "}};");
                $variationsRaw = $variationsRawDom . "}}";

                $variationsJSON = json_decode($variationsRaw, TRUE);

                $relatedCOunter = 0;
                if (!empty($variationsJSON)) {
                    foreach ($variationsJSON['product']['variants'] as $variationsDataArr) {

                        $SKUId = "";
                        if (isset($variationsDataArr['sku'])) {
                            $SKUId = $variationsDataArr['sku'];
                        }

                        $typeID = "";
                        if (isset($variationsDataArr['id'])) {
                            $typeID = $variationsDataArr['id'];
                        }

                        $varColor = "";
                        $varSize = "";

                        $prdt_image = $singImage;

                        $prdt_price = "";
                        if (isset($variationsDataArr['price'])) {
                            $prdt_price = $variationsDataArr['price'];
                            $prdt_price = ($prdt_price / 100);
                        }


                        $related_productsUrl = strtok($url, "?") . "?variant=" . $typeID;

                        $prdt_stock = "";

                        $relatedPageHTML = $this->getContent($related_productsUrl);
                        if (empty($relatedPageHTML['ERR'])) {

                            $doc_related = new DOMDocument();
                            @$doc_related->loadHTML($relatedPageHTML['EXE']);
                            $xpathRelatedPage = new DOMXPath($doc_related);


                            $prdt_stockDom = $xpathRelatedPage->query("//input[@class='input-text qty text tc qty_pr_js']/@max");
                            if ($prdt_stockDom->length > 0) {
                                $prdt_stock = $this->trimCustom($prdt_stockDom->item(0)->nodeValue);
                            }


                            $varColorDom = $xpathRelatedPage->query("//div[@data-opname='color']//span[contains(@class,'nt_name_current')]");
                            if ($varColorDom->length > 0) {
                                $varColor = $this->trimCustom($varColorDom->item(0)->nodeValue);
                            }

                            $varSizeDom = $xpathRelatedPage->query("//div[@data-opname='size']//span[contains(@class,'nt_name_current')]");
                            if ($varSizeDom->length > 0) {
                                $varSize = $this->trimCustom($varSizeDom->item(0)->nodeValue);
                            }
                        }


                        if (!empty($varColor) && (!empty($varSize))) {

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
                        }

                        if (!empty($varColor) && (empty($varSize))) {
                            $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $SKUId;
                            $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $SKUId;
                            $mainDataArr['variants'][$relatedCOunter]['typeID'] = $typeID;
                            $mainDataArr['variants'][$relatedCOunter]['variantName'] = $varColor;
                            $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $prdt_image;
                            $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $prdt_price;

                            $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                                "Color" => $varColor,
                            );
                        }

                        if (empty($varColor) && (!empty($varSize))) {

                            $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $SKUId;
                            $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $SKUId;
                            $mainDataArr['variants'][$relatedCOunter]['typeID'] = $typeID;
                            $mainDataArr['variants'][$relatedCOunter]['variantName'] = $varSize;
                            $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $prdt_image;
                            $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $prdt_price;
                            $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                                "Size" => $varSize,
                            );
                        }


                        $mainDataArr['variants'][$relatedCOunter]['inventory'] = $prdt_stock ?: 0;
                        $relatedCOunter++;
                    }
                }

                return $mainDataArr;
            }
        } else {
            echo 'URLNotOpen: ' . $url . $lb;
        }
    }

    protected function getContent($url)
    {

        $productUrl = new ProductUrl(compact('url'));
        $content = $productUrl->getPageContent();

        $result = [];
        $result['EXE'] = $content;

        return $result;
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