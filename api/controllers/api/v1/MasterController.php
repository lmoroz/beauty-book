<?php

namespace app\controllers\api\v1;

use app\models\Master;
use app\models\TimeSlot;
use yii\rest\ActiveController;
use yii\filters\Cors;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Master API controller.
 *
 * GET    /api/v1/masters                   → list
 * GET    /api/v1/masters/{id}              → view
 * GET    /api/v1/masters/{id}/schedule     → free slots for a date
 * POST   /api/v1/masters                   → create
 * PUT    /api/v1/masters/{id}              → update
 * DELETE /api/v1/masters/{id}              → delete
 */
class MasterController extends ActiveController
{
    public $modelClass = 'app\models\Master';

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();

        // CORS for Vue dev server
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

        // Customize index action to expand services and filter by salon
        $actions['index']['prepareDataProvider'] = function () {
            $query = $this->modelClass::find()
                ->with(['activeServices'])
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

    /**
     * Get available time slots for a master on a given date.
     *
     * GET /api/v1/masters/{id}/schedule?date=2025-12-20
     *
     * Uses Redis cache with key `cache:schedule:{master_id}:{date}` (TTL 5 min).
     */
    public function actionSchedule(int $id): array
    {
        $master = Master::findOne($id);
        if (!$master) {
            throw new NotFoundHttpException('Master not found.');
        }

        $date = \Yii::$app->request->get('date');
        if (!$date) {
            throw new BadRequestHttpException('Parameter "date" is required (format: Y-m-d).');
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new BadRequestHttpException('Invalid date format. Use Y-m-d.');
        }

        // Try Redis cache first
        $cacheKey = "cache:schedule:{$id}:{$date}";
        $cached = \Yii::$app->redis->get($cacheKey);

        if ($cached !== null && $cached !== false) {
            return json_decode($cached, true);
        }

        // Query free slots
        $slots = TimeSlot::findFreeSlots($id, $date)->asArray()->all();

        $result = [
            'master_id' => $id,
            'date' => $date,
            'slots' => $slots,
            'total' => count($slots),
        ];

        // Cache for 5 minutes
        \Yii::$app->redis->set($cacheKey, json_encode($result), 'EX', 300);

        return $result;
    }
}

