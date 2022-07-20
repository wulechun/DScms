<?php

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;

use think\facade\Lang;
use think\Validate;

class Jobcv extends AdminControl
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/jobcv.lang.php');
    }

    /**
     * 简历管理
     */
    public function index()
    {
        $model_jobcv = model('jobcv');
        $condition = array();
        $jobcv_list = $model_jobcv->getJobcvList($condition, '*', 5);
        foreach ($jobcv_list as $k => $v){
            $job_position = model('job')->getOneJob([['job_id','=',$v['job_id']]],'job_position');
            $jobcv_list[$k]['jobcv_job_id'] = $job_position['job_position'];
        }
        View::assign('jobcv_list', $jobcv_list);
        View::assign('show_page', $model_jobcv->page_info->render());
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 查看简历详细
     * @return type
     */
    public function detail()
    {
        $jobcv_id = input('param.jobcv_id');
        if ($jobcv_id) {
            $condition[]=array('jobcv_id','=',$jobcv_id);
            model('jobcv')->editJobcv($condition,['jobcv_readok'=>1]);
            $resume_info = Db::name('jobcv')->where($condition)->find();
            $resume_info['job_name'] = input('param.job_name');
            View::assign('resume_info', $resume_info);
            $this->setAdminCurItem('detail');
            return View::fetch();
        }else{
            View::assign(\lang('param_error'));
        }
    }

    /**
     * 删除简历
     */
    public function del()
    {
        $jobcv_id = intval(input('param.jobcv_id'));
        if ($jobcv_id > 0) {
            $condition[]=array('jobcv_id','=',$jobcv_id);
            $result = model('jobcv')->delJobcv($condition);
            if ($result) {
                $this->log(lang('ds_jobcv') . '-' . lang('del_succ') . '[' . $jobcv_id . ']', null);
                ds_json_encode(10000, lang('del_succ'));
            } else {
                ds_json_encode(10001, lang('del_fail'));
            }
        } else {
            ds_json_encode(10001, lang('del_fail'));
        }

    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     * @return array
     */
    protected function getAdminItemList()
    {
        $menu_array = array(
            array(
                'name' => 'index', 'text' => lang('ds_manage'), 'url' => url('jobcv/index')
            )
        );
        return $menu_array;
    }

}

?>
