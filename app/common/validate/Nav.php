<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/10
 * Time: 17:40
 */

namespace app\common\validate;


use think\Validate;

class Nav extends Validate
{
    protected $rule = [
        'nav_title'=>'require|max:12',
        'nav_url'=>'require|url',
    ];
    protected $message = [
        'nav_title.require'=>'请输入导航标题',
        'nav_title.max:12'=>'导航最大长度为12',
        'nav_url.require'=>'请输入导航链接',
        'nav_url.url'=>'请输入正确的导航链接',
    ];

    protected $scene = [
        'add' => ['nav_title','nav_url'],
        'edit' => ['nav_title','nav_url'],
    ];
}