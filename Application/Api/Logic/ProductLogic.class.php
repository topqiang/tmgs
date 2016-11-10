<?php
namespace Api\Logic;
/**
 * Class ProductLogic
 * @package Api\Logic
 */
class ProductLogic extends BaseLogic{
    /**
     * @param array $request
     * 商品详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     * 商品id：goods_id
     */
    public function productDetails($request = array()){

        //验证参数的合法性
        if(empty($request['goods_id'])){
            apiResponse('error','商品ID不能为空');
        }
        if(!empty($request['m_id'])){
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
            $result_data['un_read_num'] = ''.$un_read_num;
        }else{
            $result_data['un_read_num'] = '0';
        }
        //获取商品详细信息
        $where['id'] = $request['goods_id'];
        $where['is_shelves'] = 1;
        $goods_info = M('Goods')->where($where)
            ->field('id as goods_id,merchant_id,unit_id,thr_g_t_id,cn_goods_name as goods_name,cn_goods_brands as goods_brands,cn_delivery_cost as delivery_cost,cn_goods_introduction as goods_introduction,goods_pic')
            ->find();
        if(empty($goods_info)){
            $result['goods_info'] = array();
            apiResponse('success','商品详情有误',$result);
        }

        $unit = M('Unit') ->where(array('id'=>$goods_info['unit_id'])) ->field('id as unit_id, unit') ->find();

        $goods_info['unit_id'] = $unit['unit'];

        //获取商家基本信息，判断商家是否存在

        $merchant_info = M('Merchant')->where(array('id'=>$goods_info['merchant_id']))->field('id as merchant_id,head_pic,merchant_name')->find();

        if(empty($merchant_info)){
            apiResponse('error','商家信息有误');
        }
        $goods_pic = explode(',',$goods_info['goods_pic']);
        $pic = array();
        $i = 0;
        foreach($goods_pic as $k =>$v){
            $path = M('File')->where(array('id'=>$v))->getField('path');
            if($path){
                $pic[$i]['goods_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default';
                $i = $i+1;
            }
        }
        $goods_info['goods_pic']  = $pic;
        //获取价格
        unset($where);
        $where['goods_id'] = $goods_info['goods_id'];
        $price = M('GoodsProduct') ->where($where) ->field('cn_price as price') ->order('cn_price asc') ->find();
        $goods_info['price'] = $price['price']?$price['price']:'0.00';

        //商品详情处理
        preg_match_all('/src=\"\/?(.*?)\"/',$goods_info['goods_introduction'],$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $goods_info['goods_introduction'] = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$goods_info['goods_introduction']);
            }
        }

        //获取商品的好评 中评 差评数量
        $comment_goods_num = M('Evaluate')->where(array('g_id'=>$goods_info['goods_id'],'rank'=>1))->count();
        $goods_info['comment_good_num'] = $comment_goods_num?$comment_goods_num.'':'0';
        $comment_middle_num = M('Evaluate')->where(array('g_id'=>$goods_info['goods_id'],'rank'=>2))->count();
        $goods_info['comment_middle_num'] = $comment_middle_num?$comment_middle_num.'':'0';
        $comment_bad_num = M('Evaluate')->where(array('g_id'=>$goods_info['goods_id'],'rank'=>3))->count();
        $goods_info['comment_bad_num'] = $comment_bad_num?$comment_bad_num.'':'0';
        $goods_info['comment_num'] = ''.($goods_info['comment_good_num']+$goods_info['comment_middle_num']+$goods_info['comment_bad_num']);


        $goods_info['return_price'] = '0.00';
        $result_data['goods_info'] = $goods_info;

