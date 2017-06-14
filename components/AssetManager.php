<?php
/**
 * Date: 07.06.2017
 * Time: 23:11
 */

namespace ait\utilities\components;


use ait\utilities\helpers\FileHelper;
use Yii;
use yii\helpers\StringHelper;
use yii\helpers\Url;

/**
 * Class AssetManager
 * @package ait\utilities\components
 *
 *  'assetManager' => [
 *      'class' => 'app\components\AssetManager',
 *      'directInjection' => true;
 *      'beforeCopy' => function ($from, $to) {
 *          return !is_file($from) || !file_exists($to) || (filesize($from) !== filesize($to));
 *      };
 *      'excludeOptions' => ['only' => ['*.js', '*.css', '*.map']];
 *  ],
 *
 */
class AssetManager extends \yii\web\AssetManager
{
    public $directInjection = false;
    public $excludeOptions = [];

    protected function publishFile($src)
    {
        $baseUrl = $this->baseUrl;
        $fileName = basename($src);
        $dir = !$this->directInjection ? $this->hash($src) : '';
        $dstDir = $this->basePath . (!empty($dir) ? DIRECTORY_SEPARATOR : '') . $dir;
        $dstFile = $dstDir . DIRECTORY_SEPARATOR . $fileName;

        if (!is_dir($dstDir)) {
            FileHelper::createDirectory($dstDir, $this->dirMode, true);
        }

        if ($this->directInjection &&
            StringHelper::startsWith(FileHelper::normalizePath($src),
                FileHelper::normalizePath(Yii::getAlias('@webroot')))
        ) {
            $dstFile = FileHelper::normalizePath($src);
            $baseUrl = StringHelper::dirname($dstFile);
            $baseUrl = str_replace(FileHelper::normalizePath(Yii::getAlias('@webroot')), '', $baseUrl);
            $baseUrl = str_replace('\\', '/', $baseUrl);
        } elseif ($this->linkAssets) {
            if (!is_file($dstFile)) {
                symlink($src, $dstFile);
            }
        } elseif (@filemtime($dstFile) < @filemtime($src)) {
            copy($src, $dstFile);
            if ($this->fileMode !== null) {
                @chmod($dstFile, $this->fileMode);
            }
        }

        return [$dstFile, $baseUrl . (!empty($dir) ? '/' : '') . "$dir/$fileName"];
    }

    protected function publishDirectory($src, $options)
    {
        $baseUrl = $this->baseUrl;
        $dir = !$this->directInjection ? $this->hash($src) : '';
        $dstDir = $this->basePath . (!empty($dir) ? DIRECTORY_SEPARATOR : '') . $dir;
        if ($this->linkAssets && !$this->directInjection) {
            if (!is_dir($dstDir)) {
                FileHelper::createDirectory(dirname($dstDir), $this->dirMode, true);
                symlink($src, $dstDir);
            }
        } elseif ($this->directInjection ||
            !empty($options['forceCopy']) ||
            ($this->forceCopy && !isset($options['forceCopy'])) || !is_dir($dstDir)
        ) {

            if ($this->directInjection &&
                StringHelper::startsWith(FileHelper::normalizePath($src),
                    FileHelper::normalizePath(Yii::getAlias('@webroot')))
            ) {
                $dstDir = FileHelper::normalizePath($src);
                $baseUrl = FileHelper::normalizePath($src);
                $baseUrl = str_replace(FileHelper::normalizePath(Yii::getAlias('@webroot')), '', $baseUrl);
                $baseUrl = str_replace('\\', '/', $baseUrl);
            } else {
                $opts = array_merge(
                    $options,
                    [
                        'dirMode' => $this->dirMode,
                        'fileMode' => $this->fileMode,
                        'copyEmptyDirectories' => false,
                    ]
                );

                if ($this->directInjection && !empty($this->excludeOptions)) {
                    $opts = array_merge($opts, $this->excludeOptions);
                }
                if (!isset($opts['beforeCopy'])) {
                    if ($this->beforeCopy !== null) {
                        $opts['beforeCopy'] = $this->beforeCopy;
                    } else {
                        $opts['beforeCopy'] = function ($from, $to) {
                            return strncmp(basename($from), '.', 1) !== 0;
                        };
                    }
                }
                if (!isset($opts['afterCopy']) && $this->afterCopy !== null) {
                    $opts['afterCopy'] = $this->afterCopy;
                }
                $opts['link'] = $this->linkAssets;
                FileHelper::copyDirectory($src, $dstDir, $opts);

            }
        }

        return [$dstDir, $baseUrl . (!empty($dir) ? '/' : '') . $dir];
    }

    public function getAssetUrl($bundle, $asset)
    {
        if (($actualAsset = $this->resolveAsset($bundle, $asset)) !== false) {
            if (strncmp($actualAsset, '@web/', 5) === 0) {
                $asset = substr($actualAsset, 5);
                $basePath = Yii::getAlias('@webroot');
                $baseUrl = Yii::getAlias('@web');
            } else {
                $asset = Yii::getAlias($actualAsset);
                $basePath = $this->basePath;
                $baseUrl = $this->baseUrl;
            }
        } else {
            $basePath = $bundle->basePath;
            $baseUrl = $bundle->baseUrl;
        }

        if (!Url::isRelative($asset) || strncmp($asset, '/', 1) === 0) {
            return $asset;
        }

        if ($this->appendTimestamp && ($timestamp = @filemtime("$basePath/$asset")) > 0) {
            return "$baseUrl/$asset?v=$timestamp";
        } else {
            return "$baseUrl/$asset";
        }
    }
}