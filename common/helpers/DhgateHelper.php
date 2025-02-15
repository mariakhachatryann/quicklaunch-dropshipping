<?php

namespace common\helpers;

use DOMDocument;
use DOMXPath;
use yii\helpers\ArrayHelper;

class DhgateHelper extends BaseScrapHelper
{


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

    protected function getDataFromContent(string $url, string $content): array
    {

        $batch = [];
        $batch1 = [];
        $batch2 = [];
        $batch3 = [];
        $batch4 = [];
        $batch5 = [];
        $batch6 = [];
        $batch7 = [];

        $html = $this->QueryGET($url);


        $d = new DOMDocument();
        @$d->loadHTML($html);
        //libxml_clear_errors();
        $xpath = new DOMXPath($d);
        $ls_ad = $xpath->query('//*[@id="productdisplayForm"]/div/div[1]/div/h1');
        $batch1['title'] = isset($ls_ad->item(0)->nodeValue) ? $ls_ad->item(0)->nodeValue : '';


        $ls_ad = $xpath->query('//*[@id="productdisplayForm"]/div/div[1]/ul/li/div[2]/div/div[2]/div[1]/span[1]');
        $batch1['rating'] = isset($ls_ad->item(0)->nodeValue) ? $ls_ad->item(0)->nodeValue : '';

        $ls_ad = $xpath->query('//*[@id="productdisplayForm"]/div/div[1]/ul/li/div[3]/span/a/span/b');
        $batch1['reviews'] = isset($ls_ad->item(0)->nodeValue) ? $ls_ad->item(0)->nodeValue : '';

        $ls_ad = $xpath->query('//*[@id="productdisplayForm"]/div/div[1]/ul/li/span[2]/span/b');
        $batch1['transactions'] = isset($ls_ad->item(0)->nodeValue) ? $ls_ad->item(0)->nodeValue : '';

        $d = new DOMDocument();
        @$d->loadHTML($html);
        $xpath = new DOMXPath($d);
        $ls_ad = $xpath->query('//*[@id="productdisplayForm"]/div/div[@class="wprice-wrap clearfix"]/div/div[@class="lineprice clearfix j-line-price "]/div[@class="wprice-list js-wholesale-box"]/ul/li');
        $first = 0;
        $last = $ls_ad->length;
        $last = $last - 1;

        if ($ls_ad->length != 0) {
            $ls_ads = $xpath->query('//*[@id="productdisplayForm"]/div/div[@class="wprice-wrap clearfix"]/div/div[@class="lineprice clearfix j-line-price "]/div[@class="wprice-list js-wholesale-box"]/ul/li/span[1]');
            $max = $ls_ads->item($first)->nodeValue;
            $batch1['max_price'] = str_replace(['$', ','], '', $max);

        }
        if ($ls_ad->length != 0) {
            $ls_ads = $xpath->query('//*[@id="productdisplayForm"]/div/div[@class="wprice-wrap clearfix"]/div/div[@class="lineprice clearfix j-line-price "]/div[@class="wprice-list js-wholesale-box"]/ul/li/span[1]');
            $min = $ls_ads->item($last)->nodeValue;
            $batch1['min_price'] = str_replace(['$', ','], '', $min);

        } else {
            $ls_ad = $xpath->query('//*[@id="productdisplayForm"]/div/div[@class="wprice-wrap clearfix"]/div/div[@class="lineprice wprice-line-not clearfix "]/div/span[1]');
            $min = $ls_ad->item(0)->nodeValue;
            $max = $ls_ad->item(0)->nodeValue;
            $batch1['min_price'] = str_replace(['$', ','], '', $min);
            $batch1['max_price'] = str_replace(['$', ','], '', $max);

        }

        $d = new DOMDocument();
        @$d->loadHTML($html);
        //libxml_clear_errors();
        $xpath = new DOMXPath($d);
        $ls_ads = $xpath->query('//div[@class="bread-crumbs-inner"]/a');
        if ($ls_ads->length != 0) {
            foreach ($ls_ads as $node) {

                $ad_Doc = new DOMDocument();
                $cloned = $node->cloneNode(TRUE);
                $ad_Doc->appendChild($ad_Doc->importNode($cloned, True));
                $xpath = new DOMXPath($ad_Doc);

                $ls_ad = $xpath->query('//a');
                $url = $ls_ad->item(0)->getAttribute('href');

                $ur = explode('.html', $url);
                $num = $ur[0];
                $string_ar = explode('/', $num);
                $cateId = end($string_ar);

                $ls_ad = $xpath->query('//a');
                $name = $ls_ad->item(0)->nodeValue;

                $batch5 = [
                    'cateId' => $cateId,
                    'name' => $name,
                    'remark' => '',
                    'url' => $url,
                ];

            }
        }


        $d = new DOMDocument();
        @$d->loadHTML($html);
        //libxml_clear_errors();
        $xpath = new DOMXPath($d);

        $ls_ads = $xpath->query('//*[@id="productdisplayForm"]/div/div[10]/div/ul/li');
        if ($ls_ads->length != 0) {
            foreach ($ls_ads as $node) {

                $ad_Doc = new DOMDocument();
                $cloned = $node->cloneNode(TRUE);
                $ad_Doc->appendChild($ad_Doc->importNode($cloned, True));
                $xpath = new DOMXPath($ad_Doc);

                $ls_ad = $xpath->query('//li');
                $col = $ls_ad->item(0)->getAttribute('data-pic0x0url');

                $ls_ad = $xpath->query('//li');
                $attrid = $ls_ad->item(0)->getAttribute('data-attrid');

                $ls_ad = $xpath->query('//li');
                $attrvalueid = $ls_ad->item(0)->getAttribute('data-attrvalueid');


                $batch3['imagess'] = $col;
                $color = implode(',', $batch3);
                $batch3['color'] = $color;
                unset($batch3['imagess']);

            }
        }

        $d = new DOMDocument();
        @$d->loadHTML($html);
        //libxml_clear_errors();
        $xpath = new DOMXPath($d);

        $ls_ads = $xpath->query('//*[@id="productdisplayForm"]/div/div[8]/div/div[1]/ul/li');


        if ($ls_ads->length != 0) {
            foreach ($ls_ads as $node) {

                $ad_Doc = new DOMDocument();
                $cloned = $node->cloneNode(TRUE);
                $ad_Doc->appendChild($ad_Doc->importNode($cloned, True));
                $xpath = new DOMXPath($ad_Doc);

                $ls_ad = $xpath->query('//span');
                $siz = $ls_ad->item(0)->nodeValue;


                $batch2['imagess'] = $siz;
                $size = implode(',', $batch2);
                $batch2['sizes'] = $size;
                unset($batch2['imagess']);

            }
        }

        $d = new DOMDocument();
        @$d->loadHTML($html);
        //libxml_clear_errors();
        $xpath = new DOMXPath($d);
        $ls_ads = $xpath->query('//ul[@id="simgListH"]/li');
        if ($ls_ads->length != 0) {
            foreach ($ls_ads as $node) {

                $ad_Doc = new DOMDocument();
                $cloned = $node->cloneNode(TRUE);
                $ad_Doc->appendChild($ad_Doc->importNode($cloned, True));
                $xpath = new DOMXPath($ad_Doc);

                $ls_ad = $xpath->query('//span/img');
                $images = $ls_ad->item(0)->getAttribute('src');

                $batch['imagess'] = $images;
                $imagss = implode(',', $batch);
                $batch['productimages'] = $imagss;
                unset($batch['imagess']);

            }
        }


        $d = new DOMDocument();
        @$d->loadHTML($html);
        //libxml_clear_errors();
        $xpath = new DOMXPath($d);
        $attirbute = $xpath->query('//*[@id="cur-contont-desc"]/div[1]/ul/li');
        if ($attirbute->length != 0) {
            foreach ($attirbute as $node) {
                $ad_Doc = new DOMDocument();
                $cloned = $node->cloneNode(TRUE);
                $ad_Doc->appendChild($ad_Doc->importNode($cloned, True));
                $xpath = new DOMXPath($ad_Doc);

                $ls_ad = $xpath->query('//li');
                $des = $ls_ad->item(0)->nodeValue;
                $desc = explode(':', $des);
                $type = $desc[0];
                $value = $desc[1];
                $batch4[] = [$type => trim($value)];

                if ($type == 'Item Code') {
                    $batch7['item_code'] = $value;
                }

            }
        }

        $urll = 'https://www.dhgate.com/prod/ajax/pcsku.do?client=pc&language=en&version=0.1&itemCode=' . $batch7['item_code'];
        $varientdata = $this->QueryGET($urll);
        if (!empty($varientdata)) {
            $varientdata = json_decode($varientdata);
        }


        $option_names = [];
        $options_order_numbers = [];
        $options_order = [];
        $options_arr = [];
        $image_variable = 0;
        $images = [];

        foreach ($varientdata->data->itemAttrList as $key => $varient) {

            $varient_name = $varient->attrName;
            $option_names[] = $varient_name;

            foreach ($varient->itemAttrvalList as $variation_number => $varient_options) {

                if ($varient_options->islinkImg == 1) {
                    $image_variable = $key;
                    $images[$varient_options->attrValueId] = $varient_options->picUrl;
                }
                $options_order[$varient_name][] = $varient_options->attrValName;
                $options_arr[$key][$varient_options->attrValueId] = $varient_options->attrValName;
                $options_order_numbers[$key][$varient_options->attrValueId] = $variation_number;
            }

        }

        $formatted_varients = [];

        foreach ($varientdata->data->itemSkuRelAttr as $row) {

            $skuAttrVals_pieces = explode('_', $row->skuAttrVals);
            $variantName = '';
            $fulfillName = [];

            foreach ($skuAttrVals_pieces as $option_number => $piece) {
                $variantName .= $options_arr[$option_number][$piece] . ",";
                $fulfillName[$option_names[$option_number]] = $options_order_numbers[$option_number][$piece];
            }

            $variantName = substr($variantName, 0, -1);;


            $vaient_image = @$images[$skuAttrVals_pieces[$image_variable]];


            $formatted_varients[] = [
                'SKUId' => $row->skuId,
                'SKUId_old' => @$row->skuMd5,
                'typeID' => @$row->skuAttrVals,

                'variantName' => $variantName,

                'fulfillName' => $fulfillName,

                'variantImages' => $vaient_image,
                'skuPrice' => @$batch1['min_price'],

                'available' => @$row->minInventoryNumFlag,
                'inventory' => @$row->inventoryNum,
            ];

        }


        $data = [
            'productName' => $batch1['title'],
            'priceHigh' => $batch1['min_price'],
            'subcategory' => $batch5['name'],
            'priceLow' => $batch1['max_price'],
            'productID' => $batch7['item_code'],
            'url' => $url,
            'productimages' => explode(',', $batch['productimages']),
            'productdescription' => $batch4,

            'ratings' => @$batch1['reviews'],
            'rating' => @$batch1['rating'],


            'options' => $options_order,

            'variants' => $formatted_varients,


        ];

        return $data;
    }

    public function QueryGET($url, $params = [], $referer = '', $headers = [])
    {

        $agents = array(
            'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2486.0 Safari/537.36 Edge/13.10586',
            'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
            'Mozilla/5.0 (Windows NT 6.3; WOW64; rv:53.0) Gecko/20100101 Firefox/53.0',
            'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko',
            'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36'
        );

        $retrys = 0;
        retry:
        $curl = curl_init();

        //Define the options for curl
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:47.0) Gecko/20100101 Firefox/47.0',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => 0,
            //CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => $headers,
            //CURLOPT_REFERER => $referer,
            //CURLOPT_REFERER => 'https://www.chileautos.cl/autos-camionetas-y-4x4-veh%C3%ADculo?s=0&l=60',
            CURLOPT_ENCODING => ''
        ));
        $output = curl_exec($curl);

        return $output;
    }


}

