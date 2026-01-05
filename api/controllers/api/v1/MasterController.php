<?php

namespace app\controllers\api\v1;

use app\models\Master;
use app\models\TimeSlot;
use Yii;
use yii\rest\ActiveController;
use yii\filters\Cors;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
class MasterController extends ActiveController
{
    public $modelClass = 'app\models\Master';

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

        $actions['index']['prepareDataProvider'] = function () {
            $query = $this->modelClass::find()
                ->with(['activeServices', 'specializations'])
                ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);

            $salonId = \Yii::$app->request->get('salon_id');
            if ($salonId) {
                $query->andWhere(['salon_id' => (int) $salonId]);
            }

            $status = \Yii::$app->request->get('status', 'active');
            if ($status !== 'all') {
                $query->andWhere(['status' => $status]);
            }

            return new \yii\data\ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 20,
                ],
            ]);
        };

        return $actions;
    }

    public function actionSchedule(int $id): array
    {
        $master = Master::findOne($id);
        if (!$master) {
            throw new NotFoundHttpException(Yii::t('master', 'Master not found.'));
        }

        $date = \Yii::$app->request->get('date');
        if (!$date) {
            throw new BadRequestHttpException(Yii::t('master', 'Parameter "date" is required (format: Y-m-d).'));
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new BadRequestHttpException(Yii::t('master', 'Invalid date format. Use Y-m-d.'));
        }

        $serviceId = (int) \Yii::$app->request->get('service_id');

        $cacheKey = "cache:schedule:{$id}:{$date}" . ($serviceId ? ":{$serviceId}" : '');
        $cached = Yii::$app->redis->get($cacheKey);

        if ($cached !== null && $cached !== false) {
            return json_decode($cached, true);
        }

        $freeSlots = TimeSlot::findFreeSlots($id, $date)->all();

        if ($serviceId && !empty($freeSlots)) {
            $service = \app\models\Service::findOne($serviceId);
            if ($service && $service->duration_min > 0) {
                $firstSlot = $freeSlots[0];
                $slotDurationMin = (strtotime($firstSlot->end_time) - strtotime($firstSlot->start_time)) / 60;
                $slotsNeeded = max(1, (int) ceil($service->duration_min / $slotDurationMin));

                if ($slotsNeeded > 1) {
                    $freeStartTimes = [];
                    foreach ($freeSlots as $s) {
                        $freeStartTimes[$s->start_time] = $s;
                    }

                    $filtered = [];
                    foreach ($freeSlots as $s) {
                        $currentTime = $s->start_time;
                        $hasEnough = true;
                        for ($i = 1; $i < $slotsNeeded; $i++) {
                            $nextTime = date('H:i:s', strtotime($currentTime) + ($i * $slotDurationMin * 60));
                            if (!isset($freeStartTimes[$nextTime])) {
                                $hasEnough = false;
                                break;
                            }
                        }
                        if ($hasEnough) {
                            $filtered[] = $s;
                        }
                    }
                    $freeSlots = $filtered;
                }
            }
        }

        $slotsArray = array_map(function ($s) {
            return $s->toArray();
        }, $freeSlots);

        $result = [
            'master_id' => $id,
            'date' => $date,
            'slots' => $slotsArray,
            'total' => count($slotsArray),
        ];

        Yii::$app->redis->set($cacheKey, json_encode($result), 'EX', 300);

        return $result;
    }
}

