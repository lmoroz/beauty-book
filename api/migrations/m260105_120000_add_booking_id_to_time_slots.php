<?php

use yii\db\Migration;

class m260105_120000_add_booking_id_to_time_slots extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%time_slots}}', 'booking_id', $this->integer()->null()->after('block_reason'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%time_slots}}', 'booking_id');
    }
}
