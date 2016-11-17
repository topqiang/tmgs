<?php
namespace Wap\Controller;

/**
 * Class IntegralMallController
 * @package Api\Controller
 * 积分相关
 */
class IntegralMallController extends BaseController
{
    /**
     * 积分商城
     */
    public function integralmall()
    {
        $this->display();
    }

    /**
     * 联系方式
     */
    public function contactway()
    {
        $this->display();
    }

    /**
     * 兑换记录
     */
    public function jilu()
    {
        $this->display();
    }


    /**
     * 签到
     */
    public function arriveat()
    {
        $this->display();
    }


    /**
     * 抽奖
     */
    public function getgift()
    {
        $where['lo_type'] = array('in','0,1');
        $where['status'] = array('neq', 9);
        $lottery_list1 = M('Lottery')->where($where)->select();
        foreach ($lottery_list1 as $k => $v) {
            $list[$v['id']]['id'] = $v['id'];
            $list[$v['id']]['lo_name'] = $v['lo_name'];

            $list[$v['id']]['percent'] = $v['lo_percent'];
            //计算角度
            $list[$v['id']]['radius'] = (360 / count($lottery_list1)) * (count($lottery_list1) - $k - 1)
                + (mt_rand(10, 360 / count($lottery_list1) - 10))
                + (360 * 3);
        }
        session('m_id',$_GET['m_id']);
        $lottery_list3 = htmlspecialchars(json_encode($list));
        $pic = M('LotteryBackground') -> limit(1) -> getField('carousel_pic');
        $pic = M('file')->where(array('id'=>$pic))->getField('path');
        $this->assign('pic',$pic);
        $this->assign('app_type',$_GET['app_type']);
//        $this->assign('app_type','app');
        $this->assign('lottery_list', $lottery_list3);
        $this->assign('verkey', md5(md5(date('Y-m-d'))));
        $this->assign('m_id', $_GET['m_id']);
        $this->display();
    }

    /**
     * 结果
     */
    public function lottery()
    {
        $where['m_id'] = $_GET['m_id'];
        $where['lo_type'] =$_GET['lo_type'];
        $member = D('Member')->where(array('id'=>$where['m_id']))->find();
        $lottery = M('Lottery') -> where(array('id'=>$_GET['lo_id'])) ->find();
        if(empty($member['account']) ){
            // 账号为空
            exit(json_encode(array('status'=>4,'flag'=>2,'type'=>$lottery['lo_type'])));
        }
        if($member['integral'] < $lottery['lo_intergral']){
            // 积分不足
            exit(json_encode(array('status'=>3,'flag'=>2,'type'=>$lottery['lo_type'])));
        }
        $num  = M('LotteryOrder')->where(array('lottery_id'=>$lottery['id']))->count();
        if($num >= $lottery['lo_num'] && $lottery['lo_num'] != 0 && $lottery['lo_type'] !=0){
            exit(json_encode(array('status'=>7,'flag'=>2,'type'=>$lottery['lo_type'])));
        }
        // 减少积分
        M('Member') -> where(array('id'=>$_GET['m_id'])) -> setDec('integral', $lottery['lo_intergral']);
        // 增加记录
        M('IntegralCheckinLog') -> data(array('m_id'=>$_GET['m_id'],'number'=>$lottery['lo_intergral'],'title'=>'抽奖','state'=>0,'create_time'=>time()))->add();

        if($_GET['lo_id'] == 1){
            // 未抽到奖
            exit(json_encode(array('status'=>6,'flag'=>2,'type'=>$lottery['lo_type'])));
        }

        $data['m_id'] = $_GET['m_id'];
        $data['lottery_id'] = $_GET['lo_id'];
        $data['create_time'] = time();
        $data['phone'] = $member['account'];
        $data['con_inte'] = $lottery['lo_intergral'];
        $add = M('LotteryOrder') -> data($data) -> add();
        if($add){
            exit(json_encode(array('status'=>1,'flag'=>1,'type'=>$lottery['lo_type'],'lo_pic'=>$lottery['lo_pic'] )));
        }else{
            // 抽奖失败
            exit(json_encode(array('status'=>5,'flag'=>2,'type'=>$lottery['lo_type'])));
        }

    }

    /**
     * 没有中奖页面
     */
    public function regret()
    {
        $this->assign('app_type',$_GET['app_type']);
        $this->assign('m_id',$_GET['m_id']);
        $this->display();
    }

    /**
     * 中奖页面
     */
    public function awards()
    {
        $this->assign('app_type',$_GET['app_type']);
        $this->assign('m_id',$_GET['m_id']);
        $this->assign( 'back_pic', M('file')->where(array('id'=>$_GET['lo_pic']))->getField('path') );
        $this->display();
    }

    /**
     * 红包抽奖
     */
    public function redbag()
    {
        $pic = M('LotteryBackground') -> limit(1) -> getField('redbag_pic');
        $pic = M('file')->where(array('id'=>$pic))->getField('path');
        $this->assign('pic',$pic);
        $where['lo_type'] = array('in','0,2');
        $where['status'] = array('neq', 9);
        $lottery_list1 = M('Lottery')->where($where)->select();
        foreach($lottery_list1 as $k=>$v){
            $list[$v['id']] =$v['lo_percent'];
        }
        $this->assign('lo_id',$this->get_rand($list));
        $this->assign('app_type',$_GET['app_type']);
        $this->assign('m_id',$_GET['m_id']);
        $this->display();
    }


    //概率算法
    public  function get_rand($arr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = 0;
        // 提取所有的概率和
        foreach($arr as $key  =>$value){
            $proSum = $proSum + intval($value);
        }
        //概率数组循环
        foreach($arr as $key =>$value ){
            $randNum = floor(randFloat()*$proSum+1);
            if ($randNum <= intval($value)) {
                $result = $key;
                break;
            } else {
                $proSum -= intval($arr[$value]);
            }
        }
        return $result;
    }



}