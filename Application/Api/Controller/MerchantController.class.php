<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MerchantController
 * @package Api\Controller
 * 首页信息
 */
class MerchantController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }
    /* 是否是商家
     * 返回0 1 2
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 用户ID：m_id
     */
    public function merchantIndex(){
        D('Merchant','Logic')->merchantIndex(I('post.'));
    }
    /* 商家物品列表
     * 根据上下架0 1 2
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 用户ID：merchant_id
     * 查询产品 goods_name
     * 页数 t
     */
    public function goodsList(){
        D('Merchant','Logic')->goodsList(I('post.'));
    }
    /* 商品上下架
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 商品ID：goods_id
     * 类型：type=1上架 ，type=2 下架
     */
    public function frameList(){
        D('Merchant','Logic')->frameList(I('post.'));
    }
    /* 商家首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 用户ID   m_id  可以为空
     * 商家ID   merchant_id
     */
    public function merchantHome(){
        D('Merchant','Logic')->merchantHome(I('post.'));
    }
}