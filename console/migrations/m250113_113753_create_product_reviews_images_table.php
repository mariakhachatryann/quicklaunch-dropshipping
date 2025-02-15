<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_reviews_images}}`.
 */
class m250113_113753_create_product_reviews_images_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_reviews_images}}', [
            'id' => $this->primaryKey(),
            'product_review_id' => $this->integer(),
            'image_url' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB');

        $this->addForeignKey(
            'fk-product-reviews-images-product-review-id',
            '{{%product_reviews_images}}',
            'product_review_id',
            '{{%product_reviews}}',
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product_reviews_images}}');
    }
}
