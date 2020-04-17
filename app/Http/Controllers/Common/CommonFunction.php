<?php


namespace App\Http\Controllers\Common;


class CommonFunction
{
    /**
     * 字符串sql过滤
     * @return string
     */
    public function filterString($str) {
        $search = array('|', '&', ';', '$', '%', "'", "\"", "\\'", "\\\"", "<", ">", "(", ")", "+", "\\", "insert", "delete", "select", "1=1", "update", "sleep");
        $str2 = str_ireplace($search, '', $str);
        $str2 = preg_replace("{\t}", "", $str2);
        $str2 = preg_replace("{\r\n}", "", $str2);
        $str2 = preg_replace("{\r}", "", $str2);
        $str2 = preg_replace("{\n}", "", $str2);
        return $str2;
    }
    /**
     * 验证手机号码
     * @param string $phone
     * @return boolean
     */
    public function checkPhone($phone) {
        if (preg_match("/^1([3456789]\d{9})$/", $phone)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证必须为大于0的整数
     * @param int $int
     * @return boolean
     */
    public function checkInteger($int)
    {
        if(preg_match("/^[1-9]\d*$/",$int)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 验证一卡通卡号：12位数字
     * @param string $str
     * @return boolean
     */
    public function checkCardIdTwelve($str) {
        if (preg_match("/^15[0-9]{10}$/", $str)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @return mixed
     */
    public function get_client_ip($type = 0) {
        $type = $type ? 1 : 0;
        static $ip = NULL;
        if ($ip !== NULL) return $ip [$type];
        if (isset($_SERVER['HTTP_X_CLIENTIP'])) {
            $ip = $_SERVER['HTTP_X_CLIENTIP'];
        } elseif (isset ($_SERVER ['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER ['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset ($arr [$pos]);
            $ip = trim($arr [0]);
        } elseif (isset ($_SERVER ['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER ['HTTP_CLIENT_IP'];
        } elseif (isset ($_SERVER ['REMOTE_ADDR'])) {
            $ip = $_SERVER ['REMOTE_ADDR'];
        } elseif (isset($_SERVER['HTTP_X_CLIENTIP']) && isset ($_SERVER ['REMOTE_ADDR'])) {
            $ip = $_SERVER ['HTTP_X_CLIENTIP'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && isset ($_SERVER ['HTTP_X_CLIENTIP'])) {
            $ip = $_SERVER ['HTTP_X_REAL_IP'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip [$type];
    }

}