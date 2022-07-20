<?php

namespace app\mobile\controller;
use think\facade\View;
use app\BaseController;
use think\facade\Lang;
use think\Request;

class BaseHome extends BaseController
{
    public function initialize()
    {
        parent::initialize();
        //自动加入配置
        $config_list = rkcache('config', true);
        config($config_list,'ds_config');
        if (!config('ds_config.site_state')) {
            echo config('ds_config.closed_reason');
            exit;
        }
        $this->template_name = empty(config('ds_config.template_name')) ? 'default' : config('ds_config.template_name');
        $this->style_name = empty(config('ds_config.style_name')) ? 'default' : config('ds_config.style_name');
        View::assign('template_theme', $this->template_name);
        View::assign('style_theme', $this->style_name);
        View::assign('nav_header', $this->_get_nav_list());
        View::assign('link_list', $this->_get_link_list());
    }


    /**
     * 获取首页导航
     * @return array
     */
    public function _get_nav_list()
    {
        $key = 'mobile_nav';
        $mobilenav_list = rcache($key);
        if (empty($mobilenav_list)) {
            $condition[]=array('nav_display','=',2);//1为PC端  2为手机端
            $condition[]=array('nav_location','=','header');
            $condition[]=array('nav_is_show','=',1);
            //获取头部导航栏
            $mobilenav_list = model('nav')->getNavList($condition);
            wcache($key, $mobilenav_list, '', 36000);
        }
        return $mobilenav_list;
    }

    /**
     * 获取尾部友情链接
     * @return array
     */
    public function _get_link_list()
    {
        $key = 'mobile_link';
        $mobilelink_list = rcache($key);
        if (empty($mobilelink_list)) {
            $condition[]=array('link_show_ok','=',1);
            //获取尾部友链
            $mobilelink_list = model('link')->getLinkList($condition);
            wcache($key, $mobilelink_list, '', 36000);
        }
        return $mobilelink_list;
    }

}

?>
