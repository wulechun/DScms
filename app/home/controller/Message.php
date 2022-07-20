<?php
namespace app\home\controller;
use think\facade\View;
use think\facade\Lang;
class Message extends BaseMall {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/message.lang.php');
    }

    /**
     * 留言
     * @return type
     */
    public function index() {
        if (!request()->isPost()) {
            return View::fetch($this->template_dir . 'index');
        } else {
            //需要完善地方 1.对录入数据进行判断  2.对判断用户名是否存在
            $message_model = model('message');
            $data = array(
                'message_customer' => empty(session('member_name')) ? '' : session('member_name'),
                'message_title' => input('post.message_title'),
                'message_ctitle' => input('post.message_ctitle'),
                'message_content' => input('post.message_content'),
                'message_addtime' => TIMESTAMP,
            );
            //验证数据  BEGIN
            $rule = [
                    'message_title'=>'require|length:3,120',
                    'message_content'=>'require|length:1,180',
            ];
            $message = [
                    'message_title.require'=>'标题不能为空',
                    'message_title.length:3,120'=>'标题长度在3到120位',
                    'message_content.require'=>'内容不能为空',
                    'message_content.length:1,180'=>'留言内容超出限制',
            ];
            try{
            $this->validate($data,$rule,$message);
            }catch(\Exception $e){
                $this->error($e->getMessage());
            }
            //验证数据  END
            $result = $message_model->addMessage($data);
            if ($result) {
                $this->success(lang('留言成功'), 'Message/index');
            } else {
                $this->error(lang('member_add_fail'));
            }
        }
    }

}
