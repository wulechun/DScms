<?php

namespace app\home\controller;
use think\facade\View;
use think\facade\Lang;
class News extends BaseMall
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/news.lang.php');
    }

    /**
     * 新闻信息 - 新闻栏目
     * @return mixed
     */
    public function search()
    {
        $news_model = model('news');
        $column_model = model('column');
        $condition = array();
        $where = array();
        $newscolumn_id = intval(input('param.id'));
        if ($newscolumn_id > 0) {
            $condition[]=array('column_id','in',$column_model->getColumnSonIds($newscolumn_id));
        }
        $key = 'news_list_' . $newscolumn_id . '_' . input('param.page');
        $news = rcache($key);
        if (empty($news)) {
            $condition[]=array('news_displaytype','=',1);
            $where[]=array('column_module','=',COLUMN_NEWS);
            $news['news_list'] = $news_model->getnewsList($condition, '', '*', 6);
            $news['page'] = $news_model->page_info->render();
            $news['news_column_list'] = $column_model->getColumnList($where);
            //当前分类
            $news['news_column'] = array();
            if($newscolumn_id>0){
                $news['news_column'] = $column_model->getOneColumn($newscolumn_id);
                $news['news_column']=$column_model->dedeMerge($news['news_column']);
            }
            foreach ($news['news_list'] as $k => $v) {
                $news['news_list'][$k] = $news_model->dedeMerge($v);
            }
            wcache($key, $news, '', 36000);
        }
        View::assign('item_info', $news['news_column']);
        View::assign('item_list', $news['news_list']);
        View::assign('show_page', $news['page']);
        View::assign('news_column', $news['news_column_list']);
        
        
        //面包屑导航
        View::assign('ancestor', $this->get_ancestor($newscolumn_id));
        
        //SEO
        if (!empty($news['news_column'])) {
            $seo = array(
                'seo_title' => !empty($news['news_column']['seo_title']) ? $news['news_column']['seo_title'] : $news['news_column']['column_name'],
                'seo_keywords' => !empty($news['news_column']['seo_keywords']) ? $news['news_column']['seo_keywords'] : $news['news_column']['column_name'],
                'seo_description' => !empty($news['news_column']['seo_description']) ? $news['news_column']['seo_description'] : '',
            );
        }else{
            $seo = array(
                'seo_title' => '新闻资讯',
            );
        }
        $this->_assign_seo($seo);
        
        return View::fetch($this->template_dir . (config('ds_config.template_name')=='dede'?$news['news_column']['column_temp_list']:'search'));
    }

    /**
     * 新闻详情
     * @return mixed
     */
    public function detail()
    {
        $condition = array();
        $where = array();
        $news_id = intval(input('param.news_id'));
        if ($news_id <= 0) {
            $this->error('参数错误');
        }
        $news_model = model('news');
        $condition[]=array('news_id','=',$news_id);
        $news_info = $news_model->getOneNews($condition);

        $column_model=model('column');
        $column_info = $column_model->getOneColumn($news_info['column_id']);
        //获取案例列表
        $key = "newscolumn_list";
        $newscolumn_list = rcache($key);
        if (empty($newscolumn_list)) {
            $where[]=array('column_module','=',COLUMN_NEWS);
            $newscolumn_list = $column_model->getColumnList($where);
            wcache($key, $newscolumn_list, '', 36000);
        }
        View::assign('news_column', $newscolumn_list);
        View::assign('item_info', array_merge($column_model->dedeMerge($column_info),$news_model->dedeMerge($news_info)));
        
        //面包屑导航
        View::assign('ancestor', $this->get_ancestor($news_info['column_id']));
        
        //SEO赋值
        $seo = array(
            'seo_title'=> !empty($news_info['seo_title'])?$news_info['seo_title']:$news_info['news_title'],
            'seo_keywords'=> !empty($news_info['seo_keywords'])?$news_info['seo_keywords']:$news_info['news_title'],
            'seo_description'=> !empty($news_info['seo_description'])?$news_info['seo_description']:ds_substing(htmlspecialchars_decode($news_info['news_content']),0,80),
        );
        $this->_assign_seo($seo);
        return View::fetch($this->template_dir . (config('ds_config.template_name')=='dede'?$column_info['column_temp_article']:'detail'));
    }
}