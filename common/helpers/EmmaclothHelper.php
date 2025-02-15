<?php

namespace common\helpers;

use common\models\AvailableSite;
use common\models\ProductUrl;
use DOMDocument;
use DOMElement;
use DOMXPath;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class EmmaclothHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_EMMACLOTH;


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
            'price' => $aliData['priceHigh'],
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

    protected function getDataFromContent(string $url, string $content = ''): array
    {
        $baseUrl = "https://www.emmacloth.com";

        $doc = new DOMDocument();
        @$doc->loadHTML($content);
        $xpathDetailPage = new DOMXPath($doc);

        $mainDataArr = array();

        $JSONDom = $xpathDetailPage->query("//script[@id='ProductJson-product-template']");
        if ($JSONDom->length > 0) {

            $JSONRAW = $this->trimCustom($JSONDom->item(0)->nodeValue);

            $JSON = json_decode($JSONRAW, TRUE);

            $Name = "";
            if (isset($JSON['title'])) {
                $Name = $JSON['title'];
            }

            $mainDataArr['url'] = $url;
            $mainDataArr['productName'] = $Name;


            $id = "";
            if (isset($JSON['id'])) {
                $id = $JSON['id'];
            }

            $mainDataArr['productID'] = $id;
            $price_min = "";
            if (isset($JSON['price_min'])) {
                $price_min = $JSON['price_min'];
            }
            $mainDataArr['priceLow'] = $price_min / 100;

            $price_max = "";
            if (isset($JSON['price_max'])) {
                $price_max = $JSON['price_max'];
            }
            $mainDataArr['priceHigh'] = $price_max / 100;

            $images = "";
            $singImage = "";
            if (isset($JSON['images'])) {
                $img = 0;
                foreach ($JSON['images'] as $imagsSing) {

                    if ($img == 0) {
                        $singImage = "https:" . $imagsSing;
                    }

                    $mainDataArr['productimages'][$img] = "https:" . $imagsSing;
                    $img++;
                }
            }

            $description = "";
            if (isset($JSON['description'])) {
                $description = $JSON['description'];
            }

            if (!empty($description)) {
                $descParts = explode("<br>", $description);
                $prod = 0;
                foreach ($descParts as $descValue) {

                    if (!empty($descValue)) {

                        $singleDesc = strip_tags($descValue);
                        $singleDescParts = explode(": ", $singleDesc);

                        $mainDataArr['productdescription'][$prod][$singleDescParts[0]] = $singleDescParts[1] ?? '';

                        $prod++;
                    }
                }
                $mainDataArr['productdescriptiontext'] = $description;
            }

            $breadcrumbDom = $xpathDetailPage->query("//nav[@class='breadcrumb']//span");
            foreach ($breadcrumbDom as $item) {
                /**@var DOMElement $item */
                $breadcrumbName = $this->trimCustom($item->nodeValue);
                $breadcrumbUrl = $baseUrl . $this->trimCustom($item->getAttribute("href"));

                $mainDataArr['breadcrumb'][] = [
                    "name" => $breadcrumbName,
                    "url" => $breadcrumbUrl,
                ];
            }

            $colorsArr = array();
            $sizesArr = array();

            $relatedCOunter = 0;
            if (!empty($JSON['variants'])) {
                foreach ($JSON['variants'] as $variationsDataArr) {

                    $SKUId = "";
                    if (isset($variationsDataArr['sku'])) {
                        $SKUId = $variationsDataArr['sku'];
                    }

                    $typeID = "";
                    if (isset($variationsDataArr['id'])) {
                        $typeID = $variationsDataArr['id'];
                    }

                    $varColor = "";
                    if (isset($variationsDataArr['option1'])) {

                        $varColor = $variationsDataArr['option1'];

                        if (!in_array($varColor, $colorsArr)) {
                            $colorsArr[] = $varColor;
                        }
                    }

                    $varSize = "";
                    if (isset($variationsDataArr['option2'])) {
                        $varSize = $variationsDataArr['option2'];

                        if (!in_array($varSize, $sizesArr)) {
                            $sizesArr[] = $varSize;
                        }
                    }

                    $prdt_image = $singImage;

                    $prdt_price = "";
                    if (isset($variationsDataArr['price'])) {
                        $prdt_price = $variationsDataArr['price'] / 100;
                    }

                    $prdt_stock = 100;

                    $mainDataArr['variants'][$relatedCOunter]['SKUId'] = $SKUId;
                    $mainDataArr['variants'][$relatedCOunter]['SKUId_old'] = $SKUId;
                    $mainDataArr['variants'][$relatedCOunter]['typeID'] = $typeID;
                    $mainDataArr['variants'][$relatedCOunter]['variantName'] = $varColor . "," . $varSize;
                    $mainDataArr['variants'][$relatedCOunter]['variantImages'] = $prdt_image;
                    $mainDataArr['variants'][$relatedCOunter]['skuPrice'] = $prdt_price;


                    if (!empty($varColor) && (!empty($varSize))) {
                        $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                            "Color" => $varColor,
                            "Size" => $varSize,
                        );
                    }

                    if (!empty($varColor) && (empty($varSize))) {
                        $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                            "Color" => $varColor,
                        );
                    }

                    if (empty($varColor) && (!empty($varSize))) {
                        $mainDataArr['variants'][$relatedCOunter]['fulfillName'] = array(
                            "Size" => $varSize,
                        );
                    }


                    $mainDataArr['variants'][$relatedCOunter]['inventory'] = $prdt_stock;
                    $relatedCOunter++;
                }
            }
            $mainDataArr['options'] = [];
            if (!empty($colorsArr)) {
                $colr = 0;
                foreach ($colorsArr as $colors) {
                    $mainDataArr['options']['Color'][$colr] = $colors;
                    $colr++;
                }
            }

            if (!empty($sizesArr)) {
                $sz = 0;
                foreach ($sizesArr as $sizes) {
                    $mainDataArr['options']['Size'][$sz] = $sizes;
                    $sz++;
                }
            }
            return $mainDataArr;
        }

    }

}
