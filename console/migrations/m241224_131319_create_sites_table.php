<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%sites}}`.
 */
class m241224_131319_create_sites_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%sites}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'url' => $this->string(),
            'import_by_queue' => $this->boolean()->defaultValue(0),
            'color' => $this->string()->defaultValue('red'),
            'scrap_internal' => $this->boolean()->defaultValue(0),
            'monitor_available' => $this->boolean()->defaultValue(true),
            'import_by_extension' => $this->boolean()->defaultValue(0),
            'logo' => $this->string(),
            'is_new' => $this->tinyInteger()->defaultValue(0),
            'has_reviews' => $this->boolean()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%sites}}');
    }
}
