<?php
namespace Api\Logic;
/**
 * Class CheckInLogic
 * @package Api\Logic
 * 签到相关
 */
class CheckInLogic extends BaseLogic
{
    /**
     * @param $request
     * 签到页面
     * @var $model array 用户 积分相关信息
     * @var $integralRule array 积分规则信息
     */
    public function checkShow($request)
    {
        if(empty($request['m_id']) || !isset($request['m_id'])) apiResponse('error','用户ID不能为空');
        $model = M('Member') -> where(array('id'=>$request['m_id'])) -> field('integral,day') -> find();
        $integralRule = M('IntegralRule') -> alias('ir') -> limit(1) -> order('ir.id ASC') -> field('one,two,three,four,five,six,seven,f.path') ->join('__FILE__ as f ON f.id=ir.pic') ->find();
        $integralRule['path'] = C('API_URl'). $integralRule['path'] ;
        $result['intrgral_rule'] = $integralRule;
        $result['intrgral_member'] = $model;
        apiResponse('success','成功',$result);
    }

    /**
     * @param $request
     * 点击签到
     */
    public function checkClick($request)
    {
        if(empty($request['m_id']) || !isset($request['m_id'])) apiResponse('error','用户ID不能为空');
        if(empty($request['day']) || !isset($request['day'])) $request['day'] = 0;
        if($request['day'] >= 7)$request['day'] = 0;
        $end_checkin_time = M('Member') ->where(array('id'=>$request['m_id'])) ->field('end_checkin_time,integral')->find();
        if($end_checkin_time['end_checkin_time'] == strtotime(date('Y-m-d')))apiResponse('error','亲!今天您已签到');
        $tmp_day = $request['day'];
        // 返回积分规则
        $integralRule = M('IntegralRule') -> limit(1) -> order('id ASC') -> field('one,two,three,four,five,six,seven')->find();
        $integralRule = array_values($integralRule);
        $data['integral'] = $end_checkin_time['integral']*1 +  $integralRule[$tmp_day]; // 积分
        $data['day'] = $request['day'] >= 7 ? '7' : $request['day']*1 + 1 ;
        $data['end_checkin_time'] = strtotime(date('Y-m-d'));
        $checkIN = M('Member') -> where(array('id'=>$request['m_id']))->data($data)->save();
        if($checkIN){
            $log_data['m_id'] = $request['m_id'];
            $log_data['number']= $integralRule[$tmp_day];
            $log_data['create_time'] = time();
            M('IntegralCheckinLog') -> data($log_data)->add();
            apiResponse('success','签到成功,当前第'.$data['day'].'天');
        }else{
            apiResponse('error','签到失败');

        }
    }

}