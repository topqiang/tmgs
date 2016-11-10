<?php
namespace Manager\Logic;


class OpenPageLogic extends BaseLogic {

    function getList($request = array()) { }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        $param['where']['id'] = 1;
        $row = D('OpenPage')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['picture'] = api('System/getFiles',array($row['picture']));

        return $row;
    }

}