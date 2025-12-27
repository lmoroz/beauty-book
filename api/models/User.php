<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * @property int $id
 * @property string $email
 * @property string $username
 * @property string $password_hash
 * @property string $auth_key
 * @property string|null $access_token
 * @property string $role
 * @property int|null $master_id
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Master|null $master
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;

    const ROLE_CLIENT = 'client';
    const ROLE_MASTER = 'master';
    const ROLE_ADMIN = 'admin';

    public static function tableName(): string
    {
        return '{{%users}}';
    }

    public function rules(): array
    {
        return [
            [['email', 'username', 'password_hash', 'auth_key'], 'required'],
            ['email', 'email'],
            ['email', 'unique'],
            ['username', 'unique'],
            ['email', 'string', 'max' => 191],
            ['username', 'string', 'max' => 100],
            ['password_hash', 'string', 'max' => 255],
            ['auth_key', 'string', 'max' => 32],
            ['access_token', 'string', 'max' => 64],
            ['role', 'in', 'range' => [self::ROLE_CLIENT, self::ROLE_MASTER, self::ROLE_ADMIN]],
            ['role', 'default', 'value' => self::ROLE_CLIENT],
            ['status', 'integer'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_INACTIVE, self::STATUS_ACTIVE]],
            ['master_id', 'integer'],
            ['master_id', 'exist', 'targetClass' => Master::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ];
    }

    // --- IdentityInterface ---

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getAuthKey(): string
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    // --- Authentication helpers ---

    public static function findByUsername(string $username): ?self
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findByEmail(string $email): ?self
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function setPassword(string $password): void
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function generateAccessToken(): void
    {
        $this->access_token = Yii::$app->security->generateRandomString(64);
    }

    // --- Relations ---

    public function getMaster(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Master::class, ['id' => 'master_id']);
    }

    // --- Helpers ---

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isMaster(): bool
    {
        return $this->role === self::ROLE_MASTER;
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function fields(): array
    {
        $fields = parent::fields();

        unset($fields['password_hash'], $fields['auth_key']);

        return $fields;
    }
}
