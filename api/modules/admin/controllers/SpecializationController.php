<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\Specialization;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SpecializationController extends Controller
{
    public function actionIndex(): string
    {
        $models = Specialization::find()->orderBy(['sort_order' => SORT_ASC])->all();

        return $this->render('index', ['models' => $models]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Specialization();

        if ($model->load(Yii::$app->request->post())) {
            $this->prepareSlug($model);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('master', 'Specialization created.'));
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
                Yii::$app->session->setFlash('success', Yii::t('master', 'Specialization updated.'));
                return $this->redirect(['index']);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id): \yii\web\Response
    {
        $model = $this->findModel($id);

        $mastersCount = $model->getMasters()->count();
        if ($mastersCount > 0) {
            Yii::$app->session->setFlash(
                'error',
                Yii::t('master', 'Cannot delete: {count} master(s) use this specialization.', ['count' => $mastersCount])
            );
            return $this->redirect(['index']);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', Yii::t('master', 'Specialization deleted.'));
        return $this->redirect(['index']);
    }

    private function prepareSlug(Specialization $model): void
    {
        if (empty($model->slug) && !empty($model->name)) {
            $slug = strtolower(trim($model->name));
            $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
            $slug = preg_replace('/[\s-]+/', '-', $slug);
            $model->slug = $slug;
        }
    }

    private function findModel(int $id): Specialization
    {
        $model = Specialization::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Specialization not found.');
        }
        return $model;
    }
}
