<?php

declare(strict_types=1);

namespace app\modules\admin;

use Yii;
use yii\web\ForbiddenHttpException;

class Module extends \yii\base\Module
{
    public $layout = 'main';

    public function init(): void
    {
        parent::init();
        $this->setViewPath('@app/modules/admin/views');
    }

    public function beforeAction($action): bool
    {
        // Allow login action without auth
        if ($action->uniqueId === 'admin/default/login') {
            return parent::beforeAction($action);
        }

        if (Yii::$app->user->isGuest) {
            Yii::$app->user->loginRequired();
            return false;
        }

        /** @var \app\models\User $user */
        $user = Yii::$app->user->identity;

        if (!$user->isAdmin()) {
            throw new ForbiddenHttpException(Yii::t('app', 'You are not allowed to perform this action.'));
        }

        return parent::beforeAction($action);
    }
}
