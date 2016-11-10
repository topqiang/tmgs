<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class GoodsController
 * @package Api\Controller
 * 首页信息
 */
class GoodsController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * @param array $request
     * 商品详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     * 商品id：goods_id
     */
    public function goodsInfo(){
        D('Goods','Logic')->goodsInfo(I('post.'));
    }
    /**
     * @param array $request
     * 商家商品列表
     * 商家ID  merchant_id
     */
    public function merGoodsList(){
        D('Goods','Logic') -> merGoodsList(I('post.'));
    }
    /**
     * @param array $request
     * 商家添加商品
     * 商家ID  merchant_id
     */
    public function addGoods(){
        D('Goods','Logic') -> addGoods(I('post.'));
    }
    /**
     * @param array $request
     * 商家修改商品
     * 商家ID  merchant_id
     */
    public function modifyGoods(){
        D('Goods','Logic') -> modifyGoods(I('post.'));
    }
    /**
     * @param array $request
     * 删除商家商品
     * 商家ID  merchant_id
     */
    public function deleteGoods(){
        D('Goods','Logic') -> deleteGoods(I('post.'));
    }
    /**
     * @param array $request
     * 三级分类列表
     */
    public function typeList(){
        D('Goods','Logic') -> typeList(I('post.'));
    }
    /**
     * @param array $request
     * 商品属性列表
     */
    public function goodsAttributeList(){
        D('Goods','Logic') ->goodsAttributeList(I('post.'));
    }
    /**
     * @param array $request
     * 商品上下架操作
     */
    public function goodsFrame(){
        D('Goods','Logic') ->goodsFrame(I('post.'));
    }

    /**
     * @param array $request
     * 新增商品属性组
     */
    public function addAttribute(){
        D('Goods','Logic') ->addAttribute(I('post.'));
    }
    /**
     * @param array $request
     * 新增商品属性值
     */
    public function addAttributeContent(){
        D('Goods','Logic') ->addAttributeContent(I('post.'));
    }

    /**
     * @param array $request
     * 删除商品属性值
     */
    public function deleteAttributeContent(){
        D('Goods','Logic') ->deleteAttributeContent(I('post.'));
    }

    /**
     * @param array $request
     * 删除商品属性名
     */
    public function deleteAttribute(){
        D('Goods','Logic') ->deleteAttribute(I('post.'));
    }

    /**
     * 修改商品详情
     */
    public function alertGoodsFind()
    {
        D('Goods','Logic') ->alertGoodsFind(I('post.'));
    }

    /**
     * 修改商品属性
     */
    public function alertGoodsPro(){
        D('Goods','Logic') ->alertGoodsPro(I('post.'));
    }

    public function newGoodsInfo(){
        D('Goods','Logic') ->newGoodsInfo(I('post.'));
    }

}