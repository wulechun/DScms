<?php

namespace app\home\controller;
use think\facade\View;
use think\facade\Lang;
class Link extends BaseMall
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/link.lang.php');
    }

    public function search()
    {
        return View::fetch($this->template_dir.'search');
    }
}