<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class DiscoverController
 * @package Api\Controller
 * 首页信息
 */
class DiscoverController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 发现首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id    m_id
     */
    public function discoverList(){
        D('Discover','Logic')->discoverList(I('post.'));
    }
    /**
     * 发现好货
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id    可以为空
     * 分页参数  p
     */
    public function discoverGoods(){
        D('Discover','Logic')->discoverGoods(I('post.'));
    }
    /**
     * 发现好店
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID：m_id
     * 分页参数  p
     */
    public function discoverMerchant(){
        D('Discover','Logic')->discoverMerchant(I('post.'));
    }
    /**
     * 发现好服务
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 分页参数  p
     */
    public function discoverService(){
        D('Discover','Logic')->discoverService(I('post.'));
    }
}