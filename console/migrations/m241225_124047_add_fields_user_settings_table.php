<?php

use common\models\Currency;
use yii\db\Migration;

/**
 * Class m241225_124047_add_fields_user_settings_table
 */
class m241225_124047_add_fields_user_settings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user_settings}}','compare_at_price_markup', $this->boolean()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','price_by_percent', $this->double()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','price_by_amount', $this->double()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','compare_at_price_by_amount', $this->double()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','compare_at_price_by_percent', $this->double()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','date_format', $this->integer()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','enable_add_reviews', $this->integer()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','enable_add_review_images', $this->integer()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','review_text_color', $this->string()->defaultValue('black'));
        $this->addColumn('{{%user_settings}}','review_fontsize', $this->integer()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','site_theme', $this->tinyInteger()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','sku_import_type', $this->boolean()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','price_import_type', $this->boolean()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','stock_count_import_type', $this->boolean()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','image_import_type', $this->boolean()->defaultValue(0));
        $this->addColumn('{{%user_settings}}','change_variants_option_name', $this->boolean()->defaultValue(0));
        $this->addColumn('{{%user_settings}}', 'measurement', $this->integer()->defaultValue(0));
        $this->addColumn('{{%user_settings}}', 'custom_pricing_rules', $this->boolean()->defaultValue(0));
        $this->addColumn('{{%user_settings}}', 'product_currency_convertor', $this->boolean()->defaultValue(0));
        $this->addColumn('{{%user_settings}}', 'variant_price_markup', $this->boolean()->defaultValue(0));;
        $this->addColumn('{{%user_settings}}', 'use_default_currency', $this->tinyInteger()->defaultValue(0));
        $this->addColumn('{{%user_settings}}', 'currency_id', $this->integer());
        $this->addColumn('{{%user_settings}}', 'default_currency_id', $this->integer());
        $this->addColumn('{{%user_settings}}', 'reviews_auto_publish', $this->integer()->defaultValue(0));
        $this->addColumn('{{%user_settings}}', 'review_limit_per_page', $this->integer()->defaultValue(20));
        $this->addColumn('{{%user_settings}}', 'delete_multiple_products', $this->boolean()->defaultValue(0));
        $this->addColumn('{{%user_settings}}', 'import_reviews', $this->integer()->defaultValue(1));
        $this->addColumn('{{%user_settings}}', 'multiple_import', $this->integer()->defaultValue(0));
        $this->addColumn('{{%user_settings}}', 'reviews_label', $this->string()->defaultValue('Reviews'));

        $this->addForeignKey('fk-user-settings_currency_id_product',
            '{{%user_settings}}',
            'currency_id',
            Currency::tableName(),
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addForeignKey('fk-user-settings_default_currency_id_product',
            '{{%user_settings}}',
            'default_currency_id',
            Currency::tableName(),
            'id',
            'SET NULL',
            'CASCADE'
        );


        $this->dropColumn('{{%user_settings}}','price_amount');
        $this->dropColumn('{{%user_settings}}','price_percentage');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m241225_124047_add_fields_user_settings_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241225_124047_add_fields_user_settings_table cannot be reverted.\n";

        return false;
    }
    */
}
