<?php

namespace common\helpers;

use common\models\AvailableSite;
use DOMDocument;
use DOMXPath;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use simplehtmldom\HtmlWeb;


class MiniintheboxHelper extends BaseScrapHelper
{
    protected bool $convertToInt = false;
    protected string $siteName = AvailableSite::SITE_MINIINTHEBOX;

    const TYPE_COLOR = 1;

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
            'product_type' => $aliData['subcategory'] ?? '',
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

    protected function getDataFromContent(string $url, string $content): array
    {

        $urlParts = explode("_p", $url);
        $id = end($urlParts);
        $id = strtok($id, ".html");

        $base_irl = 'https://litb-cgis.rightinthebox.com/';

        // Load the page into memory
        $doc = new HtmlWeb();
        $html = $doc->load($url);

        //data
        $data = $html->find('script[id=_prodInfoConfig_]', 0)->getAttribute('data-config') ?? [];
        $data = json_decode($data, true);

        $info = $data[$id] ?? array_values($data)[0];


        //category
        $category = $html->find('li[class="curr-category"] a', 0)->plaintext ?? null;

        $breadcrumb_data = $html->find('#breadcrumb ul li');
        $breadcrumb = [];
        foreach ($breadcrumb_data as $item) {
            $tag = $item->getElementByTagName('a');
            $breadcrumb[] = [
                'name' => str_replace(['>', '<', '/b'], "", $tag->innertext),
                'url' => $tag->getAttribute('href'),
            ];
        }

        //descriptions
        $descriptions = $html->find('#productOverview .spec-item');

        $desc = [];
        if (!empty($descriptions)) {
            foreach ($descriptions as $description) {
                $desc[trim($description->children(0)->plaintext, ":")] =
                    trim($description->children(1)->plaintext, ",");
            }
        }

        $descriptionData = [];
        foreach ($desc as $name => $value) {
            $descriptionData[] = [
                'attr_name' => $name,
                'attr_value' => $value
            ];
        }

        $all_images = $html->find('#productOverview picture') ?? [];
        $images = [];
        if (!empty($all_images)) {
            foreach ($all_images as $all_image) {
                $images[] = $all_image->getAttribute('data-origin');
            }
        }


        $img_collection = $html->find('li a picture img');

        $img_colors = [];
        foreach ($img_collection as $coll) {
            if ($coll->hasAttribute('attribute_id')) {
                $attribute_id = $coll->getAttribute('attribute_id');
                $img_colors[$attribute_id] = $coll->getAttribute('data-normal');
            }
        }

        $attributes = [];
        if (!empty($info['attributes'])) {

            foreach ($info['attributes'] as $key => $attribute) {
                if ($attribute['id'] == self::TYPE_COLOR) {
                    $attributes[] = [
                        'id' => $attribute['id'],
                        'name' => $attribute['name'],
                        'items' => $attribute['items'],
                    ];
                }

                if (!empty($attributes)) {
                    if (isset($info['attributes'][$key + 1])) {
                        $attributes[] = [
                            'id' => $info['attributes'][$key + 1]['id'],
                            'name' => $info['attributes'][$key + 1]['name'],
                            'items' => $info['attributes'][$key + 1]['items'],
                        ];
                        break;
                    } elseif (isset($info['attributes'][$key - 1])) {
                        $attributes[] = [
                            'id' => $info['attributes'][$key - 1]['id'],
                            'name' => $info['attributes'][$key - 1]['name'],
                            'items' => $info['attributes'][$key - 1]['items'],
                        ];
                        break;
                    }
                } elseif (count(array_keys($info['attributes'])) == ($key + 1)) {
                    $attributes[] = [
                        'id' => $attribute['id'],
                        'name' => $attribute['name'],
                        'items' => $attribute['items'],
                    ];
                    if (isset($info['attributes'][$key - 1])) {
                        $attributes[] = [
                            'id' => $info['attributes'][$key - 1]['id'],
                            'name' => $info['attributes'][$key - 1]['name'],
                            'items' => $info['attributes'][$key - 1]['items'],
                        ];
                    }
                    break;
                }
            }
        }

        $options = [];
        foreach ($attributes as $attribute) {
            $options[$attribute['name']] = [];
            foreach ($attribute['items'] as $item) {
                $options[$attribute['name']][$item['id']] = $item['name'];
            }
        }

        $option_names = array_keys($options);

        $mainDataArr = [];
        $counter = 0;
        for ($i = 0; $i < count($attributes) - 1; $i++) {
            foreach ($attributes[$i]['items'] as $item) {
                foreach ($attributes[$i + 1]['items'] as $it) {

                    if (!empty($info['quantities'])) {
                        $quantity = 0;
                        foreach ($info['quantities'] as $q) {
                            $prod = explode('|', $q[0]);
                            $quant1 = $attributes[$i]['id'] . '_' . $item['id'];
                            $quant2 = $attributes[$i + 1]['id'] . '_' . $it['id'];

                            if (in_array($quant1, $prod) && in_array($quant2, $prod)) {
                                $quantity = $q[1];
                            }
                        }
                    } else {
                        $quantity = $info['quantity'];
                    }

                    if ($quantity == 0) {
                        continue;
                    }

                    $mainDataArr[$counter]['fulfillName'] = array(
                        $attributes[$i]['name'] => $item['name'],
                        $attributes[$i + 1]['name'] => $it['name'],
                    );

                    $mainDataArr[$counter]['typeID'] = $id;
                    $mainDataArr[$counter]['variantName'] = $item['name'] . "," . $it['name'];
                    $mainDataArr[$counter]['SKUId_old'] = $item['id'] . "_" . $it['id'];
                    $mainDataArr[$counter]['SKUId'] = $item['id'] . "_" . $it['id'];
                    $mainDataArr[$counter]['variantImages'] = $img_colors[$item['id']] ?? $base_irl . $info['image'];
                    $mainDataArr[$counter]['skuPrice'] = $item['price'] + $it['price'] + $info['salePrice'];
                    $mainDataArr[$counter]['inventory'] = $quantity;
                    $counter++;
                }
            }
        }

        $return_data = [
//            'url' => $base_irl . $info['products_url'],
            'url' => $url,
            'productName' => $info['original_name'],
            'productID' => $info['id'],
            'price' => $info['salePrice'],
            'sku' => $info['id'],
            'productimages' => $images,
            'options' => $options,
            'productdescription' => $desc,
            'breadcrumb' => $breadcrumb,
            'variants' => $mainDataArr,
            'priceHigh' => isset($info['delPrice']) ? $info['delPrice'] : $info['salePrice'],
            'subcategory' => $category,
            'inventory' => $aliData['variants'][0]['inventory'] ?? 0

        ];

        return $return_data;
    }

    protected function getDescription(array $descriptionItems): array
    {
        $descriptionData = [];

        foreach ($descriptionItems as $name => $value) {
            $descriptionData[] = [
                'attr_name' => $name,
                'attr_value' => $value
            ];
        }

        return $descriptionData;
    }

}
