<?php
namespace Manager\Api;

/**
 * Class ActionLogApi
 * @package Manager\Api
 * 记录日志
 */
class ActionLogApi {

    /**
     * 记录行为日志，并执行该行为的规则
     * @param string $unique_code 行为标识
     * @param string $record_model 触发行为的模型名
     * @param int $record_id 触发行为的记录id
     * @param int $a_id 执行行为的用户id
     * @return boolean
     */
    public static function actionLog($unique_code = '', $record_model = '', $record_id = 0, $a_id = 0) {

        //参数检查
        if(empty($unique_code) || empty($record_model) || empty($record_id)) {
            return '参数不能为空';
        }
        if(empty($a_id)) {
            $a_id = is_login();
        }

        //查询行为,判断是否正常
        $action = D('Action')->findRow(array('where'=>array('unique_code'=>$unique_code)));

        if($action['status'] != 1) {
            return '该行为被禁用或删除';
        }

        //插入行为日志
        $data['action_id']      =   $action['id'];
        $data['a_id']           =   $a_id;
        $data['action_ip']      =   ip2long(get_client_ip());
        $data['record_model']   =   $record_model;
        $data['record_id']      =   $record_id;
        $data['create_time']    =   NOW_TIME;
        $data['remark']         =   '操作url：'.$_SERVER['REQUEST_URI'];

        D('ActionLog')->data($data)->add();
    }
}