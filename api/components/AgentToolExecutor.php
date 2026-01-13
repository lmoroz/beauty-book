<?php

declare(strict_types=1);

namespace app\components;

use app\models\Booking;
use app\models\Master;
use app\models\Service;
use app\models\TimeSlot;
use Yii;
use yii\base\Component;

class AgentToolExecutor extends Component
{
    public function getToolDefinitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_masters',
                    'description' => 'Search for masters by specialization or name. Returns a list of matching masters with their specializations and top services.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'specialization' => [
                                'type' => 'string',
                                'description' => 'Specialization to filter by (e.g. "hairdresser", "nail", "makeup"). Partial match supported.',
                            ],
                            'name' => [
                                'type' => 'string',
                                'description' => 'Master name to search for. Partial match supported.',
                            ],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_master_services',
                    'description' => 'Get the list of active services offered by a specific master, including prices and durations.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'master_id' => [
                                'type' => 'integer',
                                'description' => 'The ID of the master.',
                            ],
                        ],
                        'required' => ['master_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'check_availability',
                    'description' => 'Check available time slots for a master on a given date. Optionally filter by time range.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'master_id' => [
                                'type' => 'integer',
                                'description' => 'The ID of the master.',
                            ],
                            'date' => [
                                'type' => 'string',
                                'description' => 'Date in YYYY-MM-DD format.',
                            ],
                            'time_from' => [
                                'type' => 'string',
                                'description' => 'Optional start time filter (HH:MM). Only show slots at or after this time.',
                            ],
                            'time_to' => [
                                'type' => 'string',
                                'description' => 'Optional end time filter (HH:MM). Only show slots before this time.',
                            ],
                        ],
                        'required' => ['master_id', 'date'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'create_booking',
                    'description' => 'Create a new booking for a client. Requires a free time slot, service, and client contact details.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'slot_id' => [
                                'type' => 'integer',
                                'description' => 'The ID of the time slot to book.',
                            ],
                            'service_id' => [
                                'type' => 'integer',
                                'description' => 'The ID of the service.',
                            ],
                            'client_name' => [
                                'type' => 'string',
                                'description' => 'Full name of the client.',
                            ],
                            'client_phone' => [
                                'type' => 'string',
                                'description' => 'Client phone number.',
                            ],
                        ],
                        'required' => ['slot_id', 'service_id', 'client_name', 'client_phone'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'cancel_booking',
                    'description' => 'Cancel an existing booking by its ID.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'booking_id' => [
                                'type' => 'integer',
                                'description' => 'The ID of the booking to cancel.',
                            ],
                            'reason' => [
                                'type' => 'string',
                                'description' => 'Optional cancellation reason.',
                            ],
                        ],
                        'required' => ['booking_id'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_booking_status',
                    'description' => 'Get the status of a booking by booking ID or client phone number.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'booking_id' => [
                                'type' => 'integer',
                                'description' => 'The booking ID.',
                            ],
                            'phone' => [
                                'type' => 'string',
                                'description' => 'Client phone number to look up recent bookings.',
                            ],
                        ],
                        'required' => [],
                    ],
                ],
            ],
        ];
    }

    public function execute(string $toolName, array $arguments): string
    {
        switch ($toolName) {
            case 'search_masters':
                return $this->searchMasters($arguments);
            case 'get_master_services':
                return $this->getMasterServices($arguments);
            case 'check_availability':
                return $this->checkAvailability($arguments);
            case 'create_booking':
                return $this->createBooking($arguments);
            case 'cancel_booking':
                return $this->cancelBooking($arguments);
            case 'get_booking_status':
                return $this->getBookingStatus($arguments);
            default:
                return json_encode(['error' => "Unknown tool: {$toolName}"]);
        }
    }

    private function searchMasters(array $args): string
    {
        $query = Master::find()->where(['status' => 'active'])->with(['specializations', 'services']);

        if (!empty($args['name'])) {
            $query->andWhere(['like', 'name', $args['name']]);
        }

        if (!empty($args['specialization'])) {
            $query->innerJoinWith('specializations', false)
                ->andWhere(['like', '{{%specializations}}.name', $args['specialization']]);
        }

        $masters = $query->limit(10)->all();

        $result = [];
        foreach ($masters as $master) {
            $specs = array_map(function ($s) {
                return $s->name;
            }, $master->specializations);

            $services = $master->getActiveServices()->limit(5)->all();
            $topServices = array_map(function ($s) {
                return ['name' => $s->name, 'price' => (float) $s->price, 'duration' => $s->duration_min];
            }, $services);

            $result[] = [
                'id' => $master->id,
                'name' => $master->name,
                'specializations' => $specs,
                'top_services' => $topServices,
                'bio' => $master->bio,
            ];
        }

        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    private function getMasterServices(array $args): string
    {
        $masterId = isset($args['master_id']) ? (int) $args['master_id'] : 0;
        $master = Master::findOne($masterId);

        if (!$master) {
            return json_encode(['error' => 'Master not found']);
        }

        $services = $master->getActiveServices()->with('category')->all();

        $result = [];
        foreach ($services as $s) {
            $result[] = [
                'id' => $s->id,
                'name' => $s->name,
                'description' => $s->description,
                'category' => $s->category ? $s->category->name : null,
                'duration_min' => $s->duration_min,
                'price' => (float) $s->price,
            ];
        }

        return json_encode(['master' => $master->name, 'services' => $result], JSON_UNESCAPED_UNICODE);
    }

    private function checkAvailability(array $args): string
    {
        $masterId = isset($args['master_id']) ? (int) $args['master_id'] : 0;
        $date = isset($args['date']) ? $args['date'] : '';

        if (!$masterId || !$date) {
            return json_encode(['error' => 'master_id and date are required']);
        }

        $master = Master::findOne($masterId);
        if (!$master) {
            return json_encode(['error' => 'Master not found']);
        }

        $query = TimeSlot::findFreeSlots($masterId, $date);

        if (!empty($args['time_from'])) {
            $query->andWhere(['>=', 'start_time', $args['time_from'] . ':00']);
        }
        if (!empty($args['time_to'])) {
            $query->andWhere(['<', 'start_time', $args['time_to'] . ':00']);
        }

        $now = date('Y-m-d');
        if ($date === $now) {
            $cutoff = date('H:i:s', strtotime('+30 minutes'));
            $query->andWhere(['>=', 'start_time', $cutoff]);
        }

        $slots = $query->all();

        $result = [];
        foreach ($slots as $slot) {
            $result[] = [
                'id' => $slot->id,
                'start_time' => substr($slot->start_time, 0, 5),
                'end_time' => substr($slot->end_time, 0, 5),
            ];
        }

        return json_encode([
            'master' => $master->name,
            'date' => $date,
            'current_time' => date('H:i'),
            'free_slots' => $result,
            'total' => count($result),
        ], JSON_UNESCAPED_UNICODE);
    }

    private function createBooking(array $args): string
    {
        $slotId = isset($args['slot_id']) ? (int) $args['slot_id'] : 0;
        $serviceId = isset($args['service_id']) ? (int) $args['service_id'] : 0;
        $clientName = isset($args['client_name']) ? $args['client_name'] : '';
        $clientPhone = isset($args['client_phone']) ? $args['client_phone'] : '';

        if (!$slotId || !$serviceId || !$clientName || !$clientPhone) {
            return json_encode(['error' => 'slot_id, service_id, client_name and client_phone are required']);
        }

        $slot = TimeSlot::findOne($slotId);
        if (!$slot) {
            return json_encode(['error' => 'Time slot not found']);
        }

        if (!$slot->isFree()) {
            return json_encode(['error' => 'Time slot is no longer available']);
        }

        $service = Service::findOne($serviceId);
        if (!$service) {
            return json_encode(['error' => 'Service not found']);
        }

        $redis = Yii::$app->redis;
        $lockKey = "lock:slot:{$slotId}";
        $lockValue = uniqid('agent_booking_', true);
        $acquired = $redis->set($lockKey, $lockValue, 'NX', 'EX', 10);

        if (!$acquired) {
            return json_encode(['error' => 'Slot is currently being booked by another client. Try again.']);
        }

        try {
            $fresh = TimeSlot::findOne($slotId);
            if (!$fresh || !$fresh->isFree()) {
                return json_encode(['error' => 'Slot was just booked. Please choose another.']);
            }

            $booking = new Booking();
            $booking->time_slot_id = $slotId;
            $booking->service_id = $serviceId;
            $booking->client_name = $clientName;
            $booking->client_phone = $clientPhone;

            if (!$booking->save()) {
                return json_encode(['error' => 'Validation failed', 'details' => $booking->getErrors()]);
            }

            $fresh->status = TimeSlot::STATUS_BOOKED;
            $fresh->booking_id = $booking->id;
            $fresh->save(false);

            Yii::$app->queue->push('notifications', [
                'type' => 'booking_confirmation',
                'booking_id' => $booking->id,
            ]);

            Yii::$app->schedulePublisher->publishSlotBooked(
                $fresh->master_id, $fresh->id, $fresh->date
            );

            return json_encode([
                'success' => true,
                'booking_id' => $booking->id,
                'master' => $fresh->master->name,
                'date' => $fresh->date,
                'time' => substr($fresh->start_time, 0, 5) . ' - ' . substr($fresh->end_time, 0, 5),
                'service' => $service->name,
                'status' => $booking->status,
            ], JSON_UNESCAPED_UNICODE);
        } finally {
            $currentValue = $redis->get($lockKey);
            if ($currentValue === $lockValue) {
                $redis->del($lockKey);
            }
        }
    }

    private function cancelBooking(array $args): string
    {
        $bookingId = isset($args['booking_id']) ? (int) $args['booking_id'] : 0;
        $reason = isset($args['reason']) ? $args['reason'] : null;

        if (!$bookingId) {
            return json_encode(['error' => 'booking_id is required']);
        }

        $booking = Booking::findOne($bookingId);
        if (!$booking) {
            return json_encode(['error' => 'Booking not found']);
        }

        if ($booking->status === Booking::STATUS_CANCELLED) {
            return json_encode(['error' => 'Booking is already cancelled']);
        }

        if (!$booking->cancel($reason)) {
            return json_encode(['error' => 'Failed to cancel booking']);
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

        return json_encode([
            'success' => true,
            'booking_id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    private function getBookingStatus(array $args): string
    {
        $bookingId = isset($args['booking_id']) ? (int) $args['booking_id'] : 0;
        $phone = isset($args['phone']) ? $args['phone'] : '';

        if ($bookingId) {
            $booking = Booking::findOne($bookingId);
            if (!$booking) {
                return json_encode(['error' => 'Booking not found']);
            }
            return $this->formatBookingInfo($booking);
        }

        if ($phone) {
            $bookings = Booking::find()
                ->where(['client_phone' => $phone])
                ->andWhere(['!=', 'status', Booking::STATUS_CANCELLED])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit(5)
                ->all();

            if (empty($bookings)) {
                return json_encode(['error' => 'No bookings found for this phone number']);
            }

            $result = [];
            foreach ($bookings as $b) {
                $result[] = json_decode($this->formatBookingInfo($b), true);
            }

            return json_encode(['bookings' => $result], JSON_UNESCAPED_UNICODE);
        }

        return json_encode(['error' => 'Provide booking_id or phone']);
    }

    private function formatBookingInfo(Booking $booking): string
    {
        $slot = $booking->timeSlot;
        $service = $booking->service;
        $master = $slot ? $slot->master : null;

        return json_encode([
            'booking_id' => $booking->id,
            'status' => $booking->status,
            'client_name' => $booking->client_name,
            'master' => $master ? $master->name : null,
            'service' => $service ? $service->name : null,
            'date' => $slot ? $slot->date : null,
            'time' => $slot ? substr($slot->start_time, 0, 5) . ' - ' . substr($slot->end_time, 0, 5) : null,
            'created_at' => $booking->created_at,
        ], JSON_UNESCAPED_UNICODE);
    }
}
