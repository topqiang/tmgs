<?php

namespace Manager\Controller;

/**
 * 商家
 * Class MerchantController
 * @package Manager\Controller
 */
class MerchantController extends BaseController
{

    /**
     * 修改基本资料
     */
    function updateIndex()
    {
        $this->checkRule(self::$rule);
        if (!IS_POST) {
            if ($_GET['id']) {
                $Object = D(CONTROLLER_NAME, 'Logic');
                $row = $Object->findRow(I('get.'));
                if ($row) {
                    $this->getUpdateRelation();
                    $this->assign('region_province',M('region')->where(array('region_type'=>1))->select());
                    $this->assign('region_city',M('region')->where(array('parent_id'=>$row['province']))->select());
                    $this->assign('region_district',M('region')->where(array('parent_id'=>$row['city']))->select());
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
            $this->display('updateIndex');
        } else {
            $result = $_POST;
            $result['update_time'] = time();
            if ($result != '') {
                $where['id'] = $result['id'];
                unset($result['id']);
            }
            $Model = M('merchant')->where($where)->data($result)->save();
            if ($Model) {
                $this->success('成功', Cookie('__forward__'));
            } else {
                $this->error('失败');
            }
        }
    }

    /**
     * 修改验证资料
     */
    function updateVarify()
    {
        $this->checkRule(self::$rule);
        if (!IS_POST) {
            if ($_GET['id']) {
                $Object = D(CONTROLLER_NAME, 'Logic');
                $row = $Object->findRow(I('get.'));
                if ($row) {
                    $this->getUpdateRelation();
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
            $this->display('updateVarify');
        } else {
            $result = $_POST;
            $result['update_time'] = time();
            if ($result != '') {
                $where['id'] = $result['id'];
                unset($result['id']);
            }
            $Model = M('merchant')->where($where)->data($result)->save();
            if ($Model) {
                $this->success('成功', U('Merchant/updateIndex', array('id' => $where['id'])));
            } else {
                $this->error('失败');
            }
        }
    }

    /**
     * 修改余额
     */
    function updateBalance()
    {
        $this->checkRule(self::$rule);
        if (!IS_POST) {
            if ($_GET['id']) {
                $Object = D(CONTROLLER_NAME, 'Logic');
                $row = $Object->findRow(I('get.'));
                if ($row) {
                    $this->getUpdateRelation();
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
            $this->display('updateBalance');
        } else {
            $result = $_POST;
            $result['update_time'] = time();
            if ($result != '') {
                $where['id'] = $result['id'];
                unset($result['id']);
            }
            $Model = M('merchant')->where($where)->data($result)->save();
            if ($Model) {
                $this->success('成功', U('Merchant/updateIndex', array('id' => $where['id'])));
            } else {
                $this->error('失败');
            }
        }
    }

    /**
     * 添加
     */
    function add()
    {
        $MerModel = M('Merchant');
        $this->checkRule(self::$rule);
        if (!IS_POST) {
            $this->getAddRelation();
            $this->assign('region',M('region')->where(array('region_type'=>array('in','1')))->select());
            $this->display('add');
        } else {
            $M = M('Merchant')->where(array('account' => $_POST['account']))->count();
            if ($M > 0) $this->error('该手机号已存在');
            $getEaseMobRes = $this->getEaseMobRes();
            $emobCurl = D('Easemob','Service')->createUser($getEaseMobRes);
            if(!$emobCurl['error']){
                $result = $_POST;
                $result['easemob_account'] =$getEaseMobRes['username'];
                $result['easemob_password'] =$getEaseMobRes['password'];
                $result['create_time'] = time();
                $result['password'] = md5($result['password']);
                $result['status'] = 1;
                $add = D('Merchant') -> addRow($result);
            }
            if ($add) {
                $this->success('添加成功', U('Merchant/updateIndex', array('id' => $add)));
            } else {
                $this->error('添加商家失败');
            }
        }
    }

    /**
     * 验证商家密码
     */
    function verifyMerPass()
    {
        if ($_POST) {
            $M = M('Merchant')->where(array('account' => $_POST['account']))->count();
            if ($M > 0) {
                $this->ajaxReturn(array(
                    'status' => 2,
                    'msg'   =>  '该手机号已是商家'
                ));
            }
        }
    }

    /**
     * 验证管理员密码
     */
    function verifyPassWord()
    {
        if ($_POST) {
            $adminId = $_SESSION['toocms_admin']['admin']['a_id']; // 管理员密码
            $pass = trim($_POST['pass']);
            $M = M('administrator')->where(array('id' => $adminId))->getField('password');
            if ($M == md5($pass)) {
                $return['status'] = 1;
                $return['adminPass'] = $pass;
            } else {
                $return['status'] = 2;
            }
            $this->ajaxReturn($return);
        }
    }

    /**
     * 操作修改 好服务 好店铺
     */
    function handleGood()
    {
        $where['id'] = $_POST['id'];
        $status = $_POST['status'] == 1 ? 0 : 1;
        $data[$_POST['type']] = $status;
        $M = M('Merchant') -> where($where) -> data($data) -> save();
        if($M){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

     /**
     * 修改
     */
    function audit() {
        $this->checkRule(self::$rule);
        if(!IS_POST) {
            if ($_GET['id']) {
                $Object = D(CONTROLLER_NAME,'Logic');
                $row = $Object->findRow(I('get.'));                
                if ($row) {
                    $this->getUpdateRelation();
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
            $this->display('audit');
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

    /**
     * 地区
     */
    function getRegionList()
    {
        $post = $_POST;
        if($post){
            $where['parent_id'] = $post['parent_id'];
            $where['region_type'] = array('neq',0);
            $list = M('region')->where($where)->select();
            $this->ajaxReturn(array('data'=>$list,'status'=>1,'info'=>'获取成功'));return false;
        }
        $this->ajaxReturn(array('data'=>array(),'status'=>0,'info'=>'失败'));return false;

    }

}
