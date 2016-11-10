<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/13
 * Time: 13:59
 */

namespace Common\Model;


class RegionModel extends BaseModel{

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {

        
        $model = $this->alias('r')
                    ->where($param['where'])
                    ->order($param['order']);
        $list = $model->select();
        return $list;
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {


    }


    function cityLinkage($param = array()){
        $result = D('Region')->where($param['where'])->filed('id,region_name')->select();
        return $result;
    }
}