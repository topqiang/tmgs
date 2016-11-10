<?php

namespace Merchant\Controller;


class DemoController extends BaseController {

    
    public function index(){
        
            $this->display('index');
        
    }

    public function form_1() {
        $this->display('form_1');
    }

}
