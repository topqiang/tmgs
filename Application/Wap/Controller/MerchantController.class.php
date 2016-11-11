<?php
namespace Wap\Controller;

/**
 * Class MerchantController
 * @package Wap\Controller
 * 店铺相关
 */
class MerchantController extends BaseController{

    /**
     * 首页
     */
    public function shoplist()
    {
        $this->display();
    }

    /**
     * 商家详情
     */
    public function shopinfo()
    {   
        $url = U("Api/Merchant/merchantHome");
        $merchant_id = $_GET['merchant_id'];
        $a  = $this->PostUrl($url,$param);
        //重新对接口
        $this->display();
    }
}