<?php

namespace app\common\model;
use think\facade\Db;



class link extends BaseModel
{
    public $page_info;


    /**
     * 新增友情链接
     * @author csdeshang
     * @param type $param
     * @return type
     */
    public function addLink($data)
    {
        $data['lang_mark'] = config('lang.default_lang');
        return Db::name('link')->insertGetId($data);
    }

    /**
     * 编辑友情链接
     * @author csdeshang
     * @param type $condition
     * @param type $update
     * @return type
     */
    public function editLink($condition, $update)
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('link')->where($condition)->update($update);
    }

    /**
     * 删除友情链接
     * @author csdeshang
     * @param type $condition
     * @return type
     */
    public function delLink($condition)
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        $link_array = $this->getlinkList($condition, 'link_id,link_weblogo');
        $linkid_array = array();
        foreach ($link_array as $value) {
            $linkid_array[] = $value['link_id'];
            @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_CASES . DIRECTORY_SEPARATOR . $value['link_weblogo']);
        }
        return Db::name('link')->where(array(array('link_id','in', $linkid_array)))->delete();
    }


    /**
     * 获取友情链接列表
     * @author csdeshang
     * @param type $condition 条件
     * @param type $field   字段
     * @param type $page    分页
     * @param type $order   排序
     * @return type
     */
    public function getLinkList($condition, $field = '*', $page = 0, $order = 'link_order asc, link_id desc')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        if ($page) {
            $res = Db::name('link')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$page,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('link')->where($condition)->field($field)->order($order)->select()->toArray();
        }
    }

    /**
     * 取单个友链内容
     * @author csdeshang
     * @param type $condition
     * @param type $field
     * @return type
     */
    public function getOneLink($condition, $field = '*')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('link')->field($field)->where($condition)->find();
    }
}

?>
