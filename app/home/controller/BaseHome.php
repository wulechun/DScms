<?php

namespace app\home\controller;
use think\facade\View;
use app\BaseController;
use think\facade\Lang;
/**
 * 基类
 */
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
        if (in_array(cookie('ds_home_lang'), array('zh-cn', 'en-us'))) {
            config(array('default_lang'=>cookie('ds_home_lang')),'lang');
        }
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '.php');
        
        $this->template_name = empty(config('ds_config.template_name')) ? 'default' : config('ds_config.template_name');
        $this->style_name = empty(config('ds_config.style_name')) ? 'default' : config('ds_config.style_name');
        View::assign('template_theme', $this->template_name);
        View::assign('style_theme', $this->style_name);
        View::assign('navs', $this->_get_nav_list());
        View::assign('link_list', $this->_get_link_list());
    }
    
    /**
     * SEO赋值
     * @param type $seo
     */
    function _assign_seo($seo = array()) {
        $seo_home_title_type = config('ds_config.seo_home_title_type');
        if(!isset($seo['seo_title'])){
            $seo['seo_title']  = config('ds_config.site_name');
        }
        switch ($seo_home_title_type) {
            case 0:
                View::assign('seo_title', $seo['seo_title']);
                break;
            case 1:
                View::assign('seo_title', $seo['seo_title'].'-'.config('ds_config.seo_home_keywords'));
                break;
            case 2:
                View::assign('seo_title', $seo['seo_title'].'-'.config('ds_config.seo_home_title'));
                break;
            case 2:
                View::assign('seo_title', $seo['seo_title'].'-'.config('ds_config.seo_home_title').'-'.config('ds_config.seo_home_keywords'));
                break;
        }
        if(isset($seo['seo_keywords'])){
            View::assign('seo_keywords', $seo['seo_keywords']);
        }else{
            View::assign('seo_keywords', config('ds_config.seo_home_keywords'));
        }
        if(isset($seo['seo_description'])){
            View::assign('seo_description', $seo['seo_description']);
        }else{
            View::assign('seo_description', config('ds_config.seo_home_description'));
        }
    }


    /**
     * 获取首页导航
     * @return array
     */
    public function _get_nav_list()
    {
        $condition = array();
        $key = 'home_nav';
        $data = rcache($key);
        if (empty($data)) {
            //获取头部导航栏
            $condition[]=array('nav_display','=',1);//1为PC端  2 为手机端
            $condition[]=array('nav_is_show','=',1);
            $nav_model=model('nav');
            $data = array(
                'header' => $nav_model->getNavList(array_merge(array(array('nav_location','=','header')),$condition)), 
                'middle' => $nav_model->getNavList(array_merge(array(array('nav_location','=','middle')),$condition)), 
                'footer' => $nav_model->getNavList(array_merge(array(array('nav_location','=','footer')),$condition)),
            );
            
            wcache($key, $data, '', 36000);
        }
        return $data;
    }

    /**
     * 获取尾部友情链接
     * @return array
     */
    public function _get_link_list()
    {
        $condition = array();
        $key = 'home_link';
        $homelink_list = rcache($key);
        if (empty($homelink_list)) {
            //获取尾部友链
            $condition[]=array('link_show_ok','=',1);
            $homelink_list = model('link')->getLinkList($condition);
            wcache($key, $homelink_list, '', 36000);
        }
        return $homelink_list;
    }

}

?>
