<?php

namespace Manager\Controller;

/**
 * 统计模块
 * Class StatisticsController
 * @package Manager\Controller
 */
class StatisticsController extends BaseController {
    /**
     * 用户注册量统计
     *
     */
    public function userRegSta()
    {
        $row['userRegNum'] = number_format(M('Member') -> where(array('status'=>array('neq',9)))->field('id')->count()); //当前注册人数(用户)
        $row['MerRegNum'] = number_format(M('Merchant') -> where(array('status'=>array('neq',9)))->field('id')->count()); //当前注册人数
        $timea = array_column(M('Member') ->field('create_time')-> select(),'create_time');
        $timeb = array_column(M('Merchant')->field('create_time')->select(),'create_time');
        $timeArray = array_merge($timea,$timeb);
        $timeReturn = array('max'=>date('Y-m-d H:i:s',max($timeArray)),'min'=>date('Y-m-d H:i:s',min($timeArray)));
        if($_POST['start_time'] == ''){
            $start_time = date('Y-m-d',(time() - intval(7*24 * 3600)));
            $end_time = date('Y-m-d',time());
        }else{
            $start_time  =  $_POST['start_time'];
            $end_time = $_POST['end_time'];
        }
        $parameterArray = array(
            array('title'=>'用户注册量','where'=>array('status'=>array('neq',9)),'obj'=>M('Member'),'flag'=>array('Count','id')),
            array('title'=>'商家注册量','where'=>array('status'=>array('neq',9)),'obj'=>M('Merchant'),'flag'=>array('Count','id')),
        );
        $this->statisticsMap($start_time,$end_time,$parameterArray);
        $this->assign('time',$timeReturn);
        $this->assign('row',$row);
        $this->display();
    }

    /**
     * 平台销售额统计
     */
    public function saleSta()
    {
        $row = M('divide') -> field('account_platform_balance_a,account_platform_balance_b')->find(); // 分钱两个账号的钱
        $row['account_platform_balance_a'] = number_format($row['account_platform_balance_a'],2,'.',',');
        $row['account_platform_balance_b'] = number_format($row['account_platform_balance_b'],2,'.',',');
        $row['sale'] = number_format(M('order') -> where(array('status'=>array('NOTIN','0,6')))->getField('SUM(totalprice) as totalprice'),2,'.',',');
        $row['recharge'] = number_format(M('recharge') -> where(array('status'=>1))->getField('SUM(money) as money'),2,'.',',');
        $timea = array_column(M('order') ->field('submit_order_time')-> select(),'submit_order_time');
        $timeReturn = array('max'=>date('Y-m-d H:i:s',max($timea)),'min'=>date('Y-m-d H:i:s',min($timea)));
        if($_POST['start_time'] == ''){
            $start_time = date('Y-m-d',(time() - intval(7*24 * 3600)));
            $end_time = date('Y-m-d',time());
        }else{
            $start_time  =  $_POST['start_time'];
            $end_time = $_POST['end_time'];
        }
        $parameterArray = array(
            array('title'=>'平台销售额','where'=>array('status'=>array('NOTIN','0,6')),'obj'=>M('Order'),'flag'=>array('Count','SUM(totalprice)')),
        );
        $this->statisticsOrderMap($start_time,$end_time,$parameterArray);
        $this->assign('row',$row);
        $this->assign('time',$timeReturn);
        $this->display();
    }




    // 获取 统计图
    public function statisticsMap($start_time='',$end_time='',$parameter=array())
    {
        $abscissa =   D('Statistical','Service')->createX($start_time,$end_time);// 获取横坐标
        $abscissa= $abscissa['x_date'];
        $resultData =   D('Statistical','Service')->getLineData($start_time,$end_time,$parameter);
        $data = D('Statistical','Service')->createLine($resultData); // 获得时间段下的数据
        $this->assign('abscissa', $abscissa);
        $this->assign('data', $data);
    }


    // 获取 统计图
    public function statisticsOrderMap($start_time='',$end_time='',$parameter=array())
    {
        $abscissa =   D('Statistical','Service')->createX($start_time,$end_time);// 获取横坐标
        $abscissa= $abscissa['x_date'];
        $resultData =   D('Statistical','Service')->getLineOrderData($start_time,$end_time,$parameter);
        $data = D('Statistical','Service')->createLine($resultData); // 获得时间段下的数据
        $this->assign('abscissa', $abscissa);
        $this->assign('data', $data);
    }

}
