<?php
/**
 * Date: 09.06.2017
 * Time: 19:12
 */

namespace ait\utility\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\AssetBundle;

/**
 * Class View
 * @package ait\utility\components
 *
 * Appended loadJs for post loading all scripts & styles that will be registered by $this->register*** functions
 * Prevented to append loaded scripts. Add 'force' => true to force load script. see below.
 * $this->registerJsFile('@web/js/modules/app/search-cities.min.js', ['depends' => [JqueryAsset::className()], 'force' => true]);
 *
 */
class View extends \yii\web\View
{

    private $_assetManager;

    protected $deepLevels = [
        self::POS_HEAD => 0,
        self::POS_BEGIN => 0,
        self::POS_END => 0,
        self::POS_READY => 0,
        self::POS_LOAD => 0,
    ];

    protected $registeredJsFiles = [];
    protected $forcedJsFiles = [];

    /**
     * Wraps given content into conditional comments for IE, e.g., `lt IE 9`.
     * @param string $content raw HTML content.
     * @param string $condition condition string.
     * @return string generated HTML.
     */
    private static function wrapIntoCondition($content, $condition)
    {
        if (strpos($condition, '!IE') !== false) {
            return "<!--[if $condition]><!-->\n" . $content . "\n<!--<![endif]-->";
        }
        return "<!--[if $condition]>\n" . $content . "\n<![endif]-->";
    }

