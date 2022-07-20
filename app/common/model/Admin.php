<?php
namespace app\common\model;
use think\facade\Db;

class Admin extends BaseModel
{

    /**
     * 获取管理员列表
     * @author csdeshang
     * @param type $condition 条件
     * @param type $field     字段
     * @param type $page      分页
     * @param type $order     排序
     * @return type
     */
    public function getAdminList($condition = array(), $field = '*', $page = 0, $order = 'admin_id desc')
    {
        if ($page) {
            $member_list = Db::name('admin')->alias('a')->join('admingroup g', 'g.group_id = a.admin_group_id', 'LEFT')->where($condition)->order($order)->paginate(['list_rows'=>$page,'query' => request()->param()],false);
            $this->page_info = $member_list;
            return $member_list->items();
        } else {
            return Db::name('admin')->alias('a')->join('admingroup g', 'g.group_id = a.admin_group_id', 'LEFT')->where($condition)->order($order)->select()->toArray();
        }
    }

    /**
     * 新增管理员
     * @author csdeshang
     * @param type $data
     * @return type
     */
    public function addAdmin($data)
    {
        return Db::name('admin')->insertGetId($data);
    }

    /**
     * 编辑管理员
     * @author csdeshang
     * @param type $condition
     * @param type $data
     * @return type
     */
    public function editAdmin($condition, $data)
    {
        return Db::name('admin')->where($condition)->update($data);
    }

    /**
     * 删除管理员
     * @author csdeshang
     * @param type $condition
     * @return type
     */
    public function delAdmin($condition)
    {
        return Db::name('admin')->where($condition)->delete();
    }

    /**
     * 取单个管理员
     * @author csdeshang
     * @param type $condition
     * @param type $field
     * @return type
     */
    public function getOneAdmin($condition, $field = '*')
    {
        return Db::name('admin')->field($field)->where($condition)->find();
    }
}