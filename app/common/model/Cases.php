<?php

namespace app\common\model;
use think\facade\Db;



class Cases extends BaseModel
{
    public $page_info;

    /*
     * 新增案例
     */
    /**
     * 新增案例
     * @author csdeshang
     * @param type $param
     * @return type
     */
    public function addCases($data)
    {
        $data['lang_mark'] = config('lang.default_lang');
        return Db::name('cases')->insertGetId($data);
    }

    /**
     * 编辑案例
     * @author csdeshang
     * @param array $condition 条件
     * @param type $update 更新数据
     * @return type
     */
    public function editCases($condition, $update)
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('cases')->where($condition)->update($update);
    }

    /**
     * 删除案例
     * @author csdeshang
     * @param unknown $condition
     * @return boolean
     */
    public function delCases($condition,$flag=true)
    {
        if($flag){
            $condition[]=array('lang_mark','=',config('lang.default_lang'));
        }
        $cases_array = Db::name('cases')->where($condition)->field('cases_id,cases_imgurl')->select()->toArray();
        $casesid_array = array();
        foreach ($cases_array as $value) {
            $casesid_array[] = $value['cases_id'];
            @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_CASES . DIRECTORY_SEPARATOR . $value['cases_imgurl']);
        }
        return Db::name('cases')->where(array(array('cases_id','in', $casesid_array)))->delete();
    }

    /**
     * 获取案例列表
     * @author csdeshang
     * @param array $condition 条件
     * @param type $field  字段
     * @param type $page   分页
     * @param type $order  排序
     * @param type $limit  数量限制
     * @return type 数组
     */
    public function getCasesList($condition, $field = '*', $page = 0, $limit = 0, $order = 'cases_order desc, cases_id desc')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));

        if ($page) {
            $res = Db::name('cases')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$page,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('cases')->where($condition)->field($field)->order($order)->page($page)->limit($limit)->select()->toArray();
        }
    }

    /**
     * 取单个案例内容
     * @author csdeshang
     * @param array $condition
     * @param type $field
     * @return type
     */
    public function getOneCases($condition, $field = '*')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('cases')->field($field)->where($condition)->find();
    }
    
    public function dedeMerge($row, $attr=array()) {
        if(!isset($row['cases_id'])){
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
        $row['filename'] = $row['arcurl'] = (String) url('Cases/detail', ['cases_id' => $row['cases_id']]);
        $row['typeurl'] = (String) url('Cases/search', ['id' => $row['column_id']]);
        $row['picname'] = get_cases_img($row['cases_imgurl']);
        $row['stime'] = date('Y-m-d', $row['cases_addtime']);
        $row['typelink'] = "<a href='" . $row['typeurl'] . "'>" . $row['column_name'] . "</a>";
        $row['image'] = "<img src='" . $row['picname'] . "' border='0' width='$imgwidth' height='$imgheight' alt='" . preg_replace("#['><]#", "", $row['cases_title']) . "'>";
        $row['imglink'] = "<a href='" . $row['filename'] . "'>" . $row['image'] . "</a>";
        $row['fulltitle'] = $row['cases_title'];
        $row['title'] = mb_substr($row['cases_title'], 0, $titlelen);
        if (isset($row['color']) && $row['color'] != '')
            $row['title'] = "<font color='" . $row['color'] . "'>" . $row['title'] . "</font>";
        $row['textlink'] = "<a href='" . $row['filename'] . "'>" . $row['title'] . "</a>";
        $row['plusurl'] = $row['phpurl'] = '';
        $row['memberurl'] = '';
        $row['templeturl'] = PLUGINS_SITE_ROOT;
        $new_row = [
            'id' => $row['cases_id'],
            'body' => $row['cases_content'],
            'title' => $row['title'],
            'typeid' => $row['column_id'],
            'sortrank' => $row['cases_order'],
            'writer' => $row['cases_issue'],
            'click' => $row['cases_hits'],
            'senddate' => $row['cases_addtime'],
            'pubdate' => $row['cases_addtime'],
            'description' => $row['seo_description'],
        ];
        return array_merge($row, $new_row);
    }

}

?>
