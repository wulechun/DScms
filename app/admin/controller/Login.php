<?php

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;

use app\BaseController;
use think\facade\Lang;
use think\captcha\facade\Captcha;

class Login extends BaseController
{

    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/login.lang.php');
    }

    /**
     * 登录
     * @return mixed
     */
    public function index()
    {
        if (session('admin_id')) {
            $this->success('已经登录', 'admin/Index/index');
        }
        if (request()->isPost()) {

            $admin_name = input('post.admin_name');
            $admin_password = input('post.admin_password');
            $captcha = input('post.captcha');

            $data = array(
                'admin_name' => $admin_name,
                'admin_password' => $admin_password,
                'captcha' => $captcha,
            );

            //验证数据  BEGIN
            $rule = [
                'admin_name'=>'require|min:5',
                'admin_password'=>'require|min:6',
                'captcha'=>'require|min:3',
            ];
            $message=[
                'admin_name.require'=>'帐号为必填',
                'admin_name.min:5'=>'帐号长度至少为5位',
                'admin_password.require'=>'密码为必填',
                'admin_password.min:6'=>'帐号长度至少为6位',
                'captcha.require'=>'验证码为必填',
                'captcha.min:3'=>'帐号长度至少为3位',
            ];
            try{
            $this->validate($data,$rule,$message);
            }catch(\Exception $e){
                ds_json_encode(10001,$e->getMessage());
            }
           
            //验证数据  END
            if (!captcha_check(input('post.captcha'))) {
                //验证失败
                ds_json_encode(10001,'验证码错误');
            }

            $condition[]=array('admin_name','=',$admin_name);
            $condition[]=array('admin_password','=',md5($admin_password));

            $admin_info = Db::name('admin')->where($condition)->find();
            if (is_array($admin_info) and !empty($admin_info)) {
                //更新 admin 最新信息
                $update_info = array(
                    'admin_login_num' => ($admin_info['admin_login_num'] + 1),
                    'admin_login_time' => TIMESTAMP
                );
                Db::name('admin')->where(array(array('admin_id','=', $admin_info['admin_id'])))->update($update_info);

                //设置 session
                session('admin_id', $admin_info['admin_id']);
                session('admin_name', $admin_info['admin_name']);
                session('admin_group_id', $admin_info['admin_group_id']);
                session('admin_is_super', $admin_info['admin_is_super']);
                ds_json_encode(10000,'登录成功', '','',false);
            } else {
                ds_json_encode(10001,'帐号密码错误');
            }
        } else {
            return View::fetch();
        }
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        //设置 session
        session(null);
        ds_json_encode(10000,'退出成功', '','',false);
    }

    /**
     *产生验证码
     */
    public function makecode()
    {
        $config = [
            'fontSize' => 20, // // 验证码字体大小
            'length' => 4, // 验证码位数
            'useNoise' => false,//是否添加杂点
            'useCurve' =>true,
            'imageH' => 50,//高度
            'imageW' => 150,
        ];
        config($config,'captcha');
        $captcha = Captcha::create();
        return $captcha;
    }
}


?>
