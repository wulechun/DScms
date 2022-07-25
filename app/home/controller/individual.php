<?php

namespace app\home\controller;
use think\facade\View;
use think\facade\Lang;
class individual extends BaseMall
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/individual.lang.php');
    }

    /**
     * 单页详情
     * @return mixed
     */
    public function index()
    {
        $condition = array();
        $where = array();
        $individual_id = intval(input('param.individual_id'));
        if ($individual_id <= 0) {
            $this->error('参数错误');
        }
        $individual_model = model('individual');
        $condition[]=array('individual_id','=',$individual_id);
        $individual_info = $individual_model->getOneindividual($condition);

        View::assign('item_info', $individual_info);
        //SEO
        if (!empty($individual_info['seo_title'])) {
            $seo = array(
                'seo_title' => $individual_info['seo_title'],
                'seo_keywords' => $individual_info['seo_keywords'],
                'seo_description' => $individual_info['seo_description'],
            );
        }else{
            $seo = array(
                'seo_title' => $individual_info['individual_title'],
            );
        }
        $this->_assign_seo($seo);
        return View::fetch($this->template_dir . 'index');
    }
}