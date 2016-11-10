<?php

namespace Manager\Logic;

/**
 * 用户管理
 * Class MemberLogic
 * @package Manager\Logic
 */
class MemberLogic extends BaseLogic
{

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array())
    {
        foreach ($request as $k => $v) {
            if (!empty($v) && $k != 'p') {
                if ($k == 'create_time') {
                    $param['where'][$k] = array(array('egt', strtotime(trim($v))), array('elt', strtotime('+1day', strtotime(trim($v)))), 'AND');
                } elseif ($k == 'inv_id') {
                    $member =
                        M('member')->
                        alias('me')->
                        where(array('account' => array('like', '%' . trim($v) . '%')))->
                        join(C("DB_PREFIX").'relation as re ON me.id=re.parent_id')->
                        field('m_id')->
                        select();
                    $param['where']['id'] = array('in',implode(',',array_column($member,'m_id')));
                } elseif ($k == 'status') {
                    $param['where'][$k] = $v - 1;
                } else {
                    $param['where'][$k] = array('like', '%' . trim($v) . '%');
                }
            }
        }
//        $param['where']['status'] = array('neq', 9);        //状态
        $param['order'] = 'create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');        //页码
        $param['parameter'] = $request;             //拼接参数

        $result = D('Member')->getList($param);
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array())
    {
        if (!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！');
            return false;
        }
        $param['where']['status'] = array('lt', 9);
        $row = D('Member')->findRow($param);
        $row['head_pic'] = api('System/getFiles', array($row['head_pic']));
        if (!$row) {
            $this->setLogicError('未查到此记录！');
            return false;
        }
        return $row;
    }
}