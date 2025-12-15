<?php

namespace app\controllers\api\v1;

use app\models\Booking;
use app\models\TimeSlot;
use yii\rest\ActiveController;
use yii\filters\Cors;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Booking API controller.
 *
 * POST   /api/v1/bookings              → create (with Redis lock)
 * GET    /api/v1/bookings/{id}         → view
 * PATCH  /api/v1/bookings/{id}/cancel  → cancel
 */
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

        // Override create — we need Redis lock for race condition protection
        unset($actions['create']);

        // Disable update/delete — bookings are cancelled, not modified
        unset($actions['update'], $actions['delete']);

        return $actions;
    }

    /**
     * Create a booking with Redis distributed lock.
     *
     * This prevents two clients from booking the same time slot simultaneously.
     * The lock key is `lock:slot:{slot_id}` with a 10-second TTL.
     */
    public function actionCreate(): Booking
    {
        $request = \Yii::$app->request;
        $slotId = (int) $request->getBodyParam('time_slot_id');
        $serviceId = (int) $request->getBodyParam('service_id');

        if (!$slotId || !$serviceId) {
            throw new BadRequestHttpException('time_slot_id and service_id are required.');
        }

        $redis = \Yii::$app->redis;
        $lockKey = "lock:slot:{$slotId}";
        $lockValue = uniqid('booking_', true);
        $lockTtl = 10; // seconds

        // Acquire distributed lock (SETNX + TTL)
        $acquired = $redis->set($lockKey, $lockValue, 'NX', 'EX', $lockTtl);

        if (!$acquired) {
            throw new BadRequestHttpException(
                'This time slot is currently being booked by another client. Please try again.'
            );
        }

        try {
            // Double-check slot availability inside the lock
            $slot = TimeSlot::findOne($slotId);
            if (!$slot) {
                throw new NotFoundHttpException('Time slot not found.');
            }

            if (!$slot->isFree()) {
                throw new BadRequestHttpException('This time slot is no longer available.');
            }

            // Create the booking
            $booking = new Booking();
            $booking->time_slot_id = $slotId;
            $booking->service_id = $serviceId;
            $booking->client_name = $request->getBodyParam('client_name', '');
            $booking->client_phone = $request->getBodyParam('client_phone', '');
            $booking->client_email = $request->getBodyParam('client_email');
            $booking->notes = $request->getBodyParam('notes');

            if (!$booking->validate()) {
                \Yii::$app->response->statusCode = 422;
                return $booking;
            }

            // Use transaction to ensure atomicity
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                // Mark slot as booked
                $slot->status = TimeSlot::STATUS_BOOKED;
                if (!$slot->save(false)) {
                    throw new ServerErrorHttpException('Failed to update time slot.');
                }

                // Save booking
                if (!$booking->save(false)) {
                    throw new ServerErrorHttpException('Failed to create booking.');
                }

                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }

            // TODO: Push notification to Redis queue
            // \Yii::$app->redis->lpush('queue:notifications', json_encode([
            //     'type' => 'booking_confirmation',
            //     'booking_id' => $booking->id,
            // ]));

            \Yii::$app->response->statusCode = 201;
            return $booking;

        } finally {
            // Release lock (only if we still own it)
            $currentValue = $redis->get($lockKey);
            if ($currentValue === $lockValue) {
                $redis->del($lockKey);
            }
        }
    }

    /**
     * Cancel a booking.
     * PATCH /api/v1/bookings/{id}/cancel
     */
    public function actionCancel(int $id): Booking
    {
        $booking = Booking::findOne($id);
        if (!$booking) {
            throw new NotFoundHttpException('Booking not found.');
        }

        if ($booking->status === Booking::STATUS_CANCELLED) {
            throw new BadRequestHttpException('Booking is already cancelled.');
        }

        $reason = \Yii::$app->request->getBodyParam('reason');

        if (!$booking->cancel($reason)) {
            throw new ServerErrorHttpException('Failed to cancel booking.');
        }

        return $booking;
    }
}
