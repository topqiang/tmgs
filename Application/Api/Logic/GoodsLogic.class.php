<?php
namespace Api\Logic;
/**
 * Class GoodsLogic
 * @package Api\Logic
 */
class GoodsLogic extends BaseLogic{
    /**
     * 商品详情
     * 用户id：m_id
     * 商品id：goods_id
     */
    public function goodsInfo($request = array()){
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
        $where['audit_status'] = array('IN',array(0,1));
        $goods_info = M('Goods')->where($where)
            ->field('id as goods_id,merchant_id,unit_id,thr_g_t_id,cn_goods_name as goods_name,cn_goods_brands as goods_brands,cn_delivery_cost as delivery_cost,cn_goods_introduction as goods_introduction,goods_pic,is_integrity_fourteen,sales')
            ->find();
        if(empty($goods_info)){
            $result_data['goods_info'] = '';
            apiResponse('success','商品详情有误',$result_data);
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
                $pic[$i]['goods_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';
                $i = $i+1;
            }
        }
        $goods_info['goods_pic']  = $pic;
        //获取价格
        unset($where);
        $where['goods_id'] = $goods_info['goods_id'];
        $price = M('GoodsProduct') ->where($where) ->field('cn_price as price, wholesale_prices') ->order('cn_price asc') ->find();
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

        $divide = M('Divide') ->find();
        $goods_info['return_price'] =''.($price['price'] - $price['wholesale_prices']) * (1 - $divide['divide_p']) * $divide['divide_m'];
        $result_data['goods_info'] = $goods_info;

        //商家信息处理
        $path = M('File')->where(array('id'=>$merchant_info['head_pic']))->getField('path');
        $merchant_info['head_pic'] = $path?C('API_URL').$path:'';
        //获取商品数量
        unset($where);
        $where['merchant_id'] = $merchant_info['merchant_id'];
        $where['is_shelves']  = 1;
        $where['audit_status'] = array('IN',array(0,1));
//        $count = M('Goods')->where($where)->count();
//        $merchant_info['goods_count'] = $count?$count.'':'0';
        $merchant_goods = M('Goods')->where($where)->select();
        $count = 0;
        foreach($merchant_goods as $k => $v){
            unset($where);
            $where['goods_id'] = $v['id'];
            $where['status']   = array('neq',9);
            $product = M('GoodsProduct') ->where($where) ->find();
            if(!$product){
                continue;
            }
            $count = $count + 1;
        }
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
        $merchant_info['easemob_account'] = '8001';

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
            apiResponse('error','商品信息设置有误，等待商家重新设置');
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
        $attr_array = array_unique($attr_array);
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
        $goods_attr_group = array_values($goods_attr_group);
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
        $divide = M('Divide') ->find();
        //获取产品列表
        $product_list = M('GoodsProduct') ->where(array('goods_id'=>$goods_info['goods_id'],'status'=>1))
            ->field('id as product_id,attr_key_group, wholesale_prices, cn_price as price') ->select();
        foreach($product_list as $k =>$v){
            $return_price = ($v['price'] - $v['wholesale_prices']) * (1 - $divide['divide_p']) * $divide['divide_m'] ;
            $product_list[$k]['return_price'] = $return_price?$return_price.'':'0.00';
        }

        if(empty($product_list)){
            $product_list = array();
        }
        $result_data['product_list'] = $product_list;
		$result_data['share_url'] = 'http://www.taomim.com/index.php/Api/WapRegister/wapRegister';

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

    /**
     * @param array $request
     * 新版商品详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     * 商品id：goods_id
     */
    public function newGoodsInfo($request = array()){
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
        $where['goods.id'] = $request['goods_id'];
        $where['goods.is_shelves'] = 1;
        $where['goods.audit_status'] = array('IN',array(0,1));
        $goods_info = M('Goods') ->alias('goods')
            ->where($where)
            ->field('goods.id as goods_id, goods.merchant_id, goods.unit_id, goods.thr_g_t_id, goods.cn_goods_name as goods_name, goods.cn_goods_brands as goods_brands, goods.cn_delivery_cost as delivery_cost, goods.cn_goods_introduction as goods_introduction, goods.goods_pic, goods.is_integrity_fourteen, goods.sales, unit.unit, MIN(g_p.cn_price) as price, g_p.wholesale_prices, merchant.head_pic as merchant_head_pic, merchant.merchant_name')
            ->join(array(
                'LEFT JOIN  '.C('DB_PREFIX').'goods_product g_p ON g_p.goods_id = goods.id',
                'LEFT JOIN  '.C('DB_PREFIX').'unit unit ON unit.id = goods.unit_id',
                'LEFT JOIN  '.C('DB_PREFIX').'merchant merchant ON merchant.id = goods.merchant_id',
            ))
            ->find();
        if(empty($goods_info)){
            $result_data['goods_info'] = '';
            apiResponse('success','商品详情有误',$result_data);
        }

        $goods_pic = explode(',',$goods_info['goods_pic']);
        $pic = array();
        foreach($goods_pic as $k =>$v){
            $path = M('File')->where(array('id'=>$v))->getField('path');
            if($path){
                $pic[$k]['goods_pic'] = $path?C('API_URL').$path:'';
            }
        }
        $goods_info['goods_pic']  = $pic;
        $merchant_path = M('File') ->where(array('id'=>$goods_info['merchant_head_pic'])) ->getField('path');
        $goods_info['merchant_head_pic'] = $merchant_path?C("API_URL").$merchant_path:C("API_URL").'/Uploads/Merchant/default.png';
        //商品详情处理
        preg_match_all('/src=\"\/?(.*?)\"/',$goods_info['goods_introduction'],$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $goods_info['goods_introduction'] = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$goods_info['goods_introduction']);
            }
        }
        $divide = M('Divide') ->find();
        $goods_info['return_price'] =''.($goods_info['price'] - $goods_info['wholesale_prices']) * (1 - $divide['divide_p']) * $divide['divide_m'];

