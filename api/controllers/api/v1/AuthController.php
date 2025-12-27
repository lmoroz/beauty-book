<?php

declare(strict_types=1);

namespace app\controllers\api\v1;

use app\models\LoginForm;
use Yii;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\UnauthorizedHttpException;

class AuthController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:3000'],
                'Access-Control-Request-Method' => ['POST', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
            ],
        ];

        return $behaviors;
    }

    public function actionLogin(): array
    {
        $form = new LoginForm();
        $form->login = Yii::$app->request->getBodyParam('login');
        $form->password = Yii::$app->request->getBodyParam('password');

        $user = $form->login();

        if (!$user) {
            Yii::$app->response->statusCode = 422;
            return ['errors' => $form->getErrors()];
        }

        return [
            'access_token' => $user->access_token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'master_id' => $user->master_id,
            ],
        ];
    }

    public function actionMe(): array
    {
        $token = Yii::$app->request->getHeaders()->get('Authorization');

        if ($token && preg_match('/^Bearer\s+(.+)$/i', $token, $matches)) {
            $token = $matches[1];
        } else {
            $token = Yii::$app->request->get('access-token');
        }

        if (!$token) {
            throw new UnauthorizedHttpException(Yii::t('app', 'Access token is required.'));
        }

        /** @var \app\models\User|null $user */
        $user = \app\models\User::findIdentityByAccessToken($token);

        if (!$user) {
            throw new UnauthorizedHttpException(Yii::t('app', 'Invalid access token.'));
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role,
            'master_id' => $user->master_id,
        ];
    }
}
