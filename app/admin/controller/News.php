<?php

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;

use think\facade\Lang;
use think\Validate;

class News extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/news.lang.php');
    }

    /**
     * 新闻管理
     * @return type
     */
    public function index() {
        $model_news = model('news');
        $condition = array();
        $news_list = $model_news->getNewsList($condition,'', '*', 10);
        View::assign('news_list', $news_list);
        View::assign('show_page', $model_news->page_info->render());

        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 新增新闻
     * @return type
     */
    public function add() {
        if (request()->isPost()) {
            $column_id = intval(input('post.column_id'));
            if ($column_id <= 0) {
                $this->error('必须选择栏目');
            }
            $model_news = model('news');
            $data = array(
                'column_id' => $column_id,
                'news_title' => input('post.news_title'),
                'news_order' => input('post.news_order') ? 1 : 0,
                'news_wap_ok' => input('post.news_wap_ok') ? 1 : 0,
                'news_displaytype' => input('post.news_displaytype'),
                'news_content' => input('post.news_content'),
                'news_addtime' => TIMESTAMP,
                'news_recycle' => NEWS_RECYCLE_OK,
                'news_issue' => $this->admin_info['admin_name'],
                'seo_title' => input('post.seo_title'),
                'seo_keywords' => input('post.seo_keywords'),
                'seo_description' => input('post.seo_description'),
            );
            //新闻主图处理 上传文件保存路径
            $upload_file = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_NEWS;
            if (!empty($_FILES['news_imgurl']['name'])) {
                $file = request()->file('news_imgurl');
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
                    $file_name = \think\facade\Filesystem::putFile('', $file, 'uniqid');
                    $data['news_imgurl'] = $file_name;
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $new_validate = ds_validate('news');
            if (!$new_validate->scene('add')->check($data)){
                $this->error($new_validate->getError());
            }
            $result = $model_news->addNews($data);
            if ($result) {
                $this->success(lang('add_succ'), url('News/index'));
            } else {
                $this->error(lang('add_fail'));
            }
        } else {
            $news = array(
                'news_wap_ok' => 0,
                'news_displaytype' => 0,
                'column_id' => 0,
            );
            $contion=array();
            $contion[] = array('column_module' ,'=', COLUMN_NEWS);
            $column_list = Db::name('column')->where($contion)->select()->toArray();
            
            $pic_list = model('pic')->getPicList(array(array('pic_id' ,'=', 0),array('pic_type','=','news')));
            View::assign('news_pic_type', ['pic_type' => 'news']);
            View::assign('pic_list', $pic_list);
            
            View::assign('column_list', $column_list);
            View::assign('news', $news);
            $this->setAdminCurItem('add');
            return View::fetch('form');
        }
    }

    /**
     * 编辑新闻
     * @return type
     */
    public function edit() {
        $model_news = model('news');
        $news_id = intval(input('param.news_id'));
        if ($news_id <= 0) {
            $this->error(lang('param_error'));
        }
        $condition[]=array('news_id','=',$news_id);
        $news = $model_news->getOneNews($condition);
        if(empty($news)){
            $this->error('系统错误');
        }
        if (request()->isPost()) {
            $data = array(
                'column_id' => input('post.column_id'),
                'news_title' => input('post.news_title'),
                'seo_title' => input('post.seo_title'),
                'seo_keywords' => input('post.seo_keywords'),
                'news_order' => input('post.news_order'),
                'news_wap_ok' => input('post.news_wap_ok') ? 1 : 0,
                'news_displaytype' => input('post.news_displaytype') ? 1 : 0,
                'seo_description' => input('post.seo_description'),
                'news_content' => input('post.news_content'),
                'news_updatetime' => TIMESTAMP,
                'news_issue' => $this->admin_info['admin_name']
            );
            if (!empty($_FILES['news_imgurl']['name'])) {
                //上传文件保存路径
                $upload_file = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_NEWS;
                $file = request()->file('news_imgurl');
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
                    $file_name = \think\facade\Filesystem::putFile('', $file, 'uniqid');
                    //还需删除原来图片
                    $news_img_ori = input('param.news_img_ori');
                    if ($news_img_ori) {
                        @unlink($upload_file . DIRECTORY_SEPARATOR . $news_img_ori);
                    }
                    $data['news_imgurl'] = $file_name;
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $new_validate = ds_validate('news');
            if (!$new_validate->scene('edit')->check($data)){
                $this->error($new_validate->getError());
            }
            $result = $model_news->editNews($condition, $data);
            if ($result) {
                $this->success(lang('edit_succ'), url('News/index'));
            } else {
                $this->error(lang('edit_fail'));
            }
        } else {
            $pic_list = model('pic')->getPicList(array(array('pic_type_id' ,'=', $news_id),array('pic_type','=','news')));
            View::assign('news_pic_type', ['pic_type' => 'news']);
            View::assign('pic_list', $pic_list);
            
            $contion=array();
            $contion[] = array('column_module' ,'=', COLUMN_NEWS);
            $column_list = Db::name('column')->where($contion)->select()->toArray();
            
            
            View::assign('column_list', $column_list);
            View::assign('news', $news);
            $this->setAdminCurItem('edit');
            return View::fetch('form');
        }
    }

    /**
     * 删除新闻
     */
    public function del() {
        $news_id = intval(input('param.news_id'));
        if ($news_id > 0) {
            $condition[]=array('news_id','=',$news_id);
            $result = model('news')->delNews($condition);
            if ($result) {
                ds_json_encode(10000, lang('del_succ'));
            } else {
                ds_json_encode(10001, lang('del_fail'));
            }
        } else {
            ds_json_encode(10001, lang('param_error'));
        }
    }

    /**
     * zjax操作
     */
    function ajax() {
        $branch = input('param.branch');
        switch ($branch) {
            case 'news':
                $news_mod = model('news');
                $condition=array();
                $condition[] = array('news_id' ,'=', intval(input('param.id')));
                $update[input('param.column')] = input('param.value');
                $news_mod->editnews($condition, $update);
                echo 'true';
        }
    }

    /**
     * 设置新闻
     */
    function setnews() {
        $news_type = input('param.news_type');
        $news_id = input('param.news_id');
        $res = model('news')->getOneNews([['news_id' ,'=', $news_id]], $news_type);
        $id = $res[$news_type] == 0 ? 1 : 0;
        $update[$news_type] = $id;
        $condition[]=array('news_id','=',$news_id);
        if (model('news')->editnews($condition, $update)) {
            ds_json_encode(10000, lang('edit_succ'));
        } else {
            $this->error(lang('edit_fail'));
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     * @return string
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'index', 'text' => lang('ds_manage'), 'url' => url('News/index')
            ), array(
                'name' => 'add', 'text' => lang('ds_add'), 'url' => url('News/add')
            ),
        );
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit', 'text' => lang('ds_edit'), 'url' => url('News/edit')
            );
        }
        return $menu_array;
    }

}
