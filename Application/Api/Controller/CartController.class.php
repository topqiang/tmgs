<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class CartController
 * @package Api\Controller
 * 首页信息
 */
class CartController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 购物车首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID：m_id
     */
    public function cartList(){
        D('Cart','Logic') -> cartList(I('post.'));
    }
    /**
     * 购物车删除
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 购物车ID  cart_id  购物车ID json串：[{"cart_id":"1"},{"cart_id":"2"}]
     * 用户ID：m_id
     */
    public function cartDelete(){
        D('Cart','Logic') -> cartDelete(I('post.'));
    }
    /**
     * 购物车移入收藏
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID   m_id
     * 购物车ID json串：[{"cart_id":"1"},{"cart_id":"2"}]
     */
    public function cartCollect(){
        D('Cart','Logic') -> cartCollect(I('post.'));
    }
    /**
     * 加入购物车
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 商品ID    goods_id
     * 商品数量  num
     * 商品属性  attr_con_json    json串：[{"attr_con_id":"4"},{"attr_con_id":"10"}]
     */
    public function  shoppingCart(){
        D('Cart','Logic')->shoppingCart(I('post.'));
    }
    /**
     * 购物车编辑
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID      m_id
     * 购物车属性  cart_json    json串：[{"cart_id":"4","num":"5"},{"cart_id":"10","num","num":"6"}]
     */
    public function  modifyCart(){
        D('Cart','Logic')->modifyCart(I('post.'));
    }
}