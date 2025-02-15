<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_reviews}}`.
 */
class m241226_125529_create_product_reviews_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_reviews}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer(),
            'reviewer_name' => $this->string(),
            'rate' => $this->double(),
            'review' => $this->text(),
            'date' => $this->integer(),
            'user_id' => $this->integer(),
            'status' => $this->integer()->defaultValue(0),
            'shopify_product_id' => $this->bigInteger(),
            'review_hash' => $this->string()->defaultValue(null),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-product-reviews-product-id',
            '{{%product_reviews}}',
            'product_id',
            '{{%products}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'review_user_id',
            '{{%product_reviews}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product_reviews}}');
    }
}
