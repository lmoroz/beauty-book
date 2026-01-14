<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\Booking;
use app\models\Master;
use app\models\Salon;
use app\models\Service;
use app\models\User;
use Yii;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class DefaultController extends Controller
{
    public function actionIndex(): string
    {
        /** @var User|null $user */
        $user = Yii::$app->user->identity;
        $isSuperAdmin = $user !== null && $user->isSuperAdmin();

        $snapshotData = [];
        if ($isSuperAdmin) {
            $salon = Salon::find()->one();
            $settings = $salon !== null ? $salon->getSettingsArray() : [];
            $snapshotFile = Yii::getAlias('@app/runtime/snapshots/latest.sql');
            clearstatcache(true, $snapshotFile);
            $exists = file_exists($snapshotFile);
            $snapshotData = [
                'autoResetEnabled' => !empty($settings['auto_reset_enabled']),
                'snapshotExists' => $exists,
                'snapshotDate' => $exists
                    ? date('Y-m-d H:i:s', filemtime($snapshotFile))
                    : null,
                'snapshotSize' => $exists
                    ? round(filesize($snapshotFile) / 1024, 1)
                    : null,
            ];
        }

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
            'isSuperAdmin' => $isSuperAdmin,
            'snapshotData' => $snapshotData,
        ]);
    }

    public function actionSnapshot(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        if (!$user->isSuperAdmin()) {
            throw new ForbiddenHttpException();
        }

        $snapshotFile = Yii::getAlias('@app/runtime/snapshots/latest.sql');
        $oldMtime = file_exists($snapshotFile) ? filemtime($snapshotFile) : 0;

        $phpBin = PHP_BINARY;
        if (strpos($phpBin, 'fpm') !== false) {
            $cliBin = dirname($phpBin) . '/php';
            $phpBin = file_exists($cliBin) ? $cliBin : 'php';
        }
        $envPrefix = 'YII_ENV=prod YII_DEBUG=0';
        $cmd = $envPrefix . ' ' . $phpBin . ' ' . escapeshellarg(Yii::getAlias('@app/yii')) . ' snapshot/create 2>&1';
        $output = shell_exec($cmd);

        clearstatcache(true, $snapshotFile);
        $newMtime = file_exists($snapshotFile) ? filemtime($snapshotFile) : 0;

        if ($newMtime > $oldMtime && filesize($snapshotFile) > 0) {
            Yii::$app->session->setFlash('success', 'Снэпшот создан: ' . date('Y-m-d H:i:s', $newMtime));
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка создания снэпшота: ' . ($output ?: 'unknown error'));
        }

        return $this->redirect(['index']);
    }

    public function actionToggleReset(): Response
    {
        /** @var User $user */
        $user = Yii::$app->user->identity;
        if (!$user->isSuperAdmin()) {
            throw new ForbiddenHttpException();
        }

        $salon = Salon::find()->one();
        if ($salon === null) {
            Yii::$app->session->setFlash('error', 'Салон не найден');
            return $this->redirect(['index']);
        }

        $settings = $salon->getSettingsArray();
        $currentValue = !empty($settings['auto_reset_enabled']);
        $settings['auto_reset_enabled'] = !$currentValue;
        $salon->settings = json_encode($settings, JSON_UNESCAPED_UNICODE);
        $salon->save(false);

        $status = $settings['auto_reset_enabled'] ? 'включён' : 'выключен';
        Yii::$app->session->setFlash('success', "Автосброс {$status}");

        return $this->redirect(['index']);
    }

    /**
     * @return string|Response
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

    public function actionLogout(): Response
    {
        Yii::$app->user->logout();
        return $this->redirect(['login']);
    }
}
