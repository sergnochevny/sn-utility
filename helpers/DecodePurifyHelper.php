<?php
/**
 * Date: 04.04.2017
 * Time: 19:56
 */

namespace ait\utilities\helpers;

class DecodePurifyHelper
{

    public static function purify($data)
    {
        if (preg_match('#[ÂâÃÅÆË]+#', $data, $matches)) {
            $data = mb_convert_encoding($data, 'Windows-1252', 'UTF-8');
            if (preg_match_all('|â.{4}|', $data, $matches)) {
                foreach ($matches as $_matches) {
                    foreach ($_matches as $match) {
                        $data = str_replace($match, mb_convert_encoding($match, 'ISO-8859-1', 'UTF-8'), $data);
                    }
                }
            }
            if (preg_match_all('|â.{2}|', $data, $matches)) {
                foreach ($matches as $_matches) {
                    foreach ($_matches as $match) {
                        $data = str_replace($match, mb_convert_encoding($match, 'ISO-8859-1', 'UTF-8'), $data);
                    }
                }
            }
        }
        return $data;
    }

}