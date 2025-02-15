<?php

namespace common\services;

use GuzzleHttp\Exception\RequestException;

class ShopifyGraphQLService
{
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    public function sendQuery(string $query, array $variables = []): array
    {
        try {
            $response = $this->client->query(['query' => $query, 'variables' => $variables]);
            $responseBody = json_decode($response->getBody()->getContents(), true);
            if (!empty($responseBody['errors'])) {
                return [
                    'error' => $responseBody['errors'],
                    'status_code' => $response->getStatusCode(),
                ];
            }

            return $responseBody['data'] ?? [];

        } catch (RequestException $e) {
            return [
                'error' => $e->getMessage(),
                'status_code' => $e->getResponse() ? $e->getResponse()->getStatusCode() : null,
            ];
        }
    }

    public function getProducts(int $first = 10): array
    {
        $query = <<<QUERY
            query GetProducts(\$first: Int) {
                products(first: \$first) {
                    nodes {
                        id
                        title
                    }
                }
            }
        QUERY;

        return $this->sendQuery($query, ['first' => $first]);
    }

    public function getProduct(int $productId)
    {
        $query = <<<QUERY
            query GetProduct(\$productId: ID!) {
                product(id: \$productId) {
                    id
                    title
                    variants(first: 10) {
                        nodes {
                            id
                            title
                        }
                    }
                    collections(first: 10) {
                        nodes {
                            id
                            title
                        }
                    }
                }
            }
        QUERY;

        $response = $this->sendQuery($query,  ['productId' => $productId]);
        return $response['product'];
    }

