<?php

namespace app\common\model;
use think\facade\Db;



class Nav extends BaseModel
{
    public $page_info;


    /**
     * 新增导航
     * @author csdeshang
     * @param type $param
     * @return type
     */
    public function addNav($data)
    {
        $data['lang_mark'] = config('lang.default_lang');
        return Db::name('nav')->insertGetId($data);
    }

    /**
     * 编辑导航
     * @author csdeshang
     * @param type $condition
     * @param type $update
     * @return type
     */
    public function editNav($condition, $update)
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('nav')->where($condition)->update($update);
    }

    /**
     * 删除导航
     * @author csdeshang
     * @param type $condition
     * @return type
     */
    public function delNav($condition)
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('nav')->where($condition)->delete();
    }

    /**
     * 获取导航列表
     * @author csdeshang
     * @param type $condition 条件
     * @param type $field  字段
     * @param type $page   分页
     * @param type $order  数量限制
     * @return type
     */
    public function getNavList($condition, $field = '*', $page = 0, $order = 'nav_order asc, nav_id desc')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        if ($page) {
            $res = Db::name('nav')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$page,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('nav')->where($condition)->field($field)->order($order)->select()->toArray();
        }
    }

    /**
     * 取单个导航内容
     * @author csdeshang
     * @param type $condition
     * @param type $field
     * @return type
     */
    public function getOneNav($condition, $field = '*')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('nav')->field($field)->where($condition)->find();
    }
}

?>