        //获取商品的好评 中评 差评数量
        $comment_goods_num = M('Evaluate')->where(array('g_id'=>$goods_info['goods_id'],'rank'=>1))->count();
        $goods_info['comment_good_num'] = $comment_goods_num?$comment_goods_num.'':'0';
        $comment_middle_num = M('Evaluate')->where(array('g_id'=>$goods_info['goods_id'],'rank'=>2))->count();
        $goods_info['comment_middle_num'] = $comment_middle_num?$comment_middle_num.'':'0';
        $comment_bad_num = M('Evaluate')->where(array('g_id'=>$goods_info['goods_id'],'rank'=>3))->count();
        $goods_info['comment_bad_num'] = $comment_bad_num?$comment_bad_num.'':'0';
        $goods_info['comment_num'] = ''.($goods_info['comment_good_num']+$goods_info['comment_middle_num']+$goods_info['comment_bad_num']);

        //获取商品数量
        unset($where);
        $where['merchant_id'] = $goods_info['merchant_id'];
        $where['is_shelves']  = 1;
        $where['audit_status'] = array('IN', array(0,1));
        $goods_count = M('Goods')->where($where)->count();
        $merchant_info['goods_count'] = $goods_count?$goods_count.'':'0';

        //获取收藏人数
        $count = M('Collect')->where(array('handle_id'=>$goods_info['merchant_id'],'type'=>1,'status'=>array('neq',9)))->count();
        $merchant_info['collect_count'] = $count?$count.'':'0';

        //获取好评中评差评总数
        $h_count = M('Evaluate')->where(array('merchant_id'=>$goods_info['merchant_id'],'rank'=>1))->count();
        $z_count = M('Evaluate')->where(array('merchant_id'=>$goods_info['merchant_id'],'rank'=>2))->count();
        $c_count = M('Evaluate')->where(array('merchant_id'=>$goods_info['merchant_id'],'rank'=>3))->count();
        $all_count = $h_count+$z_count+$c_count;
        $merchant_info['goods_comment']  = $h_count.'';
        $merchant_info['middle_comment'] = $z_count.'';
        $merchant_info['bad_comment']    = $c_count.'';

        //分别获取近7天的好评 中评 差评数量
        unset($where);
        $time = strtotime("-7 day");
        $h_count_n = M('Evaluate')->where(array('merchant_id'=>$goods_info['merchant_id'],'rank'=>1,'evaluate_time'=>array('egt',$time)))->count();
        $z_count_n = M('Evaluate')->where(array('merchant_id'=>$goods_info['merchant_id'],'rank'=>2,'evaluate_time'=>array('egt',$time)))->count();
        $c_count_n = M('Evaluate')->where(array('merchant_id'=>$goods_info['merchant_id'],'rank'=>3,'evaluate_time'=>array('egt',$time)))->count();
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
        $easemob_account = M('Merchant')->where(array('id'=>$goods_info['merchant_id']))->getField('easemob_account');
        $merchant_info['easemob_account'] = '8001';

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
        $result_data['goods_info'] = $goods_info;

