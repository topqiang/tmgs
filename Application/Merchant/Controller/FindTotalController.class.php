<?php

namespace Merchant\Controller;

/**
 * 发现模块
 * @author zhouwei
 * Class FindTotalController
 * @package Merchant\Controller
 */
class FindTotalController extends BaseController
{
    /**
     * 添加
     */
    public function add() {
//        $this->checkRule(self::$rule);
        if(!IS_POST) {
            $this->getAddRelation();
            $this->display('add');
        } else {
            $Object = D(CONTROLLER_NAME,'Logic');
            $result = $Object->update(I('post.'));
            if($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

    public function getAddRelation()
    {
        $model = M('MerchantAdvertising')->where(array('type'=>array('in','3,4,5')))->field('money,title,type')->select();
        $this->assign('ma',$model);
        parent::getIndexRelation(); // TODO: Change the autogenerated stub
    }
    /**
     * [getGoodsName 获得商品名称]
     * @author zhouwei
     * @param  [type] $name [商品名称]
     * @return [type]       [description]
     */
    public function getGoodsName($name)
    {
        $session = session('merInfo');
        if($name != ''){
            $model = M() -> query("SELECT cn_goods_name,id FROM __GOODS__ WHERE trim(REPLACE(`cn_goods_name`,' ','')) LIKE trim(REPLACE('%".$name."%',' ','')) AND merchant_id = ".$session['mer_id']." AND status != 9 LIMIT 20");
            // $model =  M('Goods')->where("trim(REPLACE(`cn_goods_name`,' ','')) LIKE trim(REPLACE('%".$name."%',' ','')) AND merchant_id = ".$session['mer_id'])->field('cn_goods_name,id')->limit(20)->select();
        }else{
            $model =  array();
        }
        $this ->ajaxReturn($model);
    }

    /**
     * [addFindTotal 下单]
     * @var array data
     */
    public function addFindTotal()
    {
//============================= 参数 =============================
        $session = session('merInfo');
        sort($_POST['start_time']); // 排序选择的时间
        $data['merchant_id'] = $session['mer_id']; // 商家ID
        $data['type'] = $_POST['type']; // 办理 发现类目
        // type = 3 object_id 为 商品ID 其余 为 商家ID
        if($data['type'] == 3  && !empty($_POST['goods_id'])){
            $data['object_id'] = $_POST['goods_id']; // 商品ID
        }else{
            $data['object_id'] = $data['merchant_id']; // 商家ID
        }
        $data['money'] = $_POST['money']; // 价格
        $data['order_sn'] = time().rand(11111,99999); // 订单号
        $data['time'] = implode(',',$_POST['start_time']);  // 时间
        $data['pay_type'] = $_POST['pay_type']; // 支付方式
        $data['create_time'] = time(); // 价格
//============================= 参数 =============================
        // 当 没选择 商品的时候
        if($data['money'] == 0)$this->ajaxReturn(array('status'=>0,'info'=>'请选择投放时间','url'=>''));
        if($data['pay_type'] == 4){
            // 余额支付
            $merchant = M('Merchant')->where(array('id'=>$session['mer_id']))->getField('balance'); // 查询余额
            if($data['money'] > $merchant){
                $this->ajaxReturn(array('status'=>0,'info'=>'您的账户余额不足,请更换支付方式','url'=>''));
            }else{
                $data['pay_status'] = 1;
                $add = M('FindTotal')->data($data)->add(); // 添加总订单
                M('Merchant')->where(array('id'=>$session['mer_id']))->setDec('balance',$data['money']); // 商家余额减掉 $data['money']
                $this->addPayLog(array('type' => 2,'object_id'=>$session['mer_id'],'title'=> discover($data['type']),'content'=> '余额支付','symbol'=>0,'money'=>$_POST['money'])); // 支付记录
                foreach($_POST['start_time'] as $k => $v){
                    $list[$k]['type'] = $data['type'];
                    $list[$k]['object_id'] = $data['object_id'];
                    $list[$k]['merchant_id'] = $data['merchant_id'];
                    $list[$k]['f_t_id'] = $add;
                    $list[$k]['start_time'] = strtotime($v);
                    $list[$k]['end_time'] = strtotime("+1day",strtotime($v));
                }
                $AddArr = M('FindBranch')->addAll($list);
                if($AddArr){
                    $this->ajaxReturn(array('status'=>1,'info'=>'办理成功','url'=>U('FindTotal/Index')));
                }
            }
        }else{
            // 第三方支付
            $add = M('FindTotal')->data($data)->add();
            if($add){
                $this -> ajaxReturn(array(
                        'status'=>1,
                        'info'=>'即将跳转到支付界面',
                        'data'=>
                            array('order_sn'=>$data['order_sn']),
                        'url'=>''
                    ));
            }else{
                $this -> ajaxReturn(array(
                    'status' => 0,
                    'info'  => '下单失败'
                ));
            }
        }
    }



}