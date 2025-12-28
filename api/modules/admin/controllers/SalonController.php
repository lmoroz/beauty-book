<?php

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\models\Salon;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class SalonController extends Controller
{
    public function actionUpdate(): string|\yii\web\Response
    {
        $model = Salon::find()->one();

        if (!$model) {
            throw new NotFoundHttpException('Salon not found.');
        }

        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Настройки салона обновлены.');
                return $this->redirect(['update']);
            }
        }

        return $this->render('update', ['model' => $model]);
    }
}
