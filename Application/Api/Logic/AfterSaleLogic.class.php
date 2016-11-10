<?php

namespace Api\Logic;

/**
 * 售后处理
 * Class AfterSaleLogic
 * @package Api\Logic
 */
class AfterSaleLogic extends BaseLogic
{
    /**
     * 用户: 申请售后
     */
    public function afterSaleOne($request=array())
    {
        if(empty($request['type'])     || !isset($request['type']))  apiResponse('error','售后类型不能为空');
        if(empty($request['goods_status'])   || !isset($request['goods_status']))  apiResponse('error','货物状态不能为空');
        if(empty($request['reason'])   || !isset($request['reason']))  apiResponse('error','原因不能为空');
        if(empty($request['money'])    || !isset($request['money']))  apiResponse('error','退款金额不能为空');
        if(empty($request['m_id'])     || !isset($request['m_id']))  apiResponse('error','用户ID不能为空');
        if(empty($request['order_id']) || !isset($request['order_id']))  apiResponse('error','订单号不能为空');
        if(empty($request['merchant_id']) || !isset($request['merchant_id']))  apiResponse('error','商家ID不能为空');

        $data = array();
        $data['type']     = $request['type'] ;
        $data['goods_status']   = $request['goods_status'] ;
        $data['reason']   = $request['reason'] ;
        $data['m_id']     = $request['m_id'] ;
        $data['order_id'] = $request['order_id'] ;
        $order = M('order') -> where(array('id'=>$request['order_id'] )) ->getField('temp_status');
        if($order != '0'){
            apiResponse('error','您的订单有拒绝记录,不能在提交');
        }
        $data['merchant_id'] = $request['merchant_id'] ;
        $data['money'] = $request['money'] ;
        $data['status'] = 1;
        if($request['explan']) $data['explan']  = $request['explan']; // 说明

        if (!empty($_FILES['certificate']['name'])) {
            $res = api('UploadPic/upload', array(array('save_path' => 'AfterSale')));
            $certificate = array();
            foreach ($res as $value) {
                $certificate[] = $value['id'];
            }
            $data['certificate'] = implode(',',$certificate);
        }

        $add = M('AfterSale') -> data($data) -> add();
        if($add){
            $order_status = M('Order')->where(array('id'=>$request['order_id']))->getField('status');
            M('Order') -> where(array('id'=>$request['order_id'])) -> data(array('status'=>7,'temp_status'=>$order_status)) -> save();
            $sale_data['title'] = '买家发起申请';
            $sale[0]['key'] = '货物状态';
            $sale[1]['key'] = '原因';
            $sale[2]['key'] = '金额';
            $sale[0]['value'] = $request['goods_status'];
            $sale[1]['value'] = $request['reason'];
            $sale[2]['value'] = $request['money'];
            if($data['certificate']) $sale[3]['value'] = $data['certificate'] ;
                $sale_data['content'] = serialize($sale);
            $sale_data['status'] =  1;
            $sale_data['log_type'] =  1;
            $sale_data['as_id'] = $add;
            $sale_data['create_time'] = time();
            M('AfterSaleLog') -> data($sale_data) ->add();

            apiResponse('success','提交成功');
        }else{
            apiResponse('error','提交失败');
        }
    }

