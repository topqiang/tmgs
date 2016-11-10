<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class BaseController
 * @package Api\Controller
 * 基类
 */
class BaseController extends Controller{
    /**
     * 初始化
     */
    public function _initialize(){
    }

    /**
     * 空方法操作
     */
    public function _empty(){
        apiResponse('error','Request path error');
    }

}
