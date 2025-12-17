<?php

namespace app\controllers\api\v1;

use app\models\Booking;
use app\models\TimeSlot;
use Yii;
use yii\rest\ActiveController;
use yii\filters\Cors;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class BookingController extends ActiveController
{
    public $modelClass = 'app\models\Booking';

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:3000'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
            ],
        ];

        return $behaviors;
    }

    public function actions(): array
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update'], $actions['delete']);

        return $actions;
    }

    public function actionCreate(): Booking
    {
        $request = Yii::$app->request;
        $slotId = (int) $request->getBodyParam('time_slot_id');
        $serviceId = (int) $request->getBodyParam('service_id');

        if (!$slotId || !$serviceId) {
            throw new BadRequestHttpException(
                Yii::t('booking', 'time_slot_id and service_id are required.')
            );
        }

        $redis = Yii::$app->redis;
        $lockKey = "lock:slot:{$slotId}";
        $lockValue = uniqid('booking_', true);
        $lockTtl = 10;

        $acquired = $redis->set($lockKey, $lockValue, 'NX', 'EX', $lockTtl);

        if (!$acquired) {
            throw new BadRequestHttpException(
                Yii::t('booking', 'This time slot is currently being booked by another client. Please try again.')
            );
        }

        try {
            $slot = TimeSlot::findOne($slotId);
            if (!$slot) {
                throw new NotFoundHttpException(
                    Yii::t('booking', 'Time slot not found.')
                );
            }

            if (!$slot->isFree()) {
                throw new BadRequestHttpException(
                    Yii::t('booking', 'This time slot is no longer available.')
                );
            }

            $booking = new Booking();
            $booking->time_slot_id = $slotId;
            $booking->service_id = $serviceId;
            $booking->client_name = $request->getBodyParam('client_name', '');
            $booking->client_phone = $request->getBodyParam('client_phone', '');
            $booking->client_email = $request->getBodyParam('client_email');
            $booking->notes = $request->getBodyParam('notes');

            if (!$booking->validate()) {
                Yii::$app->response->statusCode = 422;
                return $booking;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $slot->status = TimeSlot::STATUS_BOOKED;
                if (!$slot->save(false)) {
                    throw new ServerErrorHttpException(
                        Yii::t('booking', 'Failed to update time slot.')
                    );
                }

                if (!$booking->save(false)) {
                    throw new ServerErrorHttpException(
                        Yii::t('booking', 'Failed to create booking.')
                    );
                }

                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            Yii::$app->queue->push('notifications', [
                'type' => 'booking_confirmation',
                'booking_id' => $booking->id,
            ]);

            Yii::$app->response->statusCode = 201;
            return $booking;

        } finally {
            $currentValue = $redis->get($lockKey);
            if ($currentValue === $lockValue) {
                $redis->del($lockKey);
            }
        }
    }

    public function actionCancel(int $id): Booking
    {
        $booking = Booking::findOne($id);
        if (!$booking) {
            throw new NotFoundHttpException(
                Yii::t('booking', 'Booking not found.')
            );
        }

        if ($booking->status === Booking::STATUS_CANCELLED) {
            throw new BadRequestHttpException(
                Yii::t('booking', 'Booking is already cancelled.')
            );
        }

        $reason = Yii::$app->request->getBodyParam('reason');

        if (!$booking->cancel($reason)) {
            throw new ServerErrorHttpException(
                Yii::t('booking', 'Failed to cancel booking.')
            );
        }

        Yii::$app->queue->push('notifications', [
            'type' => 'booking_cancellation',
            'booking_id' => $booking->id,
            'reason' => $reason,
        ]);

        return $booking;
    }
}
