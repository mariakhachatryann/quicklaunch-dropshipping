<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bulk_monitoring}}`.
 */
class m250113_104722_create_bulk_monitoring_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bulk_monitoring}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk-bulk-monitoring-user',
            '{{%bulk_monitoring}}',
            'user_id',
            \common\models\User::tableName(),
            'id',
            'CASCADE'
        );

        $this->createTable('{{%bulk_monitoring_items}}', [
            'id' => $this->primaryKey(),
            'status' => $this->integer(),
            'url' => $this->string(600),
            'bulk_monitoring_id' => $this->integer(),
            'product_id' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk-bulk-monitoring-item',
            '{{%bulk_monitoring_items}}',
            'bulk_monitoring_id',
            'bulk_monitoring',
            'id',
            'CASCADE'
        );

        $this->addForeignKey('fk-bulk-monitoring-item-product',
            '{{%bulk_monitoring_items}}',
            'product_id',
            \common\models\Product::tableName(),
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bulk_monitoring}}');
    }
}
