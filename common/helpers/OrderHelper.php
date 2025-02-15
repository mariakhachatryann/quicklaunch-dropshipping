<?php

namespace common\helpers;

use common\models\Product;

class OrderHelper
{
    const DEFAULT_LIMIT = 250;
    const DEFAULT_CREATED_AT_MAX = '+1 day';
    const DEFAULT_CREATED_AT_MIN = '-1 month';

    const STATUS_PAID = 'paid';
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PENDING = 'pending';
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_PARTIALLY_PAID = 'partially_paid';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_VOIDED = 'voided';
    const STATUS_ANY = 'any';


    const FULFILLMENT_STATUS_ANY = 'any';
    const FULFILLMENT_STATUS_UNFULFILLED = 'unfulfilled';
    const FULFILLMENT_STATUS_SHIPPED = 'shipped';
    const FULFILLMENT_STATUS_UNSHIPPED = 'unshipped';
    const FULFILLMENT_STATUS_PARTIAL = 'partial';


    const STATUS_OPEN = 'open';
    const STATUS_CLOSED = 'closed';

    const FINANCIAL_STATUSES = [
        self::STATUS_PAID => 'Paid',
        self::STATUS_UNPAID => 'Unpaid',
        self::STATUS_PENDING => 'Pending',
        self::STATUS_AUTHORIZED => 'Authorized',
        self::STATUS_PARTIALLY_PAID => 'Partially Paid',
        self::STATUS_REFUNDED => 'Refunded',
        self::STATUS_VOIDED => 'Voided',
        self::STATUS_ANY => 'Any',
    ];
    const STATUSES = [
        self::STATUS_OPEN => 'open',
        self::STATUS_CLOSED => 'closed',
        self::STATUS_ANY => 'Any',
    ];

    const FULFILLMENT_STATUSES = [
        self::FULFILLMENT_STATUS_ANY => 'Any',
        self::FULFILLMENT_STATUS_UNFULFILLED => 'Unfulfilled',
        self::FULFILLMENT_STATUS_SHIPPED => 'Shipped',
        self::FULFILLMENT_STATUS_UNSHIPPED => 'Unshipped',
        self::FULFILLMENT_STATUS_PARTIAL => 'Partial',

    ];


    public function getShopifyOrderMainInfo($order)
    {
        $orderData = [];
        if ($order) {
            $orderData['id'] = $order['id'];
            $customer = $order['customer'];
            if ($customer) {
                $customerInfo = [
                    'customer_firstName' => $order['customer']['firstName'],
                    'customer_lastName' => $order['customer']['lastName'],
                    'customer_email' =>  $order['customer']['email'],
                    'customer_fullName' => $order['customer']['firstName'] . ' ' . $order['customer']['lastName']
                ];
            } else {
                $customerInfo = [];
            }
            
            $orderData['customer'] = $customerInfo;
            $orderData['date'] = $order['createdAt'];
            $orderData['number'] = $order['confirmationNumber'];
            $orderData['total_price'] = $order['currentTotalPriceSet'];
            $orderData['financial_status'] = $order['displayFinancialStatus'];
            $orderData['fulfillment_status'] = $order['displayFulfillmentStatus'];
            $address = $order['shippingAddress'];

            $orderAddress = [];
            if ($address) {
                $orderAddress['address_name'] = $address['name'];
                $orderAddress['address_address1'] = $address['address1'];
                $orderAddress['address_address2'] = $address['address2'];
                $orderAddress['address_city'] = $address['city'];
                $orderAddress['address_country'] = $address['country'];
                $orderAddress['full_address'] = implode(', ', $orderAddress);
                $orderAddress['address_firstName'] = $address['firstName'];
                $orderAddress['address_lastName'] = $address['lastName'];
                $orderAddress['address_company'] = $address['company'];
                $orderAddress['address_province'] = $address['province'];
                $orderAddress['address_zip'] = $address['zip'];
                $orderAddress['address_phone'] = $address['phone'];
                $orderAddress['address_provinceCode'] = $address['provinceCode'];
                $orderAddress['address_countryCode'] = $address['countryCode'];
                $orderAddress['address_countryName'] = $address['country'];
            }
            $orderData['address'] = $orderAddress;
        }

        return $orderData;
    }

    public function getShopifyOrderFullInfo($order)
    {
        $orderData = [];
        if ($order) {

            $orderData = $this->getShopifyOrderMainInfo($order);
            $orderData['subTotal'] = $order['currentSubtotalPriceSet']['shopMoney']['amount'];
            $orderData['totalDiscounts'] = $order['currentTotalDiscountsSet']['shopMoney']['amount'];
            $orderData['lineItems'] = [];

            foreach ($order['lineItems']['edges']['nodes'] as $lineItem) {
                $item = [];
                $item['product_id'] = $lineItem['product']['id'];
                $product = Product::find()->select(['src_product_url'])->where(['shopify_id' => $item['product_id']])->one();
                $item['product_src_url'] = $product ? $product->src_product_url : '';
                $item['title'] = $lineItem['title'];
                $item['quantity'] = $lineItem['quantity'];
                $item['price'] = $lineItem['originalUnitPrice'];
                $item['variant_id'] = $lineItem['variant']['id'];
                $item['variant_title'] = $lineItem['variantTitle'];

                $item['total'] = $item['price'] * $item['quantity'];
                $orderData['lineItems'][] = $item;
            }

        }
        return $orderData;


    }

}