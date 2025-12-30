<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\Master;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class MasterController extends Controller
{
    public function actionIndex(): string
    {
        $query = Master::find()
            ->with(['specializations'])
            ->orderBy(['sort_order' => SORT_ASC]);

        $status = Yii::$app->request->get('status');
        if ($status) {
            $query->andWhere(['status' => $status]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'masters' => $dataProvider->getModels(),
        ]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Master();

        if ($model->load(Yii::$app->request->post())) {
            $this->prepareModel($model);
            $this->handlePhotoUpload($model);
            $model->specialization_ids = Yii::$app->request->post('specialization_ids', []);
            if ($model->save()) {
                $model->saveSpecializations();
                Yii::$app->session->setFlash('success', 'Мастер создан.');
                return $this->redirect(['view', 'id' => $model->id]);
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
            $this->prepareModel($model);
            $this->handlePhotoUpload($model);
            $model->specialization_ids = Yii::$app->request->post('specialization_ids', []);
            if ($model->save()) {
                $model->saveSpecializations();
                Yii::$app->session->setFlash('success', 'Мастер обновлён.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id): \yii\web\Response
    {
        $model = $this->findModel($id);
        $model->status = 'inactive';
        $model->save(false);

        Yii::$app->session->setFlash('success', 'Мастер деактивирован.');
        return $this->redirect(['index']);
    }

    private function prepareModel(Master $model): void
    {
        if (empty($model->slug) && !empty($model->name)) {
            $slug = strtolower(trim($model->name));
            $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
            $slug = preg_replace('/[\s-]+/', '-', $slug);
            $model->slug = $slug;
        }
    }

    private function handlePhotoUpload(Master $model): void
    {
        $file = UploadedFile::getInstanceByName('photo_file');

        if (!$file || $file->error !== UPLOAD_ERR_OK) {
            return;
        }

        $uploadDir = Yii::getAlias('@webroot/uploads/masters');

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $ext = strtolower($file->extension);
        $filename = $model->slug ?: ('master_' . time());
        $filename = preg_replace('/[^a-z0-9_-]/', '', $filename);
        $saveName = $filename . '.' . $ext;

        $file->saveAs($uploadDir . '/' . $saveName);

        $model->photo = '/uploads/masters/' . $saveName;
    }

    private function findModel(int $id): Master
    {
        $model = Master::find()->with(['specializations'])->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('Master not found.');
        }
        return $model;
    }
}
