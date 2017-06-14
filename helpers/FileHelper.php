<?php

namespace ait\utilities\helpers;

use yii\base\InvalidParamException;
use yii\helpers\StringHelper;

/**
 * Date: 14.06.2017
 * Time: 13:40
 */
class FileHelper extends \yii\helpers\FileHelper
{

    /**
     * @inheritdoc
     */
    private static function firstWildcardInPattern($pattern)
    {
        $wildcards = ['*', '?', '[', '\\'];
        $wildcardSearch = function ($r, $c) use ($pattern) {
            $p = strpos($pattern, $c);

            return $r === false ? $p : ($p === false ? $r : min($r, $p));
        };

        return array_reduce($wildcards, $wildcardSearch, false);
    }

    /**
     * @inheritdoc
     */
    private static function parseExcludePattern($pattern, $caseSensitive)
    {
        if (!is_string($pattern)) {
            throw new InvalidParamException('Exclude/include pattern must be a string.');
        }

        $result = [
            'pattern' => $pattern,
            'flags' => 0,
            'firstWildcard' => false,
        ];

        if (!$caseSensitive) {
            $result['flags'] |= self::PATTERN_CASE_INSENSITIVE;
        }

        if (!isset($pattern[0])) {
            return $result;
        }

        if ($pattern[0] === '!') {
            $result['flags'] |= self::PATTERN_NEGATIVE;
            $pattern = StringHelper::byteSubstr($pattern, 1, StringHelper::byteLength($pattern));
        }
        if (StringHelper::byteLength($pattern) && StringHelper::byteSubstr($pattern, -1, 1) === '/') {
            $pattern = StringHelper::byteSubstr($pattern, 0, -1);
            $result['flags'] |= self::PATTERN_MUSTBEDIR;
        }
        if (strpos($pattern, '/') === false) {
            $result['flags'] |= self::PATTERN_NODIR;
        }
        $result['firstWildcard'] = self::firstWildcardInPattern($pattern);
        if ($pattern[0] === '*' && self::firstWildcardInPattern(StringHelper::byteSubstr($pattern, 1, StringHelper::byteLength($pattern))) === false) {
            $result['flags'] |= self::PATTERN_ENDSWITH;
        }
        $result['pattern'] = $pattern;

        return $result;
    }

    private static function normalizeOptions(array $options)
    {
        if (!array_key_exists('caseSensitive', $options)) {
            $options['caseSensitive'] = true;
        }
        if (isset($options['except'])) {
            foreach ($options['except'] as $key => $value) {
                if (is_string($value)) {
                    $options['except'][$key] = self::parseExcludePattern($value, $options['caseSensitive']);
                }
            }
        }
        if (isset($options['only'])) {
            foreach ($options['only'] as $key => $value) {
                if (is_string($value)) {
                    $options['only'][$key] = self::parseExcludePattern($value, $options['caseSensitive']);
                }
            }
        }
        return $options;
    }

    /**
     * @inheritdoc
     */
    public static function copyDirectory($src, $dst, $options = [])
    {
        $src = static::normalizePath($src);
        $dst = static::normalizePath($dst);

        if ($src === $dst || strpos($dst, $src . DIRECTORY_SEPARATOR) === 0) {
            throw new InvalidParamException('Trying to copy a directory to itself or a subdirectory.');
        }
        if (!is_dir($dst)) {
            static::createDirectory($dst, isset($options['dirMode']) ? $options['dirMode'] : 0775, true);
        }

        $handle = opendir($src);
        if ($handle === false) {
            throw new InvalidParamException("Unable to open directory: $src");
        }
        if (!isset($options['basePath'])) {
            // this should be done only once
            $options['basePath'] = realpath($src);
            $options = self::normalizeOptions($options);
        }
        while (($file = readdir($handle)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $from = $src . DIRECTORY_SEPARATOR . $file;
            $to = $dst . DIRECTORY_SEPARATOR . $file;
            if (static::filterPath($from, $options)) {
                if (isset($options['beforeCopy']) && !call_user_func($options['beforeCopy'], $from, $to)) {
                    continue;
                }
                if (is_file($from)) {
                    copy($from, $to);
                    if (isset($options['fileMode'])) {
                        @chmod($to, $options['fileMode']);
                    }
                } else {
                    // recursive copy, defaults to true
                    if (!isset($options['recursive']) || $options['recursive']) {
                        static::copyDirectory($from, $to, $options);
                    }
                }
                if (isset($options['afterCopy'])) {
                    call_user_func($options['afterCopy'], $from, $to);
                }
            }
        }
        closedir($handle);
    }

}