<?php

use yii\db\Migration;

/**
 * Create bookings table.
 */
class m251211_000005_create_bookings_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%bookings}}', [
            'id' => $this->primaryKey()->unsigned(),
            'time_slot_id' => $this->integer()->unsigned()->notNull(),
            'service_id' => $this->integer()->unsigned()->notNull(),
            'client_name' => $this->string(255)->notNull(),
            'client_phone' => $this->string(20)->notNull(),
            'client_email' => $this->string(255)->null(),
            'status' => $this->string(20)->notNull()->defaultValue('pending')
                ->comment('pending, confirmed, completed, cancelled, no_show'),
            'notes' => $this->text()->null(),
            'cancelled_at' => $this->timestamp()->null(),
            'cancel_reason' => $this->string(500)->null(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Foreign keys
        $this->addForeignKey(
            'fk_bookings_time_slot_id',
            '{{%bookings}}',
            'time_slot_id',
            '{{%time_slots}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk_bookings_service_id',
            '{{%bookings}}',
            'service_id',
            '{{%services}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Index: active bookings sorted by date
        $this->createIndex('idx_bookings_status_created', '{{%bookings}}', ['status', 'created_at']);

        // Index: lookup by client phone
        $this->createIndex('idx_bookings_client_phone', '{{%bookings}}', ['client_phone']);

        // One booking per time slot (enforce at DB level)
        $this->createIndex('idx_bookings_time_slot_unique', '{{%bookings}}', ['time_slot_id'], true);
    }

    public function safeDown()
    {
        $this->dropTable('{{%bookings}}');
    }
}
