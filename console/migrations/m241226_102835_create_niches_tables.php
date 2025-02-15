<?php

use yii\db\Migration;

/**
 * Class m241226_102835_create_niches_tables
 */
class m241226_102835_create_niches_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%niches}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'description' => $this->string(),
            'is_trending' => $this->boolean(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241226_102835_create_niches_tables cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241226_102835_create_niches_tables cannot be reverted.\n";

        return false;
    }
    */
}
