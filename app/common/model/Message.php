<?php

namespace app\common\model;
use think\facade\Db;



class Message extends BaseModel
{
    public $page_info;
    
    
    /**
     * 添加留言
     * @author csdeshang
     * @param type $param
     * @return type
     */
    public function addMessage($data)
    {
        return Db::name('message')->insertGetId($data);
    }

    /**
     * 删除留言
     * @author csdeshang
     * @param type $condition
     * @return type
     */
    public function delMessage($condition)
    {
        return Db::name('message')->where($condition)->delete();
    }

    /**
     * 回复留言
     * @author csdeshang
     * @param type $condition
     * @param type $update
     * @return type
     */
    public function editMessage($condition, $update)
    {
        return Db::name('message')->where($condition)->update($update);
    }

    /**
     * 获取留言列表
     * @author csdeshang
     * @param type $condition 条件
     * @param type $field 字段
     * @param type $page  分页
     * @param type $order 排序
     * @param type $limit 限制
     * @return type
     */
    public function getMessageList($condition, $field = '*', $page = 0, $limit = 0, $order = 'message_addtime asc, message_id desc')
    {
        if ($page) {
            $res = Db::name('message')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$page,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('message')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select()->toArray();
        }
    }

    /**
     * 取单个留言内容
     * @author csdeshang
     * @param type $condition
     * @param type $field
     * @return type
     */
    public function getOneMessage($condition, $field = '*')
    {
        return Db::name('message')->field($field)->where($condition)->find();
    }
}

?>
