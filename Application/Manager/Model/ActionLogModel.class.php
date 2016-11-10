<?php
namespace Manager\Model;

/**
 * Class ActionLogModel
 * @package Manager\Model
 * 行为日志 模型
 */
class ActionLogModel extends BaseModel {


    /**
     * @param array $param  综合条件参数
     * @return array
     * 获取列表
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->alias('act_log')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->alias('act_log')
                        ->field('act_log.*,act.unique_code,act.name,admin.account')
                        ->where($param['where'])
                        ->join(array(
                            'LEFT JOIN '.C('DB_PREFIX').'action act ON act.id = act_log.action_id AND act.status < 9',
                            'LEFT JOIN '.C('DB_PREFIX').'administrator admin ON admin.id = act_log.a_id AND admin.status < 9',
                        ))
                        ->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param $param
     * @return mixed
     * 获取一行
     */
    function findRow($param = array()) {
        $row = $this->alias('act_log')
                    ->field('act_log.*,act.unique_code,act.name,act.remark action_remark,admin.account')
                    ->where($param['where'])
                    ->join(array(
                        'LEFT JOIN '.C('DB_PREFIX').'action act ON act.id = act_log.action_id AND act.status < 9',
                        'LEFT JOIN '.C('DB_PREFIX').'administrator admin ON admin.id = act_log.a_id AND admin.status < 9',
                    ))
                    ->find();
        return $row;
    }
}