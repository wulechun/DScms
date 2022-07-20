<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/10
 * Time: 14:33
 */

namespace app\common\validate;


use think\Validate;

class Adv extends Validate
{

    protected $rule =  [
        'ap_id'=>'require',
        'adv_title'=>'require|length:5,16',
        'adv_link'=>'require|url',
        'adv_code'=>'require',
    ];

    protected $message =  [
        'ap_id.require'=>'请选择所属广告位!',
        'adv_title.require'=>'请填写广告名称!',
        'adv_title.length:5,16'=>'广告名称5-16长度',
        'adv_link.require'=>'请填写广告链接地址',
        'adv_link.url'=>'广告链接地址确保是url格式',
        'adv_code.require'=>'请选择广告图片',
    ];

    protected $scene = [
        'add' => ['ap_id','adv_title','adv_link','adv_code'],
        'edit' => ['ap_id','adv_title','adv_link'],
    ];
}