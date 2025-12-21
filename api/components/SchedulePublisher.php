<?php

declare(strict_types=1);

namespace app\components;

use Yii;
use yii\base\Component;

class SchedulePublisher extends Component
{
    public string $redis = 'redis';

    public string $keyPrefix = 'sse:last_event:schedule:';

    public int $eventTtl = 60;

    public function publishSlotBooked(int $masterId, int $slotId, string $date): void
    {
        $this->publish($masterId, [
            'action' => 'slot_booked',
            'master_id' => $masterId,
            'slot_id' => $slotId,
            'date' => $date,
        ]);
    }

    public function publishSlotFreed(int $masterId, int $slotId, string $date): void
    {
        $this->publish($masterId, [
            'action' => 'slot_freed',
            'master_id' => $masterId,
            'slot_id' => $slotId,
            'date' => $date,
        ]);
    }

    protected function publish(int $masterId, array $data): void
    {
        $redis = Yii::$app->get($this->redis);
        $key = $this->keyPrefix . $masterId;

        $data['_event_id'] = (int) (microtime(true) * 1000);
        $data['_published_at'] = date('Y-m-d H:i:s');

        $redis->set($key, json_encode($data, JSON_UNESCAPED_UNICODE));
        $redis->expire($key, $this->eventTtl);

        $cacheKey = "cache:schedule:{$masterId}:{$data['date']}";
        $redis->del($cacheKey);
    }
}
