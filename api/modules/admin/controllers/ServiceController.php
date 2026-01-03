<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\Service;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ServiceController extends Controller
{
    public function actionIndex(): string
    {
        $query = Service::find()->with(['master', 'category'])->orderBy(['master_id' => SORT_ASC, 'sort_order' => SORT_ASC]);

        $masterId = Yii::$app->request->get('master_id');
        if ($masterId) {
            $query->andWhere(['master_id' => (int) $masterId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        return $this->render('index', [
            'services' => $dataProvider->getModels(),
        ]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Service();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Услуга создана.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', ['model' => $model]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Услуга обновлена.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id): \yii\web\Response
    {
        $model = $this->findModel($id);
        $model->is_active = false;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'Услуга деактивирована.');
        return $this->redirect(['index']);
    }

    private function findModel(int $id): Service
    {
        $model = Service::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Service not found.');
        }
        return $model;
    }
}
