<?php

use common\models\User;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%bulk_imports}}`.
 */
class m250110_110623_create_bulk_imports_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bulk_imports}}', [
            'id' => $this->primaryKey(),
            'category_url' => $this->string(600),
            'user_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey('fk-bulk_imports-user_id-users-id',
            '{{%bulk_imports}}',
            'user_id',
            User::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%bulk_imports}}');
    }
}
