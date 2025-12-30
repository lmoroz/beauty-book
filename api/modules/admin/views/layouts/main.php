<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Html;
use yii\helpers\Url;

$user = Yii::$app->user->identity;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Html::encode($this->title ?? 'Admin') ?> — BeautyBook Admin</title>
    <?php $this->head() ?>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f6f9;
            color: #333;
            font-size: 14px;
        }
        .admin-header {
            background: #2c3e50;
            color: #fff;
            padding: 0 24px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .admin-header__brand {
            font-size: 16px;
            font-weight: 600;
            color: #f1c40f;
            text-decoration: none;
        }
        .admin-header__user {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }
        .admin-header__user a {
            color: #ecf0f1;
            text-decoration: none;
        }
        .admin-header__user a:hover { text-decoration: underline; }
        .admin-nav {
            background: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 0 24px;
            display: flex;
            gap: 0;
        }
        .admin-nav a {
            display: block;
            padding: 12px 16px;
            color: #666;
            text-decoration: none;
            font-size: 13px;
            border-bottom: 2px solid transparent;
            transition: color 0.15s, border-color 0.15s;
        }
        .admin-nav a:hover { color: #2c3e50; }
        .admin-nav a.active {
            color: #2c3e50;
            border-bottom-color: #3498db;
            font-weight: 600;
        }
        .admin-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        .admin-content h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        table.grid {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }
        table.grid th {
            background: #f8f9fa;
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 2px solid #e9ecef;
        }
        table.grid td {
            padding: 10px 12px;
            border-bottom: 1px solid #f0f0f0;
        }
        table.grid tr:hover td { background: #f8f9fa; }
        table.grid tr:last-child td { border-bottom: none; }
        .btn {
            display: inline-block;
            padding: 7px 16px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            line-height: 1.4;
        }
        .btn-primary {
            background: #3498db;
            color: #fff;
        }
        .btn-primary:hover { background: #2980b9; }
        .btn-success {
            background: #27ae60;
            color: #fff;
        }
        .btn-success:hover { background: #229954; }
        .btn-danger {
            background: #e74c3c;
            color: #fff;
        }
        .btn-danger:hover { background: #c0392b; }
        .btn-sm { padding: 4px 10px; font-size: 12px; }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
            font-size: 13px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52,152,219,0.15);
        }
        .form-group .error { color: #e74c3c; font-size: 12px; margin-top: 4px; }
        .detail-view {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .detail-view table { width: 100%; border-collapse: collapse; }
        .detail-view th {
            background: #f8f9fa;
            padding: 10px 16px;
            text-align: left;
            width: 200px;
            font-weight: 600;
            font-size: 13px;
            color: #666;
            border-bottom: 1px solid #eee;
        }
        .detail-view td {
            padding: 10px 16px;
            border-bottom: 1px solid #f0f0f0;
        }
        .card {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            padding: 20px;
            text-align: center;
        }
        .stat-card__value {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
        }
        .stat-card__label {
            font-size: 13px;
            color: #999;
            margin-top: 4px;
        }
        .actions {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .breadcrumb {
            font-size: 13px;
            color: #999;
            margin-bottom: 16px;
        }
        .breadcrumb a { color: #3498db; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .flash-success {
            background: #d4edda;
            color: #155724;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        .flash-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
    </style>
</head>
<body>
<?php $this->beginBody() ?>

<header class="admin-header">
    <a href="<?= Url::to(['/admin/default/index']) ?>" class="admin-header__brand">
        ✦ BeautyBook Admin
    </a>
    <?php if ($user): ?>
    <div class="admin-header__user">
        <span><?= Html::encode($user->username) ?></span>
        <a href="<?= Url::to(['/admin/default/logout']) ?>">Выйти</a>
    </div>
    <?php endif; ?>
</header>

<?php if ($user): ?>
<nav class="admin-nav">
    <?php
    $controllerId = Yii::$app->controller->id ?? '';
    $items = [
        ['label' => 'Дашборд', 'url' => '/admin/default/index', 'id' => 'default'],
        ['label' => 'Мастера', 'url' => '/admin/master/index', 'id' => 'master'],
        ['label' => 'Специализации', 'url' => '/admin/specialization/index', 'id' => 'specialization'],
        ['label' => 'Услуги', 'url' => '/admin/service/index', 'id' => 'service'],
        ['label' => 'Бронирования', 'url' => '/admin/booking/index', 'id' => 'booking'],
        ['label' => 'Салон', 'url' => '/admin/salon/update', 'id' => 'salon'],
    ];
    foreach ($items as $item): ?>
        <a href="<?= Url::to([$item['url']]) ?>"
           class="<?= $controllerId === $item['id'] ? 'active' : '' ?>">
            <?= $item['label'] ?>
        </a>
    <?php endforeach; ?>
</nav>
<?php endif; ?>

<main class="admin-content">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="flash-success"><?= Yii::$app->session->getFlash('success') ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="flash-error"><?= Yii::$app->session->getFlash('error') ?></div>
    <?php endif; ?>

    <?= $content ?>
</main>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
