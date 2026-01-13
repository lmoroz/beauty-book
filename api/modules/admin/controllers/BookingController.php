<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\Booking;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class BookingController extends Controller
{
    public function actionIndex(): string
    {
        $query = Booking::find()
            ->with(['timeSlot.master', 'service'])
            ->orderBy(['created_at' => SORT_DESC]);

        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['bookings.status' => $status]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 30],
        ]);

        return $this->render('index', [
            'bookings' => $dataProvider->getModels(),
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionConfirm(int $id): \yii\web\Response
    {
        $model = $this->findModel($id);
        $model->status = Booking::STATUS_CONFIRMED;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'Бронирование подтверждено.');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionCancel(int $id): \yii\web\Response
    {
        $model = $this->findModel($id);

        $slotsToFree = \app\models\TimeSlot::find()
            ->where(['booking_id' => $model->id])
            ->all();

        if (empty($slotsToFree)) {
            $slot = $model->timeSlot;
            if ($slot) {
                $slotsToFree = [$slot];
            }
        }

        $model->cancel('Cancelled by admin');

        foreach ($slotsToFree as $slot) {
            Yii::$app->schedulePublisher->publishSlotFreed(
                $slot->master_id,
                $slot->id,
                $slot->date
            );
        }

        Yii::$app->session->setFlash('success', 'Бронирование отменено.');
        return $this->redirect(['view', 'id' => $id]);
    }

    private function findModel(int $id): Booking
    {
        $model = Booking::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Booking not found.');
        }
        return $model;
    }
}
