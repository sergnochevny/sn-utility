<?php
/**
 * Date: 16.03.2017
 * Time: 20:15
 */

namespace ait\utility\behaviors;

use yii\behaviors\SluggableBehavior as yiiSluggable;

class SluggableBehavior extends yiiSluggable
{

    /**
     * @inheritdoc
     */
    protected function generateSlug($slugParts)
    {
        $out = parent::generateSlug($slugParts);
        $out = preg_replace('/[^a-zA-Z0-9]+/i', ' ', $out);
        $out = trim(preg_replace('/\s{2,}/', ' ', $out));
        $out = str_replace(' ', '-', $out);
        return  strtolower($out);
    }

}