    /**
     * 商家:同意/拒绝
     */
    public function afterSaleTwo($request=array())
    {
        if(empty($request['order_id']) || !isset($request['order_id']))  apiResponse('error','订单号不能为空');
        if(empty($request['merchant_id']) || !isset($request['merchant_id']))  apiResponse('error','商家ID不能为空');
        if(empty($request['status']) || !isset($request['status']))  apiResponse('error','状态不能为空');
        $status = $request['status'];
        $model = M('AfterSale') -> where(array('order_id'=>$request['order_id'])) -> data(array('status'=>$status==1 ? 2 : 9))->save();
        if($status == 9){
            $temp = M('order') -> where(array('id'=>$request['order_id'])) -> getField('temp_status');
            M('order') -> where(array('id'=>$request['order_id'])) -> data(array('status'=>$temp)) -> save();
        }
        if($model){
            $asId = M('AfterSale') -> where(array('order_id'=>$request['order_id'])) -> getField('id');
            $sale_data['title'] = $status==1 ? '卖家已同意申请' : '卖家已拒绝申请';
            $sale_data['status'] =  $status==1 ? 2 : 9;
            $sale_data['log_type'] =  2;
            $sale_data['as_id'] = $asId;
            $sale_data['create_time'] = time();
            M('AfterSaleLog') -> data($sale_data) ->add();
            apiResponse('success','确认操作成功');
        }else{
            apiResponse('success','确认操作失败');
        }
    }



    /**
     * 商家:填写地址
     */
    public function afterSaleThree($request=array())
    {
        $request = $_POST;
        if(empty($request['order_id']) || !isset($request['order_id']))  apiResponse('error','订单号不能为空');
        // ==============
        if(empty($request['name']) || !isset($request['name']))  apiResponse('error','收货人不能为空');
        if(empty($request['phone']) || !isset($request['phone']))  apiResponse('error','电话不能为空');
        if(empty($request['address']) || !isset($request['address'])) apiResponse('error','请输入收货地址');
        if(empty($request['explain']) || !isset($request['explain'])) apiResponse('error','请输入说明');

        $model = M('AfterSale') -> where(array('order_id'=>$request['order_id'])) -> data(array('status'=>3))->save();
        if($model){
            $asId = M('AfterSale') -> where(array('order_id'=>$request['order_id'])) -> getField('id');
            $sale[0]['key'] = '退货地址';
            $sale[1]['key'] = '说明';
            $sale[0]['value'] = $request['address'] . '('.$request['name'].'收)'. $request['phone'] ;
            $sale[1]['value'] = $request['explain'];
            $sale_data['title'] = '卖家已确认退货地址';
            $sale_data['log_type'] =  2;
            $sale_data['content'] =  serialize($sale);
            $sale_data['as_id'] = $asId;
            $sale_data['status'] =  3;
            $sale_data['create_time'] = time();
            M('AfterSaleLog') -> data($sale_data) ->add();
            unset($sale,$sale_data['title'],$sale_data['content']);
            $sale_data['status'] =  0;
            $money = M('AfterSale') -> where(array('order_id'=>$request['order_id'])) -> getField('money');
            $sale_data['title'] = '淘米公社已冻结卖家资金'.$money.'元,确保退货资金安全';
            M('AfterSaleLog') -> data($sale_data) ->add();
            apiResponse('success','退货地址填写成功');
        }else{
            apiResponse('success','退货地址填写失败');
        }

    }

    /**
     * 用户:填写快递
     */
    public function afterSaleFour($request=array())
    {
        if(empty($request['order_id']) || !isset($request['order_id']))  apiResponse('error','订单号不能为空');
        if(empty($request['m_id']) || !isset($request['m_id']))  apiResponse('error','用户ID不能为空');
        if(empty($request['delivery_code']) || !isset($request['delivery_code'])) apiResponse('error','快递代码不能为空');
        if(empty($request['company_name']) || !isset($request['company_name'])) apiResponse('error','快递名称不能为空');
        if(empty($request['delivery_sn']) || !isset($request['delivery_sn'])) apiResponse('error','快递单号不能为空');

        $model = M('AfterSale') -> where(array('order_id'=>$request['order_id'])) -> data(array('status'=>4))->save();
        if($model){
            $asId = M('AfterSale') -> where(array('order_id'=>$request['order_id'])) -> getField('id');
            $sale[0]['key'] = '物流公司';
            $sale[1]['key'] = '物流单号';
            $sale[2]['key'] = '快递方式';
            $sale[0]['value'] =  $request['company_name'];
            $sale[1]['value'] =  $request['delivery_sn'];
            $sale[2]['value'] =  '快递';
            $sale[3]['value'] =  $request['delivery_code'];
            $sale_data['title'] = '买家已经退货';
            $sale_data['log_type'] =  1;
            $sale_data['status'] =  4;
            $sale_data['content'] =  serialize($sale);
            $sale_data['as_id'] = $asId;
            $sale_data['create_time'] = time();
            M('AfterSaleLog') -> data($sale_data) ->add();
            apiResponse('success','快递信息提交成功');
        }else{
            apiResponse('success','快递信息提交失败');
        }
    }

