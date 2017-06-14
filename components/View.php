<?php
/**
 * Date: 09.06.2017
 * Time: 19:12
 */

namespace ait\utilities\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\AssetBundle;

class View extends \yii\web\View
{
    protected $deepLevels = [
        self::POS_HEAD => 0,
        self::POS_BEGIN => 0,
        self::POS_END => 0,
        self::POS_READY => 0,
        self::POS_LOAD => 0,
    ];

    protected $registeredJsFiles = [];
    protected $forceJsFiles = [];

    /**
     * @inheritdoc
     */
    protected function renderBodyBeginHtml()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    protected function renderHeadHtml()
    {
        $lines = [];
        if (!empty($this->metaTags)) {
            $lines[] = implode("\n", $this->metaTags);
        }

        if (!empty($this->linkTags)) {
            $lines[] = implode("\n", $this->linkTags);
        }

        if (!empty($this->css)) {
            $lines[] = implode("\n", $this->css);
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * @inheritdoc
     */
    protected function renderBodyEndHtml($ajaxMode)
    {
        $lines = [];

        $_scripts = ["(function(){function fb(a){function d(g){var h=document.createElement('link');h.setAttribute('href',g),h.setAttribute('rel','stylesheet'),document.head.appendChild(h)}for(var e;0<a.length&&(e=a.shift());)d(e)};"];
        $_scripts[] = "Array.isArray||(Array.isArray=function(a){return'[object Array]'===Object.prototype.toString.call(a)});function fa(j,k){function l(q){return function(){console.log(q+' was loaded.'),1>--o&&k()}}function m(q){var r=document.createElement('script');r.setAttribute('src',q),r.onload=l(q),document.head.appendChild(r)}for(var n,o=j.length;0<j.length&&(n=j.shift());)if(Array.isArray(n)){var p=j.splice(0,j.length);o-=p.length,fa(n,function(){0<p.length?fa(p,k):k()})}else m(n)};";
        $_scripts[] = "fb(['";
        if (!empty($this->cssFiles)) {
            $_scripts[] = implode("','", array_keys($this->cssFiles));
        }
        $_scripts[] = "']);";

        $_scripts[] = "fa([";
        $js_stack = [];
        if (!empty($this->jsFiles[self::POS_HEAD])) {
            ksort($this->jsFiles[self::POS_HEAD], SORT_NUMERIC);
            foreach ($this->jsFiles[self::POS_HEAD] as $jsFiles) {
                $js_stack[] = "['" . implode("','", array_keys($jsFiles)) . "']";
            }
        }

        if (!empty($this->jsFiles[self::POS_END])) {
            ksort($this->jsFiles[self::POS_END], SORT_NUMERIC);
            foreach ($this->jsFiles[self::POS_END] as $jsFiles) {
                $js_stack[] = "['" . implode("','", array_keys($jsFiles)) . "']";
            }
        }
        $_scripts[] = implode(",", $js_stack) . "], [";

        $js_stack = '';
        if (!empty($this->forceJsFiles)) {
            $js_stack = "'" . implode("','", array_keys($this->forceJsFiles)) . "'";
        }
        $_scripts[] = $js_stack . "], function() {";

        if (!empty($this->js[self::POS_HEAD])) {
            $_scripts[] = $this->js[self::POS_HEAD];
        }

        if ($ajaxMode) {
            $scripts = [];
            if (!empty($this->js[self::POS_END])) {
                $scripts[] = implode("", $this->js[self::POS_END]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $scripts[] = implode("", $this->js[self::POS_READY]);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $scripts[] = implode("", $this->js[self::POS_LOAD]);
            }
            if (!empty($scripts)) {
                $lines[] = Html::script(implode("", $scripts), ['type' => 'text/javascript']);
            }
        } else {
            if (!empty($this->js[self::POS_END])) {
                $_scripts[] = $this->js[self::POS_END];
            }
            if (!empty($this->js[self::POS_READY])) {
                $js = "jQuery(document).ready(function () {" . implode("", $this->js[self::POS_READY]) . "});";
                $_scripts[] = $js;
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $js = "jQuery(window).on('load', function () {" . implode("", $this->js[self::POS_LOAD]) . "});";
                $_scripts[] = $js;
            }
        }
        $_scripts[] = '});})();';


        return (empty($lines) ? '' : implode("", $lines)) . (empty($_scripts) ? '' : preg_replace("/ {2,}/", " ", strtr(Html::script(implode("\n", $_scripts), ['type' => 'text/javascript']), ["\n" => '', "\r" => ''])));
    }

    /**
     * @param $bundle
     * @param $am
     */
    protected function _registerDependencies($bundle, $am)
    {
        $pos = isset($bundle->jsOptions['position']) ? $bundle->jsOptions['position'] : null;
        $level = (isset($bundle->jsOptions['level']) ? $bundle->jsOptions['level'] : 1);
        $bundle->jsOptions['level'] = !empty($bundle->depends) ? $level : 0;
        if (!empty($bundle->depends) &&
            ($level++ > $this->deepLevels[empty($pos) ? self::POS_END : $pos])
        ) $this->deepLevels[empty($pos) ? self::POS_END : $pos] = $level;
        foreach ($bundle->depends as $dep) {
            $_bundle = $am->getBundle($dep);
            $_bundle->jsOptions['level'] = (!isset($_bundle->jsOptions['level']) ||
                (!empty($_bundle->jsOptions['level']) && ($_bundle->jsOptions['level'] < $level))) ?
                $level : $_bundle->jsOptions['level'];
            $this->registerAssetBundle($dep, $pos);
        }
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        parent::clear();
        $this->registeredJsFiles = [];
        $this->forceJsFiles = [];
        foreach ($this->deepLevels as $key => $value) {
            $this->deepLevels[$key] = 0;
        };
    }

    /**
     * @inheritdoc
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        $url = Yii::getAlias($url);
        $key = $key ?: $url;

        $depends = ArrayHelper::remove($options, 'depends', []);
        $level = ArrayHelper::remove($options, 'level', 0);
        $forceLoad = ArrayHelper::remove($options, 'force', false);

        if (empty($depends)) {
            $position = ArrayHelper::remove($options, 'position', self::POS_END);
            if (empty($this->registeredJsFiles[$key])) {
                $this->registeredJsFiles[$key] = $key;
                if ($forceLoad) $this->forceJsFiles[$key] = $key;
                $level = empty($level) ? $level : $this->deepLevels[$position] - --$level;
                $this->jsFiles[$position][$level][$key] = Html::jsFile($url, $options);
            }
        } else {
            $options = $forceLoad ? array_merge($options, ['force', $forceLoad]) : $options;
            $this->getAssetManager()->bundles[$key] = Yii::createObject([
                'class' => AssetBundle::className(),
                'baseUrl' => '',
                'js' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
                'jsOptions' => $options,
                'depends' => (array)$depends,
            ]);
            $this->registerAssetBundle($key);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerAssetBundle($name, $position = null)
    {
        $am = $this->getAssetManager();
        if (!isset($this->assetBundles[$name])) {
            $this->assetBundles[$name] = false;
            $bundle = $am->getBundle($name);
            // register dependencies
            $this->_registerDependencies($bundle, $am);
            if (empty($this->assetBundles[$name])) $this->assetBundles[$name] = $bundle;

        } elseif ($this->assetBundles[$name] === false) {
            throw new InvalidConfigException("A circular dependency is detected for bundle '$name'.");
        } else {
            $bundle = $this->assetBundles[$name];
            $this->_registerDependencies($bundle, $am);
        }

        if ($position !== null) {
            $pos = isset($bundle->jsOptions['position']) ? $bundle->jsOptions['position'] : null;
            if ($pos === null) {
                $bundle->jsOptions['position'] = $pos = $position;
            } elseif ($pos > $position) {
                throw new InvalidConfigException("An asset bundle that depends on '$name' has a higher javascript file position configured than '$name'.");
            }
            // update position for all dependencies
            foreach ($bundle->depends as $dep) {
                $this->registerAssetBundle($dep, $pos);
            }
        }

        return $bundle;
    }

}