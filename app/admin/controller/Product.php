<?php

namespace app\admin\controller;
use think\facade\View;
use think\facade\Db;

use think\facade\Lang;
use think\Validate;

class Product extends AdminControl
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/' . config('lang.default_lang') . '/product.lang.php');
    }

    /**
     * 产品管理
     * @return type
     */
    public function index()
    {
        $model_product = model('product');
        $condition = array();
        $product_list = $model_product->getProductList($condition,'*',5);

        View::assign('show_page', $model_product->page_info->render());
        View::assign('product_list', $product_list);
        $this->setAdminCurItem('index');
        return View::fetch();
    }

    /**
     * 新增产品
     * @return type
     */
    public function add()
    {
        if (request()->isPost()) {
            $column_id = intval(input('post.column_id'));
            $data = array(
                'product_title' => input('post.product_title'),
                'seo_title' => input('post.seo_title'),
                'seo_keywords' => input('post.seo_keywords'),
                'seo_description' => input('post.seo_description'),
                'product_content' => input('post.product_content'),
                'product_order' => input('post.product_order'),
                'product_wap_ok' => input('post.product_wap_ok') ? 1 : 0,
                'product_displaytype' => input('post.product_displaytype') ? 1 : 0,
                'product_issue' => $this->admin_info['admin_name'],
                'product_recycle' => PRODUCT_RECYCLE_OK,
                'column_id' => $column_id,
                'column_id2' => intval(input('post.column_id2')),
            );
            if (!input('param.product_addtime')) {
                $data['product_addtime'] = TIMESTAMP;
            } else {
                $data['product_addtime'] = strtotime(input('param.product_addtime'));
            }

            //上传文件保存路径
            $upload_file = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_PRODUCT;
            if (!empty($_FILES['product_img']['name'])) {
                $file = request()->file('product_img');
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
                    $data['product_imgurl'] = $file_name;
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
            }
            //验证器
            $product_validate = ds_validate('product');

            if (!$product_validate->scene('add')->check($data)){
                $this->error($product_validate->getError());
            }
            $result = model('product')->addproduct($data);
            if ($result){
                $this->success(lang('add_succ'), url('product/index'));
            }
            $this->error(lang('add_fail'));
        } else {
            $product = array(
                'product_show' => 1,
                'product_addtime' => TIMESTAMP,
                'product_displaytype' => 1,
                'product_wap_ok' => 1,
                'column_id' => 0,
                'column_id2' => 0,
            );
            $contion=array();
            $contion[] = array(['column_module' ,'=', COLUMN_PRODUCT], ['parent_id', '=', 0]);
            $column_list = Db::name('column')->where($contion)->order('column_id' ,'asc')->select();
            $column_list_array = array();
            foreach($column_list as $k=>$v){
                $column_list_child = Db::name('column')->where('parent_id', '=', $v['column_id'])->order('column_id' ,'asc')->column('column_id,column_name');
                $column_list_array[] = [$v['column_id'],$v['column_name'],$column_list_child];
            }
            $pic_list = model('pic')->getPicList(array(array('pic_id' ,'=', 0)));
            View::assign('product', $product);
            View::assign('product_pic_type', ['pic_type' => 'product']);
            View::assign('pic_list', $pic_list);
            View::assign('column_list', json_encode($column_list_array, JSON_UNESCAPED_UNICODE));
            $this->setAdminCurItem('add');
            return View::fetch('form');
        }
    }

    /**
     * 编辑产品
     * @return type
     */
    public function edit()
    {
        $product_id = intval(input('param.product_id'));
        if ($product_id <= 0) {
            $this->error('系统错误');
        }
        $product = model('product')->getOneProduct([['product_id' ,'=', $product_id]]);
        if(empty($product)){
            $this->error('系统错误');
        }
        if (request()->isPost()) {
            $data = array(
                'product_title' => input('post.product_title'),
                'seo_title' => input('post.seo_title'),
                'seo_keywords' => input('post.seo_keywords'),
                'seo_description' => input('post.seo_description'),
                'product_content' => input('post.product_content'),
                'product_order' => input('post.product_order'),
                'product_imgurl' => input('post.product_imgurl'),
                'product_issue' => $this->admin_info['admin_name'],
                'column_id' =>  input('post.column_id'),
                'column_id2' =>  input('post.column_id2'),
            );
            if (!input('param.product_updatetime')) {
                $data['product_updatetime'] = TIMESTAMP;
            } else {
                $data['product_updatetime'] = strtotime(input('param.product_updatetime'));
            }


            if (!empty($_FILES['product_img']['name'])) {
                //上传文件保存路径
                $upload_file = BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_PRODUCT;
                $file = request()->file('product_img');
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
                    $product_img_ori = $product['product_imgurl'];
                    if ($product_img_ori) {
                        @unlink($upload_file . DIRECTORY_SEPARATOR . $product_img_ori);
                    }
                    $data['product_imgurl'] = $file_name;
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                } 
            }
            //验证器
            $product_validate = ds_validate('product');

            if (!$product_validate->scene('edit')->check($data)){
                $this->error($product_validate->getError());
            }
            $result = model('product')->editproduct([['product_id' ,'=', $product_id]], $data);
            if ($result >= 0) {
                $this->success(lang('edit_succ'), 'product/index');
            } else {
                $this->error(lang('edit_fail'));
            }
        } else {
            $condition[]=array('pic_type','=','product');
            $condition[]=array('pic_type_id','=',$product_id);
            $pic_list = model('pic')->getpicList($condition);
            View::assign('pic_list', $pic_list);

            //获取当前帮助中心的内容
            $contion=array();
            $contion[] = array(['column_module' ,'=', COLUMN_PRODUCT], ['parent_id', '=', 0]);
            $column_list = Db::name('column')->where($contion)->order('column_id' ,'asc')->select();
            $column_list_array = array();
            foreach($column_list as $k=>$v){
                $column_list_child = Db::name('column')->where('parent_id', '=', $v['column_id'])->order('column_id' ,'asc')->column('column_id,column_name');
                $column_list_array[] = [$v['column_id'],$v['column_name'],$column_list_child];
            }
            View::assign('column_list', json_encode($column_list_array, JSON_UNESCAPED_UNICODE));
            View::assign('product_pic_type', ['pic_type' => 'product']);
            View::assign('product', $product);
            $this->setAdminCurItem('edit');
            return View::fetch('form');
        }
    }

    /**
     * 删除产品
     */
    function del()
    {
        $product_id = intval(input('param.product_id'));
        if ($product_id) {
            $condition[]=array('product_id','=',$product_id);
            $result = model('product')->delproduct($condition,false);
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
            case 'product':
                $product_mod = model('product');
                $condition=array();
                $condition[] = array('product_id' ,'=', intval(input('param.id')));
                $update[input('param.column')] = input('param.value');
                $product_mod->editproduct($condition, $update);
                echo 'true';
        }
    }

    /**
     * 设置产品
     */
    function setproduct()
    {
        $product_type = input('param.product_type');
        $product_id = input('param.product_id');
        $res = model('product')->getOneProduct([['product_id' ,'=', $product_id]], $product_type);
        $id = $res[$product_type] == 0 ? 1 : 0;
        $update[$product_type] = $id;
        $condition[]=array('product_id','=',$product_id);
        if (model('product')->editproduct($condition, $update)) {
            ds_json_encode(10000, lang('edit_succ'));
        } else {
            $this->error(lang('edit_fail'));
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     * @return string
     */
    protected function getAdminItemList()
    {
        $menu_array = array(
            array(
                'name' => 'index', 'text' => '管理', 'url' => url('product/index')
            ), array(
                'name' => 'add', 'text' => '新增', 'url' => url('product/add')
            ),
        );
        if (request()->action() == 'edit') {
            $menu_array[] = array(
                'name' => 'edit', 'text' => '编辑', 'url' => url('product/edit')
            );
        }
        return $menu_array;
    }
}