<?php

namespace Api\Logic;

/**
 * Class DonateLoveLogic
 * @package Api\Logic
 * 爱心捐助
 */
class DonateLoveLogic extends BaseLogic
{
    /**
     * @param $request
     * 爱心捐助
     */
    public function donateLoveList($request = array())
    {
        // 开头轮播图
        $carousel = M('LoveMap')->limit(1)->order('id ASC')->field('pic1,pic2,pic3,pic4,pic5')->find();
        if ($carousel) {
            foreach ($carousel as $k => $v) {
                $temp[]['pic'] = C('API_URL') . M('file')->where(array('id' => $v))->getField('path');
            }
            $result['love_pic'] = $temp;
        } else {
            $result['love_pic'] = [];
        }
        $result['love_sum'] = M('DonateLoveOrder')->field('id')->count(); // 统计爱心订单总数
        $model = M('DonateLove')->
        alias('d')->
        join('__FILE__ as f ON d.cover_pic=f.id')->
        field('d.id as dl_id, d.project_name, d.project_aims_money, d.project_current_money, f.path,d.project_Introduction,d.status')->
        select();
        if ($model) {
            foreach ($model as $k => $v) {
                $model[$k]['path'] = C('API_URL') . $v['path'];
                if($v['project_current_money'] / $v['project_aims_money'] >= 1){
                    $model[$k]['completeness'] = 1;
                }else{
                    $model[$k]['completeness'] = number_format($v['project_current_money'] / $v['project_aims_money'], 2, '.', '');  // 当前完成度
                }
                $model[$k]['order_num'] = M('DonateLoveOrder')->where(array('dl_id' => $v['id']))->count(); // 统计当前订单的订单数
                if ($v['status'] == 1) {
                    unset($model[$k]['status']);
                    $result['loveing_project'][] = $model[$k];
                }
                if ($v['status'] == 2) {
                    unset($model[$k]['status']);
                    $result['loveout_project'][] = $model[$k];
                }
            }
        } else {
            $result['loveout_project'] = [];
            $result['loveing_project'] = [];
        }
        apiResponse('success', '成功', $result);
    }

    /**
     * @param $request
     * 捐助详情
     */
    public function donateLoveDetail($request = array())
    {
        if (empty($request['dl_id']) || !isset($request['dl_id'])) apiResponse('error', '项目ID不能为空');
        if (empty($request['m_id']) || !isset($request['m_id'])) apiResponse('error', '用户ID不能为空');

        // 项目详情
        $object_detail = M('DonateLove')->where(array('id' => $request['dl_id']))->field('id as dl_id,project_name,project_Introduction,project_aims_money,project_current_money,project_content,contacts,status,top_pic')->find();
        if ($object_detail) {
            $object_detail['top_pic'] = C('API_URl') . M('file')->where(array('id' => $object_detail['top_pic']))->getField('path');
             $content = $object_detail['project_content'];
             preg_match_all('/src=\"\/?(.*?)\"/',$content,$match);
            foreach($match[1] as $key => $src){
                if(!strpos($src,'://')){
                    $content = str_replace('/'.$src,'http://'.$_SERVER['HTTP_HOST']."/".$src."\" width=80% height=au" , $content);
                }
            }
            $object_detail['project_content'] = $content;
            $object_detail['completeness'] = number_format($object_detail['project_current_money'] / $object_detail['project_aims_money'], 2, '.', '');
            $object_detail['order_num'] = M('DonateLoveOrder')->where(array('dl_id' => $object_detail['dl_id']))->count(); // 统计当前订单的订单数
        }
        // 捐助动态
        $object_dynamic =
            M('DonateLoveOrder')->
            alias('d')->
            where(array('d.dl_id' => $request['dl_id'], 'd.pay_status' => 1))->
            join('__MEMBER__ as m ON m.id=d.m_id', 'LEFT')->
            join('__FILE__ as f ON f.id=m.head_pic', "LEFT")->
            limit('1')->
            order('create_time DESC')->
            field("d.money,d.create_time,d.comment,m.nickname,IFNULL(f.path,'') as path,m.id as m_id")->
            find();
        $object_dynamic['money'] = $object_dynamic['money'] ? $object_dynamic['money'] : '';
        $object_dynamic['comment'] = $object_dynamic['comment'] ? $object_dynamic['comment'] : '';
        $object_dynamic['nickname'] = $object_dynamic['nickname'] ? $object_dynamic['nickname'] : '';
        $object_dynamic['m_id'] = $object_dynamic['m_id'] ? $object_dynamic['m_id'] : '';
        $object_dynamic['path'] = $object_dynamic['path'] ? C('API_URL') . $object_dynamic['path'] : '';
        $object_dynamic['create_time'] = $object_dynamic['create_time'] ? date('Y.m.d', $object_dynamic['create_time']) : '';
        $result['object_detail'] = $object_detail ? $object_detail : [];
        $result['object_dynamic'] = $object_dynamic ? $object_dynamic : [];
        apiResponse('success', '成功', $result);
    }

