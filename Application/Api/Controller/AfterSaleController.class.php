<?php

namespace Api\Controller;

/**
 * 售后处理
 * Class AfterSaleController
 * @package Api\Controller
 */
class AfterSaleController extends BaseController
{
    /**
     * 用户: 申请售后
     */
    public function afterSaleOne()
    {
        D(CONTROLLER_NAME,'Logic')->afterSaleOne(I('post.'));
    }


    /**
     * 商家:同意
     */
    public function afterSaleTwo()
    {
        D(CONTROLLER_NAME,'Logic')->afterSaleTwo(I('post.'));
    }


    /**
     * 商家:填写地址
     */
    public function afterSaleThree()
    {
        D(CONTROLLER_NAME,'Logic')->afterSaleThree(I('post.'));
    }


    /**
     * 用户:填写快递
     */
    public function afterSaleFour()
    {
        D(CONTROLLER_NAME,'Logic')->afterSaleFour(I('post.'));
    }


    /**
     * 商家:确认收货
     */
    public function afterSaleFive()
    {
        D(CONTROLLER_NAME,'Logic')->afterSaleFive(I('post.'));
    }

    /**
     * 用户: 获得售后原因
     */
    public function getSaleCause()
    {
        D(CONTROLLER_NAME,'Logic')->getSaleCause(I('post.'));
    }

    /**
     * 商家: 退换货记录
     */
    public function merSaleLog()
    {
        D(CONTROLLER_NAME,'Logic')->merSaleLog(I('post.'));
    }

    /**
     * 用户: 退换货记录
     */
    public function memSaleLog()
    {
        D(CONTROLLER_NAME,'Logic')->memSaleLog(I('post.'));
    }
}