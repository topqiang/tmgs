<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MerchantOrderController
 * @package Api\Controller
 * 商家订单模块
 */
class MerchantOrderController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 订单列表
     * 商家ID     merchant_id
     * 类别       type 2  待发货  3  待收货  5  订单完成  6  取消订单  8  全部
     * 分页参数   p
     */
    public function orderList(){
        D('MerchantOrder','Logic') ->orderList(I('post.'));
    }

    /**
     * 订单详情
     * 商家ID       merchant_id
     * 订单ID       order_id
     */
    public function orderInformation(){
        D('MerchantOrder','Logic') ->orderInformation(I('post.'));
    }

    /**
     * 配送订单
     * 订单ID     order_id
     * 物流ID     delivery_id
     * 物流单号   delivery_sn
     */
    public function deliveryOrder(){
        D('MerchantOrder','Logic') ->deliveryOrder(I('post.'));
    }

    /**
     * 物流列表
     */
    public function deliveryList(){
        D('MerchantOrder','Logic') ->deliveryList(I('post.'));
    }
    /**
     * 评价列表
     * 商家ID   merchant_id
     * 分页参数    p
     */
    public function evaluateList(){
        D('MerchantOrder','Logic') ->evaluateList(I('post.'));
    }
    /**
     * 售后处理页
     * 商家ID   merchant_id
     */
    public function salesProcessPage(){
        D('MerchantOrder','Logic') ->salesProcessPage(I('post.'));
    }
    /**
     * 售后处理
     * 商家ID   merchant_id
     */
    public function dealProcess(){
        D('MerchantOrder','Logic') ->dealProcess(I('post.'));
    }
    /**
     * 售后处理详情
     * 商家ID   merchant_id
     */
    public function dealProcessInfo(){
        D('MerchantOrder','Logic') ->dealProcessInfo(I('post.'));
    }
    /**
     * 填写收货地址信息
     * 商家ID   merchant_id
     */
    public function merchantAddress(){
        D('MerchantOrder','Logic') ->merchantAddress(I('post.'));
    }

    /*删除订单*/
    public function merchantDelOrder(){
        D('MerchantOrder','Logic') ->merchantDelOrder(I('post.'));
    }
}