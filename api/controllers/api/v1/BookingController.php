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

        $behaviors['rateLimiterCreate'] = [
            'class' => \app\components\RateLimiter::class,
            'limit' => 5,
            'window' => 60,
            'only' => ['create'],
            'category' => 'booking',
        ];

        $behaviors['rateLimiterCancel'] = [
            'class' => \app\components\RateLimiter::class,
            'limit' => 10,
            'window' => 60,
            'only' => ['cancel'],
            'category' => 'booking',
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

        $service = \app\models\Service::findOne($serviceId);
        if (!$service) {
            throw new NotFoundHttpException(
                Yii::t('booking', 'Service not found.')
            );
        }

        $slot = TimeSlot::findOne($slotId);
        if (!$slot) {
            throw new NotFoundHttpException(
                Yii::t('booking', 'Time slot not found.')
            );
        }

        $slotDurationMin = (strtotime($slot->end_time) - strtotime($slot->start_time)) / 60;
        $slotsNeeded = max(1, (int) ceil($service->duration_min / $slotDurationMin));

        $allSlots = TimeSlot::findConsecutiveFreeSlots(
            $slot->master_id,
            $slot->date,
            $slot->start_time,
            $slotsNeeded
        );

        if (count($allSlots) < $slotsNeeded) {
            throw new BadRequestHttpException(
                Yii::t('booking', 'Not enough consecutive free slots for this service ({n} slots needed).', ['n' => $slotsNeeded])
            );
        }

        $redis = Yii::$app->redis;
        $lockValue = uniqid('booking_', true);
        $lockTtl = 10;
        $lockKeys = [];

        foreach ($allSlots as $s) {
            $lockKey = "lock:slot:{$s->id}";
            $acquired = $redis->set($lockKey, $lockValue, 'NX', 'EX', $lockTtl);
            if (!$acquired) {
                foreach ($lockKeys as $lk) {
                    $redis->del($lk);
                }
                throw new BadRequestHttpException(
                    Yii::t('booking', 'One of the required time slots is currently being booked. Please try again.')
                );
            }
            $lockKeys[] = $lockKey;
        }

        try {
            foreach ($allSlots as $s) {
                $fresh = TimeSlot::findOne($s->id);
                if (!$fresh || !$fresh->isFree()) {
                    throw new BadRequestHttpException(
                        Yii::t('booking', 'Time slot {time} is no longer available.', ['time' => $s->start_time])
                    );
                }
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
                if (!$booking->save(false)) {
                    throw new ServerErrorHttpException(
                        Yii::t('booking', 'Failed to create booking.')
                    );
                }

                foreach ($allSlots as $s) {
                    $s->status = TimeSlot::STATUS_BOOKED;
                    $s->booking_id = $booking->id;
                    if (!$s->save(false)) {
                        throw new ServerErrorHttpException(
                            Yii::t('booking', 'Failed to update time slot.')
                        );
                    }
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

            foreach ($allSlots as $s) {
                Yii::$app->schedulePublisher->publishSlotBooked(
                    $s->master_id, $s->id, $s->date
                );
            }

            Yii::$app->response->statusCode = 201;
            return $booking;

        } finally {
            foreach ($lockKeys as $lk) {
                $currentValue = $redis->get($lk);
                if ($currentValue === $lockValue) {
                    $redis->del($lk);
                }
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

        $slot = $booking->timeSlot;
        if ($slot) {
            Yii::$app->schedulePublisher->publishSlotFreed(
                $slot->master_id, $slot->id, $slot->date
            );
        }

        return $booking;
    }
}
