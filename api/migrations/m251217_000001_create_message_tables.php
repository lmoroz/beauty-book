<?php

use yii\db\Migration;

class m251217_000001_create_message_tables extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%source_message}}', [
            'id' => $this->primaryKey(),
            'category' => $this->string(255)->notNull()->defaultValue('app'),
            'message' => $this->text()->notNull(),
        ]);

        $this->createIndex('idx-source_message-category', '{{%source_message}}', 'category');

        $this->createTable('{{%message}}', [
            'id' => $this->integer()->notNull(),
            'language' => $this->string(16)->notNull(),
            'translation' => $this->text(),
        ]);

        $this->addPrimaryKey('pk-message', '{{%message}}', ['id', 'language']);
        $this->addForeignKey(
            'fk-message-source_message',
            '{{%message}}',
            'id',
            '{{%source_message}}',
            'id',
            'CASCADE',
            'RESTRICT'
        );

        $messages = [
            'booking' => [
                'time_slot_id and service_id are required.',
                'This time slot is currently being booked by another client. Please try again.',
                'Time slot not found.',
                'This time slot is no longer available.',
                'Failed to update time slot.',
                'Failed to create booking.',
                'Booking not found.',
                'Booking is already cancelled.',
                'Failed to cancel booking.',
                'This time slot is already booked.',
            ],
            'master' => [
                'Master not found.',
                'Parameter "date" is required (format: Y-m-d).',
                'Invalid date format. Use Y-m-d.',
            ],
        ];

        $translations = [
            'booking' => [
                'time_slot_id and service_id are required.' =>
                    'Необходимо указать time_slot_id и service_id.',
                'This time slot is currently being booked by another client. Please try again.' =>
                    'Это время сейчас бронируется другим клиентом. Попробуйте ещё раз.',
                'Time slot not found.' =>
                    'Запись на это время не найдена.',
                'This time slot is no longer available.' =>
                    'Это время больше недоступно.',
                'Failed to update time slot.' =>
                    'Не удалось обновить слот расписания.',
                'Failed to create booking.' =>
                    'Не удалось создать бронирование.',
                'Booking not found.' =>
                    'Бронирование не найдено.',
                'Booking is already cancelled.' =>
                    'Бронирование уже отменено.',
                'Failed to cancel booking.' =>
                    'Не удалось отменить бронирование.',
                'This time slot is already booked.' =>
                    'Это время уже занято.',
            ],
            'master' => [
                'Master not found.' =>
                    'Мастер не найден.',
                'Parameter "date" is required (format: Y-m-d).' =>
                    'Параметр «date» обязателен (формат: ГГГГ-ММ-ДД).',
                'Invalid date format. Use Y-m-d.' =>
                    'Неверный формат даты. Используйте ГГГГ-ММ-ДД.',
            ],
        ];

        foreach ($messages as $category => $msgs) {
            foreach ($msgs as $msg) {
                $this->insert('{{%source_message}}', [
                    'category' => $category,
                    'message' => $msg,
                ]);
                $sourceId = $this->db->getLastInsertID('{{%source_message}}');

                $translation = $translations[$category][$msg] ?? null;
                if ($translation) {
                    $this->insert('{{%message}}', [
                        'id' => $sourceId,
                        'language' => 'ru-RU',
                        'translation' => $translation,
                    ]);
                }
            }
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{%message}}');
        $this->dropTable('{{%source_message}}');
    }
}