        unset($where);
        $where['goods_id'] = $request['goods_id'];
        $where['status']   = array('neq',9);
        $product = M('goods_product') ->where($where) ->field('id as product_id, attr_key_group') ->select();
        if(!$product){
            apiResponse('error','商品信息设置有误，等待商家重新设置');
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
        $attr_array = array_unique($attr_array);
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
        $goods_attr_group = array_values($goods_attr_group);
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

        $divide = M('Divide') ->find();
        //获取产品列表
        $product_list = M('GoodsProduct') ->where(array('goods_id'=>$goods_info['goods_id'],'status'=>1))
            ->field('id as product_id,attr_key_group, wholesale_prices, cn_price as price') ->select();
        foreach($product_list as $k =>$v){
            $return_price = ($v['price'] - $v['wholesale_prices']) * (1 - $divide['divide_p']) * $divide['divide_m'] ;
            $product_list[$k]['return_price'] = $return_price?$return_price.'':'0.00';
        }

        if(empty($product_list)){
            $product_list = array();
        }
        $result_data['product_list'] = $product_list;
        $result_data['share_url'] = 'http://www.taomim.com/index.php/Api/WapRegister/wapRegister';

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



    /**
     * 商家商品列表
     * 商家ID         merchant_id
     * 搜索名称       goods_name
     * 按照销量排序   sales
     * 按照价格排序   price
     * 按照创建时间   create_time
     * 分页参数       p
     */
    public function merGoodsList($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //搜索名称不能为空
        if($request['goods_name']){
            $where['goods.cn_goods_name'] = array('like','%'.$request['goods_name'].'%');
        }
        if(!$request['p']){
            apiResponse('error','分页参数不能为空');
        }
        $where['goods.merchant_id'] = $request['merchant_id'];
        $where['goods.audit_status'] = array('neq',9);
        $where['goods.status'] = array('neq',9);
        //排序方式
        if($request['sales']){
            $order = 'goods.sales desc';
        }elseif($request['price']){
            $order = 'price asc';
        }elseif($request['time']){
            $order = 'goods.create_time desc';
        }else{
            $order = 'goods.sort desc, goods.id desc';
        }
        //搜索商品数量
        $count = M('Goods') -> alias('goods')->where($where) ->count();
        //多表查询商品列表
        $goods_list = M('Goods')
            ->alias('goods')
            ->where($where)
            ->field('goods.id as goods_id, goods.cn_goods_name as goods_name, goods.goods_pic, goods.is_shelves, goods.sales, goods.create_time, MIN(g_p.cn_price) as price , goods.fir_g_t_id, goods.sec_g_t_id, goods.thr_g_t_id')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'goods_product g_p ON g_p.goods_id = goods.id',
            ))
            ->order($order)
            ->group('goods.id')
            ->page($request['p'].',15')
            ->select();


        if(!$goods_list){
            $goods_list = array();
            apiResponse('success','',$goods_list);
        }

        foreach($goods_list as $k =>$v){
            $goods_pic = explode(',',$v['goods_pic']);
            $path = M('File') ->where(array('id'=>$goods_pic[0])) ->getField('path');
            $goods_list[$k]['goods_pic'] = $path?C("API_URL").$path:'';

            if(!$v['price']){
                $goods_list[$k]['price'] = '0.00';
            }
        }

