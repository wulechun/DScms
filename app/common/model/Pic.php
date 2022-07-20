<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/13 0013
 * Time: 11:27
 */

namespace app\common\model;
use think\facade\Db;




class Pic extends BaseModel
{
    
    /**
     * 增加图片
     * @author csdeshang
     * @param type $data
     * @return type
     */
    public function addPic($data)
    {
        return Db::name('pic')->insertGetId($data);
    }

    /**
     * 编辑更新图片
     * @author csdeshang
     * @param type $condition
     * @param type $data
     * @return type
     */
    public function editPic($condition, $data)
    {
        return Db::name('pic')->where($condition)->update($data);
    }

    /**
     * 获取图片列表
     * @author csdeshang
     * @param type $condition 条件
     * @param type $page 分页
     * @param type $order 排序
     * @return type
     */
    public function getPicList($condition, $page = '', $order = 'pic_id desc')
    {
        if ($page) {
            $res = Db::name('pic')->where($condition)->order($order)->paginate(['list_rows'=>$page,'query' => request()->param()],false);
            $this->page_info = $res;
            return $res->items();
        } else {
            return Db::name('pic')->where($condition)->order($order)->select()->toArray();
        }
    }

    /**
     * 删除图片
     * @author csdeshang
     * @param type $condition
     * @param type $attach_type
     * @return type
     */
    public function delPic($condition, $attach_type)
    {
        switch ($attach_type) {
            case 'cases':
                $attach_type = ATTACH_CASES;
                break;
            case 'news':
                $attach_type = ATTACH_NEWS;
                break;
            case 'product':
                $attach_type = ATTACH_PRODUCT;
            default:
                break;
        }
        $casespic_list = $this->getpicList($condition);
        foreach ($casespic_list as $key => $casespic) {
            @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . $attach_type . DIRECTORY_SEPARATOR . $casespic['pic_cover']);
        }
        return Db::name('pic')->where($condition)->delete();
    }
}