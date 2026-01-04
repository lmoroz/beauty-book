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
use yii\web\ForbiddenHttpException;
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

    /**
     * @return User
     */
    private function authenticateMaster()
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

        $bookingsBySlot = [];
        $slotIds = array_map(function ($s) { return $s->id; }, $slots);
        if ($slotIds) {
            $bookings = Booking::find()
                ->where(['time_slot_id' => $slotIds])
                ->andWhere(['!=', 'status', Booking::STATUS_CANCELLED])
                ->with('service')
                ->indexBy('time_slot_id')
                ->all();
            $bookingsBySlot = $bookings;
        }

        $result = [];
        foreach ($slots as $slot) {
            $entry = [
                'id' => $slot->id,
                'date' => $slot->date,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'status' => $slot->status,
            ];

            if (isset($bookingsBySlot[$slot->id])) {
                $b = $bookingsBySlot[$slot->id];
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
}
