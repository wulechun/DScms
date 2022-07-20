<?php

namespace app\mobile\controller;
use think\facade\View;

class News extends BaseMall
{
    public function initialize()
    {
        parent::initialize();
    }
    /**
     * 案例信息 - 案例栏目
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
            $column_info = $column_model->getOneColumn($newscolumn_id);
            View::assign('item_info', $column_model->dedeMerge($column_info));
        }
        $key = 'news_list_' . $newscolumn_id . '_' . input('param.page');
        $news = rcache($key);
        if (empty($news)) {
            $condition[]=array('news_displaytype','=',1);
            $where[]=array('column_module','=',COLUMN_NEWS);
            $news['news_list'] = $news_model->getnewsList($condition,0, '*', 6);
            $news['page'] = $news_model->page_info->render();
            $news['news_column_list'] = $column_model->getColumnList($where);
            foreach ($news['news_list'] as $k => $v) {
                $news['news_list'][$k] = $news_model->dedeMerge($v);
            }
            wcache($key, $news, '', 36000);
        }
        View::assign('item_list', $news['news_list']);
        View::assign('show_page', $news['page']);
        View::assign('news_column', $news['news_column_list']);
        return View::fetch($this->template_dir . (config('ds_config.template_name')=='dede'?$column_info['column_temp_list']:'search'));
    }

    /**
     * 案例详情页
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
        
        return View::fetch($this->template_dir . (config('ds_config.template_name')=='dede'?$column_info['column_temp_article']:'detail'));
    }

}
