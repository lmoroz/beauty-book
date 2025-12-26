<?php

use yii\db\Migration;

/**
 * Create services table.
 */
class m251211_000003_create_services_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%services}}', [
            'id' => $this->primaryKey()->unsigned(),
            'master_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'category' => $this->string(100)->null()->comment('e.g. haircut, coloring, nails, skincare'),
            'duration_min' => $this->smallInteger()->unsigned()->notNull()->comment('Service duration in minutes'),
            'price' => $this->decimal(10, 2)->notNull(),
            'is_active' => $this->boolean()->notNull()->defaultValue(1),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Foreign key
        $this->addForeignKey(
            'fk_services_master_id',
            '{{%services}}',
            'master_id',
            '{{%masters}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Index: services by master and active status
        $this->createIndex('idx_services_master_active', '{{%services}}', ['master_id', 'is_active']);

        // Index: filter by category
        $this->createIndex('idx_services_category', '{{%services}}', ['category']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%services}}');
    }
}