    /**
     * @param $ajaxMode
     * @param $lines
     */
    protected function renderJs($ajaxMode, &$lines)
    {
        if (!empty($this->js[self::POS_HEAD])) {
            $lines[] = implode("", $this->js[self::POS_HEAD]);
        }

        if ($ajaxMode) {
            if (!empty($this->js[self::POS_END])) {
                $lines[] = implode("", $this->js[self::POS_END]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $lines[] = implode("", $this->js[self::POS_READY]);
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $lines[] = implode("", $this->js[self::POS_LOAD]);
            }
        } else {
            if (!empty($this->js[self::POS_END])) {
                $lines[] = implode("", $this->js[self::POS_END]);
            }
            if (!empty($this->js[self::POS_READY])) {
                $lines[] = "jQuery(document).ready(function () {" . implode("", $this->js[self::POS_READY]) . "});";
            }
            if (!empty($this->js[self::POS_LOAD])) {
                $lines[] = "jQuery(window).on('load', function () {" . implode("", $this->js[self::POS_LOAD]) . "});";
            }
        }
    }

    /**
     * @param $ajaxMode
     * @param $am AssetManager
     * @return mixed|string
     */
    protected function renderBodyEndHtmlOnload($ajaxMode, $am)
    {
        $lines = [];
        if ($am->injectionCssScheme == AssetManager::SCHEME_INJECTION_ONLOAD) {
            $lines[] = "(function(){window.fb||(window.fb=function(b){function c(i){var j=document.createElement('link');j.setAttribute('href',i),j.setAttribute('rel','stylesheet'),document.body.appendChild(j)}for(var f;0<b.length&&(f=b.shift());)c(f)});";
            $lines[] = "window.fb(['";
            if (!empty($this->cssFiles)) {
                $lines[] = implode("','", array_keys($this->cssFiles));
            }
            $lines[] = "']);";
        }
        if ($am->injectionJsScheme == AssetManager::SCHEME_INJECTION_ONLOAD) {
            if (empty($lines)) $lines = ["(function(){"];
            $lines[] = "Array.isArray||(Array.isArray=function(b){return'[object Array]'===Object.prototype.toString.call(b)}),window.fn||(window.fn=function(b,c,d){for(var t,e=function(w){for(var x=0;x<c.length;x++){var y=c[x],z=new RegExp('^'+f(y).split('\\*').join('.*')+'$').test(w);if(!0===z)return!0}return!1},f=function(w){return w.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g,'\\$&')},g=function(w){return!!document.querySelectorAll('script[src=\''+w+'\']').length},h=function(w){return function(){1>--u&&d()}},s=function(w){if((!g(w))||e(w)){var x=document.createElement('script');x.setAttribute('src',w),x.onload=h(w),document.body.appendChild(x)}else h(w)()},u=b.length;0<b.length&&(t=b.shift());)if(Array.isArray(t)){var v=b.splice(0,b.length);u-=v.length,window.fn(t,c,function(){0<v.length?window.fn(v,c,d):d()})}else s(t)});";
            $lines[] = "window.yii&&function(a){a&&Array.isArray(a)&&(window.yii.reloadableScripts=window.yii.reloadableScripts.concat(a))}(";
            if (!empty($this->forcedJsFiles)) {
                $lines[] = "['" . implode("','", array_keys($this->forcedJsFiles)) . "']";
            }
            $lines[] = ");";
            $lines[] = "window.fn([";
            $scripts = [];
            if (!empty($this->jsFiles[self::POS_HEAD])) {
                ksort($this->jsFiles[self::POS_HEAD], SORT_NUMERIC);
                foreach ($this->jsFiles[self::POS_HEAD] as $jsFiles) {
                    $scripts[] = "['" . implode("','", array_keys($jsFiles)) . "']";
                }
            }

            if (!empty($this->jsFiles[self::POS_END])) {
                ksort($this->jsFiles[self::POS_END], SORT_NUMERIC);
                foreach ($this->jsFiles[self::POS_END] as $jsFiles) {
                    $scripts[] = "['" . implode("','", array_keys($jsFiles)) . "']";
                }
            }
            $lines[] = implode(",", $scripts) . "], [";
            $scripts = '';
            if (!empty($this->forcedJsFiles)) {
                $scripts = "'" . implode("','", array_keys($this->forcedJsFiles)) . "'";
            }
            $lines[] = $scripts . "], function() {";
            $this->renderJs($ajaxMode, $lines);
        }
        if (!empty($lines)) $lines[] = '});})();';

        return (empty($lines) ? '' : preg_replace("/ {2,}/", " ", strtr(Html::script(implode("\n", $lines), ['type' => 'text/javascript']), ["\n" => '', "\r" => ''])));
    }

    /**
     * @param $ajaxMode
     * @param $am AssetManager
     * @return mixed|string
     */
    protected function renderBodyEndHtmlStandard_Inline($ajaxMode, $am)
    {
        $res = '';
        if ($am->injectionJsScheme == AssetManager::SCHEME_INJECTION_STANDARD ||
            $am->injectionJsScheme == AssetManager::SCHEME_INJECTION_INLINE
        ) {
            $res = parent::renderBodyEndHtml($ajaxMode);
        }
        return $res;
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
        $am = $this->getAssetManager();
        if ($am->injectionCssScheme !== AssetManager::SCHEME_INJECTION_ONLOAD) {
            if (!empty($this->cssFiles)) {
                $lines[] = implode("\n", $this->cssFiles);
            }
        }
        if ($am->injectionJsScheme !== AssetManager::SCHEME_INJECTION_ONLOAD) {
            if (!empty($this->jsFiles[self::POS_HEAD])) {
                $lines[] = implode("\n", $this->jsFiles[self::POS_HEAD]);
            }

            if (!empty($this->js[self::POS_HEAD])) {
                $lines[] = Html::script(implode("\n", $this->js[self::POS_HEAD]), ['type' => 'text/javascript']);
            }
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }

    /**
     * @inheritdoc
     */
    protected function renderBodyBeginHtml()
    {
        $res = '';
        $am = $this->getAssetManager();

        if ($am->injectionJsScheme !== AssetManager::SCHEME_INJECTION_ONLOAD) {
            $res = parent::renderBodyBeginHtml();
        }

        return $res;
    }

    /**
     * @inheritdoc
     */
    protected function renderBodyEndHtml($ajaxMode)
    {
        $am = $this->getAssetManager();
        return $this->renderBodyEndHtmlOnload($ajaxMode, $am) . $this->renderBodyEndHtmlStandard_Inline($ajaxMode, $am);
    }

    /**
     * @param $bundle AssetBundle
     * @param $am AssetManager
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
     * Registers the asset manager being used by this view object.
     * @return \ait\utility\components\AssetManager the asset manager. Defaults to the "assetManager" application component.
     */
    public function getAssetManager()
    {
        return $this->_assetManager ?: Yii::$app->getAssetManager();
    }

    /**
     * Sets the asset manager.
     * @param \ait\utility\components\AssetManager $value the asset manager
     */
    public function setAssetManager($value)
    {
        $this->_assetManager = $value;
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        parent::clear();
        $this->registeredJsFiles = [];
        $this->forcedJsFiles = [];
        foreach ($this->deepLevels as $key => $value) {
            $this->deepLevels[$key] = 0;
        };
    }

    public function registerCssFileInline($url, $options = [], $key = null)
    {
        $url = Yii::getAlias($url);
        $key = $key ?: $url;
        $noscript = false;
        $condition = null;

        $depends = ArrayHelper::remove($options, 'depends', []);

        if (empty($depends)) {

            if (isset($options['condition'])) {
                $condition = $options['condition'];
                unset($options['condition']);
            }

            if (isset($options['noscript']) && $options['noscript'] === true) {
                unset($options['noscript']);
                $noscript = true;
            }

            $css = Html::style(file_get_contents($url), $options);
            $css = !empty($noscript) ? '<noscript>' . $css . '</noscript>' : $css;
            $css = !empty($condition) ? self::wrapIntoCondition($css, $condition) : $css;

            $this->cssFiles[$key] = $css;

        } else {
            $this->getAssetManager()->bundles[$key] = Yii::createObject([
                'class' => AssetBundle::className(),
                'baseUrl' => '',
                'css' => [strncmp($url, '//', 2) === 0 ? $url : ltrim($url, '/')],
                'cssOptions' => $options,
                'depends' => (array)$depends,
            ]);
            $this->registerAssetBundle($key);
        }
    }

    public function registerCssFile($url, $options = [], $key = null)
    {
        $am = $this->getAssetManager();

        switch ($am->injectionCssScheme) {
            case AssetManager::SCHEME_INJECTION_INLINE:
                $this->registerCssFileInline($url, $options, $key);
                break;
            default:
                parent::registerCssFile($url, $options, $key);
                break;
        }
    }

    public function registerJsFileInline($url, $options = [], $key = null)
    {
        $url = Yii::getAlias($url);
        $key = $key ?: $url;
        $condition = null;

        $depends = ArrayHelper::remove($options, 'depends', []);

        if (empty($depends)) {
            $position = ArrayHelper::remove($options, 'position', self::POS_END);
            $options['type'] = 'text/javascript';
            if (isset($options['condition'])) {
                $condition = $options['condition'];
                unset($options['condition']);
            }
            $js = Html::script(file_get_contents($url), $options);
            $js = (!empty($condition)) ? self::wrapIntoCondition($js, $condition) : $js;

            $this->jsFiles[$position][$key] = $js;

        } else {
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

    public function registerJsFileOnload($url, $options = [], $key = null)
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
                if ($forceLoad) $this->forcedJsFiles[$key] = $key;
                $level = empty($level) ? $level : $this->deepLevels[$position] - --$level;
                $this->jsFiles[$position][$level][$key] = Html::jsFile($url, $options);
            }
        } else {
            $options = $forceLoad ? array_merge($options, ['force' => $forceLoad]) : $options;
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
    public function registerJsFile($url, $options = [], $key = null)
    {
        $am = $this->getAssetManager();

        switch ($am->injectionJsScheme) {
            case AssetManager::SCHEME_INJECTION_ONLOAD:
                $this->registerJsFileOnload($url, $options, $key);
                break;
            case AssetManager::SCHEME_INJECTION_INLINE:
                $this->registerJsFileInline($url, $options, $key);
                break;
            default:
                parent::registerJsFile($url, $options, $key);
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