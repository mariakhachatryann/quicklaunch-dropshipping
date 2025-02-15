<?php

use yii\db\Migration;

/**
 * Class m250108_103236_add_store_name_and_logo_fields_to_user_settings_table
 */
class m250108_103236_add_store_name_and_logo_fields_to_user_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(\common\models\UserSetting::tableName(), 'logo', $this->string());
        $this->addColumn(\common\models\UserSetting::tableName(), 'store_name', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250108_103236_add_store_name_and_logo_fields_to_user_settings_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250108_103236_add_store_name_and_logo_fields_to_user_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
