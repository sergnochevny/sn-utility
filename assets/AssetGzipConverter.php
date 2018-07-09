<?php
/**
 * Date: 21.03.2017
 * Time: 11:49
 */

namespace sn\utility\assets;


use Phar;
use PharData;
use yii\base\Component;
use yii\web\AssetConverterInterface;

class AssetGzipConverter extends Component implements AssetConverterInterface
{
    public function convert($asset, $basePath)
    {
        if ((strrpos($asset, '.js') || strrpos($asset, '.css')) && (strrpos($asset, '.gz') == false)) {
            $target = $asset . '.gz';
            $dest = $basePath . DIRECTORY_SEPARATOR . $target;
            $source = $basePath . DIRECTORY_SEPARATOR . $asset;
            if (@filemtime($dest) < @filemtime($source)) {
                $r_source = fopen($source, 'r');
                $r_dest = gzopen($dest, 'w');
                gzwrite($r_dest, fread($r_source, filesize($source)));
                fclose($r_source);
                gzpassthru($r_dest);
                gzclose($r_dest);
            }
        }
        return $asset;
    }


}