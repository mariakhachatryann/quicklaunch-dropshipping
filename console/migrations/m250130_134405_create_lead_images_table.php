<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%lead_images}}`.
 */
class m250130_134405_create_lead_images_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%lead_images}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text(),
            'lead_id' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey('fk-lead_images_id',
            '{{%lead_images}}',
            'lead_id',
            \common\models\Lead::tableName(),
            'id',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%lead_images}}');
    }
}
