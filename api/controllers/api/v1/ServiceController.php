<?php

declare(strict_types=1);

namespace app\controllers\api\v1;

use yii\filters\Cors;
use yii\rest\ActiveController;

class ServiceController extends ActiveController
{
    public $modelClass = 'app\models\Service';

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
                ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);

            $masterId = \Yii::$app->request->get('master_id');
            if ($masterId) {
                $query->andWhere(['master_id' => (int) $masterId]);
            }

            $category = \Yii::$app->request->get('category');
            if ($category) {
                $query->andWhere(['category' => $category]);
            }

            $showAll = \Yii::$app->request->get('show_all', false);
            if (!$showAll) {
                $query->andWhere(['is_active' => true]);
            }

            return new \yii\data\ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => 50,
                ],
            ]);
        };

        return $actions;
    }
}
