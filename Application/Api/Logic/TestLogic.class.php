<?php
namespace Api\Logic;

/**
 * Class TestLogic
 * @package Api\Logic
 */
class TestLogic extends BaseLogic{
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
        $where['goods.good_product'] = 1;
        $where['goods.audit_status'] = array('IN',array(0,1));
        $where['goods.is_shelves']   = 1;
        $goods_list = M('Goods') ->alias('goods')
            ->where($where)
            ->field('good.id as goods_id, good.cn_goods_name as goods_name, goods.goods_pic, goods.sales, MIN(g_p.cn_price) as price')
            ->join(array(
                'JOIN LEFT'.C('DB_PREFIX').'goods_product g_p ON g_p.goods_id = goods.id',
            ))
            ->group('goods.id') ->order('sort desc') ->limit(3) ->select();
        //获取商品图片  价格
        foreach($goods_list as $k =>$v){
            $path = M('File') ->where(array('id'=>$v['goods_pic'])) ->getField('path');
            $goods_list[$k]['goods_pic'] = $path?C('API_URL').$path:'';
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
            $count = M('Goods')->where($where)->count();
            $merchant_list[$k]['goods_count'] = $count?$count.'':'0';

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
            $count = M('Goods')->where($where)->count();
            $merchant_list[$k]['goods_count'] = $count?$count.'':'0';
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
        $where['goods.id'] = $request['goods_id'];
        $where['goods.is_shelves'] = 1;
        $where['goods.audit_status'] = array('IN',array(0,1));
        $goods_info = M('Goods') ->alias('goods')
            ->where($where)
            ->field('goods.id as goods_id, goods.merchant_id, goods.unit_id, goods.thr_g_t_id, goods.cn_goods_name as goods_name, goods.cn_goods_brands as goods_brands, goods.cn_delivery_cost as delivery_cost, goods.cn_goods_introduction as goods_introduction, goods.goods_pic, goods.is_integrity_fourteen, goods.sales, unit.unit, MIN(g_p.cn_price) as price, g_p.wholesale_prices, merchant.head_pic, merchant.merchant_name, ')
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

        apiResponse('error','',$goods_info);

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
     * 商品列表
     * 用户ID    m_id
     * 关键字    keywords
     * 搜索类别  g_t_id
     * 价格区间  低价  price_lower  高价  price_upper
     * 综合排序  complex_order
     * 销量排序  sales_order
     * 价格排序  price_order
     */
    public function goodsList($request = array()){

        //获取未读消息数量
        if(!empty($request['m_id'])){
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
            $result_data['un_read_num'] = ''.$un_read_num;
        }else{
            $result_data['un_read_num'] = '0';
        }
        //获取默认搜索词
        $config = D('Config')->parseList();
        $result_data['app_search'] = $config['APP_SEARCH']['CN'];
        //搜索关键字存在
        if($request['keywords']){
            $where['goods.cn_goods_name'] = array('like','%'.$request['keywords'].'%');
        }
        if($request['g_t_id']){
            $map['goods.fir_g_t_id'] = $request['g_t_id'];
            $map['goods.sec_g_t_id'] = $request['g_t_id'];
            $map['goods.thr_g_t_id'] = $request['g_t_id'];
            $map['_logic']     = 'OR';
            $where['_complex'] = $map;
        }
        if(!$request['p']){
            apiResponse('error','分页参数不能为空');
        }

        if($request['sales_order'] == 1){
            $order = 'sales asc, create_time desc';
        }elseif($request['sales_order'] == 2){
            $order = 'sales desc, create_time desc';
        }else{
            $order = 'sort desc, create_time desc';
        }

        if($request['price_order'] == 1){
            $order = 'price asc, create_time desc';
        }elseif($request['price_order'] == 2){
            $order = 'price desc, create_time desc';
        }else{
            $order = 'sort desc, create_time desc';
        }

        if($request['price_lower'] && $request['price_upper']){
            $where['g_p.cn_price'] = array('between',array($request['price_lower'],$request['price_upper']));
        }elseif($request['price_lower']){
            $where['g_p.cn_price'] = array('egt',$request['price_lower']);
        }elseif($request['price_upper']){
            $where['g_p.cn_price'] = array('elt',$request['price_upper']);
        }else{

        }

        $where['is_shelves'] = 1;
        $where['audit_status'] = array('IN',array(1,0));
        $goods_list = M('Goods') ->alias('goods')
            ->where($where)
            ->field('goods.id as goods_id, goods.cn_goods_name as goods_name, goods.goods_pic, goods.sales, goods.sort ,MIN(g_p.cn_price) as price, g_p.wholesale_prices')
            ->join(array(
                'LEFT JOIN  '.C('DB_PREFIX').'goods_product g_p ON g_p.goods_id = goods.id',
            ))
            ->order($order)
            ->group('goods.id')
            ->page($request['p'].',10')
            ->select();

        if(empty($goods_list)){
            $result_data['goods'] = array();
        }

        $divide = M('Divide') ->find();

        foreach($goods_list as $k =>$v){
            //转换商品图片
            $goods_pic = explode(',',$v['goods_pic']);
            $path = M('File')->where(array('id'=>$goods_pic[0]))->getField('path');
            $goods_list[$k]['goods_pic'] = $path?C('API_URL').$path:'';

            $return_price = ($v['price'] - $v['wholesale_prices']) * (1 - $divide['divide_p']) * $divide['divide_m'];
            $goods_list[$k]['return_price'] = $return_price;

            //获取商品的评价数量
            $comment_num = M('Evaluate') ->where(array('g_id'=>$v['goods_id'])) ->count();
            $goods_list[$k]['comment_num'] = $comment_num?''.$comment_num:'0';
        }
        $result_data['goods'] = $goods_list;
        apiResponse('success','',$result_data);
    }
    public function test(){
        $time = '2016-09-12';
        $data = strtotime($time);
        apiResponse('error',$data);
    }


    public function addOrder($request = array()){
        if(empty($request['merchant_id']))  apiResponse('error','缺少商家ID');
        if(empty($request['status']))  apiResponse('error','状态值 1待支付,2待接单,3待发货,4待收货,5待评价,6已完成,7已取消');
        if(empty($request['pay_type']))  apiResponse('error','支付方式   1  银联  2  微信  3  支付宝  4  余额');
        if(empty($request['m_id']))  apiResponse('error','请添加用户ID');
        $post = $_POST;
        $data['address'] = 'a:12:{s:2:"id";s:1:"2";s:4:"m_id";s:2:"45";s:4:"name";s:6:"石磊";s:3:"tel";s:11:"18931728128";s:11:"province_id";s:9:"河北省";s:7:"city_id";s:9:"沧州市";s:7:"area_id";s:6:"青县";s:7:"address";s:34:"皇城小区17号楼2单元1101室";s:10:"is_default";s:1:"1";s:11:"create_time";s:10:"1469319061";s:11:"update_time";s:1:"0";s:6:"status";s:1:"0";}';
        $data['merchant_id'] = $request['merchant_id'];
        $data['status'] = substr((integer)$request['status'],0,1) -1;
        $data['pay_type']=substr((integer)$request['pay_type'],0,1);
        $data['m_id'] = $request['m_id'] ;
        $data['order_g_id'] = 1;
        $data['order_sn']= time().rand(11111,99999);
        $data['delivery_sn']= time().rand(11111,99999);
        $data['delivery_code']= 'tiantian';
        $data['goods_info_serialization'] = 'a:1:{s:5:"goods";a:1:{i:0;a:5:{s:7:"product";a:1:{s:13:"attr_con_name";s:17:"包装规格:500g";}s:3:"num";s:1:"1";s:11:"goodsDetail";a:5:{s:2:"id";s:2:"25";s:10:"goods_name";s:36:"自然开口本色无漂白开心果";s:5:"price";s:5:"68.00";s:11:"trade_price";s:5:"38.00";s:9:"goods_pic";s:46:"/Uploads/Merchant/2016-06-27/5770ca523f472.jpg";}s:5:"price";d:78;s:13:"delivery_cost";s:5:"10.00";}}}';
        $data['submit_order_time'] = time();
        $data['leave_msg'] = rand(11111,99999).'默认信息';
        $data['totalprice'] = 78;
        $data['trade_price'] = 38;
        $model = M('order') -> data($data) -> add();
        if($model){
            apiResponse('success','添加成功');
        }else{
            apiResponse('error','添加失败');
        }
    }
}