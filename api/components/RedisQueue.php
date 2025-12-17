<?php

declare(strict_types=1);

namespace app\components;

use yii\base\Component;
use yii\base\InvalidConfigException;

class RedisQueue extends Component
{
    public string $redis = 'redis';

    public string $keyPrefix = 'queue:';

    public function push(string $queue, array $payload): int
    {
        $payload['_pushed_at'] = date('Y-m-d H:i:s');
        $payload['_id'] = uniqid('job_', true);

        return (int) $this->getRedis()->lpush(
            $this->buildKey($queue),
            json_encode($payload, JSON_UNESCAPED_UNICODE)
        );
    }

    public function pop(string $queue, int $timeout = 0): ?array
    {
        $result = $this->getRedis()->brpop($this->buildKey($queue), $timeout);

        if ($result === null || $result === false) {
            return null;
        }

        $data = is_array($result) ? ($result[1] ?? null) : null;

        if ($data === null) {
            return null;
        }

        return json_decode($data, true);
    }

    public function size(string $queue): int
    {
        return (int) $this->getRedis()->llen($this->buildKey($queue));
    }

    public function clear(string $queue): bool
    {
        return (bool) $this->getRedis()->del($this->buildKey($queue));
    }

    protected function buildKey(string $queue): string
    {
        return $this->keyPrefix . $queue;
    }

    protected function getRedis(): \yii\redis\Connection
    {
        return \Yii::$app->get($this->redis);
    }
}
