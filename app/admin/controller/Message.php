<?php

namespace app\admin\controller;
use think\facade\View;

use think\facade\Lang;
use think\Validate;

class Message extends AdminControl
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/message.lang.php');
    }

    /**
     * 留言管理
     */
    public function index()
    {
        $model_message = model('message');
        $condition = array();
        $message_list = $model_message->getMessageList($condition, '*', 5);
        View::assign('message_list', $message_list);
        View::assign('show_page', $model_message->page_info->render());
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 留言回复
     */
    public function reply(){
        $message_id = intval(input('param.message_id'));
        if (!request()->isPost()) {
            $message = model('message')->getOneMessage([['message_id' ,'=', $message_id]]);
            View::assign('message', $message);
            $this->setAdminCurItem('reply');
            return View::fetch('form');
        } else {
            $data = array(
                'admin_id' => $this->admin_info['admin_id'],
                'message_replytime' => TIMESTAMP,
                'message_useinfo' => input('post.message_useinfo'),
                'message_readok' => 1,
            );

            $result = model('message')->editMessage([['message_id' ,'=', $message_id]], $data);
            if ($result) {
                $this->log(lang('ds_message').'-'.lang('edit_succ') . '[' . $data['message_useinfo'] . ']', null);
                dsLayerOpenSuccess(lang('edit_succ'));
            } else {
                $this->error(lang('edit_fail'));
            }
        }
    }

    /**
     * 删除留言
     */
    public function del()
    {
        $message_id = intval(input('param.message_id'));
        if ($message_id) {
            $condition[]=array('message_id','=',$message_id);
            $result = model('message')->delMessage($condition);
            if ($result) {
                $this->log(lang('ds_message').'-'.lang('del_succ') . '[' . $message_id . ']', null);
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
                'name' => 'index', 'text' => lang('ds_manage'), 'url' => url('message/index'),
            )
        );
        return $menu_array;
    }

}

?>
