<?php

namespace app\common\model;
use think\facade\Db;



class News extends BaseModel
{
    public $page_info;


    /**
     * 新增新闻
     * @author csdeshang
     * @param type $data
     * @return type
     */
    public function addNews($data)
    {
        $data['lang_mark'] = config('lang.default_lang');
        return Db::name('news')->insertGetId($data);
    }

    /**
     * 编辑新闻
     * @author csdeshang
     * @param type $condition
     * @param type $data
     * @return type
     */
    public function editNews($condition, $data)
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('news')->where($condition)->update($data);
    }

    /**
     * 删除新闻
     * @author csdeshang
     * @param type $condition
     * @return type
     */
    public function delNews($condition)
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('news')->where($condition)->delete();
    }

    /**
     * 获取新闻列表
     * @author csdeshang
     * @param array $condition 条件数组
     * @param int $limit     数量限制
     * @param str $field     数表字段
     * @param int $page      分页页数
     * @param str $order     排序组合
     * @return array   返回数组类型数据集
     */
    public function getNewsList($condition = array(),$limit=0, $field = '*', $page = '', $order = 'news_order desc,news_id desc')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        if ($page) {
            $res = Db::name('news')->where($condition)->field($field)->order($order)->paginate(['list_rows'=>$page,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('news')->where($condition)->field($field)->order($order)->limit(is_array($limit)?$limit[0]:0,is_array($limit)?$limit[1]:$limit)->select()->toArray();
        }
    }

    /**
     * 取单个新闻
     * @author csdeshang
     * @param type $condition
     * @param type $field
     * @return type
     */
    public function getOneNews($condition, $field = '*')
    {
        $condition[]=array('lang_mark','=',config('lang.default_lang'));
        return Db::name('news')->field($field)->where($condition)->find();
    }
    
    public function dedeMerge($row,$attr=array()) {
        if(!isset($row['news_id'])){
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
        $row['filename'] = $row['arcurl'] = (String) url('News/detail', ['news_id' => $row['news_id']]);
        $row['typeurl'] = (String)url('News/search', ['id' => $row['column_id']]);
        $row['picname'] = get_news_img($row['news_imgurl']);
        $row['stime'] = date('Y-m-d', $row['news_addtime']);
        $row['typelink'] = "<a href='" . $row['typeurl'] . "'>" . $row['column_name'] . "</a>";
        $row['image'] = "<img src='" . $row['picname'] . "' border='0' width='$imgwidth' height='$imgheight' alt='" . preg_replace("#['><]#", "", $row['news_title']) . "'>";
        $row['imglink'] = "<a href='" . $row['filename'] . "'>" . $row['image'] . "</a>";
        $row['fulltitle'] = $row['news_title'];
        $row['title'] = mb_substr($row['news_title'], 0, $titlelen);
        if (isset($row['color']) && $row['color'] != '')
            $row['title'] = "<font color='" . $row['color'] . "'>" . $row['title'] . "</font>";
        $row['textlink'] = "<a href='" . $row['filename'] . "'>" . $row['title'] . "</a>";
        $row['plusurl'] = $row['phpurl'] = '';
        $row['memberurl'] = '';
        $row['templeturl'] = PLUGINS_SITE_ROOT;
        $new_row = [
            'id' => $row['news_id'],
            'body' => $row['news_content'],
            'title' => $row['title'],
            'typeid' => $row['column_id'],
            'sortrank' => $row['news_order'],
            'writer' => $row['news_issue'],
            'click' => $row['news_hits'],
            'senddate' => $row['news_addtime'],
            'pubdate' => $row['news_addtime'],
            'description' => $row['seo_description'],
        ];
        return array_merge($row, $new_row);
    }
}
