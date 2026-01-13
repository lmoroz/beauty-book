<?php

declare(strict_types=1);

namespace app\controllers\api\v1;

use app\models\Booking;
use app\models\Master;
use app\models\Service;
use app\models\TimeSlot;
use app\models\User;
use Yii;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

class MasterDashboardController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:3000'],
                'Access-Control-Request-Method' => ['GET', 'PUT', 'PATCH', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age' => 3600,
            ],
        ];

        return $behaviors;
    }

    private function authenticateMaster(): User
    {
        $token = Yii::$app->request->getHeaders()->get('Authorization');

        if ($token && preg_match('/^Bearer\s+(.+)$/i', $token, $matches)) {
            $token = $matches[1];
        } else {
            $token = null;
        }

        if (!$token) {
            throw new UnauthorizedHttpException(
                Yii::t('master', 'Access token is required.')
            );
        }

        $user = User::findIdentityByAccessToken($token);

        if (!$user) {
            throw new UnauthorizedHttpException(
                Yii::t('master', 'Invalid access token.')
            );
        }

        if (!$user->isMaster() || !$user->master_id) {
            throw new ForbiddenHttpException(
                Yii::t('master', 'Access denied. Master role required.')
            );
        }

        return $user;
    }

    public function actionStats(): array
    {
        $user = $this->authenticateMaster();
        $masterId = (int) $user->master_id;
        $today = date('Y-m-d');

        $todaySlots = TimeSlot::find()
            ->where(['master_id' => $masterId, 'date' => $today])
            ->count();

        $todayBooked = TimeSlot::find()
            ->where(['master_id' => $masterId, 'date' => $today, 'status' => TimeSlot::STATUS_BOOKED])
            ->count();

        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $weekEnd = date('Y-m-d', strtotime('sunday this week'));

        $weekBookings = (int) Booking::find()
            ->innerJoinWith('timeSlot')
            ->where(['time_slots.master_id' => $masterId])
            ->andWhere(['>=', 'time_slots.date', $weekStart])
            ->andWhere(['<=', 'time_slots.date', $weekEnd])
            ->andWhere(['!=', 'bookings.status', Booking::STATUS_CANCELLED])
            ->count();

        $weekRevenue = (float) Booking::find()
            ->innerJoinWith(['timeSlot', 'service'])
            ->where(['time_slots.master_id' => $masterId])
            ->andWhere(['>=', 'time_slots.date', $weekStart])
            ->andWhere(['<=', 'time_slots.date', $weekEnd])
            ->andWhere(['!=', 'bookings.status', Booking::STATUS_CANCELLED])
            ->sum('services.price');

        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        $monthBookings = (int) Booking::find()
            ->innerJoinWith('timeSlot')
            ->where(['time_slots.master_id' => $masterId])
            ->andWhere(['>=', 'time_slots.date', $monthStart])
            ->andWhere(['<=', 'time_slots.date', $monthEnd])
            ->andWhere(['!=', 'bookings.status', Booking::STATUS_CANCELLED])
            ->count();

        $monthRevenue = (float) Booking::find()
            ->innerJoinWith(['timeSlot', 'service'])
            ->where(['time_slots.master_id' => $masterId])
            ->andWhere(['>=', 'time_slots.date', $monthStart])
            ->andWhere(['<=', 'time_slots.date', $monthEnd])
            ->andWhere(['!=', 'bookings.status', Booking::STATUS_CANCELLED])
            ->sum('services.price');

        $weekTotal = TimeSlot::find()
            ->where(['master_id' => $masterId])
            ->andWhere(['>=', 'date', $weekStart])
            ->andWhere(['<=', 'date', $weekEnd])
            ->andWhere(['!=', 'status', TimeSlot::STATUS_BLOCKED])
            ->count();

        $weekOccupancy = $weekTotal > 0
            ? round($weekBookings / $weekTotal * 100, 1)
            : 0;

        return [
            'today' => [
                'total_slots' => (int) $todaySlots,
                'booked_slots' => (int) $todayBooked,
            ],
            'week' => [
                'bookings' => $weekBookings,
                'revenue' => $weekRevenue ?: 0,
                'occupancy' => $weekOccupancy,
            ],
            'month' => [
                'bookings' => $monthBookings,
                'revenue' => $monthRevenue ?: 0,
            ],
        ];
    }

    public function actionBookings(): array
    {
        $user = $this->authenticateMaster();
        $masterId = (int) $user->master_id;
        $today = date('Y-m-d');

        $status = Yii::$app->request->get('status', 'upcoming');

        $query = Booking::find()
            ->innerJoinWith(['timeSlot', 'service'])
            ->where(['time_slots.master_id' => $masterId]);

        if ($status === 'upcoming') {
            $query->andWhere(['>=', 'time_slots.date', $today])
                ->andWhere(['not in', 'bookings.status', [
                    Booking::STATUS_CANCELLED,
                    Booking::STATUS_COMPLETED,
                ]]);
        } elseif ($status === 'past') {
            $query->andWhere(['<', 'time_slots.date', $today]);
        }

        $query->orderBy(['time_slots.date' => SORT_ASC, 'time_slots.start_time' => SORT_ASC]);

        $bookings = $query->all();

        return array_map(function (Booking $b) {
            $slot = $b->timeSlot;
            $svc = $b->service;
            return [
                'id' => $b->id,
                'client_name' => $b->client_name,
                'client_phone' => $b->client_phone,
                'status' => $b->status,
                'date' => $slot ? $slot->date : null,
                'start_time' => $slot ? $slot->start_time : null,
                'end_time' => $slot ? $slot->end_time : null,
                'service_name' => $svc ? $svc->name : null,
                'service_price' => $svc ? (float) $svc->price : null,
                'duration_min' => $svc ? (int) $svc->duration_min : null,
                'created_at' => $b->created_at,
            ];
        }, $bookings);
    }

    public function actionSchedule(): array
    {
        $user = $this->authenticateMaster();
        $masterId = (int) $user->master_id;

        $weekStart = Yii::$app->request->get('week_start');
        if (!$weekStart || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekStart)) {
            $weekStart = date('Y-m-d', strtotime('monday this week'));
        }

        $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));

        $slots = TimeSlot::find()
            ->where(['master_id' => $masterId])
            ->andWhere(['>=', 'date', $weekStart])
            ->andWhere(['<=', 'date', $weekEnd])
            ->orderBy(['date' => SORT_ASC, 'start_time' => SORT_ASC])
            ->all();

        if (empty($slots)) {
            TimeSlot::generateWeekSlots($masterId, $weekStart);

            $slots = TimeSlot::find()
                ->where(['master_id' => $masterId])
                ->andWhere(['>=', 'date', $weekStart])
                ->andWhere(['<=', 'date', $weekEnd])
                ->orderBy(['date' => SORT_ASC, 'start_time' => SORT_ASC])
                ->all();
        }

        $bookingsBySlot = [];
        $slotIds = array_map(function ($s) {
            return $s->id;
        }, $slots);
        $bookingIds = array_filter(array_map(function ($s) {
            return $s->booking_id;
        }, $slots));

        if ($slotIds) {
            $bookings = Booking::find()
                ->where(['time_slot_id' => $slotIds])
                ->andWhere(['!=', 'status', Booking::STATUS_CANCELLED])
                ->with('service')
                ->indexBy('time_slot_id')
                ->all();
            $bookingsBySlot = $bookings;
        }

        $bookingsById = [];
        if ($bookingIds) {
            $extra = Booking::find()
                ->where(['id' => array_unique($bookingIds)])
                ->andWhere(['!=', 'status', Booking::STATUS_CANCELLED])
                ->with('service')
                ->indexBy('id')
                ->all();
            $bookingsById = $extra;
        }

        $result = [];
        foreach ($slots as $slot) {
            $entry = [
                'id' => $slot->id,
                'date' => $slot->date,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'status' => $slot->status,
                'block_reason' => $slot->block_reason,
            ];

            $b = null;
            if (isset($bookingsBySlot[$slot->id])) {
                $b = $bookingsBySlot[$slot->id];
            } elseif ($slot->booking_id && isset($bookingsById[$slot->booking_id])) {
                $b = $bookingsById[$slot->booking_id];
            }

            if ($b) {
                $entry['booking'] = [
                    'id' => $b->id,
                    'client_name' => $b->client_name,
                    'service_name' => $b->service ? $b->service->name : null,
                ];
            }

            $result[] = $entry;
        }

        return [
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'slots' => $result,
        ];
    }

    public function actionServices(): array
    {
        $user = $this->authenticateMaster();
        $masterId = (int) $user->master_id;

        $services = Service::find()
            ->where(['master_id' => $masterId])
            ->with('category')
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->all();

        return array_map(function (Service $s) {
            $cat = $s->category;
            return [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'category' => $cat ? ['id' => $cat->id, 'name' => $cat->name] : null,
                'duration_min' => (int) $s->duration_min,
                'price' => (float) $s->price,
                'is_active' => (bool) $s->is_active,
                'sort_order' => (int) $s->sort_order,
            ];
        }, $services);
    }

    public function actionProfile(): array
    {
        $user = $this->authenticateMaster();
        $master = Master::findOne($user->master_id);

        return [
            'id' => $master->id,
            'name' => $master->name,
            'slug' => $master->slug,
            'bio' => $master->bio,
            'photo' => $master->photo,
            'phone' => $master->phone,
            'email' => $master->email,
            'status' => $master->status,
            'specializations' => array_map(function ($s) {
                return ['id' => $s->id, 'name' => $s->name];
            }, $master->specializations),
        ];
    }

    public function actionUpdateProfile(): array
    {
        $user = $this->authenticateMaster();
        $master = Master::findOne($user->master_id);

        $request = Yii::$app->request;
        $allowed = ['bio', 'phone', 'email'];
        foreach ($allowed as $attr) {
            $val = $request->getBodyParam($attr);
            if ($val !== null) {
                $master->$attr = $val;
            }
        }

        if (!$master->save()) {
            Yii::$app->response->statusCode = 422;
            return ['errors' => $master->getErrors()];
        }

        return $this->actionProfile();
    }

    public function actionToggleSlot(): array
    {
        $user = $this->authenticateMaster();
        $masterId = (int) $user->master_id;

        $slotId = (int) Yii::$app->request->getBodyParam('slot_id');
        if (!$slotId) {
            throw new BadRequestHttpException(
                Yii::t('master', 'Parameter "slot_id" is required.')
            );
        }

        $slot = TimeSlot::findOne(['id' => $slotId, 'master_id' => $masterId]);
        if (!$slot) {
            throw new NotFoundHttpException(
                Yii::t('master', 'Time slot not found.')
            );
        }

        if ($slot->status === TimeSlot::STATUS_BOOKED) {
            throw new BadRequestHttpException(
                Yii::t('master', 'Cannot modify a booked slot.')
            );
        }

        if ($slot->status === TimeSlot::STATUS_FREE) {
            $slot->status = TimeSlot::STATUS_BLOCKED;
            $reason = Yii::$app->request->getBodyParam('reason');
            $slot->block_reason = in_array($reason, ['lunch', 'break']) ? $reason : null;
        } else {
            $slot->status = TimeSlot::STATUS_FREE;
            $slot->block_reason = null;
        }

        $slot->save(false);

        return [
            'id' => $slot->id,
            'status' => $slot->status,
            'block_reason' => $slot->block_reason,
        ];
    }

    public function actionBookingDetail(): array
    {
        $user = $this->authenticateMaster();
        $masterId = (int) $user->master_id;

        $slotId = (int) Yii::$app->request->get('slot_id');
        if (!$slotId) {
            throw new BadRequestHttpException(
                Yii::t('master', 'Parameter "slot_id" is required.')
            );
        }

        $slot = TimeSlot::findOne(['id' => $slotId, 'master_id' => $masterId]);
        if (!$slot) {
            throw new NotFoundHttpException(
                Yii::t('master', 'Time slot not found.')
            );
        }

        $booking = Booking::find()
            ->where(['time_slot_id' => $slotId])
            ->andWhere(['!=', 'status', Booking::STATUS_CANCELLED])
            ->with('service')
            ->one();

        if (!$booking) {
            throw new NotFoundHttpException(
                Yii::t('booking', 'Booking not found for this slot.')
            );
        }

        $svc = $booking->service;

        return [
            'id' => $booking->id,
            'client_name' => $booking->client_name,
            'client_phone' => $booking->client_phone,
            'client_email' => $booking->client_email,
            'status' => $booking->status,
            'notes' => $booking->notes,
            'date' => $slot->date,
            'start_time' => $slot->start_time,
            'end_time' => $slot->end_time,
            'service' => $svc ? [
                'name' => $svc->name,
                'price' => (float) $svc->price,
                'duration_min' => (int) $svc->duration_min,
            ] : null,
            'created_at' => $booking->created_at,
        ];
    }

    public function actionConfirmBooking(): array
    {
        $user = $this->authenticateMaster();
        $masterId = (int) $user->master_id;

        $bookingId = (int) Yii::$app->request->getBodyParam('booking_id');
        if (!$bookingId) {
            throw new BadRequestHttpException(
                Yii::t('booking', 'Parameter "booking_id" is required.')
            );
        }

        $booking = Booking::find()
            ->innerJoinWith('timeSlot')
            ->where(['bookings.id' => $bookingId, 'time_slots.master_id' => $masterId])
            ->one();

        if (!$booking) {
            throw new NotFoundHttpException(
                Yii::t('booking', 'Booking not found.')
            );
        }

        if ($booking->status !== Booking::STATUS_PENDING) {
            throw new BadRequestHttpException(
                Yii::t('booking', 'Only pending bookings can be confirmed.')
            );
        }

        $booking->status = Booking::STATUS_CONFIRMED;
        $booking->save(false);

        return ['id' => $booking->id, 'status' => $booking->status];
    }

    public function actionCancelBooking(): array
    {
        $user = $this->authenticateMaster();
        $masterId = (int) $user->master_id;

        $bookingId = (int) Yii::$app->request->getBodyParam('booking_id');
        if (!$bookingId) {
            throw new BadRequestHttpException(
                Yii::t('booking', 'Parameter "booking_id" is required.')
            );
        }

        $booking = Booking::find()
            ->innerJoinWith('timeSlot')
            ->where(['bookings.id' => $bookingId, 'time_slots.master_id' => $masterId])
            ->one();

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

        $slotsToFree = TimeSlot::find()
            ->where(['booking_id' => $booking->id])
            ->all();

        if (empty($slotsToFree)) {
            $slot = $booking->timeSlot;
            if ($slot) {
                $slotsToFree = [$slot];
            }
        }

        $reason = Yii::$app->request->getBodyParam('reason', 'Cancelled by master');
        $booking->cancel($reason);

        foreach ($slotsToFree as $slot) {
            Yii::$app->schedulePublisher->publishSlotFreed(
                $slot->master_id,
                $slot->id,
                $slot->date
            );
        }

        return ['id' => $booking->id, 'status' => $booking->status];
    }
}
