<?php

use yii\db\Migration;

class m260106_100000_drop_bookings_time_slot_unique_index extends Migration
{
    public function safeUp()
    {
        $this->execute('ALTER TABLE {{%bookings}} DROP FOREIGN KEY fk_bookings_time_slot_id');
        $this->dropIndex('idx_bookings_time_slot_unique', '{{%bookings}}');
        $this->createIndex('idx_bookings_time_slot_id', '{{%bookings}}', 'time_slot_id');
        $this->addForeignKey(
            'fk_bookings_time_slot_id',
            '{{%bookings}}',
            'time_slot_id',
            '{{%time_slots}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->execute('ALTER TABLE {{%bookings}} DROP FOREIGN KEY fk_bookings_time_slot_id');
        $this->dropIndex('idx_bookings_time_slot_id', '{{%bookings}}');
        $this->createIndex('idx_bookings_time_slot_unique', '{{%bookings}}', 'time_slot_id', true);
        $this->addForeignKey(
            'fk_bookings_time_slot_id',
            '{{%bookings}}',
            'time_slot_id',
            '{{%time_slots}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }
}
