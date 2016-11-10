<?php
namespace Manager\Logic;
/**
 * Created by PhpStorm.
 * User: xuexiaofeng
 * Date: 2015-10-12 0012
 * Time: 16:22
 * 城市相关 逻辑处理
 */
class RegionLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    public function getList($request = array()){
        //标题模糊查询
        if(!empty($request['region_name'])) {
            $param['where']['r.region_name']   = array('like','%'.$request['region_name'].'%');
        }
        $param['where']['r.region_type'] = 2;
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数
        $param['order'] = 'r.is_hot desc,r.sort desc';
        $result = D('Region')->getList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return boolean
     * @return array
     * 返回一行数据
     */
    public function findRow($request = array()){
        if(!empty($request['id'])) {
            $param['where']['r.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('Region')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }


}