    /**
     * 商家:确认收货
     */
    public function afterSaleFive($request=array())
    {
        if(empty($request['order_id']) || !isset($request['order_id']))  apiResponse('error','订单号不能为空');
        if(empty($request['merchant_id']) || !isset($request['merchant_id']))  apiResponse('error','商家ID不能为空');

        $model = M('AfterSale') -> where(array('order_id'=>$request['order_id'])) -> data(array('status'=>6))->save();
        if($model){
            $asId = M('AfterSale') -> where(array('order_id'=>$request['order_id'])) -> getField('id');
            $sale_data['create_time'] = time();
            $sale_data['title'] = '卖家以确认收货';
            $sale_data['log_type'] =  2;
            $sale_data['status'] =  5;
            $sale_data['as_id'] = $asId;
                M('AfterSaleLog') -> data($sale_data) ->add();

            $money = M('AfterSale') -> where(array('order_id'=>$request['order_id'])) -> field('money,m_id') -> find();
            M('Member')->where(array('id'=>$money['m_id'])) ->setInc('balance',$money['money']);
            $order_money = M('order') -> where(array('id'=>$request['order_id'])) -> getField('totalprice');
            $backMoney = $order_money-$money['money'] ;
            if($backMoney > 0){
                M('Merchant')->where(array('id'=>$request['merchant_id'])) -> setInc('balance',$backMoney);
                $data['type'] = '2';
                $data['object_id'] = $request['merchant_id'];
                $data['title'] = '订单快递费';
                $data['content'] = '增加余额';
                $data['symbol'] = '1';
                $data['money'] = $backMoney;
                $data['create_time'] = time();
                M('PayLog') -> data($data) ->add();
                unset($data);
            }
            $sale_data['status'] =  0;
            $sale_data['title'] = '淘米公社转移冻结资金'.$money['money'].'元';
                M('AfterSaleLog') -> data($sale_data) ->add();

            $sale[0]['value'] =  '卖家已同意申请,并确认退货,成功退款'.$money['money'].'元';
            $sale_data['title'] = '退款成功';
            $sale_data['status'] =  6;
            $sale_data['content'] =  serialize($sale);
                M('AfterSaleLog') -> data($sale_data) ->add();

            $data['type'] = '1';
            $data['object_id'] = $money['m_id'];
            $data['title'] = '订单退款';
            $data['content'] = '增加余额';
            $data['symbol'] = '1';
            $data['money'] = $money['money'];
            $data['create_time'] = time();
            M('PayLog') -> data($data) ->add();

            apiResponse('success','确认退货完成');
        }else{
            apiResponse('success','确认退货失败');
        }
    }

    /**
     * 获得 售后服务原因
     * @param $request array 返回原因
     */
    public function getSaleCause($request=array())
    {
        $config = M('Config') -> where(array('name'=>'AFTER_SALE_CAUSE')) -> getField('value');
        $config_arr = explode(',',$config);
        foreach($config_arr as $k=>$v){
            $result[]['reason'] = $v;
        }
        apiResponse('success','',$result);
    }

