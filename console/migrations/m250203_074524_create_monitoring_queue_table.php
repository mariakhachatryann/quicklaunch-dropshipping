<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%monitoring_queue}}`.
 */
class m250203_074524_create_monitoring_queue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%monitoring_queues}}', [
            'id' => $this->primaryKey(),
            'product_id' => $this->integer(),
            'status' => $this->tinyInteger()->defaultValue(0),
            'error_msg' => $this->text(),
            'import_reviews' => $this->boolean()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-product-id-monitoring',
            '{{%monitoring_queues}}',
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
        $this->dropTable('{{%monitoring_queue}}');
    }
}
