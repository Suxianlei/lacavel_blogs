<?php


namespace App\Http\Controllers\Common;


class HttpResponse
{
    /**
     * @param $code
     * 0:表示成功  1:表示失败  1001：鉴权失败
     * @param $msg
     * 提示信息
     * @param $count
     * 数据条数
     * @param $data
     * 数据
     * @return string
     */
    static public function exitJSON($code,$msg,$data=[],$count=null){
        $R = new \stdClass();
        $R->code = $code;
        $R->msg = $msg;
        $R->count = $count;
        $R->data = $data;
        $res = JSON::Encode($R);
        header('Content-Type: application/json');
        exit($res);
    }
}