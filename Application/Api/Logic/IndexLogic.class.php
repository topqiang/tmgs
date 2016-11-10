<?php
namespace Api\Logic;
/**
 * Class IndexLogic
 * @package Api\Logic
 */
class IndexLogic extends BaseLogic{

    /**
     * 选择城市
     * 传递参数的方式：post
     * 需要传递的参数：
     * 地址ID   region_id
     */
    public function cityList($request = array()){
        if($request['region_name']){
            $where['region_name'] = array('like','%'.$request['region_name'].'%');
        }
        $where['region_type'] = 2;
        $result = M('Region') ->where($where) ->field('id as region_id, region_name') ->order('letter asc') ->select();
        if(!$result){
            $result = array();
        }
        apiResponse('success','',$result);
    }
    /**
     * 首页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID     m_id
     * 城市名称   region_name
     */
    public function index ($request = array()){

        if(!$request['region_name']){
            apiResponse('error','城市地址不能为空');
        }

        $where['region_name']  = array('like','%'.$request['region_name'].'%');
        $where['region_type'] = 2;
        $region_info = M('Region')->where($where)->find();
        if($region_info){
            $region_id   = $region_info['id'];
            $region_name = $region_info['region_name'];
        }else{
            $region_id = 140;
            $region_name = '沧州市';
        }
        unset($where);
        $region_arr[] = 1;
        $region_arr[] = $region_id;

        $where['region_id'] = array('in',$region_arr);
        $where['status']    = array('eq',1);
        $advert = M('Advert') ->where($where) ->field('id as advert_id, ad_pic, ad_url') ->select();

        foreach($advert as $k =>$v){
            $path = M('File') ->where(array('id'=>$v['ad_pic'])) ->getField('path');
            $advert[$k]['ad_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Manager/default.png';
        }
        if(!$advert){
            $advert['ad_pic'] = C('API_URL').'/Uploads/Manager/default.png';
        }

        $result['region_id']   = $region_id;
        $result['region_name'] = $region_name;
        $result['advert']      = $advert;

        //获取未读消息数量
        if(!empty($request['m_id'])){
            $data['m_id'] = $request['m_id'];
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($data['m_id']);
            $result['un_read_num'] = ''.$un_read_num;
        }else{
            $result['un_read_num'] = '0';
        }

        //获取默认搜索词
        $config = D('Config')->parseList();
        $result['app_search'] = $config['APP_SEARCH']['CN'];

        //获取商品分类
        unset($where);
        $where['is_app_show']  = 1 ;
        $where['status']       = array('neq',9);

        $result['up'] = M('GoodsType') -> where($where) -> field('id as g_t_id,cn_type_name as type_name,app_picture') -> order('sort asc, create_time desc') ->limit(8) ->select();

        if(!$result['up']){
            $result['up'] = array();
        }
        foreach($result['up'] as $k =>$v){
            $path = M('File')->where(array('id'=>$v['app_picture']))->getField('path');
            $result['up'][$k]['app_picture'] = $path?C('API_URL').$path:'';
        }
        //获取APP楼层一楼
        unset($where);
        $where['floor_id'] = 4;

        $first_floor = M('FloorPicture') ->where($where) ->field('grade, pic, key') ->order('grade asc') ->limit(8) ->select();
        if(!$first_floor){
            $first_floor = array();
        }

        foreach($first_floor as $k =>$v){
            $path = M('File')->where(array('id'=>$v['pic']))->getField('path');
            $first_floor[$k]['pic'] = $path?C('API_URL').$path:'';
        }
        $result['first_floor'] = $first_floor;

        $result['hot_class'] = M('GoodsType') -> where(array('classify_show'=>1,'status'=>array('neq',9))) -> field('id as g_t_id,type_picture') -> order('sort asc, create_time desc') ->limit(8) ->select();

        if(!$result['hot_class']){
            $result['hot_class'] = array();
        }else{
            foreach($result['hot_class'] as $key =>$value){
                $path = M('File')->where(array('id'=>$value['type_picture']))->getField('path');
                $result['hot_class'][$key]['type_picture'] = $path?C('API_URL').$path:'';
            }
        }
        //获取APP楼层二楼
        unset($where);
        $where['giftware'] = 1;
        $where['status'] = 1;
        $where['audit_status'] = array('lt',2);
        $second_floor = M('Goods') ->where($where) ->field('id as goods_id ,cn_price,cn_goods_name,sales,goods_pic') ->select();
        if(!$second_floor){
            $second_floor = array();
        }else{
            foreach($second_floor as $k =>$v){
                $path = M('File')->where(array('id'=>$v['goods_pic']))->getField('path');
                $second_floor[$k]['goods_pic'] = $path?C('API_URL').$path:'';
                $second_floor[$k]['comment'] = M('Evaluate')->where(array('g_id'=>$v['id']))->count();
                $money = M('GoodsProduct') -> where(array('goods_id'=>$v['id']))->getField('MIN(cn_price)');
                if($money != 0){
                    $second_floor[$k]['cn_price'] = $money;
                }
            }
        }
        $result['second_floor'] = $second_floor;

        apiResponse('success','',$result);
    }
    /**
     * 商品分类页
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     */
    public function  goodsType($request = array()){
        if(!empty($request['m_id'])){
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
            $result['un_read_num'] = ''.$un_read_num;
        }else{
            $result['un_read_num'] = 0;
        }
        //type 为三级分类
        $where['type'] = 3;
        $where['status'] = array('neq',9);

        $result['type'] = M('GoodsType') -> where($where) ->field('id as g_t_id, cn_type_name as type_name') ->select();

        if(empty($result['type'])){
            $result['type'] = array();
        }
        apiResponse('success','',$result);
    }

    /**
     * @param array $request
     * 商家列表
     * 搜索关键字  keywords
     * 用户ID      m_id
     */
    public function merchantList($request = array()){
        //默认关键字不能为空
        if(empty($request['keywords'])){
            apiResponse('error','搜索关键字不能为空');
        }
        //获取默认搜索词
        $config = D('Config')->parseList();
        $result_data['app_search'] = $config['APP_SEARCH']['CN'];

        //获取未读消息数量
        if(!empty($request['m_id'])){
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
            $result_data['un_read_num'] = ''.$un_read_num;
        }else{
            $result_data['un_read_num'] = '0';
        }
        //搜集相关商家
        $where['merchant_name'] = array('like','%'.$request['keywords'].'%');
        $where['status']           = array('eq',1);
        $merchant_list = M('Merchant')->where($where)->field('id as merchant_id,merchant_name,head_pic')->select();

        if(empty($merchant_list)){
            $merchant_list = array();
        }
        foreach($merchant_list as $k =>$v){
            //获取头像
            $path = M('File')->where(array('id'=>$v['head_pic']))->getField('path');
            $merchant_list[$k]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';
            //获取销量和商品数量
            unset($where);
            $where['merchant_id'] = $v['merchant_id'];
            $where['is_shelves'] = 1;
            $where['audit_status'] = 1;
            $sales = M('Goods')->where($where)->getField('SUM(sales) as sales');
            $count = M('Goods')->where($where)->count();
            $merchant_list[$k]['sales'] = $sales?''.$sales:'0';
            $merchant_list[$k]['count'] = $count?''.$count:'0';
            //获取四件商家的商品
            $goods_list = M('Goods')->where($where)->field('id as goods_id,goods_pic')->limit(4)->select();

            if($goods_list){
                foreach ($goods_list as $key => $val) {
                    $goods_pic = explode(',', $val['goods_pic']);
                    $path = M('File')->where(array('id' => $goods_pic[0]))->getField('path');
                    $goods_list[$key]['goods_pic'] = $path?C('API_URL').$path:'';
                    unset($where);
                    $where['goods_id'] = $val['goods_id'];
                    $where['status'] = array('neq',9);
                    $price = M('GoodsProduct') ->where($where) ->field('cn_price as price') ->order('cn_price asc')->find();
                    $goods_list[$key]['price'] = $price['price']?$price['price']:'0.00';
                }
            }else{
                $goods_list = array();
            }
            $merchant_list[$k]['goods_list'] = $goods_list;
        }
        $result_data['merchant_list'] = $merchant_list;
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
            $where['cn_goods_name'] = array('like','%'.$request['keywords'].'%');
        }
        if($request['g_t_id']){
            $where['_string'] = 'fir_g_t_id='.$request['g_t_id'].' OR sec_g_t_id='.$request['g_t_id'].' OR thr_g_t_id='.$request['g_t_id'];
        }
        $where['is_shelves'] = 1;
        $where['audit_status'] = array('IN',array(1,0));
        $goods_list = M('Goods')->where($where)->field('id as goods_id,cn_goods_name as goods_name,goods_pic,sales,sort')->select();

        if(empty($goods_list)){
            $result_data['goods'] = array();
            apiResponse('success','请求成功',$result_data);
        }

        $divide = M('Divide') ->find();

        foreach($goods_list as $k =>$v){
            //转换商品图片
            $goods_pic = explode(',',$v['goods_pic']);
            $path = M('File')->where(array('id'=>$goods_pic[0]))->getField('path');
            $goods_list[$k]['goods_pic'] = $path?C('API_URL').$path:'';

            //判断商品的产品表里面是否有商品，如果有商品更新最低价格，否则不更新
            $price = M('GoodsProduct')->where(array('goods_id'=>$v['goods_id']))->field('wholesale_prices, cn_price as price') ->order('cn_price asc') ->find();

            if($price){
                $goods_list[$k]['price'] = $price['price'];
                $return_price = ($price['price'] - $price['wholesale_prices']) * (1 - $divide['divide_p']) * $divide['divide_m'];
                $goods_list[$k]['return_price'] = $return_price;

            }else{
                unset($goods_list[$k]);
                continue;
            }

            //获取商品的评价数量
            $comment_num = M('Evaluate') ->where(array('g_id'=>$v['goods_id'])) ->count();
            $goods_list[$k]['comment_num'] = $comment_num?''.$comment_num:'0';
        }
        //如果赛选条件中包含了价格区间，那就进行赛选
        if($request['price_lower'] || $request['price_upper']){
            $goods = array();
            foreach($goods_list as $key =>$value){
                if($request['price_lower'] && $request['price_upper']){
                    if($value['price']>=$request['price_lower'] && $value['price']<=$request['price_upper']){
                        $goods[] = $value;
                    }
                }else if($request['price_lower']){
                    if($value['price']>=$request['price_lower']){
                        $goods[] = $value;
                    }
                }else{
                    if($value['price']<=$request['price_upper']){
                        $goods[] = $value;
                    }
                }
            }
        }else{
            $goods = $goods_list;
        }

        if(empty($goods)){
            $result_data['goods'] = array();
            apiResponse('success','',$result_data);
        }
        $goods = array_values($goods);
        //排序
        foreach($goods as $k =>$v){
            $volume[$k] = $v['sales'];
            $volume1[$k] = $v['price'];
            $volume2[$k] = $v['sort'];
        }

        if(!empty($request['complex_order'])||!empty($request['sales_order'])||!empty($request['price_order'])){
            if($request['complex_order']==1){
                array_multisort($volume1,SORT_ASC,$goods);
                $result_data['goods'] = $goods;
            }

            if($request['sales_order']==1){
                array_multisort($volume, SORT_ASC,$goods);
                $result_data['goods'] = $goods;
            }else if($request['sales_order']==2){
                array_multisort($volume, SORT_DESC,$goods);
                $result_data['goods'] = $goods;
            }

            if($request['price_order']==1){
                //$result_data['goods']= $this->sortGoods($goods,'SORT_ASC','price');
                array_multisort($volume1, SORT_ASC,$goods);
                $result_data['goods'] = $goods;

            }else if($request['price_order']==2){
//                $result_data['goods'] = $this->sortGoods($goods,'SORT_DESC','price');
                array_multisort($volume1, SORT_DESC,$goods);
                $result_data['goods'] = $goods;
            }
        }else{
            //$result_data['goods'] = $this->sortGoods($goods,'SORT_DESC','sort');
           // array_multisort($volume3, SORT_DESC,$volume,SORT_ASC,$goods);
            $result_data['goods'] = $goods;
        }
        apiResponse('success','',$result_data);
    }

