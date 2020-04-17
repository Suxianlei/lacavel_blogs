<?php


namespace App\Http\Controllers\Common;


class Verify
{
    /**
     * 判断是否缺少请求参数 （拒绝负值通过）
     *
     * @param
     *            args 接收不定参数
     * @return boolean http请求中是否存在所有参数
     */
    static public function existsingAll()
    {
        $num = func_num_args();
        $args = func_get_args();
        foreach ($args as $key) {
            if (! isset($_REQUEST[$key]) || $_REQUEST[$key] < 0) {
                HttpResponse::exitJSON(1, "缺少请求参数$key~！");
            }
        }
        return true;
    }

    /**
     * 判断是否缺少请求参数（允许负值通过）
     *
     * @param
     *            args 接收不定参数
     * @return boolean http请求中是否存在所有参数
     */
    static public function existsingAllKeys()
    {
        $num = func_num_args();
        $args = func_get_args();
        foreach ($args as $key) {
            if (! isset($_REQUEST[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * 身份证正反面识别
     * @param $img_url
     * @param string $side
     * @return bool|string
     */
    static public function verifyCard($img_url,$side= 'face'){
        $url = "https://dm-51.data.aliyun.com/rest/160601/ocr/ocr_idcard.json";
        //$appcode = "6b1d3e8c715c49c897fe9153e3f28033";已经使用完毕
        $appcode = "032e8ac0252346dd91297f956bbc128a";//捎来捎去
        $file = $img_url;
        //如果输入带有inputs, 设置为True，否则设为False
        $is_old_format = false;
        //如果没有configure字段，config设为空
        $config = array(
            "side" => $side// $side 是face或者back
        );
        //$config = array();
        // if($fp = fopen($file, "rb", 0)) {
        //$binary = fread($fp, filesize($file)); // 文件读取
        //$binary = fread($fp, filesize($file));
        @$binary = file_get_contents($file);
        // fclose($fp);
        $base64 = base64_encode($binary); // 转码
        // }
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
        $querys = "";
        if($is_old_format == TRUE){
            $request = array();
            $request["image"] = array(
                "dataType" => 50,
                "dataValue" => "$base64"
            );

            if(count($config) > 0){
                $request["configure"] = array(
                    "dataType" => 50,
                    "dataValue" => json_encode($config)
                );
            }
            $body = json_encode(array("inputs" => array($request)));
        }else{
            $request = array(
                "image" => "$base64"
            );
            if(count($config) > 0){
                $request["configure"] = json_encode($config);
            }
            $body = json_encode($request);
        }
        $method = "POST";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$url, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($curl);
        //$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        //$rheader = substr($result, 0, $header_size);
        //$rbody = substr($result, $header_size);
        $rbody = $result;
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        var_dump($httpCode);
        if($httpCode == 200){
            if($is_old_format){
                $output = json_decode($rbody, true);
                $result_str = $output["outputs"][0]["outputValue"]["dataValue"];
            }else{
                $result_str = $rbody;
            }
            return $result_str;
            //printf("result is :\n %s\n", $result_str);
        }else{
            $res = ["success"=>false];
            return json_encode($res);
            //printf("Http error code: %d\n", $httpCode);
            //printf("Http error code: %d\n", $httpCode);
            //printf("Error msg in body: %s\n", $rbody);
            //printf("header: %s\n", $rheader);
        }
    }

    /**
     * 银行卡识别卡号
     * @param $img_url
     * @return string
     */
    static public  function verifyBnakCard($img_url){
        $host = "https://yhk.market.alicloudapi.com";
        $path = "/rest/160601/ocr/ocr_bank_card.json";
        $method = "POST";
        $appcode = "6b1d3e8c715c49c897fe9153e3f28033";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type".":"."application/json; charset=UTF-8");
        $querys = "";
        @$binary = file_get_contents($img_url);
        $base64 = base64_encode($binary); // 转码
        $bodys = "{\"inputs\":[{\"image\":{\"dataType\":50,\"dataValue\":\"$base64\"}}]}";
        $url = $host . $path;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $rheader = substr($result, 0, $header_size);
        $rbody = substr($result, $header_size);
        if($httpCode == 200){
            $output = json_decode($rbody, true);
            $result_str = $output["outputs"][0]["outputValue"]["dataValue"];
            return $result_str;
        }else{
            $res = ["success"=>false];
            return json_encode($res);
        }

        var_dump(curl_exec($curl));
    }

    /**
     * 身份证号码识别
     * @param $cardNumber
     * @return array|bool
     */
    public function getRegionByNum($cardNumber){
        $host = "http://jisuidcard.market.alicloudapi.com";
        $path = "/idcard/query";
        $method = "GET";
        $appcode = "6b1d3e8c715c49c897fe9153e3f28033";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "idcard=".$cardNumber;
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $response =  curl_exec($curl);
        $res = json_decode($response,true);
        if($res["status"] != 0){
            return false;
        }else{
            $region = new ModelConfigRegion();
            //模糊查询省id
            $arr = [];
            $arr["province_id"] = $region->getProvinceIdByName($res["result"]["province"]);
            $arr["city_id"] = $region->getCityIdByName($res["result"]["city"],$arr["province_id"]);
            $arr["county_id"] = $region->getCountyIdByName($res["result"]["town"],$arr["city_id"]);
            return $arr;
        }

    }
    static  public function verifyBankNum($num,$name='',$phone_num='',$cert_id=''){
        $host = "http://lundroid.market.alicloudapi.com";
        $path = "/lianzhuo/verifi";
        $method = "GET";
        $appcode = "6b1d3e8c715c49c897fe9153e3f28033";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "acct_pan=".$num;
        if($name){
            $querys .= "&acct_name=$name";
        }
        if($phone_num){
            $querys .= "&phone_num=$phone_num";
        }
        if($cert_id){
            $querys .= "&cert_id=$cert_id";
        }
        //$querys = "acct_pan=$num&cert_id=$cert_id";
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        var_dump(curl_exec($curl));
    }
    //实名校验(已经付过钱)
    static public function verifyUserCard($user_number,$user_name){
        $host = "https://fediscern.market.alicloudapi.com";
        $path = "/baseinfo";
        $method = "GET";
        $appcode = "6b1d3e8c715c49c897fe9153e3f28033";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "idCard=$user_number";
        $bodys = "";
        $url = $host . $path . "?" . $querys;


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $out_put = curl_exec($curl);
        return $out_put;
    }


}