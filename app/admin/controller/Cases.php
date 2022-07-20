<?php

namespace app\admin\controller;
use think\facade\View;


use think\facade\Lang;

class Cases extends AdminControl
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/cases.lang.php');
    }

    /**
     * 案例列表
     * @return type
     */
    public function index()
    {
        $model_cases = model('cases');
        $condition = array();
        $cases_list = $model_cases->getCasesList($condition,'*',5);

        View::assign('show_page', $model_cases->page_info->render());
        View::assign('cases_list', $cases_list);
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 添加案例
     * @return type
     */
    public function add()
    {
        if (request()->isPost()) {
            $column_id = intval(input('post.column_id'));
            if ($column_id <= 0) {
                $this->error('必须选择栏目');
            }
            $data = array(
                'cases_title' => input('post.cases_title'),
                'seo_title' => input('post.seo_title'),
                'seo_keywords' => input('post.seo_keywords'),
                'seo_description' => input('post.seo_description'),
                'cases_content' => input('post.cases_content'),
                'cases_order' => input('post.cases_order'),
                'cases_wap_ok' => input('post.cases_wap_ok') ? 1 : 0,
                'cases_displaytype' => input('post.cases_displaytype') ? 1 : 0,
                'cases_issue' => $this->admin_info['admin_name'],
                'cases_recycle' => 0,
                'column_id' => $column_id,
            );
            if (!input('param.cases_addtime')) {
                $data['cases_addtime'] = TIMESTAMP;
            } else {
                $data['cases_addtime'] = strtotime(input('param.cases_addtime'));
            }

            //上传文件保存路径
            $upload_file = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_CASES;
            if (!empty($_FILES['cases_imgurl']['name'])) {
                $file = request()->file('cases_imgurl');
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
                    $data['cases_imgurl'] = $file_name;
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $cases_validate = ds_validate('cases');
            if (!$cases_validate->scene('add')->check($data)){
                $this->error($cases_validate->getError());
            }
            $result = model('cases')->addCases($data);
            if ($result){
                $this->success(lang('add_succ'), url('cases/index'));
            }else{
                $this->error(lang('add_fail'));
            }
        } else {
            $cases = array(
                'cases_show' => 1,
                'cases_addtime' => TIMESTAMP,
                'cases_displaytype' => 1,
                'cases_wap_ok' => 1,
                'column_id' => 0,
            );
            $column_list = model('column')->getColumnList([['column_module','=',COLUMN_CASES]]);
            $pic_list = model('pic')->getPicList(array(array('pic_id' ,'=', 0)));
            View::assign('cases', $cases);
            View::assign('cases_pic_type', ['pic_type' => 'cases']);
            View::assign('pic_list', $pic_list);
            View::assign('column_list', $column_list);
            $this->setAdminCurItem('add');
            return View::fetch('form');
        }
    }

    /**
     * 编辑案例
     * @return type
     */
    public function edit()
    {
        $cases_id = intval(input('param.cases_id'));
        if ($cases_id <= 0) {
            $this->error('系统错误');
        }
        $cases = model('cases')->getOneCases([['cases_id' ,'=', $cases_id]]);
        if(empty($cases)){
            $this->error('系统错误');
        }
        if (request()->isPost()) {
            $data = array(
                'cases_title' => input('post.cases_title'),
                'seo_title' => input('post.seo_title'),
                'seo_keywords' => input('post.seo_keywords'),
                'seo_description' => input('post.seo_description'),
                'cases_content' => input('post.cases_content'),
                'cases_order' => input('post.cases_order'),
                'cases_issue' => $this->admin_info['admin_name'],
                'column_id' => input('post.column_id'),
            );
            if (!input('param.cases_updatetime')) {
                $data['cases_updatetime'] = TIMESTAMP;
            } else {
                $data['cases_updatetime'] = strtotime(input('param.cases_updatetime'));
            }

            if (!empty($_FILES['cases_imgurl']['name'])) {
                //上传文件保存路径
                $upload_file = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_CASES;
                $file = request()->file('cases_imgurl');
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
                    $cases_img_ori = $cases['cases_imgurl'];
                    if ($cases_img_ori) {
                        @unlink($upload_file . DIRECTORY_SEPARATOR . $cases_img_ori);
                    }
                    $data['cases_imgurl'] = $file_name;
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            $cases_validate = ds_validate('cases');
            if (!$cases_validate->scene('edit')->check($data)){
                $this->error($cases_validate->getError());
            }
            $result = model('cases')->editCases([['cases_id' ,'=', $cases_id]], $data);
            if ($result >= 0) {
                $this->success(lang('edit_succ'), 'cases/index');
            } else {
                $this->error(lang('edit_fail'));
            }
        } else {
            $condition[]=array('pic_type','=','cases');
            $condition[]=array('pic_type_id','=',$cases_id);
            $pic_list = model('pic')->getpicList($condition);
            View::assign('pic_list', $pic_list);

            //获取当前帮助中心的内容
            $column_list = model('column')->getColumnList([['column_module','=',COLUMN_CASES]]);
            View::assign('cases_pic_type', ['pic_type' => 'cases']);
            View::assign('cases', $cases);
            View::assign('column_list', $column_list);
            $this->setAdminCurItem('edit');
            return View::fetch('form');
        }
    }

    /**
     * 删除案例
     */
    function del()
    {
        $cases_id = intval(input('param.cases_id'));
        if ($cases_id) {
            $condition[]=array('cases_id','=',$cases_id);
            $result = model('cases')->delCases($condition);
            model('pic')->delPic([['pic_type_id','=',$cases_id],['pic_type','=','cases']],'cases');
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
     * ajax操作
     */
    function ajax()
    {
        $branch = input('param.branch');
        switch ($branch) {
            case 'cases':
                $cases_mod = model('cases');
                $condition=array();
                $condition = array('cases_id' ,'=', intval(input('param.id')));
                $update[input('param.column')] = input('param.value');
                $cases_mod->editcases($condition, $update);
                echo 'true';
        }
    }

    /**
     * 设置案例
     */
    function setcases()
    {
        $cases_type = input('param.cases_type');
        $cases_id = input('param.cases_id');
        $res = model('cases')->getOneCases([['cases_id' ,'=', $cases_id]], $cases_type);
        $id = $res[$cases_type] == 0 ? 1 : 0;
        $update[$cases_type] = $id;
        $condition[]=array('cases_id','=',$cases_id);
        if (model('cases')->editCases($condition, $update)) {
            ds_json_encode(10000, lang('edit_succ'));
        } else {
            $this->error(lang('edit_fail'));
        }
    }

    /**
     * 获取栏目列表,针对控制器下的栏目
     * @return string
     */
    protected function getAdminItemList()
    {
        $menu_array = array(
            array(
                'name' => 'index', 'text' => '管理', 'url' => url('Cases/index')
            ), array(
                'name' => 'add', 'text' => '新增', 'url' => url('Cases/add')
            ),
        );
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit', 'text' => '编辑', 'url' => url('Cases/edit')
            );
        }
        return $menu_array;
    }
}