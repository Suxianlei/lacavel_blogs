<?php


namespace App\Http\Controllers\Common;


class JSON
{

    /**
     * 转义JSON格式中的特殊字符
     *
     * @param string $str
     */
    public static function trunMeaningString($str)
    {
        // $str = str_replace ( "\r\n", "", $str );
        // $str = str_replace ( "\n", "", $str );
        // $str = str_replace ( "\r", "", $str );
        $str = str_replace('"', '\"', $str);
        $str = str_replace("'", "\'", $str);
        // $str = str_replace ( ':', '\:', $str );
        $str = str_replace('[', '\[', $str);
        $str = str_replace(']', '\]', $str);
        $str = str_replace('{', '\{', $str);
        $str = str_replace('}', '\}', $str);
        return $str;
    }

    /**
     * 将数据编码成JSON格式字符串（支持中文）
     *
     * @param array $arr
     * @return string
     */
    public static function Encode($arr)
    {
        return urldecode(json_encode(JSON::url_encode($arr)));
    }

    /**
     * 将json字符串转换成对象（支持中文）
     *
     * @param string $json
     * @return \stdClass | array
     */
    public static function Decode($json)
    {
        $json = iconv('GBK', 'utf-8', $json);
        return json_decode($json);
    }

    /**
     * json_ncode前先进行url编码
     *
     * @param array $arr
     * @return string|array
     */
    private static function url_encode($arr)
    {
        if (is_string($arr)) {
            return urlencode($arr);
        }
        if (is_object($arr)) {
            $arr = (array) $arr;
        }
        if (is_array($arr)) {
            foreach ($arr as $key => $value) {
                $arr[urlencode($key)] = JSON::url_encode($value);
            }
        }
        return $arr;
    }

}