    /**
     * @param $list
     * @param $direction
     * @param $field
     * 排序函数     未改
     */
    public function sortGoods($list,$direction,$field){
        $sort = array(
            'direction' => $direction, //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序
            'field'     => $field,       //排序字段
        );
        $arrSort = array();
        foreach($list AS $uniqid => $row){
            foreach($row AS $key=>$value){
                $arrSort[$key][$uniqid] = $value;
            }
        }
        if($sort['direction']){
            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $list);
        }
        return $list;
    }
    /**
     * 商品总分类
     * 用户ID    m_id
     */
    public function classification($request = array()){
        if(!empty($request['m_id'])){
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
            $result['un_read_num'] = ''.$un_read_num;
        }else{
            $result['un_read_num'] = '0';
        }
        //获取默认搜索词
        $config = D('Config')->parseList();
        $result['app_search'] = $config['APP_SEARCH']['CN'];
        $where['type']   = 1;
        $where['status'] = array('neq',9);
        $goods_type = M('GoodsType') ->where($where) ->field('id as g_t_id, cn_type_name as type_name')
            ->order('sort asc ,create_time desc') ->select();
        if(!$result){
            $result['goods_type'] = array();
        }
        foreach($goods_type as $k =>$v){
            unset($where);
            $where['parent_id'] = $v['g_t_id'];
            $where['type']   = 2;
            $where['status'] = array('neq',9);
            $type_two = M('GoodsType') ->where($where) ->field('id as g_t_id, cn_type_name as type_name') ->order('sort asc ,create_time desc') ->select();
            if($type_two){
                $goods_type[$k]['type_two'] = $type_two;
            }else{
                $goods_type[$k]['type_two'] = array();
            }
            foreach($type_two as $key =>$val){
                unset($where);
                $where['parent_id'] = $val['g_t_id'];
                $where['type']   = 3;
                $where['status'] = array('neq',9);
                $type_three = M('GoodsType') ->where($where) ->field('id as g_t_id, cn_type_name as type_name, type_picture') ->order('sort asc, create_time desc') ->select();
                if($type_three){
                    $goods_type[$k]['type_two'][$key]['type_three'] = $type_three;
                }else{
                    $goods_type[$k]['type_two'][$key]['type_three'] = array();
                }
                $index1 = 0;
                foreach($type_three as $keys => $value){
                    $path = M('File') ->where(array('id'=>$value['type_picture'])) ->getField('path');
                    unset($where);
                    $where['thr_g_t_id'] = $value['g_t_id'];
                    $where['is_shelves'] = 1;
                    $where['audit_status'] = array('neq',2);
                    $goods = M('Goods') ->where($where) ->find();
                    $goods_pic = explode(',',$goods['goods_pic']);
                    $path1 = M('File') ->where(array('id'=>$goods_pic[0])) ->getField('path');
                    if($path){
                        $goods_type[$k]['type_two'][$key]['type_three'][$keys]['type_picture'] = C("API_URL").$path;
                    }elseif($path1){
                        $goods_type[$k]['type_two'][$key]['type_three'][$keys]['type_picture'] = C("API_URL").$path1;
                    }else{
                        unset($goods_type[$k]['type_two'][$key]['type_three'][$keys]);
                    }


//                    unset($where);
//                    unset($data);
//                    $where['thr_g_t_id'] = $value['g_t_id'];
//                    $where['is_shelves'] = 1;
//                    $where['audit_status'] = array('neq',2);
//                    $product = M('Goods') ->where($where) ->find();
//                    if(!$product){
//                        unset($goods_type[$k]['type_two'][$key]['type_three'][$keys]);
//                        continue;
//                    }else{
//
//                        $index1 += 1;
//                    }
                }
                $goods_type[$k]['type_two'][$key]['type_three'] = array_values($goods_type[$k]['type_two'][$key]['type_three']);
//                if($index1 == 0){
//                    unset($goods_type[$k]['type_two']);
//                    continue;
//                }else{
//                    $goods_type[$k]['type_two'] = array_values($goods_type[$k]['type_two']);
//                }
            }
        }
        $goods_type = array_values($goods_type);
        $result['goods_type'] = $goods_type;
        apiResponse('success','',$result);
    }
    /**
     * 商品总分类
     * 用户ID    m_id
     * 父级ID    parent_id
     */
    public function changeClassification($request = array()){
        //如果用户不为空   返回未读消息条数
        if(!empty($request['m_id'])){
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
            $result['un_read_num'] = ''.$un_read_num;
        }else{
            $result['un_read_num'] = '0';
        }
        //如果父级ID存在  则显示父级ID的子类  否则
        if($request['parent_id']){
            $where['parent_id'] = $request['parent_id'];
        }else{
            $where['type'] = 1;
            $where['status'] = array('neq',9);
            $type = M('GoodsType') ->where($where) ->field('id as g_t_id, cn_type_name as type_name')
                ->order('sort asc ,create_time desc') ->find();
            unset($where);
            $where['parent_id'] = $type['g_t_id'];
        }
        //获取默认搜索词
        $config = D('Config')->parseList();
        $result['app_search'] = $config['APP_SEARCH']['CN'];

        $where['type']   = 2;
        $where['status'] = array('neq',9);
        $goods_type = M('GoodsType') ->where($where) ->field('id as g_t_id, cn_type_name as type_name')
            ->order('sort asc ,create_time desc') ->select();
        if(!$result){
            $result['goods_type'] = array();
        }

        unset($where);
        $where['type'] = 1;
        $where['status'] = array('neq',9);
        $first_type = M('GoodsType') ->where($where) ->field('id as g_t_id, cn_type_name as type_name')
            ->order('sort asc ,create_time desc') ->select();
        $result['first_type'] = $first_type;

        foreach($goods_type as $k => $v){
            unset($where);
            $where['parent_id'] = $v['g_t_id'];
            $where['type']   = 3;
            $where['status'] = array('neq',9);
            $type_two = M('GoodsType') ->where($where) ->field('id as g_t_id, cn_type_name as type_name, type_picture') ->order('sort asc ,create_time desc') ->select();
            if($type_two){
                $goods_type[$k]['type_two'] = $type_two;
            }else{
                $goods_type[$k]['type_two'] = array();
            }
            $index1 = 0;
            foreach($type_two as $key => $val){
                $path = M('File') ->where(array('id'=>$val['type_picture'])) ->getField('path');
                unset($where);
                $where['thr_g_t_id'] = $val['g_t_id'];
                $where['is_shelves'] = 1;
                $where['audit_status'] = array('neq',2);
                $goods = M('Goods') ->where($where) ->find();
                $goods_pic = explode(',',$goods['goods_pic']);
                $path1 = M('File') ->where(array('id'=>$goods_pic[0])) ->getField('path');
                if($path){
                    $goods_type[$k]['type_two'][$key]['type_picture'] = C("API_URL").$path;
                }elseif($path1){
                    $goods_type[$k]['type_two'][$key]['type_picture'] = C("API_URL").$path1;
                }else{
                    unset($goods_type[$k]['type_two'][$key]);
                }
            }
            $goods_type[$k]['type_two'] = array_values($goods_type[$k]['type_two']);
        }

        $goods_type = array_values($goods_type);
        $result['goods_type'] = $goods_type;
        apiResponse('success','',$result);
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
     * 分页参数  p
     */
    public function newGoodsList($request = array()){

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

        }

        if($request['price_order'] == 1){
            $order = 'price asc, create_time desc';
        }elseif($request['price_order'] == 2){
            $order = 'price desc, create_time desc';
        }else{

        }

        if(!$request['sales_order']&&!$request['price_order']){
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
            if(!$v['price'] || !$v['wholesale_prices']){
                unset($goods_list[$k]);
                continue;
            }

            //转换商品图片
            $goods_pic = explode(',',$v['goods_pic']);
            $path = M('File')->where(array('id'=>$goods_pic[0]))->getField('path');
            $goods_list[$k]['goods_pic'] = $path?C('API_URL').$path:'';

            $return_price = ($v['price'] - $v['wholesale_prices']) * (1 - $divide['divide_p']) * $divide['divide_m'];
            $goods_list[$k]['return_price'] = $return_price?$return_price.'':'0.00';

            //获取商品的评价数量
            $comment_num = M('Evaluate') ->where(array('g_id'=>$v['goods_id'])) ->count();
            $goods_list[$k]['comment_num'] = $comment_num?''.$comment_num:'0';
        }
        $goods_list = array_values($goods_list);
        $result_data['goods'] = $goods_list;
        apiResponse('success','',$result_data);
    }
}