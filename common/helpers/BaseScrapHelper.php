<?php


namespace common\helpers;


use common\models\AvailableSite;

abstract class BaseScrapHelper
{
    protected bool $convertToInt = true;
    protected string $siteName = '';

    
    abstract protected function getDataFromContent(string $url, string $content): array;
    
    public function getProductVariants(string $content, string $url): array
    {
        $aliData = $this->getDataFromContent($url, $content);
        $variants = [];
        if ($aliData) {
            if (!empty($aliData['variants'])) {
                foreach ($aliData['variants'] as $variantData) {
                    $variants[] = [
                        'default_sku' => $variantData['SKUId_old'] ?? $variantData['SKUId'],
                        'sku' => $variantData['SKUId_old'] ?? $variantData['SKUId'],
                        'price' => $variantData['skuPrice'],
                        'compare_at_price' => $variantData['skuPrice'],
                        'inventory_quantity' => $variantData['inventory'],
                    ];
                }
            } else {
                $variants[] = [
                    'default_sku' => $aliData['productID'],
                    'sku' => $aliData['productID'],
                    'price' => isset($aliData['skuPrice']) ? $aliData['skuPrice'] : $aliData['price'],
                    'compare_at_price' => isset($aliData['skuPrice']) ? $aliData['skuPrice'] : $aliData['price'],
                    'inventory_quantity' => isset($aliData['inventory']) ? $aliData['inventory'] : $aliData['stock'],
                ];
            }
        } else {
            $variants = [];
        }


        return [
            'success' => 1,
            'data' => compact('variants')
        ];
    }

    public function removeEmojisFromString(string $emojiString)
    {
        if (!$emojiString) {
            return '';
        }
        $pattern = "~[^a-zA-Z0-9_ !@#$%^&*();\\\/|<>\"'+.,:?=-]~";
        return preg_replace($pattern, '', $emojiString);
    }

    protected function getOptions(array $aliData): array
    {
        $options = [];
        if (!empty($aliData['options'])) {
            foreach ($aliData['options'] as $optionName => $optionData) {
                $options[] = [
                    'name' => $optionName,
                    'values' => $optionData
                ];
            }
        }
        return $options;
    }

    protected function getDescription(array $descriptionItems): array
    {
        $descriptionData = [];

        foreach ($descriptionItems as $descriptionItem) {
            foreach ($descriptionItem as $name => $value) {
                $descriptionData[] = [
                    'attr_name' => $name,
                    'attr_value' => $value
                ];
            }
        }
        return $descriptionData;
    }

    protected function getVariants(array $aliData): array
    {

        $variantNameIndex = 'SKUId_old';
        $variants = $this->initFirstVariant($aliData['options']);
        if (!empty($aliData['variants'])) {
            $variantImageExists = false;
            foreach ($aliData['variants'] as $variantData) {
                $variant = [];
                if (isset($variantData['variantImages']) && $variantData['variantImages']) {
                    $variantImageExists = true;
                    $variant[] = [
                        'type' => 'img',
                        'name' => $variantData['variantImages'],
                    ];
                }
                $options = $aliData['options'] ?? [];

                $variant = $this->getVariantName($variant, $options, $variantData);

                $variant[] = [
                    'type' => 'text',
                    'name' => $variantData[$variantNameIndex],
                    'input' => 1
                ];

                $variant[] = [
                    'type' => 'text',
                    'name' => $variantData['skuPrice'],
                    'input' => 1
                ];

                $variant[] = [
                    'type' => 'text',
                    'name' => $variantData['skuPrice'],
                    'input' => 1
                ];

                $variant[] = [
                    'type' => 'text',
                    'name' => $variantData['inventory'],
                    'input' => 1
                ];

                $variant[] = [
                    'type' => 'text',
                    'name' => $variantData[$variantNameIndex],
                    'input' => 1
                ];

                $variants[] = $variant;
            }
            if (!empty($variants) && !$variantImageExists) {
                array_shift($variants[0]);
            }
        }
        return $variants;
    }

    public static function getOptionNames($onlyImages, $optionNames): array
    {
        $defaultOptionNames = ['SKU', 'Price', 'CompareAtPrice', 'Quantity'];

        if (!empty($onlyImages)) {
            return array_merge(['IMG'], $optionNames, $defaultOptionNames);
        } elseif (empty($optionNames)) {
            return array_merge(['Title'], $optionNames, $defaultOptionNames);
        } else {
            return array_merge($optionNames, $defaultOptionNames);
        }

    }

    protected function initFirstVariant(array $options): array
    {
        $variants = [];

        $firstVariant = [
            [
                'type' => 'text',
                'name' => 'IMG',
                'input' => ''
            ]
        ];

        foreach (array_keys($options) as $option) {
            $firstVariant[] = [
                'type' => 'text',
                'name' => $option,
                'input' => ''
            ];
        }

        $fields = ['SKU', 'Price', 'CompareAtPrice', 'Quantity'];
        foreach ($fields as $field) {
            $firstVariant[] = [
                'type' => 'text',
                'name' => $field,
                'input' => ''
            ];
        }

        $variants[] = $firstVariant;

        return $variants;
    }

    protected function getVariantName($variant, $options, $variantData)
    {
        foreach ($options as $optionName => $optionValues) {
            $variantOptions = $variantData['fulfillName'];
            if (!$this->convertToInt) {
                $option = $variantOptions[$optionName];
            } else {
                $variantOptionKey = intval($variantOptions[$optionName]);
                $option = $optionValues[$variantOptionKey];
            }
            $variant[] = [
                'type' => 'text',
                'name' => $option,
                'input' => 1
            ];
        }
        return $variant;
    }
    
    /**
     *
     * Remove extra spaces but not space
     *
     * @param String $str
     * @return String
     */
    protected function trimCustom($str)
    {
        return trim(preg_replace('/\s\s+/', ' ', str_replace("\n", " ", $str)));
    }
    
    public function extractor($str, $from, $to)
    {
        $from_position = strpos($str, $from);
        $from_pos = $from_position + strlen($from);
        $to_pos = strpos($str, $to, $from_pos); // to must be after from
        $return = substr($str, $from_pos, $to_pos - $from_pos);
        unset($str, $from, $to, $from_pos, $to_pos);
        return $return;
    }
}