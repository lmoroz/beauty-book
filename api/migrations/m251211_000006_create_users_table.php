<?php

use yii\db\Migration;

/**
 * Create users table for system authentication.
 */
class m251211_000006_create_users_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%users}}', [
            'id' => $this->primaryKey()->unsigned(),
            'email' => $this->string(191)->notNull()->unique(),
            'username' => $this->string(100)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),
            'access_token' => $this->string(64)->null()->unique(),
            'role' => $this->string(20)->notNull()->defaultValue('client')
                ->comment('client, master, admin'),
            'master_id' => $this->integer()->unsigned()->null()
                ->comment('Link to masters table if role=master'),
            'status' => $this->smallInteger()->notNull()->defaultValue(10)
                ->comment('0=deleted, 9=inactive, 10=active'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ], 'ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        // Foreign key to masters (nullable)
        $this->addForeignKey(
            'fk_users_master_id',
            '{{%users}}',
            'master_id',
            '{{%masters}}',
            'id',
            'SET NULL',
            'CASCADE'
        );

        // Index: role-based queries
        $this->createIndex('idx_users_role_status', '{{%users}}', ['role', 'status']);

        // Insert default admin user (password: admin123)
        $this->insert('{{%users}}', [
            'email' => 'admin@beautybook.local',
            'username' => 'admin',
            'password_hash' => Yii::$app->security->generatePasswordHash('admin123'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'access_token' => Yii::$app->security->generateRandomString(64),
            'role' => 'admin',
            'status' => 10,
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%users}}');
    }
}
