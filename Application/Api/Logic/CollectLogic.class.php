<?php
namespace Api\Logic;
/**
 * Class CollectLogic
 * @package Api\Logic
 */
class CollectLogic extends BaseLogic{
    /**
     * 收藏商品列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 分页参数  p
     */
    public function collectGoodsList($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //分页参数不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }
        //获取未读消息的条数
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result['un_read_num'] = '' . $un_read_num;
        //查询商品ID
        $where['m_id'] = $request['m_id'];
        $where['status'] = array('neq',9);
        $where['type'] = 2;
        $res = M('Collect') ->where($where) -> field('handle_id') -> order('create_time desc')->page($request['p'].',10') ->select();
        if(empty($res)){
            $result['goods'] = array();
            apiResponse('success','您还未收藏商品',$result);
        }
        //查询商品详情  商品名称  商品图片  商品价格
        unset($where);
        $index = 0;
        foreach($res as $k => $v){
            $where['id'] = $res[$k]['handle_id'];
            $where['is_shelves'] = 1;
            $result['goods'][$index] = M('Goods') ->where($where) -> field('id as goods_id,cn_goods_name as goods_name,goods_pic') -> find();
            if(empty($result['goods'][$index])){
                unset($result['goods'][$index]);
                continue;
            }
            //判断是否存在图片
            if($result['goods'][$index]['goods_pic']){
                $goods_pic = explode('.',$result['goods'][$index]['goods_pic']);
                $path = M('File')->where(array('id'=>$goods_pic[0]))->getField('path');
                $result['goods'][$index]['goods_pic'] = $path?C('API_URL').$path:'';
            }
            unset($where);
            $where['goods_id'] = $result['goods'][$index]['goods_id'];
            $where['status'] = array('neq',9);
            $price = M('GoodsProduct') ->where($where) ->field('goods_id,cn_price as price') ->order('cn_price asc') ->find();
            $result['goods'][$index]['price'] = $price['price']?$price['price']:'0.00';
            $index = $index + 1;
        }
        if(empty($result['goods'])){
            $result['goods'] = array();
        }
        apiResponse('success','请求成功',$result);
    }
     /**
     * 已收藏商家
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 分页参数 p
     */
    public function collectMerchantList($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //分页参数不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }
        //获取未读消息的条数
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result['un_read_num'] = '' . $un_read_num;
        //查询收藏商家信息
        unset($where);
        $where['m_id'] = $request['m_id'];
        $where['type'] = 1;
        $where['status'] = 1;
        $res = M('Collect') ->where($where) ->field('id, handle_id') ->order('create_time desc')->page($request['p'].',10')->select();
        if(empty($res)){
            $result['merchant'] = array();
            apiResponse('success','请求成功',$result);
        }
        //查询商家详情  商家名称  商家头像
        unset($where);
        $index = 0 ;
        foreach($res as $k => $v){
            $where['id'] = $res[$k]['handle_id'];
            $where['status'] = 1;
            $result['merchant'][$index] = M('Merchant')->where($where) ->field('id as merchant_id,head_pic,merchant_name') ->find();
            if(empty($result['merchant'][$index])){
                unset($result['merchant'][$index]);
                continue;
            }
            //判断是否存在图片
            $path = M('File')->where(array('id'=>$result['merchant'][$index]['head_pic']))->getField('path');
            $result['merchant'][$index]['head_pic'] = $path?C('API_URL').$path:'';

            $index += 1;
        }
        if(empty($result['merchant'])){
            $result['merchant'] = array();
        }
        apiResponse('success','请求成功',$result);
    }
     /**
     * 取消收藏商品
     * 传递参数的方式：post
     * 需要传递的参数：
      * 用户ID  m_id
      * 商品ID  goods_id
     */
    public function exitCollectGoods($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //商品ID不能为空
        if(empty($request['goods_id'])){
            apiResponse('error','商品ID不能为空');
        }
        //用户ID  商品ID  商品类型  取消收藏
        $where['m_id'] = $request['m_id'];
        $where['handle_id'] = $request['goods_id'];
        $where['type'] = 2;
        $data['status'] = 9;
        $result = M('Collect') ->where($where) ->data($data) ->save();
        if(empty($result)){
            apiResponse('error','取消收藏失败');
        }
        apiResponse('success','取消收藏成功');
    }
     /**
     * 取消收藏商家
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 商家ID  merchant_id
     */
    public function exitCollectMerchant($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //商家ID不能为空
        if(empty($request['merchant_id'])){
            apiResponse('error','商家ID不能为空');
        }
        //用户ID  商家ID  类型  取消收藏商家
        $where['m_id'] = $request['m_id'];
        $where['handle_id'] = $request['merchant_id'];
        $where['type'] = 1;
        $data['status'] = 9;
        $result = M('Collect') ->where($where) ->data($data) ->save();
        if(empty($result)){
            apiResponse('error','取消收藏失败');
        }
        apiResponse('success','取消收藏成功');
    }
    /**
     * 收藏商品
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID      m_id
     * 商品ID      goods_id
     */
    public function collectGoods($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //商品ID不能为空
        if(empty($request['goods_id'])){
            apiResponse('error','商品ID不能为空');
        }
        //查询数据库是否有该数据
        $where['m_id'] = $request['m_id'];
        $where['handle_id'] = $request['goods_id'];
        $where['type'] = 2;
        $collect_info = M('Collect') ->where($where) ->find();
        if(empty($collect_info)){
            //没有该条记录，说明没有收藏，添加收藏记录即可
            $data['handle_id'] = $request['goods_id'];
            $data['m_id'] = $request['m_id'];
            $data['create_time'] = time();
            $data['type'] = 2;
            $data['status'] = 1;
            $res = M('Collect')->data($data)->add();
        }else{
            //已经有该条记录，判断现在是处于已收藏状态，还是处于已取消状态
            if($collect_info['status']==1){
                //已经收藏
                apiResponse('error','您已收藏该商品');
            }else{
                //已经取消状态
                unset($data);
                $data['create_time'] = time();
                $data['status']      = 1;
                $res = M('Collect')->where(array('id'=>$collect_info['id']))->data($data)->save();
            }
        }
        if($res){
            apiResponse('success','收藏成功');
        }else{
            apiResponse('error','收藏失败');
        }
    }
    /**
     * 收藏商家
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     * 商家id：merchant_id
     */
    public function collectMerchant($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }
        //商家ID不能为空
        if(empty($request['merchant_id'])){
            apiResponse('error','商家ID不能为空');
        }
        //查询数据库中是否有该条记录
        $where['m_id']      = $request['m_id'];
        $where['handle_id'] = $request['merchant_id'];
        $where['type']      = 1;
        $collect_info = M('Collect') ->where($where) ->find();
        if(empty($collect_info)){
            //没有该条记录，说明没有收藏，添加收藏记录即可
            $data['handle_id']   = $request['merchant_id'];
            $data['m_id']        = $request['m_id'];
            $data['create_time'] = time();
            $data['type']        = 1;
            $data['status']      = 1;
            $res = M('Collect')->data($data)->add();
        }else{
            //已经有该条记录，判断现在是处于已收藏状态，还是处于已取消状态
            if($collect_info['status']==1){
                //已经收藏
                apiResponse('error','您已收藏该商家');
            }else{
                //已经取消状态
                unset($data);
                $data['create_time'] = time();
                $data['status']      = 1;
                $res = M('Collect')->where(array('id'=>$collect_info['id']))->data($data)->save();
            }
        }
        if($res){
            apiResponse('success','收藏成功');
        }else{
            apiResponse('error','收藏失败');
        }
    }
}