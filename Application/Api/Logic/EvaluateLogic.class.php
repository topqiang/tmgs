<?php
namespace Api\Logic;

/**
 * Class OrderLogic
 * @package Api\Logic
 */
class EvaluateLogic extends BaseLogic{

    //初始化
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
    public function evaluationLists($request = array()){
        //用户ID  不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //订单ID不能为空
        if(empty($request['order_id'])){
            apiResponse('error','订单id不能为空');
        }
        //统计未读消息数量
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result['un_read_num'] = '' . $un_read_num;
        //根据所得条件查询订单信息
        $where['id'] = $request['order_id'];
        $where['m_id'] = $request['m_id'];
        $order_info = M('Order') ->where($where) ->field('id as order_id, m_id, order_sn, merchant_id, goods_info_serialization') ->find();
        if(empty($order_info)){
            $result['goods'] = array();
            apiResponse('success','订单详情有误',$result);
        }
        //得到订单中的商品信息
        $goods_info = unserialize($order_info['goods_info_serialization']);
        if(empty($goods_info)){
            $result['goods'] = array();
            apiResponse('success','订单详情有误',$result);
        }
        $i = 0;
        foreach ($goods_info['goods'] as $k => $v) {
            $goods[$i]['order_id'] = $order_info['order_id'];
            $goods[$i]['order_sn'] = $order_info['order_sn'];
            $goods[$i]['merchant_id'] = $order_info['merchant_id'];
            $goods[$i]['goods_id'] = $v['goodsDetail']['id'];
            $goods[$i]['goods_pic'] = $v['goodsDetail']['goods_pic'] ? C('API_URL').$v['goodsDetail']['goods_pic'] : '';
            $goods[$i]['goods_name'] = $v['goodsDetail']['goods_name'] ? $v['goodsDetail']['goods_name'] : '';
            $goods[$i]['price'] = $v['goodsDetail']['price'] ? $v['goodsDetail']['price'] : '0.00';
            $goods[$i]['num'] = $v['num'] ? $v['num'] : '0';
            $goods[$i]['attr_con_name'] = $v['product']['attr_con_name'];

            unset($where);
            $where['m_id']       = $request['m_id'];
            $where['g_id']       = $v['goodsDetail']['id'];
            $where['order_id']   = $order_info['order_id'];
            $where['order_sn']   = $order_info['order_sn'];
            $where['goods_attr'] = $goods[$i]['attr_con_name'];
            $result_data = M('Evaluate') ->where($where) ->find();
            //根据条件的不同  返货的type值不同  type = 1  已评价   type = 2   未评价
            if($result_data){
                $goods[$i]['type'] = 1;
            }else{
                $goods[$i]['type'] = 2;
            }
            $type[] = $goods[$i]['type'];
            $i = $i + 1;
        }
        if(empty($goods)){
            $result['goods'] = array();
            apiResponse('success','订单详情有误',$result);
        }
        //传值
        $result['goods'] = $goods;
        if(!empty($type)) {
            $count = count($type);
            $sum = array_sum($type);
            $type_data = $sum / $count;
            if ($type_data == 1) {
                unset($where);
                $where['id'] = $order_info['order_id'];
                $where['m_id'] = $order_info['m_id'];
                $where['remove_status'] = 0;
                $data['status'] = 5;
                $data['order_complete_time'] = time();
                $data['eval_complete_time']  = time();
                $res = M('Order')->where($where)->data($data)->save();
                if (empty($res)) {
                    apiResponse('success', '订单完成失败', $result);
                }
            }
        }

        apiResponse('success','',$result);
    }
    /**
     * 立即评价
     * 传递参数的方式：post
     * 需要传递的参数：
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
    public function goodsEvaluation ($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //商品ID不能为空
        if(empty($request['goods_id'])){
            apiResponse('error','商品ID不能为空');
        }
        //商家ID不能为空
        if(empty($request['merchant_id'])){
            apiResponse('error','商家ID不能为空');
        }
        //订单ID不能为空
        if(empty($request['order_id'])){
            apiResponse('error','订单id不能为空');
        }
        //订单号ID不能为空
        if(empty($request['order_sn'])){
            apiResponse('error','订单号不能为空');
        }
        //商品属性不能为空
        if(empty($request['attr_con_name'])){
            apiResponse('error','商品属性不能为空');
        }
        //评价等级  1  好评  2  中评  3  差评
        if($request['rank'] != 1&&$request['rank'] != 2&&$request['rank'] != 3){
            apiResponse('error','评价等级填写有误');
        }
        //商品评价内容
        if(empty($request['review'])){
            apiResponse('error','请对商品进行评价');
        }
        //上传图片可以为空
        if (!empty($_FILES['evaluate_pic']['name'])) {
            $res = api('UploadPic/upload', array(array('save_path' => "Evaluate")));
            foreach ($res as $k => $value) {
                $pic[] =  $value['id'];
            }
            $data['evaluate_pic'] = implode(',',$pic);
        }

        //将评价写入数据库
        $data['m_id'] = $request['m_id'];
        $data['g_id'] = $request['goods_id'];
        $data['merchant_id'] = $request['merchant_id'];
        $data['review'] = $request['review'];
        $data['rank'] = $request['rank'];
        $data['order_sn'] = $request['order_sn'];
        $data['order_id'] = $request['order_id'];
        $data['evaluate_time'] = time();
        $data['goods_attr'] = $request['attr_con_name'];
        $result = M('Evaluate') ->add($data);
        if(empty($result)){
            apiResponse('error', '评价失败');
        }
        unset($where);
        unset($data);
        $where['id'] = $request['order_id'];
        $where['order_sn'] = $request['order_sn'];
        $where['merchant_id'] = $request['merchant_id'];
        $where['m_id'] = $request['m_id'];
        $Order_data = M('Order') ->where($where) ->field('id as order_id, goods_info_serialization') ->find();
        if(!$Order_data){
            apiResponse('error', '订单信息不存在');
        }
        $goods_info = unserialize($Order_data['goods_info_serialization']);
        foreach($goods_info as $k =>$v){
            foreach($v as $key => $val){
                unset($where);
                unset($data);
                $where['order_id'] = $request['order_id'];
                $where['order_sn'] = $request['order_sn'];
                $where['g_id']     = $val['goodsDetail']['id'];
                $result_data = M('Evaluate') ->where($where) ->find();
                if(!$result_data){
                    break;
                }
            }
        }
        unset($where);
        unset($data);
        if($result_data){
            $where['id']       = $request['order_id'];
            $where['order_sn'] = $request['order_sn'];
            $where['m_id']     = $request['m_id'];
            $where['merchant_id'] = $request['merchant_id'];
            $data['status']    = 5;
            $result_info = M('Order') ->where($where) ->data($data) ->save();
            if(!$result_info){
                apiResponse('success', '评价成功');
            }
        }
        apiResponse('success', '评价成功');
    }
    /**
     * 我的评价列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 评价参数  type  1  好评  2  中评  3  差评  4  全部
     * 分页参数  p
     */
    public function myEvaluationList($request = array()){
        //用户ID  不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //分页参数不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }
        //评价参数参数不能为空  1  好评列表  2  中评列表  3  差评列表  4  全部列表
        if($request['type'] != 1&&$request['type'] != 2&&$request['type'] != 3&&$request['type'] != 4){
            apiResponse('error','评价参数有误');
        }
        //统计未读消息数量
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result['un_read_num'] = '' . $un_read_num;
        //得到好评中评差评全部评论的数量
        unset($where);
        $where['m_id'] = $request['m_id'];
        $where['rank'] = 1;
        $count1 = M('Evaluate') ->where($where) ->count();
        unset($where);
        $where['m_id'] = $request['m_id'];
        $where['rank'] = 2;
        $count2 = M('Evaluate') ->where($where) ->count();
        unset($where);
        $where['m_id'] = $request['m_id'];
        $where['rank'] = 3;
        $count3 = M('Evaluate') ->where($where) ->count();
        unset($where);
        $where['m_id'] = $request['m_id'];
        $count = M('Evaluate') ->where($where) ->count();
        $result['count']    = ''.$count;
        $result['count1']   = ''.$count1;
        $result['count2']   = ''.$count2;
        $result['count3']   = ''.$count3;
        unset($where);

        //根据所得条件查询评价
        $where['m_id'] = $request['m_id'];
        if($request['type'] == 4){

        }else{
            $where['rank'] = $request['type'];
        }
        $evaluate_info = M('Evaluate') ->where($where) ->field('id as evaluate_id, m_id, g_id as goods_id, merchant_id, evaluate_pic, review, rank, evaluate_time')
            -> order('evaluate_time desc') ->page($request['p'].',15') ->select();
        if(empty($evaluate_info)){
            $result['evaluate'] = array();
            apiResponse('success','您还没有评价信息',$result);
        }

        $index = 0;
        foreach($evaluate_info as $k =>$v){
            if($v['evaluate_pic']){
                $images = explode(',',$v['evaluate_pic']);
                $images1 = array();
                foreach($images as $key =>$val){
                    $path = M('File')->where(array('id'=>$images[$key]))->getField('path');
                    $images1[$key]['pic'] = $path?C('API_URL').$path:C('API_URL').'';
                }
                $v['evaluate_pic'] = $images1;
            }

            //查询商家信息
            unset($where);
            $where['id'] = $v['merchant_id'];
            $merchant[$index] = M('Merchant') ->where($where) -> field('id as merchant_id, head_pic, merchant_name') ->find();

            $path = M('File')->where(array('id'=>$merchant[$index]['head_pic']))->getField('path');
            $merchant[$index]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';


            if(!$merchant[$index]){
                continue;
            }
            unset($where);
            $where['id'] = $v['goods_id'];
            $goods[$index] = M('Goods') ->where($where) ->field('id as goods_id, cn_goods_name as goods_name') ->find();

            if(!$goods[$index]){
                continue;
            }
            $evaluate[$index]['evaluate_id']       = $v['evaluate_id'];
            $evaluate[$index]['m_id']              = $v['m_id'];
            $evaluate[$index]['merchant_id']       = $v['merchant_id'];
            $evaluate[$index]['merchant_name']     = $merchant[$index]['merchant_name'];
            $evaluate[$index]['merchant_head_pic'] = $merchant[$index]['head_pic'];
            $evaluate[$index]['goods_id']          = $v['goods_id'];
            $evaluate[$index]['goods_name']    = '商品名：'.$goods[$index]['goods_name'];
            $evaluate[$index]['evaluate_pic']      = $v['evaluate_pic'];
            $evaluate[$index]['review']            = $v['review'];
            $evaluate[$index]['rank']              = $v['rank'];
            $evaluate[$index]['evaluate_time']     = date('Y-m-d',$v['evaluate_time']);
            $index += 1 ;
        }
        $result['evaluate'] = $evaluate;
        if(empty($result['evaluate'])){
            $result['evaluate'] = array();
            apiResponse('success','您还没有评价信息',$result);
        }
        apiResponse('success','',$result);
    }
    /**
     * 商家查看评价
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 订单ID  order_id
     */
    public function merchantEvaluationLists ($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //订单ID不能为空
        if(empty($request['order_id'])){
            apiResponse('error','订单id不能为空');
        }
        //统计未读消息数量
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result['un_read_num'] = '' . $un_read_num;
        //根据所得信息查询所需订单
        $where['id'] = $request['order_id'];
        $where['status']   = 3;
        $order_info = M('Order') ->where($where) ->field('id as order_id, m_id, goods_info_serialization') ->find();

        //如果订单不存在   不报错
        if(empty($order_info)){
            $result['evaluate'] = array();
            apiResponse('success','订单还未被评价',$result);
        }

        unset($where);
        $where['id'] = $order_info['m_id'];
        $member_info = M('Member') ->where($where) ->field('id as m_id, nickname, head_pic') ->find();
        $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
        $member_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';

        if(empty($member_info)){
            $member_info['nickname'] = '淘米用户';
            $member_info['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
        }
        $goods_info = unserialize($order_info['goods_info_serialization']);
        if(empty($goods_info)){
            $result['evaluate'] = array();
            apiResponse('success','订单详情有误',$result);
        }
        $index = 0;
        foreach($goods_info as $k =>$v){
            foreach($v as $key =>$val){
                unset($where);
                $where['m_id'] = $order_info['m_id'];
                $where['g_id'] = $val['goodsDetail']['id'];
                $where['order_id'] = $order_info['order_id'];
                $where['goods_attr'] = $val['product']['cn_attr_con_name']?$val['product']['cn_attr_con_name']:'0.00';
                $evaluate = M('Evaluate') ->where($where) ->field('id as evaluate_id,m_id,g_id as goods_id,evaluate_pic,review,rank,goods_attr,evaluate_time') ->find();

                if($evaluate['evaluate_pic']){
                    $images = explode(',',$evaluate['evaluate_pic']);
                    $images1 = array();
                    foreach($images as $keys =>$values){
                        $path = M('File')->where(array('id'=>$images[$key]))->getField('path');
                        $images1[$key]['pic'] = $path?C('API_URL').$path:C('API_URL').'';
                    }
                    $evaluate['evaluate_pic'] = $images1;
                }

                $result['evaluate'][$index]['m_id'] = $member_info['m_id'];
                $result['evaluate'][$index]['nickname'] = $member_info['nickname'];
                $result['evaluate'][$index]['head_pic'] = $member_info['head_pic'];
                $result['evaluate'][$index]['goods_attr'] = $evaluate['goods_attr'];
                $result['evaluate'][$index]['evaluate_time'] = date('Y-m-d',$evaluate['evaluate_time']);
                $result['evaluate'][$index]['rank'] = $evaluate['rank'];
                $result['evaluate'][$index]['review'] = $evaluate['review'];
                $result['evaluate'][$index]['evaluate_pic'] = $evaluate['evaluate_pic'];
                $index += 1 ;
            }
        }
        if(empty($result['evaluate'])){
            $result['evaluate'] = array();
        }
        apiResponse('success','',$result);
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
    public function viewFullEvaluation($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            //统计未读消息数量
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
            $result['un_read_num'] = '' . $un_read_num;
        }else{
            $result['un_read_num'] = '0';
        }
        //商品ID不能为空
        if(empty($request['goods_id'])){
            apiResponse('error','商品id不能为空');
        }
        //评价参数不能为空
        if(empty($request['type'])){
            apiResponse('error','评价参数不能为空');
        }
        //评价参数type  为1  好评  2  中评  3  差评  4  全部
        if($request['type'] != 1&&$request['type'] != 2&&$request['type'] != 3&&$request['type'] != 4){
            apiResponse('error','评价参数输入有误');
        }
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }
        //根据商品ID  和  评价参数进行查询
        $where['g_id'] = $request['goods_id'];
        $where['rank'] = 1;
        $count1 = M('Evaluate') ->where($where) ->count();
        unset($where);
        $where['g_id'] = $request['goods_id'];
        $where['rank'] = 2;
        $count2 = M('Evaluate') ->where($where) ->count();
        unset($where);
        $where['g_id'] = $request['goods_id'];
        $where['rank'] = 3;
        $count3 = M('Evaluate') ->where($where) ->count();
        unset($where);
        $where['g_id'] = $request['goods_id'];
        $count = M('Evaluate') ->where($where) ->count();
        $result['count']    = ''.$count;
        $result['count1']   = ''.$count1;
        $result['count2']   = ''.$count2;
        $result['count3']   = ''.$count3;
        unset($where);
        $where['g_id'] = $request['goods_id'];
        if($request['type'] == 4){

        }else{
            $where['rank'] = $request['type'];
        }
        $evaluate_info = M('Evaluate') ->where($where) ->field('id as evaluate_id, m_id, g_id as goods_id, goods_attr, evaluate_pic, review, rank,evaluate_time')
            ->order('evaluate_time desc') ->page($request['p'].',15') ->select();

        if(empty($evaluate_info)){
            $result['evaluate'] = array();
            apiResponse('success','该商品还没有评论',$result);
        }
        $index = 0 ;
        foreach($evaluate_info as $k =>$v){
            if($v['evaluate_pic']){
                $images = explode(',',$v['evaluate_pic']);
                $images1 = array();
                foreach($images as $key =>$val){
                    $path = M('File')->where(array('id'=>$images[$key]))->getField('path');
                    $images1[$key]['pic'] = $path?C('API_URL').$path:C('API_URL').'';
                }
                $v['evaluate_pic'] = $images1;
            }
            unset($where);
            $where['id'] = $v['m_id'];
            $member_info = M('Member') ->where($where) ->field('id as m_id, nickname, head_pic') ->find();
            if(empty($member_info)){
                $eva[$index]['m_id'] = '';
                $eva[$index]['nickname'] = '淘米用户';
                $eva[$index]['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
            }
            $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
            $member_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';

            $evaluate[$index]['goods_id']   = $v['goods_id'];
            $evaluate[$index]['goods_attr'] = $v['goods_attr'];
            $evaluate[$index]['m_id']       = $v['m_id']?$v['m_id']:'';
            $evaluate[$index]['nickname']   = $member_info['nickname']?$member_info['nickname']:$eva[$index]['nickname'];
            $evaluate[$index]['head_pic']   = $member_info['head_pic']?$member_info['head_pic']:$eva[$index]['head_pic'];
            $evaluate[$index]['evaluate_pic'] = $v['evaluate_pic'];
            $evaluate[$index]['review']     = $v['review'];
            $evaluate[$index]['rank']       = $v['rank'];
            $evaluate[$index]['evaluate_time'] = date('Y-m-d',$v['evaluate_time']);
            $index += 1;
        }

        $result['evaluate'] = $evaluate;

        if(empty($result['evaluate'])){
            $result['evaluate'] = array();
            apiResponse('success','该商品还没有评价信息',$result);
        }
        apiResponse('success','',$result);
    }
}