        //商家信息处理
        $path = M('File')->where(array('id'=>$merchant_info['head_pic']))->getField('path');
        $merchant_info['head_pic'] = $path?C('API_URL').$path:'';
        //获取商品数量
        unset($where);
        $where['merchant_id'] = $merchant_info['merchant_id'];
        $where['is_shelves']  = 1;
        $count = M('Goods')->where($where)->count();
        $merchant_info['goods_count'] = $count?$count.'':'0';
        //获取收藏人数
        $count = M('Collect')->where(array('handle_id'=>$merchant_info['merchant_id'],'type'=>1,'status'=>array('neq',9)))->count();
        $merchant_info['collect_count'] = $count?$count.'':'0';

        //获取好评中评差评总数
        $h_count = M('Evaluate')->where(array('merchant_id'=>$merchant_info['merchant_id'],'rank'=>1))->count();
        $z_count = M('Evaluate')->where(array('merchant_id'=>$merchant_info['merchant_id'],'rank'=>2))->count();
        $c_count = M('Evaluate')->where(array('merchant_id'=>$merchant_info['merchant_id'],'rank'=>3))->count();
        $all_count = $h_count+$z_count+$c_count;
        $merchant_info['goods_comment']  = $h_count.'';
        $merchant_info['middle_comment'] = $z_count.'';
        $merchant_info['bad_comment']    = $c_count.'';

        //分别获取近7天的好评 中评 差评数量
        unset($where);
        $time = strtotime("-7 day");
        $h_count_n = M('Evaluate')->where(array('merchant_id'=>$merchant_info['merchant_id'],'rank'=>1,'evaluate_time'=>array('egt',$time)))->count();
        $z_count_n = M('Evaluate')->where(array('merchant_id'=>$merchant_info['merchant_id'],'rank'=>2,'evaluate_time'=>array('egt',$time)))->count();
        $c_count_n = M('Evaluate')->where(array('merchant_id'=>$merchant_info['merchant_id'],'rank'=>3,'evaluate_time'=>array('egt',$time)))->count();
        $all_count_n = $h_count_n+$z_count_n+$c_count_n;
        if($h_count_n==0){
            $merchant_info['goods_count_flag'] = '1';
        }else{
            if($h_count_n/$all_count_n>=$h_count/$all_count){
                $merchant_info['goods_count_flag'] = '1';
            }else{
                $merchant_info['goods_count_flag'] = '0';
            }
        }
        if($z_count_n==0){
            $merchant_info['middle_count_flag'] = '1';
        }else{
            if($z_count_n/$all_count_n>=$z_count/$all_count){
                $merchant_info['middle_count_flag'] = '1';
            }else{
                $merchant_info['middle_count_flag'] = '0';
            }
        }
        if($c_count_n==0){
            $merchant_info['bad_count_flag'] = '0';
        }else{
            if($c_count_n/$all_count_n>=$c_count/$all_count){
                $merchant_info['bad_count_flag'] = '1';
            }else{
                $merchant_info['bad_count_flag'] = '0';
            }
        }
        //获取商家的环信账号
        $easemob_account = M('Merchant')->where(array('id'=>$merchant_info['merchant_id']))->getField('easemob_account');
        $merchant_info['easemob_account'] = $easemob_account?$easemob_account:'';

        $result_data['merchant_info'] = $merchant_info;

