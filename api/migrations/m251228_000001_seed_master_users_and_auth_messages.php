<?php

use yii\db\Migration;

class m251228_000001_seed_master_users_and_auth_messages extends Migration
{
    public function safeUp()
    {
        $security = Yii::$app->security;

        $masterUsers = [
            [
                'email' => 'anna@beautybook.local',
                'username' => 'anna.petrova',
                'password_hash' => $security->generatePasswordHash('master123'),
                'auth_key' => $security->generateRandomString(),
                'access_token' => $security->generateRandomString(64),
                'role' => 'master',
                'master_id' => 1,
                'status' => 10,
            ],
            [
                'email' => 'maria@beautybook.local',
                'username' => 'maria.sidorova',
                'password_hash' => $security->generatePasswordHash('master123'),
                'auth_key' => $security->generateRandomString(),
                'access_token' => $security->generateRandomString(64),
                'role' => 'master',
                'master_id' => 2,
                'status' => 10,
            ],
            [
                'email' => 'elena@beautybook.local',
                'username' => 'elena.kozlova',
                'password_hash' => $security->generatePasswordHash('master123'),
                'auth_key' => $security->generateRandomString(),
                'access_token' => $security->generateRandomString(64),
                'role' => 'master',
                'master_id' => 3,
                'status' => 10,
            ],
        ];

        foreach ($masterUsers as $user) {
            $this->insert('{{%users}}', $user);
        }

        // i18n messages for 'app' category
        $messages = [
            'Invalid login or password.' => 'Неверный логин или пароль.',
            'Access token is required.' => 'Требуется токен доступа.',
            'Invalid access token.' => 'Недействительный токен доступа.',
            'You are not allowed to perform this action.' => 'У вас нет прав для выполнения этого действия.',
        ];

        foreach ($messages as $source => $translation) {
            $this->insert('{{%source_message}}', [
                'category' => 'app',
                'message' => $source,
            ]);

            $sourceId = $this->db->getLastInsertID();

            $this->insert('{{%message}}', [
                'id' => $sourceId,
                'language' => 'ru-RU',
                'translation' => $translation,
            ]);
        }
    }

    public function safeDown()
    {
        $this->delete('{{%users}}', ['role' => 'master']);

        $sourceIds = (new \yii\db\Query())
            ->select('id')
            ->from('{{%source_message}}')
            ->where(['category' => 'app'])
            ->column($this->db);

        if ($sourceIds) {
            $this->delete('{{%message}}', ['id' => $sourceIds]);
            $this->delete('{{%source_message}}', ['id' => $sourceIds]);
        }
    }
}
