<?php

namespace app\mobile\controller;
use think\facade\View;

class Product extends BaseMall
{
    public function initialize()
    {
        parent::initialize();
    }
    /**
     * 案例信息 - 案例栏目
     * @return mixed
     */
    public function search()
    {
        $product_model = model('product');
        $column_model = model('column');
        $condition = array();
        $where = array();
        $productcolumn_id = intval(input('param.id'));
        if ($productcolumn_id > 0) {
            $condition[]=array('column_id','in',$column_model->getColumnSonIds($productcolumn_id));
            $column_info = $column_model->getOneColumn($productcolumn_id);
            View::assign('item_info', $column_model->dedeMerge($column_info));
        }
        $key = 'product_list_' . $productcolumn_id . '_' . input('param.page');
        $product = rcache($key);
        if (!empty($product)) {
            $condition[]=array('product_displaytype','=',1);
            $where[]=array('column_module','=',COLUMN_PRODUCT);
            $product['product_list'] = $product_model->getProductList($condition, '*', 6);
            $product['page'] = $product_model->page_info->render();
            $product['product_column_list'] = $column_model->getColumnList($where);
            foreach ($product['product_list'] as $k => $v) {
                $product['product_list'][$k] = $product_model->dedeMerge($v);
            }
            wcache($key, $product, '', 36000);
        }
        View::assign('item_list', $product['product_list']);
        View::assign('show_page', $product['page']);
        View::assign('product_column', $product['product_column_list']);
        return View::fetch($this->template_dir . (config('ds_config.template_name')=='dede'?$column_info['column_temp_list']:'search'));
    }

    /**
     * 案例详情页
     * @return mixed
     */
    public function detail()
    {
        $condition = array();
        $where = array();
        $product_id = intval(input('param.product_id'));
        if ($product_id <= 0) {
            $this->error('参数错误');
        }
        $product_model = model('product');
        $condition[]=array('product_id','=',$product_id);
        $product_info = $product_model->getOneProduct($condition);

        $column_model=model('column');
        $column_info = $column_model->getOneColumn($product_info['column_id']);
        //获取案例列表
        $key = "productcolumn_list";
        $productcolumn_list = rcache($key);
        if (empty($productcolumn_list)) {
            $where[]=array('column_module','=',COLUMN_PRODUCT);
            $productcolumn_list = $column_model->getColumnList($where);
            wcache($key, $productcolumn_list, '', 36000);
        }
        View::assign('product_column', $productcolumn_list);
        View::assign('item_info', array_merge($column_model->dedeMerge($column_info),$product_model->dedeMerge($product_info)));
        
        return View::fetch($this->template_dir . (config('ds_config.template_name')=='dede'?$column_info['column_temp_article']:'detail'));
    }

}
