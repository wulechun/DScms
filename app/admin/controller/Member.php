<?php

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;

use think\facade\Lang;

class Member extends AdminControl
{

    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/member.lang.php');
    }

    /**
     * 用户列表
     * @return mixed
     */
    public function index()
    {
        $model_member = model('member');
        $condition = array();
        $member_list = $model_member->getMemberList($condition, '*', 5);
        View::assign('member_list', $member_list);
        View::assign('show_page', $model_member->page_info->render());
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 添加用户
     * @return mixed
     */
    public function add()
    {
        if (request()->isPost()) {
            $model_member = model('member');
            //判断用户名是否存在
            if ($model_member->getMemberInfo([['member_name' ,'=', input('post.member_name')]])) {
                $this->error(lang('member_existence'));
            }
            $data = array(
                'member_mobile' => input('post.member_mobile'),
                'member_email_bind' => input('post.member_email_bind') ? input('post.member_email_bind') : 0,
                'member_mobile_bind' => input('post.member_mobile_bind') ? input('post.member_mobile_bind') : 0,
                'member_email' => input('post.member_email'),
                'member_name' => input('post.member_name'),
                'member_password' => input('post.member_password') ? md5(input('post.member_password')) : md5('123'),
                'member_truename' => input('post.member_truename'),
                'member_add_time' => TIMESTAMP
            );
            //添加到数据库
            $result = $model_member->addMember($data);
            if ($result) {
                dsLayerOpenSuccess(lang('member_add_succ'));
            } else {
                $this->error(lang('member_add_fail'));
            }
        } else {
            $member_array = array(
                'member_mobile_bind' => 0,
                'member_email_bind' => 0,
                'add' => 1,
            );
            View::assign('member', $member_array);
            $this->setAdminCurItem('add');
            return View::fetch('form');
        }
    }

    /**
     * 编辑用户
     * @return type
     */
    public function edit()
    {
        $member_id = input('param.member_id');
        if (empty($member_id)) {
            $this->error(lang('param_error'));
        }
        $model_member = model('member');
        if (!request()->isPost()) {
            $condition[]=array('member_id','=',$member_id);
            $member_array = $model_member->getMemberInfo($condition);
            $member_array['add'] = 0;
            View::assign('member', $member_array);
            $this->setAdminCurItem('edit');
            return View::fetch('form');
        } else {
            $data = array(
                'member_name' => input('post.member_name'),
                'member_truename' => input('post.member_truename'),
                'member_mobile' => input('post.member_mobile'),
                'member_email' => input('post.member_email'),
                'member_email_bind' => input('post.member_email_bind') ? input('post.member_email_bind') : 0,
                'member_mobile_bind' => input('post.member_mobile_bind') ? input('post.member_mobile_bind') : 0,
            );
            if (input('post.member_password')) {
                $data['member_password'] = md5(input('post.member_password'));
            }
            //验证数据  BEGIN
            $rule = [
                'member_email'=>'email'
            ];
            $message = [
                'member_email.email'=>lang('mailbox_format_error')
            ];
            try{
            $this->validate($data,$rule,$message);
            }catch(\Exception $e){
                $this->error($e->getMessage());
            }
            //验证数据  END
            $result = $model_member->editMember(array(array('member_id' ,'=', intval($member_id))), $data);
            if ($result) {
                dsLayerOpenSuccess(lang('member_edit_succ'));
            } else {
                $this->error(lang('member_edit_fail'));
            }
        }
    }

    /**
     * 删除用户
     */
    public function del()
    {
        $member_id = input('param.member_id');
        if (empty($member_id)) {
            $this->error(lang('param_error'));
        }
        $result = Db::name('member')->delete($member_id);
        if ($result) {
            ds_json_encode(10000, lang('member_del_succ'));
        } else {
            ds_json_encode(10001, lang('member_del_fail'));
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
                'name' => 'index', 'text' => lang('ds_manage'), 'url' => url('Member/index')
            ), array(
                'name' => 'add', 'text' => lang('ds_add'), 'url' => "javascript:dsLayerOpen('".url('Member/add')."','".lang('ds_add')."')"
            ),
        );
        return $menu_array;
    }

}
