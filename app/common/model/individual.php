<?php

namespace app\common\model;
use think\facade\Db;



class individual extends BaseModel
{
    public $page_info;

    /**
     * 新增单页
     * @author csdeshang
     * @param type $param
     * @return type
     */
    public function addindividual($data)
    {
        $data['lang_mark'] = config('lang.default_lang');
        return Db::name('individual')->insertGetId($data);
    }

    /**
     * 编辑单页
     * @author csdeshang
     * @param type $condition  条件
     * @param type $update     数据
     * @return type
     */
    public function editindividual($condition, $update)
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('individual')->where($condition)->update($update);
    }

    /**
     * 删除单页
     * @author csdeshang
     * @param type $condition
     * @return type
     */
    public function delindividual($condition)
    {
        return Db::name('individual')->where($condition)->delete();
    }

    /**
     * 获取单页列表
     * @author csdeshang
     * @param array $condition 条件数组
     * @param str $field  字段
     * @param int $page   分页
     * @param str $order  排序
     * @param int $limit 数量限制
     * @return array 返回数组类型结果
     */
    public function getindividualList($condition, $field = '*', $page = 0, $order = 'individual_id desc')
    {
        //$condition[]=array('lang_mark','=',config('lang.default_lang'));
        if ($page) {
            $res = Db::name('individual')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$page,'page' => 1,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('individual')->where($condition)->field($field)->order($order)->select()->toArray();
        }
    }

    /**
     * 取单个单页内容
     * @author csdeshang
     * @param type $condition 条件
     * @param type $field  字段
     * @return type
     */
    public function getOneindividual($condition, $field = '*')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('individual')->field($field)->where($condition)->find();
    }
}

?>
