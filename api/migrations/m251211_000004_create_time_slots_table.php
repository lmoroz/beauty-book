<?php

use yii\db\Migration;

/**
 * Create time_slots table.
 */
class m251211_000004_create_time_slots_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%time_slots}}', [
            'id' => $this->primaryKey()->unsigned(),
            'master_id' => $this->integer()->unsigned()->notNull(),
            'date' => $this->date()->notNull(),
            'start_time' => $this->time()->notNull(),
            'end_time' => $this->time()->notNull(),
            'status' => $this->string(20)->notNull()->defaultValue('free')->comment('free, booked, blocked'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Foreign key
        $this->addForeignKey(
            'fk_time_slots_master_id',
            '{{%time_slots}}',
            'master_id',
            '{{%masters}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // THE key index: "free slots for a master on a given date"
        $this->createIndex('idx_time_slots_master_date_status', '{{%time_slots}}', ['master_id', 'date', 'status']);

        // Prevent duplicate slots
        $this->createIndex('idx_time_slots_unique', '{{%time_slots}}', ['master_id', 'date', 'start_time'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%time_slots}}');
    }
}
