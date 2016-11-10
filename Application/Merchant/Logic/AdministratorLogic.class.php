<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 管理员逻辑处理类
 */
class AdministratorLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['admin.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }

        $param['where']['admin.status'] = array('lt',9);
        $row = D('Administrator')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param array $request
     * @return mixed
     * 获取管理员分组列表
     */
    function getGroupList($request = array()) {
        $param['where']['status'] = 1;
        $result = D('AuthGroup')->getList($param);
        return $result;
    }


    /**
     * @param array $data
     * @return array
     * 处理data数据 密码MD5加密 创建唯一标示
     */
    protected function processData($data = array()) {
        if(empty($data['id'])) {
            $data['password'] = MD5(MD5($data['password']));
            $data['unique_code'] = date('YmdHis').get_vc(1,1);
        }
        return $data;
    }

    /**
     * @param array $request
     * @return bool
     * 修改管理员状态之前 验证是否超级管理员
     */
    protected function beforeSetStatus($request = array()) {
        if($request['ids'] == C('USER_ADMINISTRATOR')) {
            $this->setLogicError('不允许对超级管理员进行此操作！'); return false;
        }
        return true;
    }

    /**
     * @param array $request
     * @return bool
     * 登录函数
     */
    function login($request = array()) {
        if(empty($request['account'])) {
            $this->setLogicError('请输入登录账号！'); return false;
        }
        if(empty($request['password'])) {
            $this->setLogicError('请输入登录密码！'); return false;
        }
//        if(empty($request['verify'])) {
//            $this->setLogicError('请输入验证码！'); return false;
//        }
//        //检测验证码
//        if(!check_verify($request['verify'])){
//            $this->setLogicError('验证码输入错误！'); return false;
//        }
        $param['where']['account'] = $request['account'];
        $param['where']['password'] = MD5($request['password']);

        $admin = D('Administrator')->findRow($param);
        if($admin) {
			//判断该账号是否正常
            switch($admin['status']){
                case 2:
                    $this->setLogicError('该账户正在审核。'); return false;
                case 3:
                    $this->setLogicError('该账户审核失败,请重新审核。'); return false;
                case 4:
                    $this->setLogicError('该账户尚未申请商家,请先登录APP申请成为商家。'); return false;
                case 9:
                    $this->setLogicError('该账户不存在,请重新尝试。');return false;
            }
			//添加日志
            api('Merchant/ActionLog/actionLog', array('login', 'Administrator', $admin['id'], $admin['id']));
            //登录成功 修改登录信息 设置session
            $this->autoCZ($admin);
            $this->setLogicSuccess('登陆成功！'); return true;
        } else {
            //登录失败
            $this->setLogicError('账号或密码错误！'); return false;
        }
    }

    /**
     * @param $admin
     * 更新登录信息
     * 设置session
     */
    private function autoCZ($admin) {
        /* 更新登录信息 */
        $data = array(
            'model'           => 'Merchant',
            'id'              => $admin['id'],
            'login'           => array('exp', '`login`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $this->update($data);

        /* 记录登录SESSION和COOKIES */
        $session = array(
            'mer_id' => $admin['id'],
            'mer_account' => $admin['account'],
            'last_login_time' => $admin['last_login_time'],
        );

        session('merInfo', $session);
        session('mer_sign', data_auth_sign($session));
    }

    /**
     * @param array $request
     * @return bool|mixed
     * 修改密码
     */
    function rePass($request = array()) {
        if(empty($request['old_password'])) {
            $this->setLogicError('请输入原密码！'); return false;
        } if(empty($request['new_password'])) {
            $this->setLogicError('请输入新密码！'); return false;
        } if(strlen($request['new_password']) < 6 || strlen($request['new_password']) > 18) {
            $this->setLogicError('新密码长度在6--18位之间！'); return false;
        } if($request['re_new_password'] != $request['new_password']) {
            $this->setLogicError('确认新密码与新密码不一致！'); return false;
        }

        //修改
        $data['password'] = MD5($request['new_password']);
        $where['id'] = AID;
        $result = D('Merchant')->where($where)->data($data)->save();
        if($result){
            $this->setLogicSuccess('修改成功！');
        }else{
            $this->setLogicError('原密码不正确！');
        }
        return $result;
    }
}