    /**
     * @param array $request
     * 捐助详情
     */
    public function donateLoveComment($request = array())
    {
        if (empty($request['dl_id']) || !isset($request['dl_id'])) apiResponse('error', '项目ID不能为空');
        if (empty($request['p']) || !isset($request['p'])) apiResponse('error', '页码不能为空');
        $object_count = M('DonateLoveOrder')->where(array('dl_id' => $request['dl_id'], 'pay_status' => 1))->count();
        $object_dynamic =
            M('DonateLoveOrder')->
            alias('d')->
            where(array('d.dl_id' => $request['dl_id'], 'd.pay_status' => 1))->
            join('__MEMBER__ as m ON m.id=d.m_id', 'LEFT')->
            join('__FILE__ as f ON f.id=m.head_pic', "LEFT")->
            order('create_time DESC')->
            field("d.money,d.create_time,d.comment,m.nickname,IFNULL(f.path,'') as path,m.id as m_id")->
            page($request['p'] ? $request['p'] : 1, 10)->
            select();
        foreach ($object_dynamic as $k => $v) {
            $object_dynamic[$k]['path'] = $v['path'] ? C('API_URL') . $v['path'] : '';
            $object_dynamic[$k]['create_time'] = date('Y.m.d', $v['create_time']);
            $object_dynamic[$k]['comment'] = $object_dynamic[$k]['comment'] ? $object_dynamic[$k]['comment'] : '该用户暂未评论';
        }
        $result['object_count'] = $object_count ? $object_count : 0;
        $result['object_dynamic'] = $object_dynamic ? $object_dynamic : [];
        apiResponse('success', '', $result);

    }

    /**
     * @param array $request
     * 他的捐助
     */
    public function donateLoveHim($request = array())
    {
        if (empty($request['m_id']) || !isset($request['m_id'])) apiResponse('error', '用户ID不能为空');

        $Model = M('DonateLoveOrder')->where(array('m_id' => $request['m_id'], 'pay_status' => 1))->field('dl_id,money,create_time')->select();
        if ($Model) {
            foreach ($Model as $k => $v) {
                $Model[$k]['create_time'] = date('Y.m.d', $v['create_time']);
                $Model[$k]['project_name'] = M('DonateLove ')->where(array('id' => $v['dl_id']))->getField('project_name');
                unset($Model[$k]['dl_id']);
            }
            $result['him_list'] = $Model;
        } else {
            $result['him_list'] = [];
        }

        $user = M('Member')->alias('m')->where(array('m.id' => $request['m_id']))->join('__FILE__ as f ON f.id=m.head_pic', 'LEFT')->field('f.path,m.nickname')->find();
        if ($user) {
            $user['path'] = $user['path'] ? C('API_URL') . $user['path'] : '';
            $result['him_info'] = $user;
        } else {
            $result['him_info'] = [];
        }
        apiResponse('success', '', $result);
    }

