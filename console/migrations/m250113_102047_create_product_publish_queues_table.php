<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%product_publish_queues}}`.
 */
class m250113_102047_create_product_publish_queues_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%product_publish_queues}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer(),
            'status' => $this->tinyInteger()->defaultValue(0),
            'response_text' => $this->text(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-product-id-publish',
            '{{%product_publish_queues}}',
            'product_id',
            '{{%products}}',
            '{{id}}',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%product_publish_queues}}');
    }
}
