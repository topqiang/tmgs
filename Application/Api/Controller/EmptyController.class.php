<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class EmptyController
 * @package Api\Controller
 * 空控制器
 */
class EmptyController extends BaseController{

    /**
     * 空控制器操作方法
     */
    public function index(){
        apiResponse('error','Request path error');
    }
}
