<?php

declare(strict_types=1);

namespace app\controllers\api\v1;

use app\models\Master;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ScheduleEventController extends Controller
{
    public $enableCsrfValidation = false;

    public function actionStream(int $id): void
    {
        $master = Master::findOne($id);
        if (!$master) {
            throw new NotFoundHttpException(Yii::t('master', 'Master not found.'));
        }

        $response = Yii::$app->response;
        $response->format = Response::FORMAT_RAW;
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        $response->stream = function () use ($id) {
            $redis = Yii::$app->redis;
            $channel = "schedule:{$id}";
            $lastEventId = 0;

            while (!connection_aborted()) {
                $event = $redis->get("sse:last_event:{$channel}");

                if ($event !== null && $event !== false) {
                    $data = json_decode($event, true);
                    $eventId = $data['_event_id'] ?? 0;

                    if ($eventId > $lastEventId) {
                        $lastEventId = $eventId;
                        echo "id: {$eventId}\n";
                        echo "event: schedule_update\n";
                        echo 'data: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . "\n\n";
                        ob_flush();
                        flush();
                    }
                }

                echo ": heartbeat\n\n";
                ob_flush();
                flush();

                sleep(2);
            }
        };

        $response->send();
        Yii::$app->end();
    }
}
