<?php

namespace app\admin\controller;
use think\facade\View;

use think\facade\Lang;
use think\Validate;

class individual extends AdminControl
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/individual.lang.php');
    }

    /**
     * 单页管理
     */
    public function index()
    {
        $model_individual = model('individual');
        $condition = array();
        $individual_list = $model_individual->getindividualList($condition, '*', 5);
        View::assign('individual_list', $individual_list);
        View::assign('show_page', $model_individual->page_info->render());
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 添加单页
     */
    public function add()
    {
        if (request()->isPost()) {
            $data = array(
                'individual_title' => input('post.individual_title'),
                'individual_content' => input('post.individual_content'),
                'individual_wap_ok' => input('post.individual_wap_ok') ? 1 : 0,
                'individual_displaytype' => input('post.individual_displaytype') ? 1 : 0,
                'seo_title' => input('post.seo_title'),
                'seo_keywords' => input('post.seo_keywords'),
                'seo_description' => input('post.seo_description'),
            );
            $individual_validate = ds_validate('individual');
            if (!$individual_validate->scene('add')->check($data)){
                $this->error($individual_validate->getError());
            }
            $result = model('individual')->addindividual($data);
            if ($result) {
                $this->log(lang('ds_individual').'-'.lang('add_succ') . '[' . $data['individual_title'] . ']', null);
                $this->success(lang('add_succ'), url('individual/index'));
            } else {
                $this->error(lang('add_fail'));
            }
        } else {
            $individual = array(
                'individual_wap_ok' => 1,
                'individual_displaytype' => 1,
            );
            $pic_list = model('pic')->getPicList(array(array('pic_id' ,'=', 0)));
            View::assign('individual_pic_type', ['pic_type' => 'individual']);
            View::assign('pic_list', $pic_list);
            View::assign('individual', $individual);
            $this->setAdminCurItem('add');
            return View::fetch('form');
        }
    }

    /**
     * 修改单页
     */
    public function edit()
    {
        $individual_id = intval(input('param.individual_id'));
        if (!request()->isPost()) {
            $condition[]=array('pic_type','=','individual');
            $condition[]=array('pic_type_id','=',$individual_id);
            $pic_list = model('pic')->getpicList($condition);
            View::assign('pic_list', $pic_list);
            View::assign('individual_pic_type', ['pic_type' => 'individual']);

            $individual = model('individual')->getOneindividual([['individual_id' ,'=', $individual_id]]);
            View::assign('individual', $individual);
            $this->setAdminCurItem('edit');
            return View::fetch('form');
        } else {
            $data = array(
                'individual_title' => input('post.individual_title'),
                'individual_content' => input('post.individual_content'),
                'individual_wap_ok' => input('post.individual_wap_ok') ? 1 : 0,
                'individual_displaytype' => input('post.individual_displaytype') ? 1 : 0,
                'seo_title' => input('post.seo_title'),
                'seo_keywords' => input('post.seo_keywords'),
                'seo_description' => input('post.seo_description'),
            );
            $individual_validate = ds_validate('individual');
            if (!$individual_validate->scene('edit')->check($data)){
                $this->error($individual_validate->getError());
            }
            $result = model('individual')->editindividual([['individual_id' ,'=', $individual_id]], $data);
            if ($result) {
                $this->log(lang('ds_individual').'-'.lang('edit_succ') . '[' . $data['individual_title'] . ']', null);
                $this->success(lang('edit_succ'), url('individual/index'));
            } else {
                $this->error(lang('edit_fail'));
            }
        }
    }

    /**
     * 删除单页
     */
    public function del()
    {
        $individual_id = intval(input('param.individual_id'));
        if ($individual_id>0) {
            $condition[]=array('individual_id','=',$individual_id);
            $result = model('individual')->delindividual($condition);
            if ($result) {
                $this->log(lang('ds_individual').'-'.lang('del_succ') . '[' . $individual_id . ']', null);
                ds_json_encode(10000, lang('del_succ'));
            } else {
                ds_json_encode(10001, lang('del_fail'));
            }
        } else {
            ds_json_encode(10001, lang('del_fail'));
        }
    }

    /**
     * 设置单页
     */
    function setindividual()
    {
        $individual_type = input('param.individual_type');
        $individual_id = input('param.individual_id');
        $res = model('individual')->getOneindividual([['individual_id' ,'=', $individual_id]], $individual_type);
        $id = $res[$individual_type] == 0 ? 1 : 0;
        $update[$individual_type] = $id;
        $condition[]=array('individual_id','=',$individual_id);
        if (model('individual')->editindividual($condition, $update)) {
            ds_json_encode(10000, lang('edit_succ'));
        } else {
            $this->error(lang('edit_fail'));
        }
    }

    /**
     * ajax操作
     */
    public function ajax()
    {
        $branch = input('param.branch');
        switch ($branch) {
            case 'individual':
                $individual_mod = model('individual');
                $condition=array();
                $condition[] = array('individual_id' ,'=', intval(input('param.id')));
                $update[input('param.column')] = input('param.value');
                $individual_mod->editindividual($condition, $update);
                echo 'true';
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     * @return type
     */
    protected function getAdminItemList()
    {
        $menu_array = array(
            array(
                'name' => 'index', 'text' => lang('ds_manage'), 'url' => url('individual/index')
            ), array(
                'name' => 'add', 'text' => lang('ds_add'), 'url' => url('individual/add')
            )
        );
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit', 'text' => lang('ds_edit'), 'url' => url('individual/edit')
            );
        }
        return $menu_array;
    }

}

?>
