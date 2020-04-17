<?php


namespace App\Http\Controllers\Login;


use App\Http\Controllers\Common\CommonFunction;
use App\Http\Controllers\Common\HttpResponse;
use App\Http\Controllers\Common\Login;
use App\Http\Controllers\Common\Verify;
use App\Http\Controllers\Controller;
use App\Http\Models\User\UserEmployee;
use Illuminate\Http\Request;
use Mews\Captcha\Captcha;


class LoginController extends Controller
{
    /**
     * @param Request $request
     * 登录验证
     */
    public function doLogin(Request $request){
        if(!Verify::existsingAll("account", "md5_pwd", "remember", "verification")){
            HttpResponse::exitJSON(1, "缺少参数~！");
        }
        $CommonFunction = new CommonFunction();
        $account = $CommonFunction->filterString($request->input('account'));
        $md5_pwd = $CommonFunction->filterString($request->input('md5_pwd'));
        $md5_pwd = md5($md5_pwd);
        $remember = $request->input('remember');
        $verification = $CommonFunction->filterString($request->input('verification'));
        if (!Captcha::check($verification)){
            HttpResponse::exitJSON(1, "验证码不匹配，请重新输入~！");
        }
        $user_employee_info = UserEmployee::where('employee_account',$account)->first();
        if($user_employee_info){
            $employee_type = $user_employee_info->employee_type;
            if($user_employee_info->del_flg == 0 && in_array($employee_type,array(10,11,12,13,14))){
                HttpResponse::exitJSON(1, "您的账号已经被删除了~！", $account);
            }
            $employee_pwd = $user_employee_info->employee_pwd;
            $employee_id = $user_employee_info->employee_id;
            $employee_employee = $user_employee_info->employee_employer;
            $employee_account = $user_employee_info->employee_account;
            $employee_name = $user_employee_info->employee_name;
            $employee_supplier = $user_employee_info->employee_supplier;
            $access_token = md5($account.$md5_pwd.time());
            $tmp_pwd = Login::getTmpLoginPwd($employee_id);
            if ($employee_pwd == md5($md5_pwd)) {
                UserEmployee::where('employee_account',$account)->update(['employee_logintime'=> time()]);
                $express_time = $remember == 1 ? 1296000 : 1296000;// 15天过期
                if($employee_supplier){
                    Login::setLoginStatusSupplier($employee_id, $employee_type, $employee_account, $employee_name, $employee_employee, $employee_pwd, $express_time,$employee_supplier,$access_token);
                }else{
                    Login::setLoginStatus($employee_id, $employee_type, $employee_account, $employee_name, $employee_employee, $employee_pwd, $express_time,$access_token);
                }
                $data['employee_supplier'] = $employee_supplier;
                $data['access_token'] = $access_token;
                HttpResponse::exitJSON(0, "登陆成功~！",$data);
            }else if ($employee_pwd == '' && !empty($tmp_pwd) && md5($tmp_pwd) == $md5_pwd) {
                $remember = 0;
                Login::setLoginStatus($employee_id, $employee_type, $employee_account, $employee_name, $employee_employee, $tmp_pwd, Login::TMP_EXPRESS_TIME,$access_token);
                UserEmployee::where('employee_account',$account)->update(['employee_logintime'=>time()]);
                $data['access_token'] = $access_token;
               HttpResponse::exitJSON(0, "登陆成功~！",$data);
            } else {
              HttpResponse::exitJSON(1, "帐号或密码错误，请核对后重新输入~！");
            }
        }else{
            HttpResponse::exitJSON(1, "帐号或密码错误，请核对后重新输入~！");
        }
    }
    /**
     * 注销登录
     */
    public function logOut(){
        $res = Login::loginOut();
        if($res){
            HttpResponse::exitJSON(0, "退出成功");
        }
    }
    /**
     * 获取验证码
     */
    public function imageCode(){
        $data['url'] = captcha_src();
        HttpResponse::exitJSON(0, "验证码地址获取成功",$data);
    }



}