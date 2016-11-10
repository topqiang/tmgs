<?php

namespace Manager\Logic;

/**
 * 商品
 * Class GoodsLogic
 * @package Manager\Logic
 */
class GoodsLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        foreach ($request as $k => $v) {
            if(!empty($v) && $k != 'p'){
                if ($k == 'create_time') {
                    $param['where'][$k] = array(array('egt',strtotime(trim($v))),array( 'elt',strtotime('+1day',strtotime(trim($v))) ),'AND');
                } elseif ($k == 'merchant_id') {
                    $merchant_id = M('merchant') -> where(array('merchant_name'=>array('like','%'.$v.'%')))->field('id')->select();
                    $str = implode(',',array_column($merchant_id,'id'));
                    $param['where'][$k] = array('in',$str);
                } elseif($k == 'is_shelves' || $k == 'audit_status'){
                    $param['where'][$k] = $v - 1;
                } else {
                    $param['where'][$k] = array('like','%'.trim($v).'%');
                }
            }
        }
        $param['where']['status']   = array('lt',9);        //状态
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['order']             = 'sort DESC,create_time DESC';   //排序
        $param['parameter']         = $request;             //拼接参数
        $result = D('Goods')->getList($param);
        return $result;
    }
     /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getNotList($request = array()) {
        foreach ($request as $k => $v) {
            if(!empty($v) && $k != 'p'){
                if ($k == 'create_time') {
                    $param['where'][$k] = array(array('egt',strtotime(trim($v))),array('elt',time()),'AND');
                } elseif ($k == 'merchant_id') {
                    $merchant_id = M('merchant') -> where(array('merchant_name'=>array('like','%'.$v.'%')))->field('id')->select();
                    $str = implode(',',array_column($merchant_id,'id'));
                    $param['where'][$k] = array('in',$str);
                } elseif($k == 'is_shelves' || $k == 'audit_status'){
                    $param['where'][$k] = $v - 1;
                } else {
                    $param['where'][$k] = array('like','%'.trim($v).'%');
                }
            }
        }
        $param['where']['audit_status'] = 0;
        $param['where']['status']   = array('lt',9);        //状态
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['order']             = 'sort DESC,create_time DESC';   //排序
        $param['parameter']         = $request;             //拼接参数
        $result = D('Goods')->getList($param);
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        if(!empty($request['cn_goods_name'])){
            $param['where']['cn_goods_name'] = array('like','%'.$request['cn_goods_name'].'%');
        }
        $param['where']['status'] = array('lt',9);
        $row = D('Goods')->findRow($param);
        $row['merchant_id'] = M('merchant') ->where(array('id'=>$row['merchant_id'])) -> getField('merchant_name');
        $row['unit_id'] = M('unit') ->where(array('id'=>$row['unit_id'])) -> getField('unit');
        $row['fir_g_t_id'] = M('goods_type') -> where(array('id'=>$row['fir_g_t_id'])) -> getField('cn_type_name');
        $row['sec_g_t_id'] = M('goods_type') -> where(array('id'=>$row['sec_g_t_id'])) -> getField('cn_type_name');
        $row['thr_g_t_id'] = M('goods_type') -> where(array('id'=>$row['thr_g_t_id'])) -> getField('cn_type_name');
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param array $request  model 模型  ids操作的主键ID  status要改为的状态
     * @return bool
     * 修改状态
     */
    function setStatus($request = array()) {
        //判断参数
        if(empty($request['model']) || empty($request['ids']) || !isset($request['is_shelves'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        //执行前操作
        if(!$this->beforeSetStatus($request)) { return false; }
        //判断是数组ID还是字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in',$request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }

        $data = array(
            'is_shelves'        => $request['is_shelves'],
            'update_time'   => time()
        );

        $result = D($request['model'])->where($where)->data($data)->save();

        if($result) {
            //行为日志
            api('Merchant/Goods/setStatus', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }
 /**
     * @param array $request  model 模型  ids操作的主键ID  status要改为的状态
     * @return bool
     * 修改状态
     */
    function setStatusAudit($request = array()) {
        //判断参数
        if(empty($request['model']) || empty($request['ids']) || !isset($request['audit_status'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        //执行前操作
        if(!$this->beforeSetStatus($request)) { return false; }
        //判断是数组ID还是字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in',$request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }

        $data = array(
            'audit_status'        => $request['audit_status'],
            'update_time'   => time()
        );

        $result = D($request['model'])->where($where)->data($data)->save();

        if($result) {
            //行为日志
            api('Merchant/Goods/setStatus', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }

}