<?php


namespace App\Http\Controllers\Common;


use App\Http\Models\User\UserEmployee;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class Login
{
    const FIELD_ID = 'user_employee_id';

    const EMPLOYEE_SUPPLIER = 'employee_supplier';

    const FIELD_TYPE = 'user_employee_type';

    const FIELD_ACCOUNT = 'user_employee_account';

    const FIELD_NAME = 'user_employee_name';

    const FIELD_EMPLOYEE = 'user_employee_employee';

    const FIELD_EXPRESS_TIME = 'user_login_express_time';

    const FIELD_PWD = 'user_login_express_pwd';

    const LOGIN_CACHE_KEY = 'admin_login_status';

    const TMP_EXPRESS_TIME = 1296000;

    const FIELD_ACCESS_TOKEN = 'access_token';

    static function hasPermission($type)
    {
        return true;
    }

    static function isLogin()
    {
        if (isset(self::adminSession()[Login::FIELD_EXPRESS_TIME]) && self::adminSession()[Login::FIELD_EXPRESS_TIME] > time()) {
            $user_employee_obj = new UserEmployee(self::getEmployeeId());
            $user_employee_info = $user_employee_obj->userEmployee;
            if($user_employee_info){
                $employee_pwd = $user_employee_info->employee_pwd;
            }else{
                return false;
            }
            if ($employee_pwd == self::getEmployeePwd() || (empty($employee_pwd) && self::getTmpLoginPwd() == self::getEmployeePwd())) {
                if (( env('APP_ENV')=='prod'|| env('APP_ENV')=='stage') && Redis::get(self::LOGIN_CACHE_KEY . self::getEmployeeId()) != session_id()) {
                    return false;
                }
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    static function getEmployeeId()
    {
        return isset(self::adminSession()[Login::FIELD_ID]) ? self::adminSession()[Login::FIELD_ID] : 0;
    }

    static function getEmployeeType()
    {
        return isset(self::adminSession()[Login::FIELD_TYPE]) ? self::adminSession()[Login::FIELD_TYPE] : 0;
    }

    static function getEmployeeAccount()
    {
        return isset(self::adminSession()[Login::FIELD_ACCOUNT]) ? self::adminSession()[Login::FIELD_ACCOUNT] : '';
    }

    static function getEmployeeName()
    {
        return isset(self::adminSession()[Login::FIELD_NAME]) ? self::adminSession()[Login::FIELD_NAME] : '';
    }

    static function getEmployeeEmployee()
    {
        return isset(self::adminSession()[Login::FIELD_EMPLOYEE]) ? self::adminSession()[Login::FIELD_EMPLOYEE] : '';
    }

    static function getExpressTime()
    {
        return isset(self::adminSession()[Login::FIELD_EXPRESS_TIME]) ? self::adminSession()[Login::FIELD_EXPRESS_TIME] : '';
    }

    static function getEmployeePwd()
    {
        return isset(self::adminSession()[Login::FIELD_PWD]) ? self::adminSession()[Login::FIELD_PWD] : '';
    }

    static function getAccessToken()
    {
        return isset(self::adminSession()[Login::FIELD_ACCESS_TOKEN]) ? self::adminSession()[Login::FIELD_ACCESS_TOKEN] : '';
    }

    private static $adminSession;

    /**
     * session数据
     *
     * @return array
     */
    static function adminSession()
    {
        $session = session_id();
        self::$adminSession = json_decode(Redis::get(env('APP_NAME') . "_" . $session), TRUE);
        return self::$adminSession;
    }

    static function setLoginStatus($employee_id, $employee_type, $employee_account, $employee_name, $employee_employee, $employee_pwd, $express_time, $access_token)
    {
        $adminSession = array();
        $adminSession[Login::FIELD_ID] = $employee_id;
        $adminSession[Login::FIELD_TYPE] = $employee_type;
        $adminSession[Login::FIELD_ACCOUNT] = $employee_account;
        $adminSession[Login::FIELD_NAME] = $employee_name;
        $adminSession[Login::FIELD_EMPLOYEE] = $employee_employee;
        $adminSession[Login::FIELD_PWD] = $employee_pwd;
        $adminSession[Login::FIELD_EXPRESS_TIME] = time() + $express_time;
        $adminSession[Login::FIELD_ACCESS_TOKEN] = $access_token;
        self::$adminSession = $adminSession;
        self::saveAdminSession();
        if (( env('APP_ENV')=='prod'|| env('APP_ENV')=='stage')) {
            Redis::set(self::LOGIN_CACHE_KEY . self::getEmployeeId(), session_id());
        }
    }

    static function setLoginStatusSupplier($employee_id, $employee_type, $employee_account, $employee_name, $employee_employee, $employee_pwd, $express_time, $employee_supplier, $access_token)
    {
        $adminSession = array();
        $adminSession[Login::FIELD_ID] = $employee_id;
        $adminSession[Login::FIELD_TYPE] = $employee_type;
        $adminSession[Login::FIELD_ACCOUNT] = $employee_account;
        $adminSession[Login::FIELD_NAME] = $employee_name;
        $adminSession[Login::FIELD_EMPLOYEE] = $employee_employee;
        $adminSession[Login::FIELD_PWD] = $employee_pwd;
        $adminSession[Login::EMPLOYEE_SUPPLIER] = $employee_supplier;
        $adminSession[Login::FIELD_EXPRESS_TIME] = time() + $express_time;
        $adminSession[Login::FIELD_ACCESS_TOKEN] = $access_token;
        self::$adminSession = $adminSession;
        self::saveAdminSession();
        if (( env('APP_ENV')=='prod'|| env('APP_ENV')=='stage')) {
            Redis::set(self::LOGIN_CACHE_KEY . self::getEmployeeId(), session_id());
        }
    }

    static function saveAdminSession()
    {
        Redis::set(env('APP_NAME') . "_" . session_id(), json_encode(self::$adminSession));
    }

    // static function
    static function refreshExpressTime()
    {
        if (isset(self::adminSession()[Login::FIELD_EXPRESS_TIME]) && time() < self::adminSession()[Login::FIELD_EXPRESS_TIME]) {
            self::adminSession()[Login::FIELD_EXPRESS_TIME] = self::isAdmin()[Login::FIELD_EXPRESS_TIME] + 1296000;
            self::saveAdminSession();
        }
    }

    static function loginOut()
    {
        Redis::del(env('APP_NAME') . "_" . session_id());
        if (( env('APP_ENV')=='prod'|| env('APP_ENV')=='stage')) {
            Redis::del(self::LOGIN_CACHE_KEY . self::getEmployeeId());
        }
        return true;
    }

    /**
     * 校验支付密码是否正确
     *
     * @param
     *            $md5_pwd
     * @return bool
     */
    function checkPwd($md5_pwd)
    {
        $employee_id = self::getEmployeeId();
        $employeeM = new ModelUserEmployee($employee_id);
        $employee_operation_pwd = $employeeM->getOneKey('employee_operation_pwd');
        if (empty($md5_pwd) || empty($employee_operation_pwd) || md5($md5_pwd) !== $employee_operation_pwd) {
            return false;
        }
        return true;
    }

    static function isAdmin()
    {
        // 添加陈宗成后台账号可以查看押话题数据
        if (self::getEmployeeId() == 1 || self::getEmployeeId() == 55) {
            return true;
        }
        return false;
    }

    /**
     * 写入请求日志
     */
    static function reqLog()
    {
        $ser = $_SERVER;
        $cook = $_COOKIE;
        $req = $_REQUEST;
        unset($cook['PHPSESSID']);
        unset($ser['HTTP_COOKIE']);
        unset($req['oldpwd']);
        unset($req['repwd']);
        unset($req['pwd']);

        Log::put('admin_req_addr', [
            $_SERVER['HTTP_HOST'],
            'c=' . $_REQUEST['c'],
            self::adminSession()['user_employee_id'] . '--' . self::adminSession()['user_employee_account'],
            md5(session_id()),
            json_encode($req),
            json_encode($cook),
            json_encode($ser)
        ]);
    }

    static function regLoginPwd($pwd)
    {
        if (!preg_match("/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,9}$/", $pwd)) {
            return false;
        }
        return true;
    }

    static function createPwd()
    {
        $md5time = md5(time());
        $pwd = strtoupper(substr($md5time, 8, 3)) . substr($md5time, 12, 5);
        if (!self::regLoginPwd($pwd)) {
            $pwd = self::createPwd();
        }
        return $pwd;
    }

    static function createPwdV2()
    {
        $md5time = md5(time());
        $pwd = strtoupper(substr($md5time, 8, 3)) . substr($md5time, 12, 5);
        if (!self::regLoginPwd($pwd)) {
            $pwd = self::createPwd();
        }
        return $pwd;
    }


    static function setTmpLoginPwd($employee_id)
    {
        $pwd = self::createPwd();
        Redis::set('tmp_login_pwd_' . $employee_id, $pwd, self::TMP_EXPRESS_TIME);
        return $pwd;
    }

    static function getTmpLoginPwd($employee_id = 0)
    {
        if ($employee_id == 0) {
            $employee_id = self::getEmployeeId();
        }
        return Cache::get('tmp_login_pwd_' . $employee_id);
    }

}