<?php

namespace frontend\controllers;

use \yii\web\Controller;
use \common\models\Fruit\Apple;
use \common\Exception\AppleException;

class AppleController extends Controller
{

    public function actionGenerate()
    {
        $apple = new Apple();
        $apple->save();
        return $this->getAppleJson($apple);
    }

    public function actionDrop()
    {
        $id = filter_input(INPUT_POST, 'id');
        $apple = Apple::findById($id);
        if ($apple) {
            $apple->fallToGround()->save();
        }
        return $this->getAppleJson($apple);
    }

    public function actionEat()
    {
        $id = filter_input(INPUT_POST, 'id');
        $percents = filter_input(INPUT_POST, 'percents');
        $apple = Apple::findById($id);
        try {
            if ($apple) {
                $apple->eat($percents)->save();
            }
            return $this->getAppleJson($apple);
        } catch (AppleException $exc) {
            return json_encode([
                'error' => $exc->getMessage()
            ]);
        }
    }

    public function actionRemove()
    {
        $id = filter_input(INPUT_POST, 'id');
        $apple = Apple::findById($id);
        try {
            if ($apple) {
                $apple->remove()->save();
            }
            return $this->getAppleJson($apple);
        } catch (AppleException $exc) {
            return json_encode([
                'error' => $exc->getMessage()
            ]);
        }
    }

    public function actionHour()
    {
        $apples = Apple::findAllExist();
        try {
            foreach ($apples as $apple) {
                $apple->created_at = date('Y-m-d H:i:s', strtotime($apple->created_at) - 3600);
                if ($apple->dropped_at) {
                    $apple->dropped_at = date('Y-m-d H:i:s', strtotime($apple->dropped_at) - 3600);
                }
                $apple->save();
            }
        } catch (AppleException $exc) {
            return json_encode([
                'error' => $exc->getMessage()
            ]);
        }
    }

    private function getAppleJson(Apple $apple)
    {
        $attributes = $apple->getAttributes();
        $attributes['statusName'] = Apple::getStatusName($apple->status);
        return json_encode($attributes);
    }
}
