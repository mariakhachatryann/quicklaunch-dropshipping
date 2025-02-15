<?php

use common\models\MonitoringQueue;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%import_queues}}`.
 */
class m250202_185019_create_import_queues_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%import_queues}}', [
            'id' => $this->primaryKey(),
            'site_id' => $this->integer(),
            'url' => $this->string(500),
            'status' => $this->boolean(),
            'processing_ip' => $this->string(),
            'handler' => $this->integer(),
            'country' => $this->integer(),
            'fail_count' => $this->integer()->defaultValue(0),
            'import_reviews' => $this->boolean()->defaultValue(0),
            'type' => $this->integer(),
            'monitoring_queue_id' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-import_queues_site_id',
            '{{%import_queues}}',
            'site_id',
            '{{%sites}}',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-import_queues_import_monitoring_id',
            '{{%import_queues}}',
            'monitoring_queue_id',
            MonitoringQueue::tableName(),
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%import_queues}}');
    }
}
