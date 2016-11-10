<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class ProductController
 * @package Api\Controller
 * 首页信息
 */
class ProductController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 商品详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     */
    public function productDetails (){
        D('Product','Logic')->productDetails(I('post.'));
    }

}