    public function createProduct(array $productData)
    {
        $query = <<<QUERY
            mutation ProductCreate(\$input: ProductInput!) {
                productCreate(input: \$input) {
                    product {
                        id
                        handle
                        variants(first: 10) {
                            edges {
                                node {
                                    id
                                    title
                                    price
                                    sku
                                }
                            }
                        }
                        title
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        QUERY;

        $response = $this->sendQuery($query, ['input' => $productData]);

        if (isset($response['errors'])) {
            return [
                'errors' => $response['errors'],
            ];
        }

        return $response;
    }

    public function updateProduct($productData)
    {
        $query = <<<QUERY
          mutation ProductUpdate(\$input: ProductInput!) {
            productUpdate(input: \$input) {
              product {
                id
                title
              }
              userErrors {
                field
                message
              }
            }
          }
        QUERY;

        $response = $this->sendQuery($query, ['input' => $productData]);

        if (isset($response['errors'])) {
            return [
                'errors' => $response['errors'],
            ];
        }

        return $response;
    }

    public function deleteProduct(string $productId)
    {
        $query = <<<GRAPHQL
           mutation ProductDelete(\$input: ProductDeleteInput!) {
                productDelete(input: \$input) {
                    deletedProductId
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $response = $this->sendQuery($query, ['id' => $productId]);
        if (isset($response['errors'])) {
            return [
                'errors' => $response['errors'],
            ];
        }

        return $response;
    }

    public function createProductVariants(array $variables): array
    {
        $query = <<<GRAPHQL
            mutation productVariantsBulkCreate(\$productId: ID!, \$variants: [ProductVariantsBulkInput!]!) {
                productVariantsBulkCreate(productId: \$productId, variants: \$variants) {
                    product {
                        id
                    }
                    productVariants {
                        id
                        inventoryItem { 
                           id
                        }
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        $response = $this->sendQuery($query, $variables);
        $data = [];

        if (!empty($response['productVariantsBulkCreate']['productVariants'])) {
            $variants = $response['productVariantsBulkCreate']['productVariants'];
            foreach ($variants as $variant) {
                $data[] = $variant;
            }
        }

        return $data;
    }

    public function updateProductVariants(string $productId, array $variants)
    {
        $query = <<<QUERY
          mutation UpdateProductVariantsOptionValuesInBulk(\$productId: ID!, \$variants: [ProductVariantsBulkInput!]!) {
            productVariantsBulkUpdate(productId: \$productId, variants: \$variants) {
              product {
                id
                title
                options {
                  id
                  position
                  name
                  values
                  optionValues {
                    id
                    name
                    hasVariants
                  }
                }
              }
              productVariants {
                id
                title
                selectedOptions {
                  name
                  value
                }
              }
              userErrors {
                field
                message
              }
            }
          }
        QUERY;

        $variables = [
            'productId' => $productId,
            'variants' => $variants
        ];

        $response =  $this->sendQuery($query, $variables);
        $data = [];

        if (!empty($response['productVariantsBulkUpdate']['productVariants'])) {
            $variants = $response['productVariantsBulkUpdate']['productVariants'];
            foreach ($variants as $variant) {
                $data[] = $variant;
            }
        }

        return $data;

    }

    public function deleteProductVariants(string $productId, array $variantIds)
    {
        $query = <<<QUERY
          mutation bulkDeleteProductVariants(\$productId: ID!, \$variantsIds: [ID!]!) {
            productVariantsBulkDelete(productId: \$productId, variantsIds: \$variantsIds) {
              product {
                id
                title
              }
              userErrors {
                field
                message
              }
            }
          }
        QUERY;

        $variables = [
            'productId' => $productId,
            'variantsIds' => $variantIds
        ];

        return $this->sendQuery($query, $variables);
    }

    public function addProductMedia($variables)
    {
        $query =
            'mutation productCreateMedia($media: [CreateMediaInput!]!, $productId: ID!) {
                    productCreateMedia(media: $media, productId: $productId) {
                      media {
                        id
                        alt
                        mediaContentType
                        status
                      }
                        
                      mediaUserErrors {
                        field
                        message
                      }
                      product {
                        id
                        title
                      }
                    }
                  }';

        $response = $this->sendQuery($query, $variables);
        $data = [];

        if (!empty($response['productCreateMedia']['media'])) {
            $variants = $response['productCreateMedia']['media'];
            foreach ($variants as $variant) {
                $data[] = $variant;
            }
        }

        return $data;
    }

    public function getOrders(int $first = 10): array
    {
        $query = <<<QUERY
            query GetOrders(\$first: Int) {
                orders(first: \$first) {
                    edges {
                        node {
                            id
                            name
                            totalPrice
                            createdAt
                        }
                    }
                }
            }
        QUERY;

        return $this->sendQuery($query, ['first' => $first]);
    }

    public function getOrder(string $orderId): array
    {
        $query = <<<QUERY
            query GetOrder(\$id: ID!) {
                order(id: \$id) {
                    id
                    name
                    totalPriceSet {
                        presentmentMoney {
                            amount
                        }
                    }
                    lineItems(first: 10) {
                        nodes {
                            id
                            name
                        }
                    }
                }
            }
        QUERY;

        return $this->sendQuery($query, [
            'id' => $orderId,
        ]);
    }


    public function createOrder(array $orderData): array
    {
        $mutation = <<<GRAPHQL
            mutation CreateOrder(\$input: OrderInput!) {
                orderCreate(input: \$input) {
                    order {
                        id
                        name
                        totalPrice
                        createdAt
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        GRAPHQL;

        return $this->sendQuery($mutation, ['input' => $orderData]);
    }

    public function updateOrder(array $orderData): array
    {
        $query = <<<QUERY
            mutation OrderUpdate(\$input: OrderInput!) {
                orderUpdate(input: \$input) {
                    order {
                        canMarkAsPaid
                        cancelReason
                        cancelledAt
                        clientIp
                        confirmed
                        customer {
                            displayName
                            email
                        }
                        discountCodes
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        QUERY;

        $variables = [
            'input' => $orderData,
        ];

        return $this->sendQuery($query, $variables);
    }

    public function deleteOrder(string $orderId): array
    {
        $query = <<<QUERY
            mutation OrderDelete(\$orderId: ID!) {
                orderDelete(orderId: \$orderId) {
                    deletedId
                    userErrors {
                        field
                        message
                        code
                    }
                }
            }
        QUERY;

        return $this->sendQuery($query, ['orderId' => $orderId,]);
    }


    public function getCollections()
    {
        $query = <<<QUERY
              query CustomCollectionList {
                collections(first: 50, query: "collection_type:custom") {
                  nodes {
                    id
                    handle
                    title
                    updatedAt
                    descriptionHtml
                    publishedOnCurrentPublication
                    sortOrder
                    templateSuffix
                  }
                }
              }
            QUERY;

        return $this->sendQuery($query);
    }

    public function getCollection(string $collectionId): array
    {
        $query = <<<QUERY
            query GetCollection(\$id: ID!) {
                collection(id: \$id) {
                    id
                    title
                    handle
                    updatedAt
                }
            }
        QUERY;

        return $this->sendQuery($query, ['id' => $collectionId]);
    }

    public function createCollection(string $title): array
    {
        $mutation = <<<GRAPHQL
            mutation CreateCollection(\$input: CollectionInput!) {
                collectionCreate(input: \$input) {
                    collection {
                        id
                        title
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
            GRAPHQL;

        $variables = [
            'input' => [
                'title' => $title
            ]
        ];
        $response = $this->sendQuery($mutation, $variables);
        $data = [];

        if (!empty($response['collectionCreate']['collection'])) {
            $data = $response['collectionCreate']['collection'];
        }

        return $data;
    }

    public function addProductToCollection(string $collectionId, array $productIds)
    {
        $query = <<<QUERY
          mutation collectionAddProducts(\$id: ID!, \$productIds: [ID!]!) {
            collectionAddProducts(id: \$id, productIds: \$productIds) {
              collection {
                id
                title
                products(first: 10) {
                  nodes {
                    id
                    title
                  }
                }
              }
              userErrors {
                field
                message
              }
            }
          }
        QUERY;

        $response = $this->sendQuery($query, ['id' => $collectionId, 'productIds' => $productIds]);
        $data = [];

        if (!empty($response['collectionAddProducts']['collection'])) {
            $collections = $response['collectionAddProducts']['collection'];
            foreach ($collections as $collection) {
                $data[] = $collection;
            }
        }

        return $data;
    }

    public function updateCollection(array $collectionData): array
    {
        $query = <<<QUERY
            mutation CollectionUpdate(\$input: CollectionInput!) {
                collectionUpdate(input: \$input) {
                    collection {
                        id
                        title
                        description
                        handle
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        QUERY;

        return $this->sendQuery($query, ['input' => $collectionData]);
    }

    public function deleteCollection(string $collectionId): array
    {
        $query = <<<QUERY
            mutation collectionDelete(\$input: CollectionDeleteInput!) {
                collectionDelete(input: \$input) {
                    deletedCollectionId
                    shop {
                        id
                        name
                    }
                    userErrors {
                        field
                        message
                    }
                }
            }
        QUERY;

        return $this->sendQuery($query, [
            'input' => [
                'id' => $collectionId,
            ],
        ]);
    }


    public function getInventoryItem(string $inventoryItemId)
    {
        $query = <<<QUERY
          query inventoryItem(\$id: ID!) {
            inventoryItem(id: \$id) {
              id
              tracked
              sku
            }
          }
        QUERY;

        $variables = [
            'id' => $inventoryItemId
        ];

        return $this->sendQuery($query, $variables);
    }

}
