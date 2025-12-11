<?php

use yii\db\Migration;

/**
 * Create salons table.
 */
class m251211_000001_create_salons_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%salons}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->notNull(),
            'slug' => $this->string(255)->notNull()->unique(),
            'address' => $this->string(500)->null(),
            'phone' => $this->string(20)->null(),
            'email' => $this->string(255)->null(),
            'description' => $this->text()->null(),
            'working_hours' => $this->json()->null()->comment('JSON: {"mon": {"open": "09:00", "close": "21:00"}, ...}'),
            'settings' => $this->json()->null()->comment('Salon-specific settings'),
            'is_active' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    }

    public function safeDown()
    {
        $this->dropTable('{{%salons}}');
    }
}
