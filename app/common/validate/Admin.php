<?php
/**
 * 管理员
 */
namespace app\common\validate;


use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'admin_name'=>'require|length:3,12|unique:admin,admin_name',
        'admin_password'=>'require|length:6,12',
    ];
    protected $message = [
        'admin_name.require'=>'请填写管理员名称',
        'admin_name.length:3,12'=>'管理员名称3-12长度',
        'admin_name.unique:admin,admin_name'=>'该管理员已存在',
        'admin_password.require'=>'请填写管理员密码',
        'admin_password.length:6,12'=>'管理员密码6-12的密码',
    ];

    protected $scene = [
        'add' => ['admin_name','admin_password'],
        'edit' => ['admin_name','admin_password'],
    ];
}