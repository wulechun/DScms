<?php

namespace app\common\model;
use think\facade\Db;



class Product extends BaseModel
{
    public $page_info;


    /**
     * 新增产品
     * @author csdeshang
     * @param type $param
     * @return type
     */
    public function addProduct($data)
    {
        $data['lang_mark'] = config('lang.default_lang');
        return Db::name('product')->insertGetId($data);
    }

    /**
     * 编辑产品
     * @author csdeshang
     * @param type $condition
     * @param type $update
     * @return type
     */
    public function editProduct($condition, $update)
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('product')->where($condition)->update($update);
    }

    /**
     * 删除产品
     * @author csdeshang
     * @param type $condition
     * @return type
     */
    public function delProduct($condition,$flag=true)
    {
        if($flag){
            $condition[]=array('lang_mark','=',config('lang.default_lang'));
        }
        $product_array = Db::name('product')->where($condition)->field('product_id,product_imgurl')->select()->toArray();
        $productid_array = array();
        foreach ($product_array as $value) {
            $productid_array[] = $value['product_id'];
            @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_PRODUCT . DIRECTORY_SEPARATOR . $value['product_img']);
        }
        return Db::name('product')->where(array(array('product_id','in', $productid_array)))->delete();
    }

    /**
     * 获取产品列表
     * @author csdeshang
     * @param type $condition 条件
     * @param type $field  字段
     * @param type $page   分页
     * @param type $order  排序
     * @param type $limit  数量限制
     * @return type
     */
    public function getProductList($condition, $field = '*', $page = 0, $limit = 0, $order = 'product_order desc, product_id desc')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        if ($page) {
            $res = Db::name('product')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$page,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('product')->where($condition)->field($field)->order($order)->select()->toArray();
        }
    }

    /**
     * 取单个产品内容
     * @author csdeshang
     * @param type $condition
     * @param type $field
     * @return type
     */
    public function getOneProduct($condition, $field = '*')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('product')->field($field)->where($condition)->find();
    }
    
    public function dedeMerge($row,$attr=array()) {
        if(!isset($row['product_id'])){
            return [];
        }
        extract($attr);
        if(!isset($titlelen) || !$titlelen){
            $titlelen = 30;
        }
        if(!isset($infolen) || !$infolen){
            $infolen=160;
        }
        if(!isset($imgwidth) || !$imgwidth){
            $imgwidth = 120;
        }
        if(!isset($imgheight) || !$imgheight){
            $imgheight = 90;
        }
        $column_model=model('column');
        $row['column_name']='';
        if (!isset($row['column_name'])) {
            $column_info = $column_model->getOneColumn($row['column_id']);
            $row['column_name']=$column_info['column_name'];
        }
        //处理一些特殊字段
        $row['info'] = $row['infos'] = mb_substr($row['seo_description'], 0, $infolen);
        $row['filename'] = $row['arcurl'] = (String) url('Product/detail', ['product_id' => $row['product_id']]);
        $row['typeurl'] = (String)url('Product/search', ['id' => $row['column_id']]);
        $row['picname'] = get_product_img($row['product_imgurl']);
        $row['stime'] = date('Y-m-d', $row['product_addtime']);
        $row['typelink'] = "<a href='" . $row['typeurl'] . "'>" . $row['column_name'] . "</a>";
        $row['image'] = "<img src='" . $row['picname'] . "' border='0' width='$imgwidth' height='$imgheight' alt='" . preg_replace("#['><]#", "", $row['product_title']) . "'>";
        $row['imglink'] = "<a href='" . $row['filename'] . "'>" . $row['image'] . "</a>";
        $row['fulltitle'] = $row['product_title'];
        $row['title'] = mb_substr($row['product_title'], 0, $titlelen);
        if (isset($row['color']) && $row['color'] != '')
            $row['title'] = "<font color='" . $row['color'] . "'>" . $row['title'] . "</font>";
        $row['textlink'] = "<a href='" . $row['filename'] . "'>" . $row['title'] . "</a>";
        $row['plusurl'] = $row['phpurl'] = '';
        $row['memberurl'] = '';
        $row['templeturl'] = PLUGINS_SITE_ROOT;
        $new_row = [
            'id' => $row['product_id'],
            'body' => $row['product_content'],
            'title' => $row['title'],
            'typeid' => $row['column_id'],
            'sortrank' => $row['product_order'],
            'writer' => $row['product_issue'],
            'click' => $row['product_hits'],
            'senddate' => $row['product_addtime'],
            'pubdate' => $row['product_addtime'],
            'description' => $row['seo_description'],
        ];
        return array_merge($row, $new_row);
    }
}

?>
