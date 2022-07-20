<?php

namespace app\common\model;
use think\facade\Db;



class Jobcv extends BaseModel
{
    public $page_info;

    
    /**
     * 添加简历
     * @author csdeshang
     * @param type $data   数据
     * @return type boolean 
     */
    public function addJobcv($data)
    {
        return Db::name('jobcv')->insert($data);
    }
    
    /**
     * 编辑职位
     * @author csdeshang
     * @param type $condition 条件
     * @param type $update    更新数据
     * @return type
     */
    public function editJobcv($condition, $update)
    {
        return Db::name('jobcv')->where($condition)->update($update);
    }

    /**
     * 删除简历
     * @author csdeshang
     * @param type $condition
     * @return type
     */
    public function delJobcv($condition)
    {
        return Db::name('jobcv')->where($condition)->delete();
    }

    /**
     * 获取简历列表
     * @author csdeshang
     * @param type $condition 条件  
     * @param type $field     字段
     * @param type $page      分页
     * @param type $order     排序
     * @param type $limit     限制
     * @return type           数组
     */
    public function getJobcvList($condition, $field = '*', $page = 0, $limit = 0, $order = 'jobcv_addtime desc, jobcv_id desc')
    {
        if ($page) {
            $res = Db::name('jobcv')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$page,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('jobcv')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select()->toArray();
        }
    }

    /**
     * 取单个简历内容
     * @author csdeshang
     * @param type $condition
     * @param type $field
     * @return type
     */
    public function getOneJobcv($condition, $field = '*')
    {
        return Db::name('jobcv')->field($field)->where($condition)->find();
    }
}

?>
