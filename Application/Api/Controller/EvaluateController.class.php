<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class OrderController
 * @package Api\Controller
 * 足迹模块
 */
class EvaluateController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 评价列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 订单ID  order_id
     */
    public function evaluationLists (){
        D('Evaluate','Logic')->evaluationLists(I('post.'));
    }
    /**
     * 立即评价
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language cn中文 ue英文
     * 用户ID   m_id
     * 商品ID   goods_id
     * 商家ID   merchant_id
     * 订单ID   order_id
     * 订单号   order_sn
     * 商品属性   attr_con_name
     * 评价等级   rank
     * 评价内容   view
     * 评价图片   evaluate_pic
     */
    public function goodsEvaluation (){
        D('Evaluate','Logic')->goodsEvaluation(I('post.'));
    }
    /**
     * 我的评价列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 评价参数  type  1  好评  2  中评  3  差评  4  全部
     * 分页参数  p
     */
    public function myEvaluationList(){
        D('Evaluate','Logic')->myEvaluationList(I('post.'));
    }
    /**
     * 商家查看评价
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 订单ID  order_id
     */
    public function merchantEvaluationLists (){
        D('Evaluate','Logic')->merchantEvaluationLists(I('post.'));
    }
    /**
     * 商品全部评价
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 商品ID    goods_id
     * 评价参数  type   1  好评  2  中评  3  差评  4  全部
     * 分页参数  p
     */
    public function viewFullEvaluation(){
        D('Evaluate','Logic')->viewFullEvaluation(I('post.'));
    }
}