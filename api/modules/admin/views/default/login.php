<?php

/** @var app\models\LoginForm $model */

use yii\helpers\Html;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — BeautyBook Admin</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-card {
            background: #fff;
            border-radius: 8px;
            padding: 40px;
            width: 360px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .login-card h1 {
            text-align: center;
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 4px;
        }
        .login-card p {
            text-align: center;
            color: #999;
            font-size: 13px;
            margin-bottom: 24px;
        }
        .field {
            margin-bottom: 16px;
        }
        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .field input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .field input:focus {
            outline: none;
            border-color: #3498db;
        }
        .field .error-msg {
            color: #e74c3c;
            font-size: 12px;
            margin-top: 4px;
        }
        .submit-btn {
            width: 100%;
            padding: 12px;
            background: #2c3e50;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
        }
        .submit-btn:hover {
            background: #34495e;
        }
    </style>
</head>
<body>
<div class="login-card">
    <h1>✦ BeautyBook</h1>
    <p>Вход в панель администратора</p>

    <form method="post">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

        <div class="field">
            <label for="login">Логин или email</label>
            <input id="login" type="text" name="login" value="<?= Html::encode($model->login) ?>" required autofocus>
            <?php if ($model->hasErrors('login')): ?>
                <div class="error-msg"><?= Html::encode($model->getFirstError('login')) ?></div>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="password">Пароль</label>
            <input id="password" type="password" name="password" required>
            <?php if ($model->hasErrors('password')): ?>
                <div class="error-msg"><?= Html::encode($model->getFirstError('password')) ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="submit-btn">Войти</button>
    </form>
</div>
</body>
</html>
