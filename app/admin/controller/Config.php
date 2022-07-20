<?php

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;

use think\facade\Lang;

class Config extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/config.lang.php');
    }

    /**
     * 网站配置
     * @return mixed
     */
    public function index() {
        $model_config = model('config');
        if (!request()->isPost()) {
            $list_config = rkcache('config', true);
            View::assign('list_config', $list_config);
            /* 设置卖家当前栏目 */
            $this->setAdminCurItem('base');
            return View::fetch();
        } else {
            //上传文件保存路径

            $upload_file = ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . ATTACH_COMMON;

            if (!empty($_FILES['site_logo']['name'])) {

                $file = request()->file('site_logo');
                $file_config = array(
                    'disks' => array(
                        'local' => array(
                            'root' => $upload_file
                        )
                    )
                );
                config($file_config, 'filesystem');
                try {
                    validate(['image' => 'fileExt:' . ALLOW_IMG_EXT])
                            ->check(['image' => $file]);
                    $file_name = \think\facade\Filesystem::putFileAs('', $file, 'site_logo.png');
                    $upload['site_logo'] = $file_name;
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            if (!empty($upload['site_logo'])) {
                $update_array['site_logo'] = $upload['site_logo'];
            }
            if (!empty($_FILES['member_logo']['name'])) {
                $file = request()->file('member_logo');
                $file_config = array(
                    'disks' => array(
                        'local' => array(
                            'root' => $upload_file
                        )
                    )
                );
                config($file_config, 'filesystem');
                try {
                    validate(['image' => 'fileExt:' . ALLOW_IMG_EXT])
                            ->check(['image' => $file]);
                    $file_name = \think\facade\Filesystem::putFileAs('', $file, 'member_logo.png');
                    $upload['member_logo'] = $file_name;
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            if (!empty($upload['member_logo'])) {
                $update_array['member_logo'] = $upload['member_logo'];
            }
            if (!empty($_FILES['site_mobile_logo']['name'])) {
                $file = request()->file('site_mobile_logo');
                $file_config = array(
                    'disks' => array(
                        'local' => array(
                            'root' => $upload_file
                        )
                    )
                );
                config($file_config, 'filesystem');
                try {
                    validate(['image' => 'fileExt:' . ALLOW_IMG_EXT])
                            ->check(['image' => $file]);
                    $file_name = \think\facade\Filesystem::putFileAs('', $file, 'site_mobile_logo.png');
                    $upload['site_mobile_logo'] = $file_name;
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            if (!empty($upload['site_mobile_logo'])) {
                $update_array['site_mobile_logo'] = $upload['site_mobile_logo'];
            }
            if (!empty($_FILES['site_logowx']['name'])) {
                $file = request()->file('site_logowx');
                $file_config = array(
                    'disks' => array(
                        'local' => array(
                            'root' => $upload_file
                        )
                    )
                );
                config($file_config, 'filesystem');
                try {
                    validate(['image' => 'fileExt:' . ALLOW_IMG_EXT])
                            ->check(['image' => $file]);
                    $file_name = \think\facade\Filesystem::putFileAs('', $file, 'site_logowx.png');
                    $upload['site_logowx'] = $file_name;
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            if (!empty($upload['site_logowx'])) {
                $update_array['site_logowx'] = $upload['site_logowx'];
            }

            $update_array['site_name'] = input('post.site_name');
            $update_array['icp_number'] = input('post.icp_number');
            $update_array['site_phone'] = input('post.site_phone');
            $update_array['site_tel400'] = input('post.site_tel400');
            $update_array['site_email'] = input('post.site_email');
            $update_array['flow_static_code'] = input('post.flow_static_code');
            $update_array['site_state'] = input('post.site_state');
            $update_array['closed_reason'] = input('post.closed_reason');

            $result = $model_config->updateConfig($update_array);
            if ($result) {
                dkcache('config');
                $this->log(lang('ds_edit') . lang('web_set'), 1);
                $this->success('修改成功', 'Config/index');
            } else {
                $this->log(lang('ds_edit') . lang('web_set'), 0);
            }
        }
    }

    /**
     * 防灌水设置
     */
    public function dump() {
        $model_config = model('config');
        if (!request()->isPost()) {
            $list_config = $model_config->getListConfig();
            View::assign('list_config', $list_config);
            $this->setAdminCurItem('dump');
            return View::fetch();
        } else {
            $update_array = array();
            $update_array['cache_open'] = intval(input('post.cache_open'));
            $update_array['guest_comment'] = intval(input('post.guest_comment'));
            $update_array['captcha_status_login'] = intval(input('post.captcha_status_login'));
            $update_array['captcha_status_register'] = intval(input('post.captcha_status_register'));
            $update_array['captcha_status_feedback'] = intval(input('post.captcha_status_feedback'));
            $result = $model_config->updateConfig($update_array);
            if ($result === true) {
                $this->log(lang('ds_edit') . lang('dis_dump'), 1);
                $this->success('修改成功', 'Config/dump');
            } else {
                $this->log(lang('ds_edit') . lang('dis_dump'), 0);
                $this->error(lang('修改失败'));
            }
        }
    }

    /**
     * 网站SEO设置
     */
    public function seo() {
        $model_config = model('config');
        if (!request()->isPost()) {
            $list_config = $model_config->getListConfig();
            View::assign('list_config', $list_config);
            $this->setAdminCurItem('seo');
            return View::fetch();
        } else {
            $update_array['seo_home_title'] = input('post.seo_home_title');
            $update_array['seo_home_title_type'] = input('post.seo_home_title_type');
            $update_array['seo_home_keywords'] = input('post.seo_home_keywords');
            $update_array['seo_home_description'] = input('post.seo_home_description');
            $result = $model_config->updateConfig($update_array);
            if ($result) {
                dkcache('config');
                $this->log(lang('ds_edit') . lang('web_set'), 1);
                $this->success('修改成功', 'Config/seo');
            } else {
                $this->log(lang('ds_edit') . lang('web_set'), 0);
            }
        }
    }
    /**
     * 导入织梦
     */
    public function dede_import() {
        $model_config = model('config');
        if (!request()->isPost()) {
            $this->setAdminCurItem('dede_import');
            return View::fetch();
        } else {
            file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', '');
            $root_path=input('post.root_path');
            $root_path=str_replace('\\',DIRECTORY_SEPARATOR,$root_path);
            $root_path=str_replace('/',DIRECTORY_SEPARATOR,$root_path);
            $root_path= trim($root_path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            
            $pc_template_path=input('post.pc_template_path');
            $pc_template_path=str_replace('\\',DIRECTORY_SEPARATOR,$pc_template_path);
            $pc_template_path=str_replace('/',DIRECTORY_SEPARATOR,$pc_template_path);
            $pc_template_path= trim($pc_template_path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            
            $wap_template_path=input('post.wap_template_path');
            $wap_template_path=str_replace('\\',DIRECTORY_SEPARATOR,$wap_template_path);
            $wap_template_path=str_replace('/',DIRECTORY_SEPARATOR,$wap_template_path);
            $wap_template_path= trim($wap_template_path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            
            $prefix = config('database.connections.mysql.prefix');
            $database_config = [
                'connections' => [
                    'dede' => [
                        // 数据库类型
                        'type' => 'mysql',
                        // 服务器地址
                        'hostname' => input('post.hostname'),
                        // 数据库名
                        'database' => input('post.database'),
                        // 数据库用户名
                        'username' => input('post.username'),
                        // 数据库密码
                        'password' => input('post.password'),
                        // 数据库连接端口
                        'hostport' => input('post.hostport'),
                        // 数据库连接参数
                        'params' => [],
                        // 数据库编码默认采用utf8
                        'charset' => 'utf8',
                        // 数据库表前缀
                        'prefix' => input('post.prefix'),
                    ]
                ]
            ];
            config($database_config, 'database');

            $sql = "TRUNCATE TABLE `" . $prefix . "column`;";
            Db::execute($sql);
            file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', $sql . PHP_EOL, FILE_APPEND);
            $sql = "TRUNCATE TABLE `" . $prefix . "news`;";
            Db::execute($sql);
            file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', $sql . PHP_EOL, FILE_APPEND);
            $sql = "TRUNCATE TABLE `" . $prefix . "product`;";
            Db::execute($sql);
            file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', $sql . PHP_EOL, FILE_APPEND);
            $sql = "TRUNCATE TABLE `" . $prefix . "cases`;";
            Db::execute($sql);
            file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', $sql . PHP_EOL, FILE_APPEND);
            $sql = "TRUNCATE TABLE `" . $prefix . "link`;";
            Db::execute($sql);
            file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', $sql . PHP_EOL, FILE_APPEND);

            $step = ['arctype', 0];
            $condition = [];
            $count = Db::connect('dede')->name('arctype')->count();
            $temp = Db::connect('dede')->name('arctype')->where($condition)->limit($step[1], 1000)->order('id asc')->select()->toArray();
            $column_module = 0;
            foreach ($temp as $v) {
                switch ($v['channeltype']) {
                    case 1:
                        $column_module = 1;
                        break;
                    case 2:
                        $column_module = 3;
                        break;
                    case 6:
                        $column_module = 2;
                        break;
                }
                $tempindex='';
                if(preg_match('#/(.+)\.htm#', $v['tempindex'], $matches)){
                    $tempindex=$matches[1];
                }
                $templist='';
                if(preg_match('#/(.+)\.htm#', $v['templist'], $matches)){
                    $templist=$matches[1];
                }
                $temparticle='';
                if(preg_match('#/(.+)\.htm#', $v['temparticle'], $matches)){
                    $temparticle=$matches[1];
                }
                $sql = "INSERT INTO `" . $prefix . "column` (`column_id`,`column_name`,`column_content`,`column_temp_index`,`column_temp_list`,`column_temp_article`,`column_is_part`,`parent_id`,`column_module`,`column_order`,`column_keywords`,`column_display`,`seo_title`,`seo_keywords`,`seo_description`,`lang_mark`) VALUES (" . $v['id'] . ", '" . $v['typename'] . "', '" . $v['content'] . "', '" . $tempindex . "', '" . $templist . "', '" . $temparticle . "', " . $v['ispart'] . ", " . $v['reid'] . ", " . $column_module . ", " . $v['sortrank'] . ", '" . $v['keywords'] . "', " . ($v['ishidden'] ? 0 : 1) . ", '" . $v['seotitle'] . "', '" . $v['keywords'] . "', '" . $v['description'] . "', 'zh-cn');";
                Db::execute($sql);
                file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', $sql . PHP_EOL, FILE_APPEND);
            }

            $condition = [];
            $count = Db::connect('dede')->name('archives')->count();
            $temp = Db::connect('dede')->name('archives')->where($condition)->limit($step[1], 1000)->order('id asc')->select()->toArray();
            foreach ($temp as $v) {
                switch ($v['channel']) {
                    case 1:
                        $litpic = '';
                        if ($v['litpic']) {
                            $litpic = basename($v['litpic']);
                            if (preg_match('/^https?:\/\//', $v['litpic'])) {
                                $path = $v['litpic'];
                            } else {
                                $path = $root_path . $v['litpic'];
                            }
                            $handle = @fopen($path, "r");
                            if ($handle) {
                                file_put_contents(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_NEWS . DIRECTORY_SEPARATOR . $litpic, file_get_contents($path));
                                fclose($handle);
                            }
                        }
                        $condition = [];
                        $condition[] = ['aid', '=', $v['id']];
                        $news_content = Db::connect('dede')->name('addonarticle')->where($condition)->value('body');
                        if (preg_match_all("/\bsrc\s*=\s*('|\")([^\\1]+?)\\1/i", $news_content, $matches)) {
                            foreach ($matches[2] as $val) {
                                $tmppic = basename($val);
                                if (preg_match('/^https?:\/\//', $val)) {
                                    $path = $val;
                                } else {
                                    $path = $root_path . $val;
                                }
                                $handle = @fopen($path, "r");
                                if ($handle) {
                                    file_put_contents(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_NEWS . DIRECTORY_SEPARATOR . $tmppic, file_get_contents($path));
                                    fclose($handle);
                                }
                                $news_content = str_replace($val, UPLOAD_SITE_URL . '/' . ATTACH_NEWS . '/' . $tmppic, $news_content);
                            }
                        }
                        $sql = "INSERT INTO `" . $prefix . "news` (`news_title`,`news_content`,`column_id`,`news_order`,`news_imgurl`,`news_issue`,`news_hits`,`news_addtime`,`news_recycle`,`news_displaytype`,`seo_description`,`lang_mark`) VALUES ('" . $v['title'] . "', '" . ($news_content ? $news_content : '') . "', " . $v['typeid'] . ", " . $v['sortrank'] . ", '" . $litpic . "', '" . $v['writer'] . "', " . $v['click'] . ", " . $v['senddate'] . ", " . ($v['arcrank'] == -2 ? 2 : 0) . ", " . ($v['arcrank'] != 0 ? 0 : 1) . ", '" . $v['description'] . "', 'zh-cn');";
                        Db::execute($sql);
                        file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', $sql . PHP_EOL, FILE_APPEND);
                        break;
                    case 2:
                        $litpic = '';
                        if ($v['litpic']) {
                            $litpic = basename($v['litpic']);
                            if (preg_match('/^https?:\/\//', $v['litpic'])) {
                                $path = $v['litpic'];
                            } else {
                                $path = $root_path . $v['litpic'];
                            }
                            $handle = @fopen($path, "r");
                            if ($handle) {
                                file_put_contents(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_PRODUCT . DIRECTORY_SEPARATOR . $litpic, file_get_contents($path));
                                fclose($handle);
                            }
                        }
                        $condition = [];
                        $condition[] = ['aid', '=', $v['id']];
                        $product_content = Db::connect('dede')->name('addonshop')->where($condition)->value('body');
                        if (preg_match_all("/\bsrc\s*=\s*('|\")([^\\1]+?)\\1/i", $product_content, $matches)) {
                            foreach ($matches[2] as $val) {
                                $tmppic = basename($val);
                                if (preg_match('/^https?:\/\//', $val)) {
                                    $path = $val;
                                } else {
                                    $path = $root_path . $val;
                                }
                                $handle = @fopen($path, "r");
                                if ($handle) {
                                    file_put_contents(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_PRODUCT . DIRECTORY_SEPARATOR . $tmppic, file_get_contents($path));
                                    fclose($handle);
                                }
                                $product_content = str_replace($val, UPLOAD_SITE_URL . '/' . ATTACH_PRODUCT . '/' . $tmppic, $product_content);
                            }
                        }
                        $sql = "INSERT INTO `" . $prefix . "product` (`product_title`,`product_content`,`column_id`,`product_order`,`product_imgurl`,`product_issue`,`product_hits`,`product_addtime`,`product_recycle`,`product_displaytype`,`seo_description`,`lang_mark`) VALUES ('" . $v['title'] . "', '" . ($product_content ? $product_content : '') . "', " . $v['typeid'] . ", " . $v['sortrank'] . ", '" . $litpic . "', '" . $v['writer'] . "', " . $v['click'] . ", " . $v['senddate'] . ", " . ($v['arcrank'] == -2 ? 2 : 0) . ", " . ($v['arcrank'] != 0 ? 0 : 1) . ", '" . $v['description'] . "', 'zh-cn');";
                        Db::execute($sql);
                        file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', $sql . PHP_EOL, FILE_APPEND);
                        break;
                    case 6:
                        $litpic = '';
                        if ($v['litpic']) {
                            $litpic = basename($v['litpic']);
                            if (preg_match('/^https?:\/\//', $v['litpic'])) {
                                $path = $v['litpic'];
                            } else {
                                $path = $root_path . $v['litpic'];
                            }
                            $handle = @fopen($path, "r");
                            if ($handle) {
                                file_put_contents(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_CASES . DIRECTORY_SEPARATOR . $litpic, file_get_contents($path));
                                fclose($handle);
                            }
                        }
                        $condition = [];
                        $condition[] = ['aid', '=', $v['id']];
                        $cases_content = Db::connect('dede')->name('addonimages')->where($condition)->value('body');
                        if (preg_match_all("/\bsrc\s*=\s*('|\")([^\\1]+?)\\1/i", $cases_content, $matches)) {
                            foreach ($matches[2] as $val) {
                                $tmppic = basename($val);
                                if (preg_match('/^https?:\/\//', $val)) {
                                    $path = $val;
                                } else {
                                    $path = $root_path . $val;
                                }
                                $handle = @fopen($path, "r");
                                if ($handle) {
                                    file_put_contents(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_CASES . DIRECTORY_SEPARATOR . $tmppic, file_get_contents($path));
                                    fclose($handle);
                                }
                                $cases_content = str_replace($val, UPLOAD_SITE_URL . '/' . ATTACH_CASES . '/' . $tmppic, $cases_content);
                            }
                        }
                        $sql = "INSERT INTO `" . $prefix . "cases` (`cases_title`,`cases_content`,`column_id`,`cases_order`,`cases_imgurl`,`cases_issue`,`cases_hits`,`cases_addtime`,`cases_recycle`,`cases_displaytype`,`seo_description`,`lang_mark`) VALUES ('" . $v['title'] . "', '" . ($cases_content ? $cases_content : '') . "', " . $v['typeid'] . ", " . $v['sortrank'] . ", '" . $litpic . "', '" . $v['writer'] . "', " . $v['click'] . ", " . $v['senddate'] . ", " . ($v['arcrank'] == -2 ? 2 : 0) . ", " . ($v['arcrank'] != 0 ? 0 : 1) . ", '" . $v['description'] . "', 'zh-cn');";
                        Db::execute($sql);
                        file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', $sql . PHP_EOL, FILE_APPEND);
                        break;
                }
            }


            $condition = [];
            $count = Db::connect('dede')->name('flink')->count();
            $temp = Db::connect('dede')->name('flink')->where($condition)->limit($step[1], 1000)->order('id asc')->select()->toArray();
            foreach ($temp as $v) {
                $logo = '';
                if ($v['logo']) {
                    $logo = basename($v['logo']);
                    if (preg_match('/^https?:\/\//', $v['logo'])) {
                        $path = $v['logo'];
                    } else {
                        $path = $root_path . $v['logo'];
                    }
                    $handle = @fopen($path, "r");
                    if ($handle) {
                        file_put_contents(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_LINK . DIRECTORY_SEPARATOR . $logo, file_get_contents($path));
                        fclose($handle);
                    }
                }
                $sql = "INSERT INTO `" . $prefix . "link` (`link_webname`,`link_weburl`,`link_weblogo`,`link_type`,`link_order`,`link_show_ok`,`link_addtime`,`lang_mark`) VALUES ('" . $v['webname'] . "', '" . $v['url'] . "', '" . $logo . "', " . ($logo ? 1 : 0) . ", " . $v['sortrank'] . ", " . $v['ischeck'] . ", " . $v['dtime'] . ", 'zh-cn');";
                Db::execute($sql);
                file_put_contents(ROOT_PATH . DIRECTORY_SEPARATOR . DIR_UPLOAD . DIRECTORY_SEPARATOR . 'dede.sql', $sql . PHP_EOL, FILE_APPEND);
            }
            
            
            $path=ROOT_PATH.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'home'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'dede'.DIRECTORY_SEPARATOR;
            @mkdir($path, 0755, true);
            $old_path=ROOT_PATH.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'home'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR;
            copy($old_path.'theme.info.php', $path.'theme.info.php');
            $path=PUBLIC_PATH.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'home'.DIRECTORY_SEPARATOR.'dede'.DIRECTORY_SEPARATOR.'styles'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR;
            @mkdir($path, 0755, true);
            $old_path=PUBLIC_PATH.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'home'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR.'styles'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR;
            copy($old_path.'preview.jpg', $path.'preview.jpg');
            copy($old_path.'style.info.php', $path.'style.info.php');
            $this->copyFile($pc_template_path,$path,false);
            
            if($wap_template_path!=DIRECTORY_SEPARATOR){
                $path=ROOT_PATH.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'dede'.DIRECTORY_SEPARATOR;
                @mkdir($path, 0755, true);
                $path=PUBLIC_PATH.DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR.'dede'.DIRECTORY_SEPARATOR.'styles'.DIRECTORY_SEPARATOR.'default'.DIRECTORY_SEPARATOR;
                @mkdir($path, 0755, true);
                $this->copyFile($wap_template_path,$path,true);
            }
            $model_config = model('config');
            $update['template_name'] = 'dede';
            $update['style_name'] = 'default';
            $result = $model_config->updateConfig($update);
            dkcache('config');
            $this->log(lang('dede_import'), 1);
            $this->success('导入成功', 'Config/dede_import');
        }
    }
    
    private function copyFile($path,$new_path,$if_mobile) {
        //如果是目录则继续
        if (is_dir($path)) {
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            foreach ($p as $val) {
                //排除目录中的.和..
                if ($val != "." && $val != "..") {
                    //如果是目录则递归子目录，继续操作
                    if (is_dir($path . $val)) {
                        @mkdir($new_path.$val, 0755, true);
                        //子目录中操作删除文件夹和文件
                        $this->copyFile($path.$val.DIRECTORY_SEPARATOR,$new_path.$val.DIRECTORY_SEPARATOR,$if_mobile);
                    } else {
                        if(preg_match('/\.htm$/', $val)){
                            if($if_mobile){
                                $view_path=ROOT_PATH.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'mobile'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'dede'.DIRECTORY_SEPARATOR;
                            }else{
                                $view_path=ROOT_PATH.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'home'.DIRECTORY_SEPARATOR.'view'.DIRECTORY_SEPARATOR.'dede'.DIRECTORY_SEPARATOR;
                            }
                            $file=file_get_contents($path.$val);
                            $file=preg_replace('/\{\s*dede\s*:\s*channel\b/', '{dstaglib:channel', $file);
                            $file=preg_replace('/\{\s*\/\s*dede\s*:\s*channel\s*\}/', '{/dstaglib:channel}', $file);
                            $file=preg_replace('/\{dstaglib:channel\b/', '{dstaglib:channel refObj="isset($item_info)?$item_info:0"', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*arclist\b/', '{dstaglib:arclist', $file);
                            $file=preg_replace('/\{\s*\/\s*dede\s*:\s*arclist\s*\}/', '{/dstaglib:arclist}', $file);
                            $file=preg_replace('/\{dstaglib:arclist\b/', '{dstaglib:arclist refObj="isset($item_info)?$item_info:0"', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*flink\b/', '{dstaglib:flink', $file);
                            $file=preg_replace('/\{\s*\/\s*dede\s*:\s*flink\s*\}/', '{/dstaglib:flink}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*type\b/', '{dstaglib:type', $file);
                            $file=preg_replace('/\{\s*\/\s*dede\s*:\s*type\s*\}/', '{/dstaglib:type}', $file);
                            $file=preg_replace('/\{dstaglib:type\b/', '{dstaglib:type refObj="isset($item_info)?$item_info:0"', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*channelartlist\b/', '{dstaglib:channelartlist', $file);
                            $file=preg_replace('/\{\s*\/\s*dede\s*:\s*channelartlist\s*\}/', '{/dstaglib:channelartlist}', $file);
                            preg_match_all('/\{dstaglib:channelartlist\b([\s\S]+?){\/dstaglib:channelartlist}/',$file, $matches);
                            foreach($matches[0] as $v){
                                $v_new=preg_replace('/\{dstaglib:(\w+)\b/', '{dstaglib:\\1 intag="v"', $v);
                                $v_new=preg_replace('/\{\s*dede\s*:\s*field\.(\w+)\s*\/\s*\}/', '{$v.\\1|default=""}', $v_new);
                                $v_new=preg_replace('/\{\s*dede\s*:\s*field\s+name\s*=\s*(\'|")(\w+)\1\s*\/\s*\}/', '{$v.\\2|default=""}', $v_new);
                                $v_new=str_replace('$item_info', '$v', $v_new);
                                $file= str_replace($v, $v_new, $file);
                            }
                            $file=preg_replace('/\{dstaglib:channelartlist\b/', '{dstaglib:channelartlist item="v" refObj="isset($item_info)?$item_info:0"', $file);
                            $file=preg_replace('/\[\s*field\s*:\s*global\.autoindex\s*\/\s*\]/', '{$autoindex|default=""}', $file);
                            $file=preg_replace('/\[\s*field\s*:\s*(\w+)\s*\/\s*\]/', '{$field.\\1|default=""}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*field\.(\w+)\s*\/\s*\}/', '{$item_info.\\1|default=""}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*field\s+name\s*=\s*(\'|")(\w+)\1\s*\/\s*\}/', '{$item_info.\\2|default=""}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*field\.(\w+)\s+function\s*=\s*(\'|")html2text\(\s*@me\s*\)\2\s*\/\s*\}/', '{:html2text($item_info.\\1)}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*field\.(\w+)\s+function\s*=\s*(\'|")MyDate\(\s*(\'|")([^\'"]+)\3\s*,\s*@me\s*\)\2\s*\/\s*\}/', '{$item_info.\\1|date=\\3\\4\\3}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*field\.(\w+)\s+function\s*=\s*(\'|")cn_substr\(\s*@me\s*,\s*(\d+)\s*\)\2\s*\/\s*\}/', '{:ds_substing($item_info.\\1,0,\\3)}', $file);
                            $file=preg_replace('/\[\s*field\s*:\s*(\w+)\s+function\s*=\s*(\'|")html2text\(\s*@me\s*\)\2\s*\/\s*\]/', '{:html2text($field.\\1)}', $file);
                            $file=preg_replace('/\[\s*field\s*:\s*(\w+)\s+function\s*=\s*(\'|")MyDate\(\s*(\'|")([^\'"]+)\3\s*,\s*@me\s*\)\2\s*\/\s*\]/', '{$field.\\1|date=\\3\\4\\3}', $file);
                            $file=preg_replace('/\[\s*field\s*:\s*(\w+)\s+function\s*=\s*(\'|")cn_substr\(\s*@me\s*,\s*(\d+)\s*\)\2\s*\/\s*\]/', '{:ds_substing($field.\\1,0,\\3)}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*global\.cfg_webname\s*\/\s*\}/', '{$Think.config.ds_config.site_name}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*global\.cfg_tel\s*\/\s*\}/', '{$Think.config.ds_config.site_tel400}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*global\.cfg_keywords\s*\/\s*\}/', '{$Think.config.ds_config.seo_home_keywords}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*global\.cfg_description\s*\/\s*\}/', '{$Think.config.ds_config.seo_home_description}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*global\.cfg_email\s*\/\s*\}/', '{$Think.config.ds_config.site_email}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*global\.cfg_beian\s*\/\s*\}/', '{$Think.config.ds_config.icp_number}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*global\.cfg_templets_skin\s*\/\s*\}/', ($if_mobile?'{$Think.MOBILE_SITE_ROOT}':'{$Think.HOME_SITE_ROOT}').'/{$template_theme}/styles/{$style_theme}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*include\s+filename\s*=\s*(\'|")([^\'"]+)\.htm\1\s*\/\s*\}/', '{include file=\\1dede/\\2\\1}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*pagelist\b.+?\s*\/\s*\}/', '{$show_page|raw}', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*prenext\b/', '{dstaglib:prenext refObj="isset($item_info)?$item_info:0"', $file);
                            $file=preg_replace('/\{\s*dede\s*:\s*list\b.+?\}/', '{foreach name="item_list" item="field"}', $file);
                            $file=preg_replace('/\{\s*\/\s*dede\s*:\s*list\s*\}/', '{/foreach}', $file);
                            $file=preg_replace('/\{\$item_info\.body\b.*?\}/', '{$item_info.body|raw}', $file);
                            $file=preg_replace('/\{\$item_info\.content\b.*?\}/', '{$item_info.content|raw}', $file);
                            $file=preg_replace('/\{\$field\.link\b.*?\}/', '{$field.link|raw}', $file);
                            file_put_contents($view_path.$val.'l', $file);
                        }else{
                            copy($path.$val, $new_path.$val);
                        }
                    }
                }
            }
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'base',
                'text' => lang('site_set'),
                'url' => url('admin/Config/index')
            ),
            array(
                'name' => 'dump',
                'text' => lang('anti_irrigation_set'),
                'url' => url('admin/Config/dump')
            ),
            array(
                'name' => 'seo',
                'text' => lang('seo_set'),
                'url' => url('admin/Config/seo')
            ),
            array(
                'name' => 'dede_import',
                'text' => lang('dede_import'),
                'url' => url('admin/Config/dede_import')
            ),
        );
        return $menu_array;
    }

}
