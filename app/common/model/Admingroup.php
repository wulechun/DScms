<?php
namespace app\common\model;
use think\facade\Db;


class Admingroup extends BaseModel
{
    
    /**
     * 获取权限组列表
     * @param type $field
     * @return type
     */
    public function getAdminGroupList($field = '*')
    {
        return Db::name('admingroup')->field($field)->select()->toArray();
    }

    /**
     * 新增管理员权限组
     * @param type $data
     * @return type
     */
    public function addAdminGroup($data)
    {
        return Db::name('admingroup')->insertGetId($data);
    }

    /**
     * 编辑管理员权限组
     * @param type $condition
     * @param type $data
     * @return type
     */
    public function editAdminGroup($condition, $data)
    {
        return Db::name('admingroup')->where($condition)->update($data);
    }

    /**
     * 删除管理权限组
     * @param type $condition
     * @return type
     */
    public function delAdminGroup($condition)
    {
        return Db::name('admingroup')->where($condition)->delete();
    }

    /**
     * 取单个权限组
     * @param type $condition
     * @param type $field
     * @return type
     */
    public function getOneAdmingroup($condition, $field = '*')
    {
        return Db::name('admingroup')->field($field)->where($condition)->find();
    }
}