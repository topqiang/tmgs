<?php
namespace Api\Logic;
/**
 * Class DiscoverLogic
 * @package Api\Logic
 */
class DiscoverLogic extends BaseLogic{
    /**
     * 发现首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id    m_id
     */
    public function discoverList($request = array()){
        //用户ID不能为空  获取未读消息数量
        if($request['m_id']){
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
            $result['un_read_num'] = ''.$un_read_num;
        }else{
            $result['un_read_num'] = '0';
        }
        //发现好货
        $where['good_product'] = 1;
        $where['audit_status'] = array('IN',array(0,1));
        $where['is_shelves']   = 1;
        $goods_list = M('Goods') ->where($where)
            ->field('id as goods_id, cn_goods_name as goods_name, goods_pic, sales')
            ->order('sort desc, create_time desc') ->limit(3) ->select();
        //获取商品图片  价格
        foreach($goods_list as $k =>$v){
            $path = M('File') ->where(array('id'=>$v['goods_pic'])) ->getField('path');
            $goods_list[$k]['goods_pic'] = $path?C('API_URL').$path:'';
            unset($where);
            $where['goods_id'] = $v['goods_id'];
            $where['status'] = array('neq',9);
            $price = M('GoodsProduct') ->where($where) ->order('cn_price asc') ->field('id as product_id, cn_price as price') ->find();
            $goods_list[$k]['price'] = $price['price']?$price['price']:'0.00';
            $count = M('Evaluate') ->where(array('g_id'=>$v['goods_id'])) ->count();
            $goods_list[$k]['evaluate'] = $count;
        }
        if(!$goods_list){
            $result['good_product'] = array();
        }else{
            $result['good_product'] = $goods_list;
        }
        unset($where);
        //发现好店
        $where['good_shop'] = 1;
        $where['status'] = array('neq',9);
        $merchant_list = M('Merchant') ->where($where) ->field('id as merchant_id, merchant_name, head_pic')
            ->order('create_time desc') ->limit(3) ->select();
        foreach($merchant_list as $k =>$v){
            $path = M('File') ->where(array('id'=>$v['head_pic'])) ->getField('path');
            $merchant_list[$k]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';
            unset($where);
            $where['merchant_id'] = $v['merchant_id'];
            $where['is_shelves']  = 1;
            $where['audit_status'] = array('IN',array(0,1));
//            $goods = M('Goods') ->where($where) ->select();
            $merchant_list[$k]['goods_count'] = M('Goods') ->where($where) ->count();
//            $count = 0;
//            foreach($goods as $key =>$val){
//                unset($where);
//                $where['goods_id'] = $val['id'];
//                $where['status']   = array('neq',9);
//                $price = M('GoodsProduct') ->where($where) ->find();
//                if($price){
//                    $count = $count + 1;
//                }
//            }
//            $merchant_list[$k]['goods_count'] = $count?$count.'':'0';

            $sales       = M('Goods') ->where($where) ->getField('SUM(sales) as sales');
            $merchant_list[$k]['sales'] = $sales?$sales:0;
        }
        if(!$merchant_list){
            $result['good_shop'] = array();
        }else{
            $result['good_shop'] = $merchant_list;
        }
        //获取好服务商家
        unset($where);
        $where['good_service'] = 1;
        $where['status'] = array('neq',9);
        $merchant_list = M('Merchant') ->where($where) ->field('id as merchant_id, merchant_name, head_pic')
            ->order('create_time desc') ->limit(3) ->select();
        foreach($merchant_list as $k => $v){
            $path = M('File') ->where(array('id'=>$v['head_pic'])) ->getField('path');
            $merchant_list[$k]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';
            unset($where);
            $where['merchant_id'] = $v['merchant_id'];
            $where['is_shelves']   = 1;
            $where['audit_status'] = array('IN',array(0,1));
            $merchant_list[$k]['goods_count'] = M('Goods') ->where($where) ->count();
//            $count = 0;
//            foreach($goods as $key =>$val){
//                unset($where);
//                $where['goods_id'] = $val['id'];
//                $where['status']   = array('neq',9);
//                $price = M('GoodsProduct') ->where($where) ->find();
//                if($price){
//                    $count = $count + 1;
//                }
//            }
//            $merchant_list[$k]['goods_count'] = $count?$count.'':'0';
            $sales       = M('Goods') ->where($where) ->getField('SUM(sales) as sales');
            $merchant_list[$k]['sales'] = $sales?$sales:0;
        }
        if(!$merchant_list){
            $result['good_service'] = array();
        }else{
            $result['good_service'] = $merchant_list;
        }
        apiResponse('success','',$result);
    }
    /**
     * 发现好货
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id    可以为空
     * 分页参数  p
     */
    public function discoverGoods($request = array()){
        if($request['m_id']){
            $result_data['m_id'] = $request['m_id'];
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($result_data['m_id']);
            $result['un_read_num'] = '' . $un_read_num;
        }else{
            $result['un_read_num'] = '0';
        }
        if(!$request['p']){
            apiResponse('error','分页参数不能为空');
        }
        $where['good_product'] = 1;
        $where['is_shelves']   = 1;
        $where['audit_status'] = array('IN',array(0,1));
        $goods_list = M('Goods') ->where($where)
            ->field('id as goods_id, cn_goods_name as goods_name, goods_pic, sales')
            ->order('sort desc, create_time desc')
            -> page($request['p'].',15') ->select();
        if(!$goods_list){
            $result['good_product'] = array();
            apiResponse('success','',$result);
        }
        foreach($goods_list as $k =>$v){
            $goods_pic = explode(',',$v['goods_pic']);
            $path = M('File') ->where(array('id'=>$goods_pic[0]))->getField('path');
            $goods_list[$k]['goods_pic'] = $path?C('API_URL').$path:'';
            unset($where);
            $where['goods_id'] = $v['goods_id'];
            $where['status'] = array('neq',9);
            $price = M('GoodsProduct') ->where($where) ->order('cn_price asc') ->field('id as product_id, cn_price as price') ->find();
            $goods_list[$k]['price'] = $price['price']?$price['price']:'0.00';
            $count = M('Evaluate') ->where(array('g_id'=>$v['goods_id'])) ->count();
            $goods_list[$k]['evaluate'] = $count;
        }
        $result['good_product'] = $goods_list;
        apiResponse('success','',$result);
    }
    /**
     * 发现好店
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID：m_id
     * 分页参数  p
     */
    public function discoverMerchant($request = array()){
        //分页参数不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }
        //根据用户ID获取未读消息数量
        if (!empty($request['m_id'])) {
            $result_data['m_id'] = $request['m_id'];
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($result_data['m_id']);
            $result['un_read_num'] = '' . $un_read_num;
        } else {
            $result['un_read_num'] = '0';
        }
        $where['goods_shop'] = 1;
        $where['status'] = array('neq',9);
        $merchant_list = M('Merchant') ->where($where) ->field('id as merchant_id, merchant_name, head_pic')
            ->order('create_time desc') ->page($request['p'].',15') ->select();
        if(!$merchant_list){
            $result['good_shop'] = array();
            apiResponse('success','',$result);
        }
        foreach($merchant_list as $k =>$v){
            $path = M('File') ->where(array('id'=>$v['head_pic'])) ->getField('path');
            $merchant_list[$k]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';
            unset($where);
            $where['merchant_id'] = $v['merchant_id'];
            $where['is_shelves']  = 1;
            $where['audit_status'] = array('IN',array(0,1));
            $merchant_list[$k]['goods_count'] = M('Goods') ->where($where) ->count();
            $sales       = M('Goods') ->where($where) ->getField('SUM(sales) as sales');
            $merchant_list[$k]['sales'] = $sales?$sales:0;
            $goods_list = M('Goods') ->where($where)
                ->field('id as goods_id, goods_pic') ->order('create_time desc') ->limit(3) ->select();

            foreach($goods_list as $key =>$val){
                $goods_pic = explode(',',$val['goods_pic']);
                $path1 = M('File') ->where(array('id'=>$goods_pic[0])) ->getField('path');
                $goods_list[$key]['goods_pic'] = $path1?C('API_URL').$path1:'';
                $price = M('GoodsProduct') ->where(array('goods_id'=>$val['goods_id']))
                    ->field('cn_price as price') ->order('cn_price asc') ->find();
                $goods_list[$key]['price'] = $price['price']?$price['price']:'0.00';
            }
            $merchant_list[$k]['goods_list'] = $goods_list;
        }
        $result['good_shop'] = $merchant_list;
        apiResponse('success','',$result);
    }
    /**
     * 发现好服务
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID  m_id
     * 分页参数  p
     */
    public function discoverService($request = array()){
        //分页参数不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }
        //根据用户ID获取未读消息数量
        if (!empty($request['m_id'])) {
            $result_data['m_id'] = $request['m_id'];
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($result_data['m_id']);
            $result['un_read_num'] = '' . $un_read_num;
        } else {
            $result['un_read_num'] = '0';
        }
        $where['goods_service'] = 1;
        $where['status'] = array('neq',9);
        $merchant_list = M('Merchant') ->where($where) ->field('id as merchant_id, merchant_name, head_pic')
            ->order('create_time desc') ->page($request['p'].',10') ->select();
        if(!$merchant_list){
            $merchant_list = array();
            $result['good_service'] = array();
        }
        foreach($merchant_list as $k =>$v){
            $path = M('File') ->where(array('id'=>$v['head_pic'])) ->getField('path');
            $merchant_list[$k]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';
            unset($where);
            $where['merchant_id'] = $v['merchant_id'];
            $where['is_shelves']  = 1;
            $where['audit_status'] = array('IN',array(0,1));
            $sales      = M('Goods') ->where($where) ->getField('SUM(sales) as sales');
            $merchant_list[$k]['sales'] = $sales?$sales:0;
            $merchant_list[$k]['goods_count'] = M('Goods') ->where($where) ->count();
            $goods_list = M('Goods') ->where($where)
                ->field('id as goods_id, goods_pic') ->order('create_time desc') ->limit(3) ->select();
      
            foreach($goods_list as $key =>$val){
                $goods_pic = explode(',',$val['goods_pic']);
                $path1 = M('File') ->where(array('id'=>$goods_pic[0])) ->getField('path');
                $goods_list[$key]['goods_pic'] = $path1?C('API_URL').$path1:'';
                $price = M('GoodsProduct') ->where(array('goods_id'=>$val['goods_id']))
                    ->field('cn_price as price') ->order('cn_price asc') ->find();
                $goods_list[$key]['price'] = $price['price']?$price['price']:'0.00';
            }
            if($goods_list){
                $merchant_list[$k]['goods_list'] = $goods_list;
            }else{
                $merchant_list[$k]['goods_list'] = array();
            }
        }
        $result['good_service'] = $merchant_list;
        apiResponse('success','',$result);
    }
}