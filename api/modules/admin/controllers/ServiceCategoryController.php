<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\ServiceCategory;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class ServiceCategoryController extends Controller
{
    public function actionIndex(): string
    {
        $models = ServiceCategory::find()->orderBy(['sort_order' => SORT_ASC])->all();

        return $this->render('index', ['models' => $models]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new ServiceCategory();

        if ($model->load(Yii::$app->request->post())) {
            $this->prepareSlug($model);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Категория создана.');
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
            $this->prepareSlug($model);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Категория обновлена.');
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id): \yii\web\Response
    {
        $model = $this->findModel($id);

        $servicesCount = $model->getServices()->count();
        if ($servicesCount > 0) {
            Yii::$app->session->setFlash(
                'error',
                "Нельзя удалить: {$servicesCount} услуг(а) используют эту категорию."
            );
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Категория удалена.');
        return $this->redirect(['index']);
    }

    private function prepareSlug(ServiceCategory $model): void
    {
        if (empty($model->slug) && !empty($model->name)) {
            $slug = strtolower(trim($model->name));
            $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
            $slug = preg_replace('/[\s-]+/', '-', $slug);
            $model->slug = $slug;
        }
    }

    private function findModel(int $id): ServiceCategory
    {
        $model = ServiceCategory::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Service category not found.');
        }
        return $model;
    }
}
