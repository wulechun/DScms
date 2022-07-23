<?php

namespace app\common\model;
use think\facade\Db;



class Column extends BaseModel
{

    public $page_info;


    /**
     * 获取栏目分类列表
     * @author csdeshang
     * @param type $condition  条件
     * @param type $limit  数量限制
     * @param type $order  排序
     * @return type
     */
    public function getColumnList($condition,$limit = 0,$order = 'column_id asc')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('column')->where($condition)->limit($limit)->select()->toArray();
    }

    /**
     * 编辑栏目
     * @author csdeshang
     * @param type $condition 条件
     * @param type $data  数据
     * @return type
     */
    public function editColumn($condition, $data)
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('column')->where($condition)->update($data);
    }


    /**
     * 取单个栏目分类的内容
     * @author csdeshang
     * @param int $id 栏目分类ID
     * @return array 数组类型的返回结果
     */
    public function getOneColumn($id) {
        $result = Db::name('column')->where(array(array('column_id' ,'=', intval($id))))->find();
        return $result;
    }

    /**
     * 新增栏目
     * @author csdeshang
     * @param array $param 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function add($data)
    {
        $data['lang_mark'] = config('lang.default_lang');
        $result = Db::name('column')->insertGetId($data);
        return $result;
    }

    /**
     * 删除栏目
     * @author csdeshang
     * @param int $id 记录ID
     * @return bool 布尔类型的返回结果
     */
    public function del($id)
    {
        return Db::name('column')->where(array(array("column_id" ,'=', intval($id))))->delete();
    }

    /**
     * 取栏目分类列表，按照深度归类
     * @author csdeshang
     * @param int $show_deep 显示深度
     * @return array 数组类型的返回结果
     */
    public function getTreeClassList($show_deep = '2')
    {
        $condition = array();
        $class_list = $this->getColumnList($condition); //取得$condition下的所有分类
        $show_deep = intval($show_deep);
        $result = array();
        if (is_array($class_list) && !empty($class_list)) {
            foreach($class_list as $k => $v) {
                if($v['parent_id'] == 0) {
                    $result = $this->_getTreeClassList($show_deep, $class_list, $deep = 1, $parent_id = 0, $i = $k); //取得递归下的分类
                }
            }
        }
        return $result;
    }

    public function get_has_news($condition)
    {
        $data = Db::name('news')->where($condition)->field("n.column_id ,c.column_name,c.column_id")->alias('n')->join('column c', 'n.column_id=c.column_id')->select()->toArray();

        $ids = array();
        foreach ($data AS $key => $value) {
            $ids[] = $value['column_id'];
        }
        $has_news = Db::name('column')->where(array(array('column_id', 'in', $ids)))->select()->toArray();

        foreach ($has_news as $key2 => $value2) {

            $has_news[$key2]['count'] = Db::name('news')->where(array(array('column_id', '=', $value2['column_id'])))->count();

        }

        return $has_news;
    }

    public function get_has_product($condition)
    {
        $data = Db::name('product')->where($condition)->field("p.column_id ,p.title,c.column_name,c.column_id")->alias('p')->join('column c', 'p.column_id=c.column_id', 'LEFT')->select()->toArray();
        $ids = array();
        foreach ($data AS $key => $value) {
            $ids[] = $value['column_id'];
        }
        return $has_news = Db::name('column')->where(array(array('column_id', 'in', $ids)))->select()->toArray();
    }

    /**
     * 递归 整理栏目分类
     * @author csdeshang
     * @param int $show_deep 显示深度
     * @param array $class_list 类别内容集合
     * @param int $deep 深度
     * @param int $parent_id 父类编号
     * @param int $i 上次循环编号
     * @return array $show_class 返回数组形式的查询结果
     */
    private function _getTreeClassList($show_deep, $class_list, $deep = 1, $parent_id = 0, $i = 0)
    {
        static $show_class = array(); //定义静态数组
        if (is_array($class_list) && !empty($class_list)) {
            $size = count($class_list); //取得分类条数
            if ($i == 0)
                $show_class = array(); //从0开始时清空数组，防止多次调用后出现重复
            for ($i; $i < $size; $i++) {//$i为上次循环到的栏目分类编号，避免重新从第一条开始
                $val = $class_list[$i];
                $column_id = $val['column_id'];
                $c_parent_id = $val['parent_id']; //把循环下父id赋值给新的变量
                if ($c_parent_id == $parent_id) {     //如果父id ==$parent_id（默认是0）
                    $val['deep'] = $deep;           //深度就会等于$deep++
                    $show_class[] = $val;           //转为一个如果父id是$parent_id则深度为$deep++ 的新二维数组
                    if ($deep < $show_deep && $deep < 2) {//本次深度小于显示深度时执行，避免取出的数据无用//如果深度小于显示深度 & 深度小于2 
                        $this->_getTreeClassList($show_deep, $class_list, $deep + 1, $column_id, $i + 1); //开始递归调用自身 深度加1
                    }
                }
                if ($c_parent_id > $parent_id)
                    break; //当前栏目分类的父编号大于本次递归的时（默认是0）退出循环
            }
        }
        return $show_class;
    }

    /**
     * 取指定栏目分类ID下的所有子类
     * @author csdeshang
     * @param int /array $parent_id 父ID 可以单一可以为数组
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function getChildClass($parent_id)
    {
        $all_class = $this->getColumnList(array(),0,'parent_id asc,column_id asc');
        if (is_array($all_class)) {
            if (!is_array($parent_id)) {
                $parent_id = array($parent_id);
            }
            $result = array();
            foreach ($all_class as $k => $v) {
                $id = $v['column_id']; //返回的结果包括父类
                $c_parent_id = $v['parent_id'];
                if (in_array($id, $parent_id) || in_array($c_parent_id, $parent_id)) {
                    $result[] = $v;
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 递归
     * @author csdeshang
     * @param type $data
     * @param type $id
     * @param type $lev
     * @return type
     */
    public function _get_tree($data, $id = 0, $lev = 1)
    {
        $subs = array(); // 子刊数组
        foreach ($data as $v) {
            if ($v['parent_id'] == $id) {
                $v['lev'] = $lev;
                $subs[] = $v;
                $subs = array_merge($subs, $this->_get_tree($data, $v['column_id'], $lev + 1));
            }
        }
        return $subs;
    }
    
    public function dedeMerge($row, $attr=array()) {
        if(!isset($row['column_id'])){
            return [];
        }
        extract($attr);
        if(!isset($needRel) || !$needRel){
            $needRel = false;
        }
        $row['sonids'] = $row['rel'] = '';
        if ($needRel) {
            $row['sonids'] = $this->getColumnSonIds($row['column_id']);
            if ($row['sonids'] == '')
                $row['rel'] = '';
            else
                $row['rel'] = " rel='dropmenu{$row['column_id']}'";
        }
        //处理同级栏目中，当前栏目的样式
        $row['typelink'] = $row['typeurl'] = $this->getColumnUrl($row);
        $new_row = [
            'id' => $row['column_id'],
            'typename' => $row['column_name'],
            'content' => $row['column_content'],
            'ispart' => $row['column_is_part'],
            'reid' => $row['parent_id'],
            'channeltype' => $row['column_module'],
            'sortrank' => $row['column_order'],
            'keywords' => $row['column_keywords'],
            'ishidden' => $row['column_display'],
            'seotitle' => $row['seo_title'],
            'keywords' => $row['seo_keywords'],
            'description' => $row['seo_description'],
        ];
        return array_merge($row, $new_row);
    }

    public function getColumnSonIds($column_id) {
        $sonids = rcache($column_id, 'column_son_ids_');
        if (!is_string($sonids)) {
            $condition = array();
            $condition[] = array('column_display', '=', 1);
            $condition[] = array('parent_id', '=', $column_id);
            $sonids = Db::name('column')->where($condition)->column('column_id');
            $sonids = implode(',', $sonids);
            wcache($column_id, $sonids, 'column_son_ids_');
        }
        if($sonids){
            $sonids=$column_id.','.$sonids;
        }else{
            $sonids=(String)$column_id;
        }
        return $sonids;
    }
    private function getColumnUrl($item) {
        if(!isset($item['column_id'])){
            return '';
        }
        $url = '';
        if($item['column_is_part']){
            $url = (String)url('Index/column', ['column_id' => $item['column_id']]);
        }else{
            switch ($item['column_module']) {
                case COLUMN_NEWS://文章
                    $url = (String)url('News/search', ['id' => $item['column_id']]);
                    break;
                case COLUMN_PRODUCT://商品
                    $url = (String)url('Product/search', ['id' => $item['column_id']]);
                    break;
                case COLUMN_CASES://图片
                    $url = (String)url('Cases/search', ['id' => $item['column_id']]);
                    break;
            }
        }

        return $url;
    }
}