        //获取一条评论
        $comment_info = M('Evaluate')->where(array('g_id'=>$goods_info['goods_id']))->order('evaluate_time desc')->find();
        if(empty($comment_info)){
            $result_data['comment'] = array();
        }else{
            $member_info = M('Member')->where(array('id'=>$comment_info['m_id']))->field('nickname,head_pic')->find();
            $comment['nickname'] = $member_info['nickname']?$member_info['nickname']:'淘米用户';
            $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
            $comment['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
            $comment['review'] = $comment_info['review'];
            $comment['rand'] = $comment_info['rank'];
            $comment['evaluate_time'] = date('Y-m-d',$comment_info['evaluate_time']);
            $comment['goods_attr'] = $comment_info['goods_attr'];

            if($comment_info['evaluate_pic']){
                $comment_pic = explode(',',$comment_info['evaluate_pic']);
                foreach($comment_pic as $k =>$v){
                    $path = M('File')->where(array('id'=>$v))->getField('path');
                    $pic_c[$k]['comment_pic'] = $path?C('API_URL').$path:'';
                }
                $comment['comment_pic'] = $pic_c;
            }else{
                $comment['comment_pic'] = array();
            }

            $result_data['comment'] = $comment;
        }
        unset($where);
        $where['goods_id'] = $request['goods_id'];
        $where['status']   = array('neq',9);
        $product = M('goods_product') ->where($where) ->field('id as product_id, attr_key_group') ->select();
        if(!$product){
            apiResponse('error','该商品属性有误');
        }
        $attr_con_array = array();
        $attr_array = array();
        foreach($product as $k =>$v){
            $pro = explode(',',$v['attr_key_group']);
            foreach($pro as $key =>$val){
                unset($where);
                $where['id'] = $val;
                $where['status'] = array('neq',9);
                $attr_con = M('AttributeContent') ->where($where) ->field('id as attr_con_id, attr_id, cn_attr_con_name as attr_con_name') ->find();
                $attr = M('Attribute') ->where(array('id'=>$attr_con['attr_id'],'status'=>array('neq',9))) ->field('id as attr_id, cn_attr_name as attr_name') ->find();
                $attr_con_array[] = $attr_con['attr_con_id'];
                $attr_array[] = $attr['attr_id'];
            }
        }
        foreach($attr_array as $k => $v){
            unset($where);
            $where['id'] = $v;
            $where['status'] = array('neq',9);
            $goods_attr_group[$k] = M('Attribute') ->where($where) ->field('id as attr_id, cn_attr_name as attr_name') ->find();
            unset($where);
            $where['attr_id']  = $goods_attr_group[$k]['attr_id'];
            $where['id'] = array('IN',$attr_con_array);
            $where['status'] = array('neq',9);
            $goods_attr_group[$k]['attr_con'] = M('AttributeContent') ->where($where)->field('id as attr_con_id, cn_attr_con_name as attr_con_name') ->select();
        }
        $result_data['goods_attr_group'] = $goods_attr_group;

        if($goods_attr_group){
            $attr_str = '';
            foreach($goods_attr_group as $k =>$v){
                $attr_str = $attr_str.$v['attr_name'].' ';
            }
            $result_data['attr_flag'] = '1';
            $result_data['attr_str'] = $attr_str;
        }else{
            $result_data['attr_flag'] = '0';
            $result_data['attr_str'] = '选择';
        }
        //获取产品列表
        $product_list = M('GoodsProduct') ->where(array('goods_id'=>$goods_info['goods_id'],'status'=>1))
            ->field('id as product_id,attr_key_group,cn_price as price') ->select();

        if(empty($product_list)){
            $product_list = array();
        }
        $result_data['product_list'] = $product_list;

        //查询是否已经收藏
        if($request['m_id']){
            $is_collect = M('Collect')->where(array('m_id'=>$request['m_id'],'handle_id'=>$goods_info['goods_id'],'type'=>2,'status'=>array('neq',9)))->find();
            $result_data['is_collect'] = $is_collect?'1':'0';
        }else{
            $result_data['is_collect'] = '0';
        }

        //加足迹
        if($request['m_id']){
            unset($where);
            $where['good_id'] = $goods_info['goods_id'];
            $where['u_id'] = $request['m_id'];
            $res = M('Pug')->where($where)->find();
            if(!$res){
                unset($data);
                $data['good_id'] = $goods_info['goods_id'];
                $data['u_id']    = $request['m_id'];
                $data['create_time'] = time();
                M('Pug')->data($data)->add();
            }else{
                $data['create_time'] = time();
                M('Pug') ->where($where) ->data($data) ->save();
            }
        }
        apiResponse('success','',$result_data);
    }
}