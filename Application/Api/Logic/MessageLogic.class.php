<?php
namespace Api\Logic;

/**
 * Class MessageLogic
 * @package Api\Logic
 */
class MessageLogic extends BaseLogic{

    /**
     * @param array $request
     * 消息首页
     * 用户ID     m_id
     */
    public function messageIndex($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //查找相应的系统消息和订单消息
        $where['m_id']   = $request['m_id'];
        $where['m_type'] = 1;
        $where['status'] = array('eq',0);
        $where['type']   = array('eq',1);
        $system_info = M('Message')->where($where)->field('cn_title as title,create_time')->order('create_time desc')->limit(1)->select();
        if($system_info){
            $result_data['system']['status'] = '1';
            $result_data['system']['title']  = $system_info[0]['title'];
            $result_data['system']['create_time']  = date('Y-m-d',$system_info[0]['create_time']);
        }else{
            $result_data['system']['status'] = '0';
            $result_data['system']['title']  = '';
            $result_data['system']['create_time']  = '';
        }

        $where['type']   = array('eq',2);
        $order_info = M('Message')->where($where)->field('cn_title as title,create_time')->order('create_time desc')->limit(1)->select();
        if($order_info){
            $result_data['order']['status'] = '1';
            $result_data['order']['title']  = $order_info[0]['title'];
            $result_data['order']['create_time']  = date('Y-m-d',$order_info[0]['create_time']);
        }else{
            $result_data['order']['status'] = '0';
            $result_data['order']['title']  = '';
            $result_data['order']['create_time']  = '';
        }
        apiResponse('success','',$result_data);
    }

    /**
     * @param array $request
     * 消息列表
     * 用户ID     m_id
     * 消息类型  type  1  系统消息  2  订单消息
     * 分页类型   p
     */
    public function messageList($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //消息类型  type  1  系统消息  2  订单消息

        if($request['type'] != 1&&$request['type'] != 2){
            apiResponse('error','消息类型有误');
        }
        //分页类型不能为空
        if(empty($request['p'])){
            apiResponse('error','分页类型有误');
        }

        $where['m_id'] = $request['m_id'];
        $where['type'] = $request['type'];

        $list = M('Message')->where($where)->field('id as message_id,cn_title as title,create_time,status')->order('create_time desc')->page($request['p'].',10')->select();

        if(empty($list)){
            if($request['p']==1){
                apiResponse('error','无任何数据');
            }else{
                apiResponse('error','无更多数据');
            }
        }

        foreach($list as $k =>$v){
            $list[$k]['create_time'] = date('Y-m-d', $v['create_time']);
        }
        apiResponse('success','',$list);
    }

    /**
     * @param array $request
     * 消息详情
     * 信息ID    message_id
     * 消息类型  type  1  系统消息  2  订单消息
     */
    public function messageInfo($request = array()){
        //信息ID不能为空
        if(empty($request['message_id'])){
            apiResponse('error','信息ID不能为空');
        }
        //消息类型  type  1  系统消息  2  订单消息
        if($request['type']!=1 && $request['type']!=2 ){
            apiResponse('error','消息类型有误');
        }
        //标记消息为已读
        $where['id'] = $request['message_id'];
        $data['update_time'] = time();
        $data['status'] = 1;
        M('Message')->where($where)->data($data)->save();

        $info = M('Message')->where($where)->field('cn_title as title,cn_content as content')->find();

        apiResponse('success','',$info);
    }

    /**
     * @param array $request
     * 商家消息首页
     * 商家ID     merchant_id
     */
    public function merMessageIndex($request = array()){
        //用户ID不能为空
        if(empty($request['merchant_id'])){
            apiResponse('error','商家ID不能为空');
        }
        //查找相应的系统消息和订单消息
        $where['m_id']   = $request['merchant_id'];
        $where['m_type'] = 2;
        $where['status'] = array('eq',0);
        $where['type']   = array('eq',1);
        $system_info = M('Message')->where($where)->field('cn_title as title,create_time')->order('create_time desc')->limit(1)->select();
        if($system_info){
            $result_data['system']['status'] = '1';
            $result_data['system']['title']  = $system_info[0]['title'];
            $result_data['system']['create_time']  = date('Y-m-d',$system_info[0]['create_time']);
        }else{
            $result_data['system']['status'] = '0';
            $result_data['system']['title']  = '';
            $result_data['system']['create_time']  = '';
        }

        $where['type']   = array('eq',2);
        $order_info = M('Message')->where($where)->field('cn_title as title,create_time')->order('create_time desc')->limit(1)->select();
        if($order_info){
            $result_data['order']['status'] = '1';
            $result_data['order']['title']  = $order_info[0]['title'];
            $result_data['order']['create_time']  = date('Y-m-d',$order_info[0]['create_time']);
        }else{
            $result_data['order']['status'] = '0';
            $result_data['order']['title']  = '';
            $result_data['order']['create_time']  = '';
        }
        apiResponse('success','',$result_data);
    }

    /**
     * @param array $request
     * 消息列表
     * 商家ID     merchant_id
     * 消息类型   type  1  系统消息  2  订单消息
     * 分页类型   p
     */
    public function merMessageList($request = array()){
        //用户ID不能为空
        if(empty($request['merchant_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //消息类型  type  1  系统消息  2  订单消息

        if($request['type'] != 1&&$request['type'] != 2){
            apiResponse('error','消息类型有误');
        }
        //分页类型不能为空
        if(empty($request['p'])){
            apiResponse('error','分页类型有误');
        }

        $where['m_id'] = $request['merchant_id'];
        $where['m_type'] = 2;
        $where['type'] = $request['type'];

        $list = M('Message')->where($where)->field('id as message_id,cn_title as title,create_time,status')->order('create_time desc')->page($request['p'].',10')->select();

        if(empty($list)){
            $list = array();
            if($request['p']==1){
                apiResponse('success','无任何数据',$list);
            }else{
                apiResponse('success','无更多数据',$list);
            }
        }

        foreach($list as $k =>$v){
            $list[$k]['create_time'] = date('Y-m-d', $v['create_time']);
        }
        apiResponse('success','',$list);
    }
    /**
     * 意见反馈
     * 传递参数的方式：post
     * 需要传递的参数：
     * 商家id：object_id
     * 反馈内容：content
     * 传递参数: type  1  用户  2  商家
     */
    public function feedBack($request = array()){
        if(!$request['object_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['content']){
            apiResponse('error','反馈内容不能为空');
        }
        if($request['type'] != 1&&$request['type'] != 2){
            apiResponse('error','传递参数有误');
        }
        $data['object_id'] = $request['object_id'];
        $data['content']     = $request['content'];
        $data['create_time'] = time();
        $data['type']        = $request['type'];
        $data['status']      = 0;
        $result = M('Feedback') ->add($data);
        if(!$result){
            apiResponse('error','提交失败');
        }
        apiResponse('success','提交成功');
    }
}