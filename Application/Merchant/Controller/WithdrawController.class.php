<?php

namespace Merchant\Controller;

/**
 * @author zhouwei
 * Class WithdrawController
 * @package Merchant\Controller
 * 财务管理-提现管理-控制器
 */
class WithdrawController extends BaseController
{
    public $session = '' ;

    public function _initialize()
    {
        $this->session = session('merInfo');
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 首页附加
     */
    public function getIndexRelation()
    {
        $this->assign('balance',M('Merchant')->where(array('id'=>$this->session['mer_id']))->getField('balance'));
        $this->assign('awaitBalance',M('Order')->where(array('merchant_id'=>$this->session['mer_id'],'status'=>array('in','1,2,3')))->getField('SUM(trade_price) as tradeprice'));
    }
    /**
     * 添加
     */
    public function getAddRelation()
    {
        $row['MemberCard'] = M('member_card')->where(array('type'=>2,'m_id'=>$this->session['mer_id']))->field('id,card_number')->select();
        $row['balance'] = M('Merchant')->where(array('id'=>$this->session['mer_id']))->getField('balance');
        $this->assign('row',$row);
        parent::getAddRelation(); // TODO: Change the autogenerated stub
    }
}