        $result['goods_list'] = array_values($goods_list);
        $result['count'] = $count;
        apiResponse('success','',$result);
    }

    /**
     * @param array $request
     * 商家添加商品
     * 商家ID      merchant_id
     * 一级分类ID  fir_g_t_id
     * 二级分类ID  sec_g_t_id
     * 三级分类ID  thr_g_t_id
     * 商品名称    goods_name
     * 商品品牌    goods_brands
     * 商品运费    delivery_cost
     * 供应商信息  supply_info
     * 货号信息    article_number
     * 出售价格    price
     * 批发价格    wholesale_prices
     * 商品简介    goods_introduction
     * 上传图片    goods_pic
     */
    public function addGoods($request = array()){
        if(empty($request['merchant_id']) || !isset($request['merchant_id'])) apiResponse('error','商家ID不能为空');
        if(empty($request['fir_g_t_id']) || !isset($request['fir_g_t_id'])) apiResponse('error','一级分类ID不能为空');
        if(empty($request['sec_g_t_id']) || !isset($request['sec_g_t_id'])) apiResponse('error','二级分类ID不能为空');
        if(empty($request['thr_g_t_id']) || !isset($request['thr_g_t_id'])) apiResponse('error','三级分类ID不能为空');
        if(empty($request['goods_name']) || !isset($request['goods_name'])) apiResponse('error','商品名称不能为空');
        if(empty($request['goods_brands']) || !isset($request['goods_brands'])) apiResponse('error','商品品牌不能为空');
        if(empty($request['delivery_cost']) || !isset($request['delivery_cost'])) apiResponse('error','商品运费不能为空');
        if(empty($request['article_number']) || !isset($request['article_number'])) apiResponse('error','货号不能为空');
        if(empty($request['price']) || !isset($request['price'])) apiResponse('error','出售价格不能为空');
        if(empty($request['wholesale_prices']) || !isset($request['wholesale_prices'])) apiResponse('error','批发价格不能为空');
        if(empty($request['goods_introduction']) || !isset($request['goods_introduction'])) apiResponse('error','批发价格不能为空');
        //上传头像
        if (!empty($_FILES['goods_pic']['name'])) {
            $res = api('UploadPic/upload', array(array('save_path' => 'Goods')));
            $goods_pic = array();
            foreach ($res as $value) {
                $goods_pic[] = $value['id'];
            }
            $data['goods_pic'] = implode(',',$goods_pic);
        }
        $data['merchant_id'] = $request['merchant_id'];
        $data['fir_g_t_id']  = $request['fir_g_t_id'];
        $data['sec_g_t_id']  =$request['sec_g_t_id'];
        $data['thr_g_t_id']  = $request['thr_g_t_id'];
        $data['cn_goods_name']  = $request['goods_name'];
        $data['cn_goods_brands'] = $request['goods_brands'];
        $data['cn_delivery_cost'] = $request['delivery_cost'];
        $data['supply_info'] = $request['supply_info'];
        $data['article_number'] = $request['article_number'];
        $data['cn_goods_introduction'] = $request['goods_introduction'];
        $data['create_time'] = time();
        $data['cn_price'] = $request['price'];
        $data['wholesale_prices'] = $request['wholesale_prices'];
        $add = M('Goods')->data($data)->add();
        if(!$add)apiResponse('error','添加商品失败');
        $gTypeJson = $_POST['g_type_json'];
        if($gTypeJson){
            $gTypeArr = json_decode($gTypeJson,true);
            foreach($gTypeArr['attr_group'] as $k => $v){
                $groupData['attr_key_group'] = $v['attr_key_group'];
                $groupData['wholesale_prices'] = $v['wholesale_prices'];
                $groupData['cn_price'] = $v['cn_price'];
                $groupData['status'] = 1;
                $groupData['goods_id'] = $add;
                $groupData['merchant_id'] = $gTypeArr['merchant_id'];
                M('GoodsProduct')->data($groupData)->add();
            }
        }
        apiResponse('success','添加商品成功');
    }
    /**
     * @param array $request
     * 商家修改商品
     * 商家ID  merchant_id
     * 一级分类ID  fir_g_t_id
     * 二级分类ID  sec_g_t_id
     * 三级分类ID  thr_g_t_id
     * 商品名称    goods_name
     * 商品品牌    goods_brands
     * 商品运费    delivery_cost
     * 供应商信息  supply_info
     * 货号信息    article_number
     * 出售价格    price
     * 批发价格    wholesale_prices
     * 商品简介    goods_introduction
     * 上传图片    goods_pic
     */
    public function modifyGoods($request = array()){

        //商品ID不能为空
        if(!$request['goods_id']){
            apiResponse('error','商品ID不能为空');
        }
        //一级分类可以为空
        if($request['fir_g_t_id']){
            $data['fir_g_t_id'] = $request['fir_g_t_id'];
        }
        //二级分类ID为空
        if($request['sec_g_t_id']){
            $data['sec_g_t_id'] = $request['sec_g_t_id'];
        }
        //三级分类ID为空
        if($request['thr_g_t_id']){
            $data['thr_g_t_id'] = $request['thr_g_t_id'];
        }
        //商品名称为空
        if($request['goods_name']){
            $data['cn_goods_name'] = $request['goods_name'];
        }
        //商品品牌为空
        if($request['goods_brands']){
            $data['cn_goods_brands'] = $request['goods_brands'];
        }
        //商品配送费为空
        if($request['delivery_cost']){
            $data['cn_delivery_cost'] = $request['delivery_cost'];
        }
        //商品供应商为空
        if($request['supply_info']){
            $data['supply_info'] = $request['supply_info'];
        }
        //商品货号为空
        if($request['article_number']){
            $data['article_number'] = $request['article_number'];
        }
        //出售价格不能为空
        if($request['price']){
            $data['cn_price'] = $request['price'];
        }
        //批发价格不能为空
        if($request['wholesale_prices']){
            $data['wholesale_prices'] = $request['wholesale_prices'];
        }
        //批发价格不能为空
        if($request['goods_introduction']){
            $data['cn_goods_introduction'] = $request['goods_introduction'];
        }



        //上传头像
        if (!empty($_FILES['goods_pic']['name'])) {
            $res = api('UploadPic/upload', array(array('save_path' => 'Goods')));
            $goods_pic = array();
            foreach ($res as $value) {
                $goods_pic[] = $value['id'];
            }
            $data['goods_pic'] = implode(',',$goods_pic);
        }
        if(!empty($request['old_id'])) $data['goods_pic'] = trim($data['goods_pic'].','.trim($request['old_id'],','),',');

        $where['id'] = $request['goods_id'];
        $data['update_time'] = time();
        $result = M('Goods') ->where($where) ->data($data) ->save();
        if(!$result)apiResponse('error','修改商品失败');
        $gTypeJson = $_POST['g_type_json'];
        if($gTypeJson){
            M('GoodsProduct')->where(array('goods_id'=>$request['goods_id']))->delete();
            $gTypeArr = json_decode($gTypeJson,true);
            foreach($gTypeArr['attr_group'] as $k => $v){
                $groupData['attr_key_group'] = $v['attr_key_group'];
                $groupData['wholesale_prices'] = $v['wholesale_prices'];
                $groupData['cn_price'] = $v['cn_price'];
                $groupData['status'] = 1;
                $groupData['goods_id'] = $request['goods_id'];
                $groupData['merchant_id'] = $gTypeArr['merchant_id'];
                M('GoodsProduct')->data($groupData)->add();
            }
        }
        apiResponse('success','修改商品成功');
    }

    /**
     * @param array $request
     * 删除商家商品
     * 商家ID  merchant_id
     * 商品ID  goods_id
     */
    public function deleteGoods($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['goods_id']){
            apiResponse('error','商品ID不能为空');
        }
        $where['merchant_id'] = $request['merchant_id'];
        $where['id']    = $request['goods_id'];
        $where['status']      = array('neq',9);
        $goods = M('Goods') ->where($where) ->find();
        if(!$goods){
            apiResponse('error','商品信息有误');
        }
        $data['status'] = 9;
        $result = M('Goods') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','删除失败');
        }
        apiResponse('success','删除成功');
    }
    /**
     * @param array $request
     * 商品上下架操作
     * 商品ID    goods_id
     * 操作类型  type  1  上架  2  下架
     */
    public function goodsFrame($request = array()){
        if(!$request['goods_id']){
            apiResponse('error','商品ID不能为空');
        }
        if($request['type'] != 1&&$request['type'] != 2){
            apiResponse('error','操作有误');
        }
        $where['id'] = $request['goods_id'];
        if($request['type'] == 1){
            $data['is_shelves'] = 1;
        }else{
            $data['is_shelves'] = 0;
        }
        $data['update_time'] = time();
        $result = M('Goods') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','操作失败');
        }
        apiResponse('success','操作成功');
    }

    /**s
     * @param array $request
     * 三级分类列表
     */
    public function typeList($request = array()){
        $GoodsType = M('GoodsType');
        if(empty($request['type_name']))apiResponse('error','',[]);
        $arr = array();
        $where['cn_type_name'] = array('like','%'.trim($request['type_name']).'%');
        $where['type'] = 3;
        $result = $GoodsType ->where($where) ->field('id, cn_type_name ,parent_id ') ->order('sort desc, create_time desc') ->select();
        if($result){
            foreach($result as $k => $v){
                $arr[$k]['thr_g_t_id']  = $v['id']; // 三级分类
                $arr[$k]['thr_t_name'] = $v['cn_type_name'] ; // 三级名称
                $sec_g_t_id = $GoodsType -> where(array('id'=>$v['parent_id'])) -> field('id,parent_id,cn_type_name') -> find();
                $arr[$k]['sec_g_t_id'] = $sec_g_t_id['id']; // 二级分类
                $arr[$k]['sec_t_name'] = $sec_g_t_id['cn_type_name']; // 二级名称
                $fir_g_t_id = $GoodsType -> where(array('id'=>$sec_g_t_id['parent_id'])) -> field('id,cn_type_name') -> find();
                $arr[$k]['fir_g_t_id'] = $fir_g_t_id['id']; // 一级分类
                $arr[$k]['fir_t_name'] = $fir_g_t_id['cn_type_name']; // 一级名称
            }
            unset($result);
            $result = $arr;
        }else{
            $result = array();
        }
        apiResponse('success','',$result);
    }

    /**
     * @param array $request
     * 商品属性列表
     * 商家ID      merchant_id
     * 一级分类    fir_g_t_id
     * 二级分类    sec_g_t_id
     * 三级分类    thr_g_t_id
     */
    public function goodsAttributeList($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //一级分类不能为空
        if(!$request['fir_g_t_id']){
            apiResponse('error','一级分类ID不能为空');
        }
        //二级分类不能为空
        if(!$request['sec_g_t_id']){
            apiResponse('error','二级分类ID不能为空');
        }
        //三级分类不能为空
        if(!$request['thr_g_t_id']){
            apiResponse('error','三级分类ID不能为空');
        }

        $where['is_merchant'] = array('IN','0,'.$request['merchant_id']);
        $where['fir_g_t_id']  = $request['fir_g_t_id'];
        $where['sec_g_t_id']  = $request['sec_g_t_id'];
        $where['thr_g_t_id']  = $request['thr_g_t_id'];
        $where['status']      = array('neq',9);
        $result = M('Attribute') ->where($where) ->field('id as attr_id, cn_attr_name as attr_name ,is_merchant') ->order('is_merchant asc') ->select();
        if(!$result){
            $result = array();
            apiResponse('success','',$result);
        }
        foreach($result as $k => $v){
            unset($where);
            $where['attr_id'] = $v['attr_id'];
            $where['is_merchant'] = array('IN','0,'.$request['merchant_id']);
            $where['status']  = array('neq',9);
            $result[$k]['attr_content'] = M('AttributeContent') ->where($where) ->field('id as attr_con_id ,cn_attr_con_name as attr_con_name, is_merchant') ->select();
            if(!$result[$k]['attr_content']){
                $result[$k]['attr_content'] = array();
            }
        }
        apiResponse('success','',$result);
    }

    /**
     * @param array $request
     * 新增商品属性组
     * 商家ID        merchant_id
     * 一级ID        fir_g_t_id
     * 二级ID        sec_g_t_id
     * 三级ID        thr_g_t_id
     * 属性名ID      attr_name
     */
    public function addAttribute($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //一级ID不能为空
        if(!$request['fir_g_t_id']){
            apiResponse('error','一级ID不能为空');
        }
        //二级ID不能为空
        if(!$request['sec_g_t_id']){
            apiResponse('error','二级ID不能为空');
        }
        //三级ID不能为空
        if(!$request['thr_g_t_id']){
            apiResponse('error','三级ID不能为空');
        }
        //属性名ID不能为空
        if(!$request['attr_name']){
            apiResponse('error','属性组名称不能为空');
        }
        $data['fir_g_t_id'] = $request['fir_g_t_id'];
        $data['sec_g_t_id'] = $request['sec_g_t_id'];
        $data['thr_g_t_id'] = $request['thr_g_t_id'];
        $data['cn_attr_name'] = $request['attr_name'];
        $data['is_merchant'] = $request['merchant_id'];
        $data['create_time'] = time();
        $result = M('Attribute') ->add($data);
        if(!$result){
            apiResponse('error','新增属性组失败');
        }
        apiResponse('success','新增成功');
    }

    /**
     * @param array $request
     * 新增商品属性值
     * 商家ID        merchant_id
     * 属性组ID      attr_id
     * 属性值名称    json格式   [{"attr_con_name":"这个那个"},{"attr_con_name":"那个这个"}]
     */
    public function addAttributeContent($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['attr_id']){
            apiResponse('error','属性组ID不能为空');
        }
        //购物车ID不能为空   为一个json串
        if (empty($_POST['attr_con_json'])) {
            apiResponse('error','请填写将要添加的属性');
        }
        $attr_con_json = json_decode($_POST['attr_con_json'],true);

        if(!$attr_con_json){
            apiResponse('error','格式转换错误');
        }

        foreach($attr_con_json as $k => $v){
            $data['attr_id'] = $request['attr_id'];
            $data['attr_id'] = $request['attr_id'];
            $data['cn_attr_con_name'] = $v['attr_con_name'];
            $data['is_merchant'] = $request['merchant_id'];
            $data['create_time'] = time();
            $result = M('AttributeContent') ->add($data);
            if(!$result){
                continue;
            }
        }

        apiResponse('success','添加成功');
    }

    /**
     * @param array $request
     * 删除商品属性值
     * 属性值ID      attr_con_id
     * 商家ID        merchant_id
     */
    public function deleteAttributeContent($request = array()){
        //属性值ID不能为空
        if(!$request['attr_con_id']){
            apiResponse('error','属性值ID不能为空');
        }
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        $where['attr_key_group'] = $request['attr_con_id'];
        $where['attr_key_group'] = array('like','%,'.$request['attr_con_id'].'%');
        $where['attr_key_group'] = array('like','%'.$request['attr_con_id'].',%');
        $where['attr_key_group'] = array('like','%,'.$request['attr_con_id'].',%');
        $where['_logic']         = 'OR';
        $result = M('GoodsProduct') ->where($where) ->find();
        if($result){
            apiResponse('error','如需删除属性,请先删除或修改该属性下的产品属性');
        }
        unset($where);
        $where['is_merchant'] = $request['merchant_id'];
        $where['id'] = $request['attr_con_id'];
        $res = M('AttributeContent') ->where($where) ->find();
        if(!$res){
            apiResponse('error','该属性值有误');
        }
        $data['status'] = 9;
        $res = M('AttributeContent') ->where($where) ->data($data) ->save();
        if(!$res){
            apiResponse('error','删除属性值失败');
        }
        apiResponse('success','删除成功');
    }


    /**
     * @param array $request
     * 删除商品属性值
     * 商家ID    merchant_id
     * 属性名称  attr_id
     */
    public function deleteAttribute($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //属性名称ID不能为空
        if(!$request['attr_id']){
            apiResponse('error','属性名称ID不能为空');
        }
        $where['is_merchant'] = $request['merchant_id'];
        $where['attr_id']     = $request['attr_id'];
        $where['status']      = array('neq',9);
        $count = M('AttributeContent') ->where($where) ->count();
        if($count > 0){
            apiResponse('error','如需删除属性名称, 请先删除该属性下的所有属性值');
        }
        unset($where);
        $where['is_merchant'] = $request['merchant_id'];
        $where['id']          = $request['attr_id'];
        $where['status']      = array('neq',9);
        $data['status']       = 9;
        $data['update_time']  = time();
        $result = M('Attribute') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','删除失败');
        }
        apiResponse('success','删除成功');
    }

    /**
     * 修改商品详情
     * @param $request array
     */
    public function alertGoodsFind($request = array())
    {
        if(empty($request['goods_id']) || !isset($request['goods_id'])) apiResponse('error','商品ID不能为空');
        if(empty($request['merchant_id']) || !isset($request['merchant_id'])) apiResponse('error','商家ID不能为空');
        $result = array();
        $where['id'] = $request['goods_id'];
        $where['merchant_id'] = $request['merchant_id'];
        $model = M('Goods') -> where($where) -> field() ->find();
        if(!$model)  apiResponse('success','',$result);
        $result['cn_goods_name'] = $model['cn_goods_name']; // 商品名称
        $result['goods_id'] = $model['id']; // 商品ID
        $result['wholesale_prices'] = $model['wholesale_prices']; //批发价格
        $result['cn_price'] = $model['cn_price']; // 零售价格
        $result['cn_delivery_cost'] = $model['cn_delivery_cost']; // 运费
        $result['cn_goods_brands'] = $model['cn_goods_brands'] ; // 品牌
        $result['supply_info'] = $model['supply_info'];
        $result['article_number'] = $model['article_number'] ? $model['article_number'] : ''; // 货号
        $result['cn_goods_introduction'] = $model['cn_goods_introduction'] ? $model['cn_goods_introduction'] : ''; // 商品详情
        $pic =  explode(',',$model['goods_pic']); // 图片
        if(!empty($model['goods_pic'])){
            $picModel = M('file')->where(array('id' => array('in',$pic)))->field('path,id')->select();
            foreach($picModel as $k => $v) {
                $result['goods_pic'][] = array('pic' =>C('API_URL') . $v['path'],'pic_id'=>$v['id']);
            }
        }else{
            $result['goods_pic']  = array();
        }
        $result['thr_g_t_id'] = $model['thr_g_t_id']; // 三级ID
        $result['sec_g_t_id'] = $model['sec_g_t_id']; // 二级ID
        $result['fir_g_t_id'] = $model['fir_g_t_id']; // 一级ID
        $classify = array($model['fir_g_t_id'],$model['sec_g_t_id'],$model['thr_g_t_id']);
        $classifyModel = M('GoodsType')->where(array('id'=>array('in',$classify))) -> order('id') ->getField('cn_type_name',true);
        $s ='';
        foreach($classifyModel as $key => $value){
            $s .= $value . '>>';
        }
        $result['classify'] = trim($s,'>>');
        apiResponse('success','',$result);
    }

    /**
     * 修改商品规格展示
     * @param array $request
     */
    public function alertGoodsPro($request = array()){
        if(empty($request['goods_id'])    || !isset($request['goods_id'])) apiResponse('error','商品ID不能为空');
        if(empty($request['fir_g_t_id'])  || !isset($request['fir_g_t_id'])) apiResponse('error','一级分类ID不能为空');
        if(empty($request['sec_g_t_id'])  || !isset($request['sec_g_t_id'])) apiResponse('error','二级分类ID不能为空');
        if(empty($request['thr_g_t_id'])  || !isset($request['thr_g_t_id'])) apiResponse('error','三级分类ID不能为空');
        if(empty($request['merchant_id']) || !isset($request['merchant_id'])) apiResponse('error','商家ID不能为空');
        $result = array();
        $tmpStr = '' ;
        // 商品屬性值
        $GoodsProduct = M('GoodsProduct') -> where(array('goods_id'=>$request['goods_id'],'status'=>1)) ->field('id as gp_id,attr_key_group,wholesale_prices,cn_price')-> select();
        foreach($GoodsProduct as $k => $v){
            $tmpStr .= trim($v['attr_key_group'],',') . ',';
            $GoodsProduct[$k]['attr_key_name'] = implode('-',M('AttributeContent')->where(array('status'=>'0','id'=>array('in',$v['attr_key_group'])))->getField('cn_attr_con_name',true));
        }
        $tmpStr = array_unique(explode(',',trim($tmpStr,','))); // 去重过后的数组
        $attr =
            M('Attribute')->
            where(array('status'=>'0','fir_g_t_id'=>$request['fir_g_t_id'],'sec_g_t_id'=>$request['sec_g_t_id'],'thr_g_t_id'=>$request['thr_g_t_id'],'is_merchant'=>array('in',array('0',$request['merchant_id']))))->
            field('id as attr_id,cn_attr_name as attr_name,is_merchant')->
            order('is_merchant ASC')->
            select();
        if($attr){
            foreach($attr as $k=>$v)
            {
                $attr[$k]['_child'] =  M('AttributeContent')->where(array('status'=>'0','attr_id'=>$v['attr_id'],'is_merchant'=>array('in',array('0',$request['merchant_id']))))->field('id as attr_con_id,cn_attr_con_name as attr_con_name,is_merchant')->select();
                foreach($attr[$k]['_child'] as $ck => $cv){
                    if(in_array($cv['attr_con_id'],$tmpStr)){
                        $attr[$k]['_child'][$ck]['select'] = 1;
                    }else{
                        $attr[$k]['_child'][$ck]['select'] = 0;
                    }
                }
            }
        }

        $result['attr'] = $attr ? $attr : [];
        $result['GoodsProduct'] = $GoodsProduct ? $GoodsProduct : [];
        $result['param'] = $request;
        apiResponse('success','',$result);
    }



 }