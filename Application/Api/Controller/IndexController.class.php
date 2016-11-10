<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class IndexController
 * @package Api\Controller
 * 首页信息
 */
class IndexController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 选择城市
     * 传递参数的方式：post
     * 无参数
     */
    public function cityList (){
        D('Index','Logic')->cityList(I('post.'));
    }
    /**
     * 首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID   m_id
     * 城市ID   region_id
     */
    public function index (){
        D('Index','Logic')->index(I('post.'));
    }
    /**
     * 商品分类页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     */
    public function goodsType(){
        D('Index','Logic')->goodsType(I('post.'));
    }
    /**
     * @param array $request
     * 商家列表
     * 搜索关键字  keywords
     * 用户ID      m_id
     */
    public function merchantList(){
        D('Index','Logic')->merchantList(I('post.'));
    }
    /**
     * 商品列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language cn中文，ue 英文
     * 港口id：haven_id
     * 商品分类id：g_t_id
     * 类型：type:1商品，2服务
     * 用户id：m_id
     * 关键词：keywords
     * 综合排序：complex_order:1是，2否
     * 销量排序：sales_order:1升序，2降序
     * 价格排序：price_order:1升序，2降序
     * 价格下限:price_lower
     * 价格上限：price_Upper
     */
    public function goodsList(){
        D('Index','Logic')->goodsList(I('post.'));
    }
    /**
     * 商品总分类
     */
    public function classification(){
        D('Index','Logic')->classification(I('post.'));
    }
    /**
     * 商品总分类
     */
    public function changeClassification(){
        D('Index','Logic')->changeClassification(I('post.'));
    }
    /**
     * 商品列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language cn中文，ue 英文
     * 港口id：haven_id
     * 商品分类id：g_t_id
     * 类型：type:1商品，2服务
     * 用户id：m_id
     * 关键词：keywords
     * 综合排序：complex_order:1是，2否
     * 销量排序：sales_order:1升序，2降序
     * 价格排序：price_order:1升序，2降序
     * 价格下限:price_lower
     * 价格上限：price_Upper
     */
    public function newGoodsList(){
        D('Index','Logic')->newGoodsList(I('post.'));
    }
}