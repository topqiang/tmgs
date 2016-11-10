<?php
namespace Api\Logic;
/**
 * Class MerchantLogic
 * @package Api\Logic
 */
class MerchantLogic extends BaseLogic{

    /** 我是商家
     * 返回0 1 2
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 用户ID：m_id
     */
    public function merchantIndex($request = array()){
        //检查语言版本
        $this->checkLanguage($request['language']);

        //验证参数的合法性
        if(empty($request['m_id'])){
            $message = $request['language']=='cn'?'用户id不能为空':'Parameter error';
            apiResponse('error',$message);
        }

        $result_data = array();//收集需要返回的数据

        //获取未读消息的条数
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result_data['un_read_num'] = $un_read_num?''.$un_read_num:'0';

        //获取客服电话
        $result_data['service_number'] = '8001';
        $result_data['service_pic'] = C("API_URL").'/Uploads/Member/service.png';
        //获取商家的类型,如果类型不等于2，说明暂时不是商家
        $m_type = M('Member')->where(array('id'=>$request['m_id']))->getField('m_type');
        $result_data['m_type'] = $m_type?$m_type.'':'0';
        if($m_type !=2){
            $message = $request['language']=='cn'?'请求成功':'Request successful';
            apiResponse('success',$message,$result_data);
        }

        //m_type=2表示是商家，获取商家的id，头像 店铺名称
        if($request['language']=='cn'){
            $merchant_info = M('Merchant')->where(array('m_id'=>$request['m_id']))->field('id as merchant_id,head_pic,cn_merchant_name as merchant_name,is_integrity')->find();
        }else{
            $merchant_info = M('Merchant')->where(array('m_id'=>$request['m_id']))->field('id as merchant_id,head_pic,ue_merchant_name as merchant_name,is_integrity')->find();
        }
        $path = M('File')->where(array('id'=>$merchant_info['head_pic']))->getField('path');
        $result_data['head_pic'] = $path?C('API_URL').$path:'';
        $result_data['merchant_id'] = $merchant_info['merchant_id']?$merchant_info['merchant_id']:'';
        $result_data['merchant_name'] = $merchant_info['merchant_name']?$merchant_info['merchant_name']:'';
        $result_data['is_integrity'] = $merchant_info['is_integrity']?$merchant_info['is_integrity'].'':'0';
        $message = $request['language']=='cn'?'请求成功':'Request successful';
        apiResponse('success',$message,$result_data);

    }
    /** 我是商家-商品列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 用户ID：merchant_id
     * 搜索关键词 keywords
     * 分页参数：p
     */
    public function goodsList($request = array()){
        //检查语言版本
        $this->checkLanguage($request['language']);

        //验证参数的合法性
        if(empty($request['merchant_id'])){
            $message = $request['language']=='cn'?'商家id不能为空':'Parameter error';
            apiResponse('error',$message);
        }
        if(empty($request['p'])){
            $message = $request['language']=='cn'?'分页参数不能为空':'Parameter error';
            apiResponse('error',$message);
        }
        $where['id'] = $request['merchant_id'];
        $where['status'] = array('neq',9);
        $merchant_info = M('Merchant')->where($where)->find();
        if(empty($merchant_info)){
            $message = $request['language']=='cn'?'商家信息不存在':'Merchant not found';
            apiResponse('error',$message);
        }

        $result_data = array();//收集需要返回的数据
        //获取未读消息的条数
        $un_read_num = $this->getUnReadMessageNum($merchant_info['m_id']);
        $result_data['un_read_num'] = $un_read_num?''.$un_read_num:'0';

        unset($where);
        $where['merchant_id'] = $request['merchant_id'];
        if($request['keywords']){
            if($request['language'] == 'cn'){
                $where['cn_goods_name'] = array('like','%'.$request['keywords'].'%');
            }else{
                $where['ue_goods_name'] = array('like','%'.$request['keywords'].'%');
            }
        }
        if($request['language'] == 'cn'){
            $where['cn_goods_name'] = array('neq','');
            $goods_list = M('Goods') -> where($where) ->field('id as goods_id,cn_goods_name as goods_name,cn_price as price,goods_pic,is_shelves')-> order('is_shelves desc, create_time desc') -> page($request['p'].',10') -> select();
        }else{
            $where['ue_goods_name'] = array('neq','');
            $goods_list = M('Goods') -> where($where) ->field('id as goods_id,ue_goods_name as goods_name,ue_price as price,goods_pic,is_shelves')-> order('is_shelves desc, create_time desc') -> page($request['p'].',10') -> select();
        }

        if(empty($goods_list)){
            $goods_list = array();
        }

        foreach($goods_list as $k =>$v){
            if($v['goods_pic']){
                $pic_arr = explode(',',$v['goods_pic']);
                $path = M('File')->where(array('id' => $pic_arr[0]))->getField('path');
                $goods_list[$k]['goods_pic'] = $path ? C('API_URL') . $path : '';
            }else{
                $goods_list[$k]['goods_pic'] = '';
            }

            unset($where);
            if($v['price']==0.00){
                $where['goods_id'] = $v['goods_id'];
                $where['status']   = array('neq',9);
                if($request['language'] == 'cn'){
                    $price = M('GoodsProduct') ->where($where)->field('cn_price as price') ->order('cn_price asc') ->find();
                }else{
                    $price = M('GoodsProduct') ->where($where)->field('ue_price as price') ->order('ue_price asc') ->find();
                }
                if($price){
                    $goods_list[$k]['price'] = $price['price'];
                }
            }
            $goods_list[$k]['is_integrity'] = $merchant_info['is_integrity']?$merchant_info['is_integrity'].'':'0';
        }

        $result_data['goods_list'] = $goods_list;
        $message = $request['language']=='cn'?'请求成功':'Request successful';
        apiResponse('success',$message,$result_data);

    }
    /** 商品上下架
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言：language ue 英文，cn:中文
     * 商品ID：goods_id
     * 类型：type=1上架 ，type=2 下架
     */
    public function frameList($request = array()){

        /*检查语言版本**/
        $this->checkLanguage($request['language']);

        if(empty($request['goods_id'])){
            $message = $request['language']=='cn'?'商品id不能为空':'Parameter error';
            apiResponse('error',$message);
        }
        if($request['type']!=1 && $request['type']!=2){
            $message = $request['language']=='cn'?'类型错误':'Parameter error';
            apiResponse('error',$message);
        }

        $goods_info = M('Goods')->where(array('id'=>$request['goods_id']))->field('is_shelves')->find();
        if(empty($goods_info)){
            $message = $request['language']=='cn'?'该商品不存在':'This product is not exist ';
            apiResponse('error',$message);
        }

        if($goods_info['is_shelves']==2  && $request['type'] == 1){
            $message = $request['language']=='cn'?'该商品已后台下架,禁止上架':'Prohibition shelves';
            apiResponse('error',$message);
        }

        $where['id'] = $request['goods_id'];
        $data['is_shelves']  = $request['type']==1?1:0;
        $data['update_time'] = time();
        $result = M('Goods') ->where($where) -> data($data) -> save();
        if($result){
            $message = $request['language']=='cn'?'操作成功':'Successful operation';
            apiResponse('success',$message);
        }else{
            $message = $request['language']=='cn'?'操作失败':'Operation failed';
            apiResponse('error',$message);
        }
    }
    /* 商家首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID   m_id  可以为空
     * 商家ID   merchant_id
     * 分页参数 p
     */
    public function merchantHome($request = array()){
        //商家ID不能为空
        if(empty($request['merchant_id'])){
            apiResponse('error','商家ID不能为空');
        }

        //商家ID不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }
        //获取默认搜索词
        $config = D('Config')->parseList();
        $result['app_search'] = $config['APP_SEARCH']['CN'];
        //用户ID可以为空
        if($request['m_id']){
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
            $result['un_read_num'] = '' . $un_read_num;
        }else{
            $result['un_read_num'] = 0;
        }
        //查询商家信息
        $where['id'] = $request['merchant_id'];
        $where['status'] = array('neq',9);
        $result['merchant'] = M('Merchant') ->where($where) ->field('id as merchant_id, easemob_account, merchant_name, head_pic') ->find();

        if(empty($result['merchant'])){
            $result['merchant'] = array();
            apiResponse('success','商家信息不存在',$result);
        }
//        //查询商家头像
//        $path = M('File')->where(array('id'=>$result['merchant']['head_pic']))->getField('path');
//        $result['merchant']['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';
        $result['merchant']['easemob_account']  = '8001';
        $result['merchant']['head_pic'] = C('API_URL').'/Uploads/Member/service.png';

        //判断用户是否已经收藏了该商家
        if($request['m_id']){
            $collect_res = M('Collect')->where(array('type'=>1,'handle_id'=>$request['merchant_id'],'m_id'=>$request['m_id'],'status'=>array('neq',9)))->find();
            $result['merchant']['is_collect'] = $collect_res?'1':'0';
        }else{
            $result['merchant']['is_collect'] = '0';
        }
        //查询商家收藏人数
        unset($where);
        $where['type'] = 1;
        $where['handle_id'] = $request['merchant_id'];
        $where['status'] = array('neq',9);
        $result['merchant']['collect'] = M('Collect') ->where($where) ->count();
        //查询商家商家商品
        unset($where);
        $where['merchant_id'] = $request['merchant_id'];
        $where['is_shelves'] = 1;
        $where['audit_status'] = array('IN',array(0,1));

        $test = M('Goods') ->where($where) -> field('id as goods_id, cn_goods_name as goods_name, goods_pic, sales') ->order('create_time desc') ->select();

        $count = 0;
        foreach($test as $k =>$v){
            unset($where);
            $where['goods_id'] = $v['goods_id'];
            $where['status'] = array('neq',9);
            $price = M('GoodsProduct') ->where($where) ->field('id as product_id, cn_price as price') ->order('cn_price asc') ->find();

            if(!$price){
                continue;
            }
            $count = $count + 1;
        }

        $result['merchant']['count'] = $count?$count.'':'0';
        //获取商家商品信息
        unset($where);
        $where['merchant_id'] = $request['merchant_id'];
        $where['is_shelves'] = 1;
        $where['audit_status'] = array('IN',array(0,1));
        $result['merchant']['goods'] = M('Goods') ->where($where) -> field('id as goods_id, cn_goods_name as goods_name, goods_pic, sales')
            -> order('create_time desc') -> page($request['p'].',10') -> select();

        //获取商家商品数量
        if(empty($result['merchant']['goods'])){
            $result['merchant']['goods'] = array();
            $result['merchant']['count'] = $count?$count.'':'0';
            apiResponse('success','该商家未发布商品',$result);
        }

        //获取商品图片和价格
        foreach($result['merchant']['goods'] as $k =>$v){
            $goods_pic = explode(',',$result['merchant']['goods'][$k]['goods_pic']);
            $path = M('File')->where(array('id'=>$goods_pic[0]))->getField('path');
            $result['merchant']['goods'][$k]['goods_pic'] = $path?C('API_URL').$path:'';

            unset($where);
            $where['goods_id'] = $result['merchant']['goods'][$k]['goods_id'];
            $where['status'] = array('neq',9);
            $price = M('GoodsProduct') ->where($where) ->field('id as product_id, cn_price as price') ->order('cn_price asc') ->find();

            if($price){
                $result['merchant']['goods'][$k]['price'] = $price['price']?$price['price']:'0.00';
            }else{
                unset($result['merchant']['goods'][$k]);
                continue;
            }
            unset($where);
            $where['g_id'] = $v['goods_id'];
            $comment = M('Evaluate') ->where($where) ->count();
            if($comment != 0){
                $result['merchant']['goods'][$k]['comment'] = $comment;
            }else{
                $result['merchant']['goods'][$k]['comment'] = 0;
            }
        }


        $result['merchant']['goods'] = array_values($result['merchant']['goods']);
        apiResponse('success','',$result);
    }
}