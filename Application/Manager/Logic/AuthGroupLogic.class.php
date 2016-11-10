<?php

namespace Manager\Logic;

/**
 * Class AuthGroupLogic
 * @package Manager\Logic
 * 分组权限 逻辑层
 */
class AuthGroupLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取分组列表
     */
    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);
        $param['order']             = 'id DESC';
        $param['page_size']         = C('LIST_ROWS');
        $param['parameter']         = $request;

        $result = D('AuthGroup')->getList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return bool
     * 删除分组前操作 验证是否该分组下存在管理员
     */
    protected function beforeSetStatus($request = array()) {
        if($request['status'] == 9) {
            //判断该分组下是否存在管理员
            $where['group_id'] = $request['ids'];
            $where['status']   = array('lt',9);
            $count = D('Administrator')->where($where)->count();
            if($count > 0) {
                $this->setLogicError('该分组下存在管理员，请先删除该分组下的全部管理员！'); return false;
            }
        }
        return true;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        }  else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('AuthGroup')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param array $data
     * @return array
     * 处理data数据 转化规则ID数组为字符串
     */
    protected function processData($data = array()) {
        //转化规则ID数组为字符串
        $data['rules'] = implode(',',$data['rules']);
        return $data;
    }

    /**
     * @param array $request
     * @return array
     * 获取权限规则列表
     */
    function getAccess($request = array()) {
        //根据ID 获取组的权限ID rules
        $rules = D('AuthGroup')->where(array('id'=>$request['id']))->getField('rules');
        //是否存在缓存
        $list = S('AuthRule_Cache');
        if(!$list) {
            //获取所有权限规则
            $list = D('AuthRule')->field('id,name,title,parent_id')->where(array('status'=>1))->select();
            //处理成树状结构
            $list = list_to_tree($list);
            S('AuthRule_Cache',$list);
        }
        $list = array('all_rules' => $list, 'have_rules' => explode(',',$rules));
        return $list;
    }
}