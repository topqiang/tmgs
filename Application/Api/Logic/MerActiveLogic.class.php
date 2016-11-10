<?php
namespace Api\Logic;

/**
 * Class MerActiveLogic
 * @package Api\Logic
 */
class MerActiveLogic extends BaseLogic{

    //初始化
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 0  商家活动列表
     */
    public function activePage(){
        $generalize_list = M('GeneralizeList') ->field('id as gener_id ,pic ,title, content') ->select();
        if(!$generalize_list){
            $generalize_list = array();
        }
        foreach($generalize_list as $k =>$v){
            $path = M('File') ->where(array('id'=>$v['pic'])) ->getField('path');
            $generalize_list[$k]['pic'] = $path?C('API_URL').$path:'';
        }
        apiResponse('success','',$generalize_list);
    }
    /**
     *  0  所有位置选择图
     */
    public function chooseLocation($request = array()){
        if(!$request['type']){
            apiResponse('error','位置类型不能为空');
        }
        $result = M('MerchantAdvertising') ->where(array('type'=>$request['type']))
            ->field('id as m_a_id, money, title, content')
            ->order('location asc') ->select();
        if(!$result){
            $result = array();
        }
        if($request['type'] == 1){
            foreach($result as $k => $v){
                $result[$k]['count'] = M('AdPositionTotal') ->where(array('type'=>1,'location'=>$v['location'])) ->count();
            }
        }elseif($request['type'] == 2){
            foreach($result as $k => $v){
                $result[$k]['count'] = M('AdPositionTotal') ->where(array('type'=>2,'location'=>$v['location'])) ->count();
            }
        }else{
            /***发现好货***/
        }
        apiResponse('success','',$result);
    }
    /**
     * 0 所有选择商品
     * 商家ID    merchant_id
     * 关键字    keywords
     */
    public function spreadChooseGoods($request = array()){
        if(!$request['merchant_id'])apiResponse('error','商家ID不能为空');
        if(empty($request['keywords']) && isset($request['keywords']))apiResponse('error','请输入搜索关键字');

        $where['goods.merchant_id']   = $request['merchant_id'];
        $where['goods.cn_goods_name'] = array('like','%'.$request['keywords'].'%');
        $where['goods.status']        = 1;
        $where['goods.audit_status']  = array('IN',array(0,1));
        $where['goods.is_shelves']    = 1;
        $goods_list = M('Goods') ->alias('goods') ->where($where)
            ->field('goods.id as goods_id, goods.cn_goods_name as goods_name, goods.goods_pic, goods.create_time, goods.sales, MIN(g_p.cn_price) as price')
            ->join(array(
                'LEFT JOIN  '.C('DB_PREFIX').'goods_product g_p ON g_p.goods_id = goods.id',
            ))
            ->order('create_time desc')
            ->group('goods.id')
            ->select();
        if($goods_list){
            foreach($goods_list as $k => $v){
                $pic = explode(',',$v['goods_pic']);
                $path = M('File') ->where(array('id'=>$pic[0])) ->getField('path');
                $goods_list[$k]['goods_pic'] = $path?C("API_URL").$path:'';
            }
        }else{
            $goods_list = array();
        }
        $result['goods_list'] = $goods_list;
        apiResponse('success','',$result);
    }

