<?php

namespace app\home\controller;
use think\facade\View;
use think\facade\Lang;
class Product extends BaseMall
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/product.lang.php');
    }

    /**
     * 产品信息 - 产品栏目
     * @return mixed
     */
    public function search()
    {
        $condition = array();
        $where = array();
        $product_model = model('product');
        $column_model = model('column');
        $productcolumn_id = intval(input('param.id'));
        if ($productcolumn_id > 0) {
            $condition[]=array('column_id','in',$column_model->getColumnSonIds($productcolumn_id));
        }
        $key = 'product_list_' . $productcolumn_id . '_' . input('param.page');
        $product = rcache($key);
        if (empty($product)) {
            $condition[]=array('product_displaytype','=',1);
            $where[]=array('column_module','=',COLUMN_PRODUCT);
            $product['product_list'] = $product_model->getProductList($condition, '*', 6);
            $product['page'] = $product_model->page_info->render();
            //获取所有分类
            $product['product_column_list'] = $column_model->getColumnList($where);
            //当前分类
            $product['product_column'] = array();
            if($productcolumn_id>0){
                $product['product_column'] = $column_model->getOneColumn($productcolumn_id);
                $product['product_column']=$column_model->dedeMerge($product['product_column']);
            }
            foreach ($product['product_list'] as $k => $v) {
                $product['product_list'][$k] = $product_model->dedeMerge($v);
            }
            wcache($key, $product, '', 36000);
        }
        View::assign('item_info', $product['product_column']);
        View::assign('item_list', $product['product_list']);
        View::assign('show_page', $product['page']);
        View::assign('product_column_list', $product['product_column_list']);
        
        //面包屑导航
        View::assign('ancestor', $this->get_ancestor($productcolumn_id));
        
        
        //SEO
        if (!empty($product['product_column'])) {
            $seo = array(
                'seo_title' => !empty($product['product_column']['seo_title']) ? $product['product_column']['seo_title'] : $product['product_column']['column_name'],
                'seo_keywords' => !empty($product['product_column']['seo_keywords']) ? $product['product_column']['seo_keywords'] : $product['product_column']['column_name'],
                'seo_description' => !empty($product['product_column']['seo_description']) ? $product['product_column']['seo_description'] : '',
            );
        }else{
            $seo = array(
                'seo_title' => '公司产品',
            );
        }
        $this->_assign_seo($seo);
        return View::fetch($this->template_dir . (config('ds_config.template_name')=='dede'?$product['product_column']['column_temp_list']:'search'));
    }

    /**
     * 产品详情页
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
        
        //面包屑导航
        View::assign('ancestor', $this->get_ancestor($product_info['column_id']));
        
        //SEO赋值
        $seo = array(
            'seo_title'=> !empty($product_info['seo_title'])?$product_info['seo_title']:$product_info['product_title'],
            'seo_keywords'=> !empty($product_info['seo_keywords'])?$product_info['seo_keywords']:$product_info['product_title'],
            'seo_description'=> !empty($product_info['seo_description'])?$product_info['seo_description']:ds_substing(htmlspecialchars_decode($product_info['product_content']), 0, 80),
        );
        $this->_assign_seo($seo);
        return View::fetch($this->template_dir . (config('ds_config.template_name')=='dede'?$column_info['column_temp_article']:'detail'));
    }
}