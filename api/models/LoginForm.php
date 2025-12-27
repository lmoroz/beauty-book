<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $login;
    public $password;

    private $_user = null;

    public function rules(): array
    {
        return [
            [['login', 'password'], 'required'],
            ['login', 'string'],
            ['password', 'string'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params): void
    {
        if ($this->hasErrors()) {
            return;
        }

        $user = $this->getUser();

        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError($attribute, Yii::t('app', 'Invalid login or password.'));
        }
    }

    public function login(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = $this->getUser();
        $user->generateAccessToken();
        $user->save(false);

        return $user;
    }

    private function getUser(): ?User
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->login)
                ?? User::findByEmail($this->login);
        }

        return $this->_user;
    }
}
