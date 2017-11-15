<?php
/**
 * Date: 12.04.2017
 * Time: 17:06
 */

namespace ait\utility\traits;


use Yii;

trait SaveContextTrait
{

    public function load($data, $formName = null)
    {

        /*
         * @var yii\base\Model $this
         */

        $scope = $formName === null ? $this->formName() : $formName;
        if ($scope === '' && !empty($data)) {
            $this->setAttributes($data);
            return true;
        } elseif (isset($data[$scope])) {
            $filters = Yii::$app->session->get('filters');
            $filters[$scope] = $data[$scope];
            Yii::$app->session->set('filters', $filters);
            $this->setAttributes($data[$scope]);

            if (isset($data['page'])) {
                $pages = Yii::$app->session->get('pages');
                $pages[$scope] = $data['page'];
                Yii::$app->session->set('pages', $pages);
            } else {
                $pages = Yii::$app->session->get('pages');
                if (isset($pages[$scope])) {
                    unset($pages[$scope]);
                    Yii::$app->session->set('pages', $pages);
                }
            }

            return true;
        } else {
            if (isset($data['page'])) {
                $pages = Yii::$app->session->get('pages');
                $pages[$scope] = $data['page'];
                Yii::$app->session->set('pages', $pages);
            }
            $pages = Yii::$app->session->get('pages');
            if (isset($pages[$scope])) {
                $_GET['page'] = $pages[$scope];
            }
            $filters = Yii::$app->session->get('filters');
            if (isset($filters[$scope])) {
                $this->setAttributes($filters[$scope]);

                return true;
            } else {
                return false;
            }
        }
    }

}