<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class CollectController
 * @package Api\Controller
 * 首页信息
 */
class CollectController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 收藏商品列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 分页参数  p
     */
    public function collectGoodsList(){
        D('Collect','Logic')->collectGoodsList(I('post.'));
    }
    /**
     * 已收藏商家
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 分页参数 p
     */
    public function collectMerchantList(){
        D('Collect','Logic')->collectMerchantList(I('post.'));
    }
    /**
     * 取消收藏商品
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 商品ID  goods_id
     */
    public function exitCollectGoods(){
        D('Collect','Logic')->exitCollectGoods(I('post.'));
    }
    /**
     * 取消收藏商家
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 商家ID  merchant_id
     */
    public function exitCollectMerchant(){
        D('Collect','Logic')->exitCollectMerchant(I('post.'));
    }
    /**
     * 收藏商品
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID      m_id
     * 商品ID      goods_id
     */
    public function collectGoods(){
        D('Collect','Logic')->collectGoods(I('post.'));
    }
    /**
     * 收藏商家
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     * 商家id：merchant_id
     */
    public function collectMerchant(){
        D('Collect','Logic')->CollectMerchant(I('post.'));
    }
}