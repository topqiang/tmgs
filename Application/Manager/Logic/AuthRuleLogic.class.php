<?php

namespace Manager\Logic;

/**
 * [权限添加 AuthRule]
 * @author  zhouwei
 * Class AuthRuleLogic
 * @package Manager\Logic
 */
class AuthRuleLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if(!empty($request['parid'])){
            $param['where']['parent_id'] = $request['parid'];
        }else{
            $param['where']['parent_id'] = 0;
        }

        // $param['page_size']         = C('LIST_ROWS');        //页码
        $param['page_size']         = 100;        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('AuthRule')->getList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('AuthRule')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

     /**
     * @param array $request
     * @return bool
     * 彻底删除记录
     */
    function remove($request = array()) {
        //判断参数
        if(empty($request['model']) || empty($request['ids'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        //执行前操作
        if(!$this->beforeRemove($request)){ return false; }
        //判断数组ID 字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('IN', $request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }

        $result = D($request['model'])->where($where)->delete();

        if($result) {
            D($request['model'])->where(array('parent_id'=>$request['ids']))->delete();
            //行为日志
            api('Manager/ActionLog/actionLog', array('remove',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterRemove($result,$request)) { return false; }
            $this->setLogicSuccess('删除成功！'); return true;
        } else {
            $this->setLogicError('删除失败！'); return false;
        }
    }

}