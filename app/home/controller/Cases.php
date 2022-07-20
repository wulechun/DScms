<?php

namespace app\home\controller;
use think\facade\View;
use think\facade\Lang;
class Cases extends BaseMall
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/cases.lang.php');
    }

    /**
     * 案例信息 - 案例栏目
     * @return mixed
     */
    public function search()
    {
        $cases_model = model('cases');
        $column_model = model('column');
        $condition = array();
        $where = array();
        $casescolumn_id = intval(input('param.id'));
        if ($casescolumn_id > 0) {
            $condition[]=array('column_id','in',$column_model->getColumnSonIds($casescolumn_id));
        }
        $key = 'cases_list_' . $casescolumn_id . '_' . input('param.page');
        $cases = rcache($key);
        if (empty($cases)) {
            $condition[]=array('cases_displaytype','=',1);
            $where[]=array('column_module','=',COLUMN_CASES);
            $cases['cases_list'] = $cases_model->getCasesList($condition, '*', 6);
            $cases['page'] = $cases_model->page_info->render();
            $cases['cases_column_list'] = $column_model->getColumnList($where);
            //当前分类
            $news['cases_column'] = array();
            if($casescolumn_id>0){
                $news['cases_column'] = $column_model->getOneColumn($casescolumn_id);
                $news['cases_column']=$column_model->dedeMerge($news['cases_column']);
            }
            foreach ($cases['cases_list'] as $k => $v) {
                $cases['cases_list'][$k] = $cases_model->dedeMerge($v);
            }
            wcache($key, $cases, '', 36000);
        }
        View::assign('item_info', $news['cases_column']);
        View::assign('item_list', $cases['cases_list']);
        View::assign('show_page', $cases['page']);
        View::assign('cases_column', $cases['cases_column_list']);
        
        //面包屑导航
        View::assign('ancestor', $this->get_ancestor($casescolumn_id));
        
        //SEO
        if (!empty($news['cases_column'])) {
            $seo = array(
                'seo_title' => !empty($news['cases_column']['seo_title']) ? $news['cases_column']['seo_title'] : $news['cases_column']['column_name'],
                'seo_keywords' => !empty($news['cases_column']['seo_keywords']) ? $news['cases_column']['seo_keywords'] : $news['cases_column']['column_name'],
                'seo_description' => !empty($news['cases_column']['seo_description']) ? $news['cases_column']['seo_description'] : '',
            );
        }else{
            $seo = array(
                'seo_title' => '所有案例',
            );
        }
        $this->_assign_seo($seo);
        return View::fetch($this->template_dir . (config('ds_config.template_name')=='dede'?$news['cases_column']['column_temp_list']:'search'));
    }

    /**
     * 案例详情页
     * @return mixed
     */
    public function detail()
    {
        $condition = array();
        $where = array();
        $cases_id = intval(input('param.cases_id'));
        if ($cases_id <= 0) {
            $this->error('参数错误');
        }
        $cases_model = model('cases');
        $condition[]=array('cases_id','=',$cases_id);
        $cases_info = $cases_model->getOneCases($condition);

        $column_model=model('column');
        $column_info = $column_model->getOneColumn($cases_info['column_id']);
        //获取案例列表
        $key = "casescolumn_list";
        $casescolumn_list = rcache($key);
        if (empty($casescolumn_list)) {
            $where[]=array('column_module','=',COLUMN_CASES);
            $casescolumn_list = $column_model->getColumnList($where);
            wcache($key, $casescolumn_list, '', 36000);
        }
        View::assign('cases_column', $casescolumn_list);
        View::assign('item_info', array_merge($column_model->dedeMerge($column_info),$cases_model->dedeMerge($cases_info)));
        
        //面包屑导航
        View::assign('ancestor', $this->get_ancestor($cases_info['column_id']));
        
        //SEO赋值
        $seo = array(
            'seo_title'=> !empty($cases_info['seo_title'])?$cases_info['seo_title']:$cases_info['cases_title'],
            'seo_keywords'=> !empty($cases_info['seo_keywords'])?$cases_info['seo_keywords']:$cases_info['cases_title'],
            'seo_description'=> !empty($cases_info['seo_description'])?$cases_info['seo_description']:ds_substing(htmlspecialchars_decode($cases_info['cases_content']),0,80),
        );
        $this->_assign_seo($seo);
        return View::fetch($this->template_dir . (config('ds_config.template_name')=='dede'?$column_info['column_temp_article']:'detail'));
    }

}