<?php

use yii\db\Migration;

class m251219_000001_add_rate_limiter_messages extends Migration
{
    public function safeUp()
    {
        $this->insert('{{%source_message}}', [
            'category' => 'booking',
            'message' => 'Too many requests. Please try again later.',
        ]);
        $sourceId = $this->db->getLastInsertID('{{%source_message}}');

        $this->insert('{{%message}}', [
            'id' => $sourceId,
            'language' => 'ru-RU',
            'translation' => 'Слишком много запросов. Попробуйте позже.',
        ]);
    }

    public function safeDown()
    {
        $sourceMessage = (new \yii\db\Query())
            ->from('{{%source_message}}')
            ->where([
                'category' => 'booking',
                'message' => 'Too many requests. Please try again later.',
            ])
            ->one();

        if ($sourceMessage) {
            $this->delete('{{%message}}', ['id' => $sourceMessage['id']]);
            $this->delete('{{%source_message}}', ['id' => $sourceMessage['id']]);
        }
    }
}
