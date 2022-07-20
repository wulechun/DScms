<?php

namespace app\common\model;
use think\facade\Db;



class Config extends BaseModel
{
    /**
     * 读取系统设置信息
     * @author csdeshang
     * @param string $name 系统设置信息名称
     * @return array 数组格式的返回结果
     */
    public function getRowConfig($name)
    {
        $where=array();
        $where[] = array("name",'=',$name);
        $result = Db::name('config')->where($where)->select()->toArray();
        if (is_array($result) and is_array($result[0])) {
            return $result[0];
        }
        return false;
    }

    /**
     * 读取系统设置列表
     * @author csdeshang
     * @param
     * @return array 数组格式的返回结果
     */
    public function getListConfig()
    {
        $result = Db::name('config')->select()->toArray();
        if (is_array($result)) {
            $list_config = array();
            foreach ($result as $k => $v) {
                $list_config[$v['code']] = $v['value'];
            }
        }
        return $list_config;
    }

    /**
     * 更新系统设置信息
     * @author csdeshang
     * @param array $param 更新数据
     * @return bool 布尔类型的返回结果
     */
    public function updateConfig($param)
    {
        if (empty($param)) {
            return false;
        }
        if (is_array($param)) {
            foreach ($param as $k => $v) {
                $tmp = array();
                $specialkeys_arr = array('statistics_name');
                $tmp['value'] = (in_array($k, $specialkeys_arr) ? htmlentities($v, ENT_QUOTES) : $v);
                $result = Db::name('config')->where(array(array('code','=', $k)))->update($tmp);
                dkcache('config');
            }
            return true;
        } else {
            return false;
        }
    }

}

?>
