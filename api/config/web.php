<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$redis = require __DIR__ . '/redis.php';

$config = [
    'id' => 'beautybook',
    'name' => 'BeautyBook',
    'basePath' => dirname(__DIR__),
    'language' => 'ru-RU',
    'sourceLanguage' => 'en-US',
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => 'hQEpCrz5dnOYtaKxINj_UBxKE_TuApWR',
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
                'PATCH api/v1/bookings/<id:\d+>/cancel' => 'api/v1/booking/cancel',
                'GET api/v1/masters/<id:\d+>/schedule' => 'api/v1/master/schedule',
                'GET api/v1/masters/<id:\d+>/schedule/events' => 'api/v1/schedule-event/stream',

                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/v1/master', 'pluralize' => true],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/v1/service', 'pluralize' => true],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'api/v1/booking', 'pluralize' => true],
            ],
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
