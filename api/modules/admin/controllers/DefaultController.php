<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\Booking;
use app\models\Master;
use app\models\Service;
use app\models\User;
use Yii;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex(): string
    {
        return $this->render('index', [
            'mastersCount' => Master::find()->where(['status' => 'active'])->count(),
            'servicesCount' => Service::find()->where(['is_active' => true])->count(),
            'bookingsToday' => Booking::find()
                ->joinWith('timeSlot')
                ->where(['time_slots.date' => date('Y-m-d')])
                ->andWhere(['!=', 'bookings.status', Booking::STATUS_CANCELLED])
                ->count(),
            'bookingsTotal' => Booking::find()->count(),
            'recentBookings' => Booking::find()
                ->with(['timeSlot.master', 'service'])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(10)
                ->all(),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['index']);
        }

        $model = new \app\models\LoginForm();
        $model->login = Yii::$app->request->post('login');
        $model->password = Yii::$app->request->post('password');

        if (Yii::$app->request->isPost) {
            $user = $model->login();
            if ($user && $user->isAdmin()) {
                Yii::$app->user->login($user, 3600 * 24 * 7);
                return $this->redirect(['index']);
            } elseif ($user) {
                $model->addError('login', Yii::t('app', 'You are not allowed to perform this action.'));
            }
        }

        $this->layout = false;
        return $this->renderPartial('login', ['model' => $model]);
    }

    public function actionLogout(): \yii\web\Response
    {
        Yii::$app->user->logout();
        return $this->redirect(['login']);
    }
}
