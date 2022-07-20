<?php

namespace app\mobile\controller;
use think\facade\View;

class Index extends BaseMall
{
    public function index()
    {
        View::assign('cases_list', $this->_get_cases_list());
        View::assign('product_list', $this->_get_product_list());
        View::assign('news_list', $this->_get_news_column());

        return View::fetch($this->template_dir . 'index');
    }

    
    public function column(){
        $column_id = intval(input('param.column_id'));
        if(!$column_id){
            $this->error('缺少栏目id');
        }
        $column_model=model('column');
        $column_info=$column_model->getOneColumn($column_id);
        if(!$column_info){
            $this->error('栏目不存在');
        }
        if(!$column_info['column_temp_index']){
            $this->error('栏目错误');
        }
        View::assign('item_info', $column_model->dedeMerge($column_info));
        return View::fetch($this->template_dir . $column_info['column_temp_index']);
    }

    /**
     * 获取案例列表
     * @return array
     */
    public function _get_cases_list()
    {
        $key = 'mobile_cases';
        $hoem_cases_list = rcache($key);
        if (empty($hoem_cases_list)) {
            //获取案例列表（4条）
            $hoem_cases_list = model('cases')->getCasesList([], '*', 0, 4);
            wcache($key, $hoem_cases_list, '', 36000);
        }
        return $hoem_cases_list;
    }

    /**
     * 获取产品列表
     * @return array
     */
    public function _get_product_list()
    {
        $key = 'mobile_product';
        $mobile_product_list = rcache($key);
        if (empty($mobile_product_list)) {
            //获取产品列表（4条）
            $mobile_product_list = model('product')->getProductList([], '*', 0, 4);
            wcache($key, $mobile_product_list, '', 36000);
        }
        return $mobile_product_list;
    }

    /**
     * 获取新闻栏目列表
     * @return array
     */
    public function _get_news_column()
    {
        $key = 'mobile_news';
        $mobile_news_list = rcache($key);
        if (empty($mobile_news_list)) {
            //获取新闻列表（4条）
            $mobile_news_list = model('news')->getNewsList(array(), 4);
            wcache($key, $mobile_news_list, '', 36000);
        }
        return $mobile_news_list;
    }
}
