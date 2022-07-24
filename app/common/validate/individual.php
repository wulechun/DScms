<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/10
 * Time: 16:50
 */

namespace app\common\validate;


use think\Validate;

class individual extends Validate
{
    protected  $rule = [
        'individual_title'=>'require|max:6',
        'individual_content'=>'require',
    ];
    protected  $message = [
        'individual_title.require'=>'请输入单页标题',
        'individual_title.max:6'=>'单页标题不能超过最大长度6',
        'individual_content.require'=>'请输入详细信息!',
    ];

    protected  $scene = [
        'add' => ['individual_title','individual_content',],
        'edit' => ['individual_title','individual_content',],
    ];
}