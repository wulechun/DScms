<?php

/*
 * 首页相关基本调用
 */
namespace app\home\controller;
use think\facade\View;
use think\facade\Lang;
class Index extends BaseMall
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/index.lang.php');
    }

    public function index()
    {
        View::assign('cases_list', $this->_get_cases_list());
        View::assign('product_list', $this->_get_product_list());
        View::assign('news_column', $this->_get_news_column());
        
        $this->_assign_seo();
        
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
        $key = 'home_cases';
        $hoem_cases_list = rcache($key);
        if (empty($hoem_cases_list)) {
            //获取案例列表（8条）
            $hoem_cases_list = model('cases')->getCasesList([], '*', 0, 8);
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
        $key = 'home_product';
        $home_product_list = rcache($key);
        if (empty($home_product_list)) {
            //获取产品列表（8条）
            $home_product_list = model('product')->getProductList([], '*', 0, 8);
            wcache($key, $home_product_list, '', 36000);
        }
        return $home_product_list;
    }

    /**
     * 获取新闻栏目列表
     * @return array
     */
    public function _get_news_column()
    {
        $condition = array();
        $condition[]=array('column_module','=',COLUMN_NEWS);
        $condition[]=array('parent_id','=',0);
        $key = 'home_column_news';
        $home_column_news_list = rcache($key);
        if (empty($home_column_news_list)) {
            $home_column_news_list = model('column')->getColumnList($condition, 3);
            foreach ($home_column_news_list as $key => $news_list) {
                $where = array();
                //获取新闻栏目列表
                $where[]=array('column_id','=',$news_list['column_id']);
                $home_column_news_list[$key]['news_list'] = model('news')->getNewsList($where, 5);
            }
            wcache($key, $home_column_news_list, '', 36000);
        }
        return $home_column_news_list;
    }
}