    /**
     * 用户协议
     */
    public function donateLoveAgreement($request = array())
    {
        $result = M('Article')->where(array('id' => 17))->field('title,cn_content')->find();
        $result = $result ? $result : [];
        apiResponse('success', '成功', $result);
    }

    /**
     * 评价
     */
    public function donateLoveEvaluate($request = array())
    {
        if (empty($request['order_sn']) || !isset($request['order_sn'])) apiResponse('error', '捐助记录订单号不能为空');
        if (empty($request['m_id']) || !isset($request['m_id'])) apiResponse('error', '用户ID不能为空');
        if (empty($request['comment']) || !isset($request['comment'])) $request['comment'] = '该用户暂未评论';
        $model = M('DonateLoveOrder')->where(array('order_sn' => trim($request['order_sn']), 'm_id' => $request['m_id']))->data(array('comment' => $request['comment']))->save();
        if (!$model) apiResponse('error', '评论失败');
        apiResponse('success', '评论成功,感谢您的评论!');
    }

    /**
     * 增加爱心捐助记录
     */
    public function donateLoveOrder($request = array())
    {
        if (empty($request['dl_id']) || !isset($request['dl_id'])) apiResponse('error', '爱心项目ID不能为空');
        if (empty($request['m_id']) || !isset($request['m_id'])) apiResponse('error', '用户ID不能为空');
        if (empty($request['money']) || !isset($request['money'])) apiResponse('error', '金额不能为空');
        $data['dl_id'] = $request['dl_id'];
        $data['m_id'] = $request['m_id'];
        $data['money'] = $request['money'];
        $data['order_sn'] = time() . rand(11111,99999);
        $data['create_time'] = time();
        $model = M('DonateLoveOrder') -> data($data) -> add();
        if($model){
            $result['order_sn'] =  $data['order_sn'];
            $result['money'] = $request['money'];
            apiResponse('success','下单成功',$result);
        }else{
            apiResponse('error','下单失败');
        }
    }

    /**
     * 订单状态
     */
    public function getOrderStatus($request = array())
    {
        $where['order_sn'] =$request['order_sn'];
        $loveOrder =M('DonateLoveOrder')->where($where)->find();
        if($loveOrder['pay_status'] == 1){
            apiResponse('success','支付成功');
        }else{
            apiResponse('error','支付失败');

        }
    }

    /**
     * 余额支付
     */
    public function loveOrderBalancePay($request = array())
    {
        if (empty($request['money']) || !isset($request['money'])) apiResponse('error', '金额不能为空');
        if (empty($request['order_sn']) || !isset($request['order_sn'])) apiResponse('error', '订单号不能为空');
        if (empty($request['m_id']) || !isset($request['m_id'])) apiResponse('error', '用户ID不能为空');

        $banalce = M('Member') -> where(array('id'=>$request['m_id'],'balance'=>array('egt',$request['money']))) -> find();
        if(!$banalce) apiResponse('error','您的余额不足!');

        $save = M('DonateLoveOrder') -> where(array('order_sn'=>$request['order_sn'])) -> data(array('pay_type'=>4,'pay_status'=>1)) -> save();
        if($save){
            M('Member') -> where(array('id'=>$request['m_id'])) -> setDec('balance',$request['money']);
            $id =  M('DonateLoveOrder') -> where(array('order_sn'=>$request['order_sn'])) -> getField('dl_id');
            M('DonateLove')->where(array('id'=>$id))->setInc('project_current_money',$request['money']);
            $count = M('DonateLove') -> where(array('id'=>$id)) -> field('project_current_money,project_aims_money')->find();
            if($count['project_current_money'] >= $count['project_aims_money'] ){
                M('DonateLove') -> where(array('id'=>$id)) -> data(array('status'=>2)) ->  save();
            }
            $result['order_sn'] = $request['order_sn'];
            apiResponse('success','支付成功!',$result);
        }else{
            apiResponse('error','支付失败!');
        }
    }


}