<?php

use yii\db\Migration;

class m260104_100000_add_block_reason_to_time_slots extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%time_slots}}', 'block_reason', $this->string(50)->null()->after('status'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%time_slots}}', 'block_reason');
    }
}
