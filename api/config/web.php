<?php

$params = require __DIR__ . '/params.php';
$db = file_exists(__DIR__ . '/db-local.php')
    ? require __DIR__ . '/db-local.php'
    : require __DIR__ . '/db.php';
$redis = file_exists(__DIR__ . '/redis-local.php')
    ? require __DIR__ . '/redis-local.php'
    : require __DIR__ . '/redis.php';
$llm = file_exists(__DIR__ . '/llm-local.php')
    ? require __DIR__ . '/llm-local.php'
    : require __DIR__ . '/llm.php';

$config = [
    'id' => 'beautybook',
    'name' => 'BeautyBook',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'sourceLanguage' => 'en-US',
    'timeZone' => 'Europe/Moscow',
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'hQEpCrz5dnOYtaKxINj_UBxKE_TuApWR',
            'baseUrl' => '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'redis' => $redis,
        'queue' => [
            'class' => 'app\components\RedisQueue',
            'redis' => 'redis',
            'keyPrefix' => 'queue:',
        ],
        'schedulePublisher' => [
            'class' => 'app\components\SchedulePublisher',
            'redis' => 'redis',
        ],
        'llm' => $llm,
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis',
            'defaultDuration' => 3600,
        ],
        'session' => [
            'class' => 'yii\redis\Session',
            'redis' => 'redis',
            'timeout' => 86400,
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['admin/default/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'i18n' => [
            'translations' => [
                'app' => [
                    'class' => 'yii\i18n\DbMessageSource',
                    'sourceLanguage' => 'en-US',
                ],
                'booking' => [
                    'class' => 'yii\i18n\DbMessageSource',
                    'sourceLanguage' => 'en-US',
                ],
                'master' => [
                    'class' => 'yii\i18n\DbMessageSource',
                    'sourceLanguage' => 'en-US',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                'POST api/v1/chat' => 'api/v1/chat/send',
                'GET api/v1/chat/greeting' => 'api/v1/chat/greeting',

                'POST api/v1/auth/login' => 'api/v1/auth/login',
                'GET api/v1/auth/me' => 'api/v1/auth/me',

                'PATCH api/v1/bookings/<id:\d+>/cancel' => 'api/v1/booking/cancel',
                'GET api/v1/masters/<id:\d+>/schedule' => 'api/v1/master/schedule',
                'GET api/v1/masters/<id:\d+>/schedule/events' => 'api/v1/schedule-event/stream',

                'GET api/v1/master/dashboard/stats' => 'api/v1/master-dashboard/stats',
                'GET api/v1/master/dashboard/bookings' => 'api/v1/master-dashboard/bookings',
                'GET api/v1/master/dashboard/schedule' => 'api/v1/master-dashboard/schedule',
                'GET api/v1/master/dashboard/services' => 'api/v1/master-dashboard/services',
                'GET api/v1/master/dashboard/profile' => 'api/v1/master-dashboard/profile',
                'PUT api/v1/master/dashboard/profile' => 'api/v1/master-dashboard/update-profile',
                'PATCH api/v1/master/dashboard/toggle-slot' => 'api/v1/master-dashboard/toggle-slot',
                'GET api/v1/master/dashboard/booking-detail' => 'api/v1/master-dashboard/booking-detail',
                'PATCH api/v1/master/dashboard/confirm-booking' => 'api/v1/master-dashboard/confirm-booking',
                'PATCH api/v1/master/dashboard/cancel-booking' => 'api/v1/master-dashboard/cancel-booking',

                'GET api/v1/schedule-events/<id:\d+>/stream' => 'api/v1/schedule-event/stream',

                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/v1/master', 'pluralize' => true],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/v1/service', 'pluralize' => true],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/v1/booking', 'pluralize' => true],
            ],
        ],
    ],
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '172.*', '192.168.*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '172.*', '192.168.*'],
    ];
}

return $config;
