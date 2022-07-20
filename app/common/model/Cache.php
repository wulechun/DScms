<?php

namespace app\common\model;
use think\facade\Db;


class Cache extends BaseModel
{

    public function call($method)
    {
        $method = '_' . strtolower($method);
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            return false;
        }
    }

    /**
     * 基本设置
     * @return array
     */
    private function _config()
    {
        $result = Db::name('config')->select()->toArray();
        if (is_array($result)) {
            $list_config = array();
            foreach ($result as $k => $v) {
                $list_config[$v['code']] = $v['value'];
            }
        }
        unset($result);
        return $list_config;
    }
}
