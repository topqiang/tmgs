<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/13
 * Time: 13:59
 */

namespace Common\Model;


class PayLogModel extends BaseModel{

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {

        if(!empty($param['page_size'])) {
            $total      = $this->alias('pl')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model = $this->alias('pl')
                    ->where($param['where'])
                    ->field('pl.pay_fee,pl.pay_name,pl.create_time,pl.status')
                    ->order($param['order']);
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

}