<?php

namespace Manager\Logic;

/**
 * 楼层图
 * Class FloorPictureLogic
 * @package Manager\Logic
 */
class FloorPictureLogic extends BaseLogic {


    function getList($request = array()) {
        if(!empty($request['id'])){
            $param['where']['floor_id'] = $request['id'];
        }
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('FloorPicture')->getList($param);

        return $result;
    }


    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('FloorPicture')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}