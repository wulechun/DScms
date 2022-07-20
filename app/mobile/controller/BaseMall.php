<?php

namespace app\mobile\controller;

class BaseMall extends BaseHome
{
    public function initialize()
    {
        parent::initialize();
        if(config('ds_config.template_name')=='dede'){
            $this->template_dir = $this->template_name.'/';
        }else{
            $this->template_dir = $this->template_name.'/mall/'.strtolower(request()->controller()).'/';
        }
    }
}