<?php
namespace Wap\Controller;
/**
 * Class OrderController
 * @package Wap\Controller
 * 订单相关
 */
class OrderController extends BaseController
{

    /**
     * 评价列表
     */
    public function goodrate()
    {
        $this->display();
    }

    /**
     * 提交订单
     */
    public function setOrder()
    {
        $this->display();
    }

    /**
     * 订单信息
     */
    public function orderinfo()
    {
        $this->display();
    }

    /**
     *评价商品
     */
    public function ratelist()
    {
        $this->display();
    }

    /**
     * 选择支付方式
     */
    public function paytype()
    {
        $this->display();
    }
}