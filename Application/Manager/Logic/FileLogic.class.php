<?php
namespace Manager\Logic;

/**
 * Class FileLogic
 * @package Manager\Logic
 * 文件逻辑层
 */
class FileLogic extends BaseLogic{


    /**
     * @param array $request
     * @return array
     */
    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');       //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('File')->getList($param);

        return $result;
    }


    function findRow($request = array()) {

    }
}