    /**
     * 3456 活动价格信息
     * @param array $request
     */
    public function findList($request = array()){
        // 3 好商品 4 好店 5好服务 6 云推广
        // 范围 人数 价格
        if(!$request['type'])apiResponse('error','类型不能为空');
        $model = M('MerchantAdvertising');
        $findTotal = M('FindTotal');
        switch($request['type']){
            case '3':
            case '4':
            case '5':
                // 好商品
                $result['money'] = $model -> where(array('type'=>$request['type']))->getField('money');
                $result['count'] = $findTotal->where(array('type'=>$request['type']))->count();
                break;
            case '6':
                $result['money'] = $model -> where(array('type'=>$request['type']))->getField('money');
                $result['count'] = M('CloudSpread')->where(array('pay_status'=>1))->count();
                break;
        }
        $result['scope'] = '全国';
        apiResponse('success','',$result);
    }
    /**
     * 1 2  首页广告已报名
     */
    public function firstPageList(){}
    /**
     * 1 2  首页广告报名
     * 跳转地址        url
     * 推广地址        region_id
     * 推广时间        time
     * 商家ID          merchant_id
     * 推广位置        location
     * 总金额          money
     * 广告报名        type  1  首页广告位  2  签到页广告位
     * 图片            pic
     */
    public function signFirstPage($request = array()){
        if(!$request['url']){
            apiResponse('error','请输入跳转地址');
        }
        if(!$request['region_id']){
            apiResponse('error','请选择推广地址');
        }
        if(!$request['time']){
            apiResponse('error','请选择推广时间');
        }
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['location']){
            apiResponse('error','请选择推广位置');
        }
        if(!$request['money']){
            apiResponse('error','总金额不能为空');
        }
        if($request['type'] != 1 &&$request['type'] != 2){
            apiResponse('error','广告形式有误');
        }
        //上传头像
        if (!empty($_FILES['pic']['name'])) {
            $res = api('UploadPic/upload', array(array('save_path' => 'AdPosition')));
            foreach ($res as $value) {
                $data['pic'] = $value['id'];
            }
        }
        $data['url']      = $request['url'];
        $data['region']   = $request['region_id'];
        $data['time']     = $request['time'];
        $data['order_sn'] = time().rand('10000','99999');
        $data['type']     = 1;
        $data['location'] = $request['location'];
        $data['money']    = $request['money'];
        $data['type']     = $request['type'];
        $data['merchant_id'] = $request['merchant_id'];
        $data['create_time'] = time();
        $result = M('AdPositionTotal') ->add($data);
        if(!$result){
            apiResponse('error','广告添加失败');
        }
        $res['ad_id']    = $result;
        $res['order_sn'] = $data['order_sn'];
        $res['region_id'] = $data['region'];
        $res['type']     = $data['type'];
        $res['money']    = $data['money'];
        apiResponse('success','添加成功',$res);
    }
    /**
     * 1 2  首页广告报名地点
     */
    public function choosePlace($request = array()){
        if($request['keywords']){
            $where['region_name'] = array('like','%'.$request['keywords'].'%');
        }
        $where['region_type'] = 2;
        $region = M('Region') ->where($where) ->field('id as region_id, letter, region_name') ->select();
        if(!$region){
            $region = array();
        }
        $country = M('Region') ->where(array('id'=>1)) ->field('id as region_id, letter, region_name') ->find();
        $result['region'] = $region;
        $result['country'] = $country;
        apiResponse('success','',$result);
    }
    /**
     * 1 2  已报名列表
     * 商家ID     merchant_id
     * 广告类型   type  1  首页广告页  2  钱到位广告页
     */
    public function entryList ($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if($request['type'] != 1&&$request['type'] != 2){
            apiResponse('error','广告界面有误');
        }
        $where['merchant_id'] = $request['merchant_id'];
        $where['type']        = $request['type'];
        $where['pay_status']  = 1;
        $result = M('AdPositionTotal') ->where($where) ->field('id as a_p_t_id, region, pic') ->select();
        if(!$result){
            $result = array();
        }
        foreach($result as $k =>$v){
            $path = M('File') ->where(array('id'=>$v['pic'])) ->getField('path');
            $result[$k]['pic'] = $path?C("API_URL").$path:'';
            $region = M('Region') ->where(array('id'=>$v['region'])) ->getField('region_name');
            $result[$k]['region'] = $region;
            $result_start = M('AdPosition') ->where(array('a_p_t_id'=>$v['a_p_t_id'])) ->find();
            $start_time   = $result_start['start_time'];
            $result_end   = M('AdPosition') ->where(array('a_p_t_id'=>$v['a_p_t_id'])) ->order('end_time desc') ->find();
            $end_time     = $result_end['end_time'];
            if($start_time > time()){
                $result[$k]['status'] = 0;
            }elseif($start_time < time() &&$end_time > time()){
                $result[$k]['status'] = 1;
            }else{
                $result[$k]['status'] = 2;
            }
        }
        apiResponse('success','',$result);
    }

    /**
     * 报名列表详情
     * @param request array ad_id='轮播图详情ID'
     * @return 跳转地址 广告图片 广告位置 广告范围 日期 价格
     */
    public function entryDetail($request =array())
    {
        if(empty($request['ad_id']) && !isset($request['ad_id'])){
            apiResponse('error','详情ID不能为空');
        }
        $result = M('AdPositionTotal')
            -> alias('ad')
            -> where(array('ad.id'=>$request['ad_id']))
            -> join("__FILE__ as f ON ad.pic=f.id",'LEFT')
            -> join("__REGION__ as re ON ad.region=re.id",'LEFT')
            -> field('url,f.path as pic ,re.region_name as region,time,money,ad.location,ad.type')
            -> find();
        if($result){
            $result['time'] = montageTime($result['time']);
            $result['pic'] = empty($result['pic']) ? '' : C('API_URL').$result['pic'] ;
            $result['title'] =$result['type'] == 1 ? '首页广告位-'.$result['location'] . '号位': '签到页广告位-'.$result['location'].'号位';
            unset($result['location'],$result['type']);
            apiResponse('success','',$result);
        }else{
            apiResponse('error','当前ID不存在');
        }

    }
    /**
     * 1 2  首页广告报名日历
     */
    public function chooseTime($request = array()){
        $where['region'] = $request['region']; // 地区 1全国 其余为ID
        $where['location'] = $request['location']; //排名
        $where['type'] = $request['type']; //轮播位置 1 首页 2 签到页
        $model = M('AdPositionTotal') -> where($where) ->getField('time',true);
        $time = array();
        $result = array();
        if($model)
        {
            foreach($model as $k => $v)
            {
                $tmpTime = explode(',',trim($v,','));
                foreach($tmpTime as $key => $value)
                {
                    array_push($time,$value);
                }
            }
            $time = array_unique($time);
            foreach($time as $tk => $tv){
                $result[] = array('time'=>$tv,'timestamp'=>strtotime($tv).'000');
            }
        }
        apiResponse('success','',$result);
    }

    /**
     * 3,4,5  发现列表
     * @var model array 数据库返回 [id,时间,object_id,]
     * @return result array find_id 发现ID obj_pic 图片 obj_name 名字 time_value 开始状态
     */
    public function deliveryList($request = array()){
        if(empty($request['merchant_id']) || !isset($request['merchant_id'])){
            apiResponse('error','商家ID不能为空');
        }
        if(!in_array($request['type'],array(3,4,5))) apiResponse('error','类型方式有误');

        $where['merchant_id'] = $request['merchant_id'];
        $where['type']        = $request['type'];
        $where['pay_status']  = 1;
        $result = array();
        $model = M('FindTotal')->where($where)->field('id,time,object_id')->select();
        if($model){
            foreach($model as $key => $value){
                $result[$key]['find_id'] = $value['id']; // 发现ID
                switch($request['type']){
                    case '3':
                        // 好商品
                        $goods = M('Goods')->alias('g')->where(array('g.id'=>$value['object_id']))->join("__FILE__ as f ON trim(g.goods_pic) = f.id")->field('f.path as goods_pic,g.cn_goods_name')->find();
                        $result[$key]['obj_pic'] = $goods['goods_pic'];
                        $result[$key]['obj_name']= $goods['cn_goods_name'];

                        break;
                    case '4':
                    case '5':
                        // 好商家 好服务商家
                        $merchant = M('Merchant')->alias('mer')->where(array('mer.id'=>$value['object_id']))->join("__FILE__ as f ON mer.head_pic = f.id")->field('f.path as head_pic,merchant_name')->find();
                        $result[$key]['obj_pic'] = $merchant['head_pic'];
                        $result[$key]['obj_name']= $merchant['merchant_name'];
                        break;
                }
                $result[$key]['obj_pic'] =  $result[$key]['obj_pic'] ?  C('API_URL').$result[$key]['obj_pic'] : '';
                $result[$key]['type'] = $request['type'];
                $time = explode(',',trim(trim($value['time'],',')));
                if(strtotime($time[0]) > time()) $result[$key]['time_value'] = '尚未开始';
                if(strtotime($time[0]) <= time()) $result[$key]['time_value'] = '正在进行';
                if(strtotime($time[count($time)-1]) < time()) $result[$key]['time_value'] = '已结束';
            }
        }
        apiResponse('success','',$result);
    }
    /**
     * 3  发现  详情
     * @param $request array delivery_id 详情ID type 类型
     */
    public function deliveryInfo($request = array()){
        if(empty($request['delivery_id']) || !isset($request['delivery_id'])) apiResponse('error','详情ID不能为空');
        if(empty($request['type']) || !isset($request['type'])) apiResponse('error','类型不能为空');
        $model = M('FindTotal')->where(array('id'=>$request['delivery_id']))->find();
        $result= array();
        if($model){
            if($request['type'] == 3){
                // 发现好商品
                $goods = M('Goods')->where(array('id'=>$model['object_id']))->field('id,cn_goods_name,cn_price,sales,create_time,goods_pic,cn_price')->find();
                $result['goods_name'] = $goods['cn_goods_name'];
                $result['goods_sales'] = $goods['sales'];
                $result['create_time'] = date('Y-m-d',$goods['create_time']);
                $pic= explode(',',trim($goods['goods_pic'],','));
                $result['goods_pic'] = M('File')-> where(array('id'=>$pic[0]))->getField('path');
                $result['goods_pic'] = !empty($result['goods_pic']) ? C('API_URL').$result['goods_pic'] : '' ;
                $goods_product = M('GoodsProduct')->where(array('goods_id'=>$goods['id']))-> getField('MIN(cn_price) as cn_price');
                if($goods_product && $goods_product != 0){
                    $result['goods_price'] = $goods_product;
                }else{
                    $result['goods_price'] = $goods['cn_price'];
                }
            }
            $result['time'] = montageTime($model['time']); // 时间
            $result['money'] = $model['money'];  // 价格
            apiResponse('success','',$result);
        }else{
            apiResponse('error','当前ID不存在');
        }

    }
    /**
     * 3 4 5  发现模块  新增
     * 商家ID     merchant_id
     * 对象ID     object_id
     * 操作类型   type  1  发现好货  2  发现好店  3  发现好服务
     * 订单总价   money
     * 办理时间   time
     */
    public function addDeliveryGoods($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['object_id']){
            apiResponse('error','对应ID不能为空');
        }
        if($request['type'] != 1&&$request['type'] != 2&&$request['type'] != 3){
            apiResponse('error','相应状态不能为空');
        }
        if(!$request['money']){
            apiResponse('error','消费金额不能为空');
        }
        if(!$request['time']){
            apiResponse('error','办理时间不能为空');
        }
        $data['merchant_id'] = $request['merchant_id'];
        if($request['type'] == 1){
            $data['type']        = 3;
            $data['object_id']    = $request['object_id'];
        }elseif($request['type'] == 2){
            $data['type']        = 4;
            $data['object_id']    = $request['object_id'];
        }else{
            $data['type']        = 5;
            $data['object_id']    = $request['object_id'];
        }
        $data['money']       = $request['money'];
        $data['order_sn']    = time().rand(10000,99999);
        $data['time']        = $request['time'];
        $data['create_time'] = time();
        $result = M('FindTotal') ->add($data);
        if(!$result){
            apiResponse('error','操作失败');
        }
        $res['f_t_id']   = $result;
        $res['order_sn'] = $data['order_sn'];
        $res['money']    = $request['money'];
        apiResponse('success','操作成功',$res);
    }

    /**
     * 6  推广列表
     * 商家ID     merchant_id
     */
    public function spreadList($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        $where['cloudspread.merchant_id'] = $request['merchant_id'];
        $where['cloudspread.status']      = 1;

        $result = M('CloudSpread') ->alias('cloudspread') ->where($where)
            ->field('cloudspread.id as c_s_id, cloudspread.total_number, cloudspread.change_number, cloudspread.status, goods.cn_goods_name as goods_name, goods.goods_pic')
            ->join(array(
                'LEFT JOIN  '.C('DB_PREFIX').'goods goods ON goods.id = cloudspread.goods_id',
            ))
            ->select();

        if(!$result){
            $result = array();
        }
        foreach($result as $k => $v){
            $goods_pic = explode(',',$v['goods_pic']);
            $path = M('File') ->where(array('id'=>$goods_pic[0])) ->getField('path');
            $result[$k]['goods_pic'] = $path?C("API_URL").$path:'';
        }
        apiResponse('success','',$result);
    }
    /**
     * 6  推广详情
     * 商家ID     merchant_id
     * 详情ID     c_s_id
     */
    public function spreadInfo ($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['c_s_id']){
            apiResponse('error','推广详情ID不能为空');
        }
        $where['cloudspread.merchant_id'] = $request['merchant_id'];
        $where['cloudspread.id'] = $request['c_s_id'];

        $result = M('CloudSpread') ->alias('cloudspread') ->where($where)
            ->field('cloudspread.id as c_s_id, cloudspread.total_number, cloudspread.change_number, cloudspread.pay_money, goods.cn_goods_name as goods_name, goods.goods_pic, goods.sales, goods.create_time ,MIN(g_p.cn_price) as price')
            ->join(array(
                'LEFT JOIN  '.C('DB_PREFIX').'goods goods ON goods.id = cloudspread.goods_id',
                'LEFT JOIN  '.C('DB_PREFIX').'goods_product g_p ON g_p.goods_id = cloudspread.goods_id'
            ))
            ->find();
        if(!$result){
            apiResponse('error','推广商品详情有误');
        }
        $goods_pic = explode(',',$result['goods_pic']);
        $path = M('File') ->where(array('id'=>$goods_pic[0])) ->getField('path');
        $result['goods_pic'] = $path?C('API_URL').$path:'';
        $result['create_time'] = date('Y-m-d',$result['create_time']);
        apiResponse('success','',$result);
    }
    /**
     * 6  点击记录
     * 推广ID     c_s_id
     */
    public function clickRecord ($request = array()){
        if(!$request['c_s_id']){
            apiResponse('error','推广ID不能为空');
        }
        $result = M('CloudSpreadLog') ->alias('cloudspreadlog')
            ->where(array('cloudspreadlog.cloud_spread_id'=>$request['c_s_id']))
            ->field('cloudspreadlog.id as c_s_l_id, cloudspreadlog.money, cloudspreadlog.create_time, member.account')
            ->join(array(
                'LEFT JOIN  '.C('DB_PREFIX').'member member ON member.id = cloudspreadlog.m_id',
            ))
            ->select();
        if(!$result){
            $result = array();
        }
        foreach($result as $k => $v){
            $result[$k]['create_time'] = date('Y-m-d',$result[$k]['create_time']);
        }
        apiResponse('success','',$result);
    }
    /**
     * 6  推广商品
     * 商家ID    merchant_id
     * 商品ID    goods_id
     * 推广次数  total_number
     * 支付价格  pay_money
     */
    public function spreadGoods($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['goods_id']){
            apiResponse('error','商品ID不能为空');
        }
        if(!$request['total_number']){
            apiResponse('error','请输入推广次数');
        }
        if(!$request['pay_money']){
            apiResponse('error','请输入支付价格');
        }
        $data['goods_id']    = $request['goods_id'];
        $data['merchant_id'] = $request['merchant_id'];
        $data['order_sn']    = time().rand(10000,99999);
        $data['total_number'] = $request['total_number'];
        $data['pay_money']   = $request['pay_money'];
        $data['create_time'] = time();
        $data['change_number'] = $request['total_number'];
        $result = M('CloudSpread') ->add($data);
        if(!$result){
            apiResponse('error','推广产品失败');
        }
        $res['c_s_id'] = $result;
        $res['order_sn'] = $data['order_sn'];
        $res['money']  = $data['pay_money'];

        apiResponse('success','推广商品成功',$res);
    }
    /**
     * 7  入驻积分商城  已入驻商品
     * 商家ID         merchant_id
     */
    public function enterGoodsList($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        $where['merchant_id'] = $request['merchant_id'];
        $where['status']      = array('IN','0,1');
        $result = M('IntegralMall') ->where($where) ->field('id as i_m_id, goods_name, pic, integral') ->select();
        if(!$result){
            $result = array();
        }
        foreach($result as $k => $v){
            $path = M('File') ->where(array('id'=>$v['pic'])) ->getField('path');
            $result[$k]['pic'] = $path?C("API_URL").$path:'';
        }
        apiResponse('success','',$result);
    }
    /**
     * 7  入驻积分商城  商品详情
     * 商家ID         merchant_id
     * 入驻商品ID     i_m_id
     */
    public function enterGoodsInfo($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['i_m_id']){
            apiResponse('error','入驻商品ID不能为空');
        }
        $where['merchant_id'] = $request['merchant_id'];
        $where['id']          = $request['i_m_id'];
        $result = M('IntegralMall') ->where($where) ->field('id as i_m_id, goods_name, pic, integral') ->find();
        $path = M('File') ->where(array('id'=>$result['pic'])) ->getField('path');
        $result['pic'] = $path?C("API_URL").$path:'';
        apiResponse('success','',$result);
    }
    /**
     * 7  入驻积分商城  新增商品
     * 商家ID        merchant_id
     * 商品名称      goods_name
     * 兑换积分      integral
     * 商品图片      pic
     */
    public function addEnterGoods($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['goods_name']){
            apiResponse('error','商品名称不能为空');
        }
        if(!$request['integral']){
            apiResponse('error','兑换积分不能为空');
        }
        //上传头像
        if (!empty($_FILES['pic']['name'])) {
            $res = api('UploadPic/upload', array(array('save_path' => 'IntegralMall')));
            foreach ($res as $value) {
                $data['pic'] = $value['id'];
            }
        }
        $data['merchant_id'] = $request['merchant_id'];
        $data['goods_name']  = $request['goods_name'];
        $data['integral']    = $request['integral'];
        $data['create_time'] = time();
        $result = M('IntegralMall') ->add($data);
        if(!$result){
            apiResponse('error','发布失败');
        }
        apiResponse('success','发布成功');
    }
    /**
     * 7  入驻积分商城  编辑商品
     * 商家ID           merchant_id
     * 商城ID           i_m_id
     * 商品名称         goods_name
     * 兑换积分         integral
     * 商品图片         pic
     */
    public function modifyEnterGoods($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['i_m_id']){
            apiResponse('error','商城商品ID不能为空');
        }
        if($request['goods_name']){
            $data['goods_name'] = $request['goods_name'];
        }
        if($request['integral']){
            $data['integral'] = $request['integral'];
        }
        //上传头像
        if (!empty($_FILES['pic']['name'])) {
            $res = api('UploadPic/upload', array(array('save_path' => 'IntegralMall')));
            foreach ($res as $value) {
                $data['pic'] = $value['id'];
            }
        }
        $where['id'] = $request['i_m_id'];
        $data['update_time'] = time();
        $result = M('IntegralMall') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','修改失败');
        }
        apiResponse('success','修改成功');
    }

    /**
     * [删除积分商城]
     * @param array $request
     */
    public function delIntegralGoods($request = array())
    {
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(empty($request['integral_id']) || !isset($request['integral_id'])) apiResponse('error','商城ID不能为空');
        $where['id'] = $request['integral_id']; // ID
        $where['merchant_id'] = $request['merchant_id']; // 商家ID
        $model = M('IntegralMall') -> where(array('id'=>$where['id'])) -> data(array('status'=>9)) ->save();
        if(!$model)  apiResponse('error','删除失败,请重新尝试');
        apiResponse('success','商品删除成功');
    }
    /**
     * 1 2  首页广告余额支付
     * 商家ID  merchant_id
     * 广告ID  ad_id
     */
    public function advertBalancePay($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['ad_id']){
            apiResponse('error','广告申请ID不能为空');
        }
        $merchant = M('Merchant') ->where(array('id'=>$request['merchant_id'])) ->find();
        $ad_order = M('AdPositionTotal') ->where(array('id'=>$request['ad_id'])) ->find();

        if($merchant['balance']<$ad_order['money']){
            apiResponse('error','商家余额不足');
        }
        $merchant_result = M('Merchant') ->where(array('id'=>$request['merchant_id'])) ->setDec('balance',$ad_order['money']);
        $data['pay_status'] = 1;
        $data['pay_type']   = 4;

        $res = M('AdPositionTotal') ->where(array('id'=>$request['ad_id'])) ->data($data) ->save();
        if(!$res){
            apiResponse('error','余额付款失败');
        }
        unset($data);
        unset($where);
        $time_date      = explode(',',$ad_order['time']);

        foreach($time_date as $k => $v){
            $start_time = strtotime($v);
            $end_time = $start_time + 86400;
            $data['url']    = $ad_order['url'];
            $data['pic']    = $ad_order['pic'];
            $data['region'] = $ad_order['region'];
            $data['type']   = $ad_order['type'];
            $data['merchant_id'] = $request['merchant_id'];
            $data['a_p_t_id']    = $request['ad_id'];
            $data['location']    = $ad_order['location'];
            $data['start_time']  = $start_time;
            $data['end_time']    = $end_time;
            $result = M('AdPosition') ->add($data);
        }
        unset($data);
        unset($where);
        $data['type']      = 2;
        $data['object_id'] = $request['merchant_id'];
        $data['title']     = '支出';
        if($ad_order['type'] == 1){
            $data['content']   = '首页广告位下单';
        }else{
            $data['content']   = '签到页广告位下单';
        }
        $data['symbol']    = 0;
        $data['money']     = $ad_order['money'];
        $data['create_time'] = time();
        $result = M('PayLog') ->add($data);
        apiResponse('success','广告位下单成功');
    }
    /**
     * 3 4 5  好货们的余额支付
     * 商家ID      merchant_id
     * 付款对象ID  f_t_id
     */
    public function goodsBalancePay($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['f_t_id']){
            apiResponse('error','付款对象ID不能为空');
        }
        $where['merchant_id'] = $request['merchant_id'];
        $where['id']          = $request['f_t_id'];
        $find_total = M('FindTotal') ->where($where) ->find();
        $merchant = M('Merchant') ->where(array('id'=>$request['merchant_id'])) ->find();
        if($merchant['balance'] < $find_total['money']){
            apiResponse('error','商家余额不足');
        }
        $merchant_res = M('Merchant') ->where(array('id'=>$request['merchant_id'])) ->setDec('balance',$find_total['money']);
        $data['pay_status'] = 1;
        $data['pay_type']   = 4;
        $result = M('FindTotal') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','余额支付失败');
        }
        unset($data);
        unset($where);
        $time_data = explode(',',$find_total['time']);
        foreach($time_data as $k => $v){
            $start_time = strtotime($v);
            $end_time = $start_time + 86400;
            $data['merchant_id'] = $request['merchant_id'];
            $data['type']        = $find_total['type'];
            $data['object_id']   = $find_total['object_id'];
            $data['start_time']  = $start_time;
            $data['end_time']    = $end_time;
            $data['f_t_id']      = $request['f_t_id'];
            $res = M('FindBranch') ->add($data);
        }
        unset($data);
        unset($where);
        $data['type']      = 2;
        $data['object_id'] = $request['merchant_id'];
        $data['title']     = '支出';
        if($find_total['type'] == 3){
            $data['content']   = '发现好商品下单';
        }elseif($find_total['type'] == 4) {
            $data['content']   = '发现好店下单';
        }else{
            $data['content']   = '发现好服务下单';
        }
        $data['symbol']    = 0;
        $data['money']     = $find_total['money'];
        $data['create_time'] = time();
        $result = M('PayLog') ->add($data);
        apiResponse('success','支付成功');
    }
    /**
     * 6  推广产品的余额支付
     * 商家ID       merchant_id
     * 推广对象ID   c_s_id
     */
    public function  spreadBalancePay($request = array()){
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if(!$request['c_s_id']){
            apiResponse('error','推广对象ID不能为空');
        }
        $where['merchant_id'] = $request['merchant_id'];
        $where['id']          = $request['c_s_id'];
        $CloudSpread = M('CloudSpread') ->where($where) ->find();
        $merchant = M('Merchant') ->where(array('id'=>$request['merchant_id'])) ->find();
        if($merchant['balance'] < $CloudSpread['pay_money']){
            apiResponse('error','商家余额不足');
        }
        $merchant_res = M('Merchant') ->where(array('id'=>$request['merchant_id'])) ->setDec('balance',$CloudSpread['pay_money']);
        $data['pay_status'] = 1;
        $data['pay_type']   = 4;
        $data['update_time'] = time();
        $result = M('CloudSpread') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','余额支付失败');
        }
        unset($where);
        unset($data);
        $data['type']      = 2;
        $data['object_id'] = $request['merchant_id'];
        $data['title']     = '支出';
        $data['content']   = '商品云推广';
        $data['symbol']    = 0;
        $data['money']     = $CloudSpread['pay_money'];
        $data['create_time'] = time();
        $result = M('PayLog') ->add($data);
        apiResponse('success','下单成功');
    }

    /*================= 用户 =================*/
    /**
     * 积分商城 列表
     * @param array $request
     * @author mr.zhou
     */
    public function integralList($request = array())
    {
        if(empty($request['m_id']) || !isset($request['m_id'])) apiResponse('error','用户ID不能为空');
        $result = array();
        $intrgral_list = M('IntegralMall') -> where(array('status'=>1)) -> field('id,goods_name,integral,pic') -> select();
        if($intrgral_list ){
            foreach($intrgral_list as $k=>$v){
                $result['integral_list'][$k]['integral_id'] = $v['id']; // 积分id
                $result['integral_list'][$k]['goods_name'] = $v['goods_name']; // 商品名
                $result['integral_list'][$k]['integral'] = $v['integral']; // 需要积分
                $result['integral_list'][$k]['pic'] = D('File') -> getFind($v['pic']); // 商品图片
            }
        }else{
            $result['integral_list'] = array();
        }
        $result['intrgral_balance'] = M('Member')->where(array('id'=>$request['m_id']))->getField('integral');
        apiResponse('success','成功',$result);
    }

    /**
     * 积分商城 兑换
     * @param array $request
     * @author mr.zhou
     */
    public function addIntegral($request = array()){
        if(empty($request['m_id']) || !isset($request['m_id'])) apiResponse('error','用户ID不能为空');
        if(empty($request['integral_mall_id']) || !isset($request['integral_mall_id'])) apiResponse('error','积分商品ID不能为空');
        if(empty($request['address']) || !isset($request['address'])) apiResponse('error','请输入您的地址');
        if(empty($request['name']) || !isset($request['name'])) apiResponse('error','请输入您的真实姓名');
        if(empty($request['phone']) || !isset($request['phone'])) apiResponse('error','请输入您的联系电话');
        $demand = M('IntegralMall') -> where(array('id'=>$request['integral_mall_id'])) -> getField('integral'); // 需要的积分
        $inAll  = M('Member') -> where(array('id'=>$request['m_id'])) -> getField('integral'); // 总共有多少积分
        if($demand > $inAll)apiResponse('error','积分不足');
        $data['m_id'] = $request['m_id'];
        $data['integral_mall_id'] = $request['integral_mall_id'];
        $data['address'] = $request['address'];
        $data['name'] = $request['name'];
        $data['phone'] = $request['phone'];
        $data['create_time'] = $data['update_time'] =time();
        $add = M('IntegralMallLog') -> data($data) -> add();
        if($add){
            M('Member') -> where(array('id'=>$request['m_id'])) -> setDec('integral',$demand);
            apiResponse('success','兑换积分商品成功');
        }else{
            apiResponse('error','兑换失败');
        }
    }

    /**
     * 积分商城 记录
     * @param array $request
     * @author mr.zhou
     */
    public function integralLog($request = array())
    {
        if(empty($request['m_id']) || !isset($request['m_id'])) apiResponse('error','用户ID不能为空');
        $result = array();
        $model =
            M('IntegralMallLog') ->
            alias('im') ->
            where(array('im.m_id'=>$request['m_id'])) ->
            join('__INTEGRAL_MALL__ as i ON i.id=im.integral_mall_id','LEFT')->field('i.goods_name,i.pic,i.integral,im.create_time')->select();
        foreach($model as $k=>$v){
            $pic = M('file') -> where(array('id'=>$v['pic']))->getField('path');
            $model[$k]['pic'] = $pic ? C('API_URL').$pic : '';
            $model[$k]['create_time'] = date('Y.m.d',$v['create_time']);
        }
        apiResponse('success','成功',$model);
    }


}