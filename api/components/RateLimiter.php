<?php

declare(strict_types=1);

namespace app\components;

use Yii;
use yii\base\ActionFilter;
use yii\web\TooManyRequestsHttpException;

class RateLimiter extends ActionFilter
{
    public string $redis = 'redis';

    public int $limit = 10;

    public int $window = 60;

    public string $keyPrefix = 'rate:';

    public string $category = 'booking';

    public function beforeAction($action): bool
    {
        $redis = Yii::$app->get($this->redis);
        $key = $this->buildKey($action);
        $now = microtime(true);
        $windowStart = $now - $this->window;

        $redis->zremrangebyscore($key, '-inf', (string) $windowStart);

        $count = (int) $redis->zcard($key);

        if ($count >= $this->limit) {
            $retryAfter = $this->getRetryAfter($redis, $key);

            Yii::$app->response->headers->set('X-RateLimit-Limit', (string) $this->limit);
            Yii::$app->response->headers->set('X-RateLimit-Remaining', '0');
            Yii::$app->response->headers->set('X-RateLimit-Reset', (string) ((int) ($now + $retryAfter)));
            Yii::$app->response->headers->set('Retry-After', (string) $retryAfter);

            throw new TooManyRequestsHttpException(
                Yii::t($this->category, 'Too many requests. Please try again later.')
            );
        }

        $memberId = uniqid('req_', true);
        $redis->zadd($key, (string) $now, $memberId);
        $redis->expire($key, $this->window + 1);

        Yii::$app->response->headers->set('X-RateLimit-Limit', (string) $this->limit);
        Yii::$app->response->headers->set('X-RateLimit-Remaining', (string) ($this->limit - $count - 1));
        Yii::$app->response->headers->set('X-RateLimit-Reset', (string) ((int) ($now + $this->window)));

        return true;
    }

    protected function buildKey($action): string
    {
        $ip = Yii::$app->request->getUserIP() ?? 'unknown';
        $route = $action->controller->route;

        return $this->keyPrefix . $ip . ':' . $route;
    }

    protected function getRetryAfter($redis, string $key): int
    {
        $oldest = $redis->zrange($key, 0, 0, 'WITHSCORES');

        if (empty($oldest)) {
            return $this->window;
        }

        $oldestScore = (float) ($oldest[1] ?? $oldest[0] ?? microtime(true));
        $retryAfter = (int) ceil($oldestScore + $this->window - microtime(true));

        return max(1, $retryAfter);
    }
}
