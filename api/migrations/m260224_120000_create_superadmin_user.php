<?php

use yii\db\Migration;

class m260224_120000_create_superadmin_user extends Migration
{
    public function safeUp()
    {
        $password = getenv('SUPERADMIN_PASSWORD') ?: 'Bb$up3r!2026#Kz';

        $this->insert('{{%users}}', [
            'email' => 'superadmin@beautybook.local',
            'username' => 'superadmin',
            'password_hash' => Yii::$app->security->generatePasswordHash($password),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'access_token' => Yii::$app->security->generateRandomString(64),
            'role' => 'superadmin',
            'status' => 10,
        ]);
    }

    public function safeDown()
    {
        $this->delete('{{%users}}', ['username' => 'superadmin']);
    }
}
