<?php

namespace App\Http\Models\User;

use Illuminate\Database\Eloquent\Model;



class UserEmployee extends Model
{
    protected $table = 'user_employee';
    public $timestamps = false;
    public $primaryKey = 'employee_id';

    /**
     * 检测后台用户是否登录
     *
     * @return boolean
     */
    static function is_login()
    {
        if (!empty (session('employee_id')) && !empty (session('employee_is_login')) && session('login_express_time') > time()) {
            session('login_express_time',time() + 24 * 60 * 60);
            return true;
        }
        self::logout();
        return false;
    }


}
