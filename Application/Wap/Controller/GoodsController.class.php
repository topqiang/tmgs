<?php
namespace Wap\Controller;

/**
 * Class GoodsController
 * @package Wap\Controller
 * 商品相关
 */
class GoodsController extends BaseController{

    /**
     * 首页
     */
    public function classify()
    {
        $this->display();
    }

    /**
     * 商品列表
     */
    public function goodslist()
    {
        if($_GET['g_t_id'])$this->assign('g_t_id',$_GET['g_t_id']);
        if($_GET['keywords'])$this->assign('keywords',$_GET['keywords']);
        $this->display();
    }

    /**
     * 商品详情
     */
    public function goodsinfo()
    {
        $url = 'http://2.taomim.com/index.php/Api/Goods/newGoodsInfo';
        $param = array('goods_id'=>$_GET['goods_id']);
        $a  = $this->PostUrl($url,$param);
        $json = json_decode($a,true);
        $this->assign('goods_info',$json['data']);
        $this->display();
    }
}