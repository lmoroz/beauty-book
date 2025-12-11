<?php

use yii\db\Migration;

/**
 * Create masters table.
 */
class m251211_000002_create_masters_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%masters}}', [
            'id' => $this->primaryKey()->unsigned(),
            'salon_id' => $this->integer()->unsigned()->notNull(),
            'name' => $this->string(255)->notNull(),
            'slug' => $this->string(255)->notNull(),
            'specialization' => $this->string(255)->null()->comment('e.g. hairdresser, manicurist, cosmetologist'),
            'bio' => $this->text()->null(),
            'photo' => $this->string(500)->null(),
            'phone' => $this->string(20)->null(),
            'email' => $this->string(255)->null(),
            'status' => $this->string(20)->notNull()->defaultValue('active')->comment('active, inactive, on_vacation'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Foreign key
        $this->addForeignKey(
            'fk_masters_salon_id',
            '{{%masters}}',
            'salon_id',
            '{{%salons}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Index: catalog of masters for a salon
        $this->createIndex('idx_masters_salon_status', '{{%masters}}', ['salon_id', 'status']);

        // Unique slug within salon
        $this->createIndex('idx_masters_salon_slug', '{{%masters}}', ['salon_id', 'slug'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%masters}}');
    }
}