    /**
     * 售后处理 记录
     * @param array $request
     */
    public function merSaleLog($request=array()){
        if(empty($request['order_id']) || !isset($request['order_id']))apiResponse('error','订单ID不能为空');
        if(empty($request['merchant_id']) || !isset($request['merchant_id']) )apiResponse('error','商家ID不能为空');
        $afWhere['order_id'] = $request['order_id'];
        $afWhere['merchant_id'] = $request['merchant_id'];
        $id = M('AfterSale') -> where($afWhere) -> limit(1) -> order('id DESC')-> field('id,certificate') -> find();
        $model = M('AfterSaleLog') -> where(array('as_id'=>$id['id'])) -> order('create_time ASC') -> select();
        foreach($model as $k=>$v){
            $model[$k]['asl_id'] = $v['id'];unset($model[$k]['id']);
            if($v['status'] == 1){
                if(!empty($id['certificate'])){
                    $pic = explode(',',trim(trim($id['certificate'],',')));
                    $tmp_pic = M('file')->where(array('id'=>array('in',$pic)))->field('path')->select();
                    foreach($tmp_pic as $kk=>$vv)
                    {
                        $model[$k]['pic'][$kk]['path'] = C('API_URL') . $vv['path'];
                    }
                }else{
                    $model[$k]['pic'] = [];
                }

            }

            if(!empty($v['content'])){
                $content = unserialize($v['content']);
                $tmp = array();
                foreach($content as $kk =>$vv){
                    if($vv['key']){
                        $tmp[$kk]['list'] = $vv['key'] .':'. $vv['value'] ;
                    }else{
                        if($kk != 3 && $v['status'] != 1 || $v['status'] != 4 && $kk != 2){
                            $tmp[$kk]['list'] = $vv['value'] ;
                        }
                        if($v['status'] == 4){
                            $model[$k]['delivery_sn'] = $content[1]['value'];
                            $model[$k]['delivery_code'] = $content[3]['value'];
                        }
                    }
                }
                unset($content);
                $content = $tmp;
                $model[$k]['content'] = $content;
            }else{
                $model[$k]['content'] = [];
            }
        }
        apiResponse('success','',$model);

    }


    /**
     * 售后处理 记录 [用户]
     * @param array $request
     */
    public function memSaleLog($request=array()){
        if(empty($request['order_id']) || !isset($request['order_id']))apiResponse('error','订单ID不能为空');
        if(empty($request['m_id']) || !isset($request['m_id']) )apiResponse('error','商家ID不能为空');
        $afWhere['order_id'] = $request['order_id'];
        $afWhere['m_id'] = $request['m_id'];
        $id = M('AfterSale') -> where($afWhere) -> limit(1) -> order('id DESC')-> field('id,certificate') -> find();
        $model = M('AfterSaleLog') -> where(array('as_id'=>$id['id'])) -> order('create_time ASC') -> select();
        foreach($model as $k=>$v){
            $model[$k]['asl_id'] = $v['id'];unset($model[$k]['id']);
            if($v['status'] == 1){
                if(!empty($id['certificate'])){
                    $pic = explode(',',trim(trim($id['certificate'],',')));
                    $tmp_pic = M('file')->where(array('id'=>array('in',$pic)))->field('path')->select();
                    foreach($tmp_pic as $kk=>$vv)
                    {
                        $model[$k]['pic'][$kk]['path'] = C('API_URL') . $vv['path'];
                    }
                }else{
                    $model[$k]['pic'] = [];
                }

            }

            if(!empty($v['content'])){
                $content = unserialize($v['content']);
                $tmp = array();
                foreach($content as $kk =>$vv){
                    if($vv['key']){
                        $tmp[$kk]['list'] = $vv['key'] .':'. $vv['value'] ;
                    }else{
                        if($kk != 3 && $v['status'] != 1 || $v['status'] != 4 && $kk != 2){
                            $tmp[$kk]['list'] = $vv['value'] ;
                        }
                        if($v['status'] == 4){
                            $model[$k]['delivery_sn'] = $content[1]['value'];
                            $model[$k]['delivery_code'] = $content[3]['value'];
                        }
                    }
                }
                unset($content);
                $content = $tmp;
                $model[$k]['content'] = $content;
            }else{
                $model[$k]['content'] = [];
            }
        }
        apiResponse('success','',$model);

    }
}