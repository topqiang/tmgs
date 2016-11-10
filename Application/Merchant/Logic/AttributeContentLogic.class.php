<?php

namespace Merchant\Logic;

/**
 * Class ActionLogic
 * @package Manager\Logic
 * 行为信息 逻辑层
 */
class AttributeContentLogic extends BaseLogic {
    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        $session = session('merInfo'); // mer_id
        $param['where']['is_merchant'] = $session['mer_id'];
        $param['where']['attr_id'] = $_GET['id'];
        $param['where']['status'] = array('neq',9);
        $param['order']             = 'is_merchant DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数
        $result = D('AttributeContent')->getList($param);
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
        $param['where']['status'] = array('lt',9);
        $row = D('AttributeContent')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param array $request
     * @var int $count_model 统计含有属性值的个数
     * @return bool
     * 彻底删除记录
     */
    function remove($request = array()) {
        $session = session('merInfo');
//        判断参数
        if(empty($request['model']) || empty($request['ids'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        $model =M()->query("SELECT `id`,`attr_key_group`,`goods_id`,`merchant_id` FROM __PREFIX__goods_product WHERE status != '9' AND merchant_id=".$session['mer_id']." AND FIND_IN_SET('".$request['ids']."',attr_key_group)");
        $count_model = count($model);
        if($count_model > 0){
            $goods_id = implode(',',array_column($model,'goods_id'));
            $goods = M('goods')->where(array('id'=>array('in',$goods_id),'status'=>array('neq',9)))->select();
            if($goods){
                $goods_list_id = implode(',', array_column($goods,'id'));
                $this->setLogicError('请先删除该属性值下的商品!商品ID为'.$goods_list_id); return false;
            }
            $this->setLogicError('请先删除含有该属性值的商品属性'); return false;
        }
        //执行前操作
        if(!$this->beforeRemove($request)){ return false; }
        //判断数组ID 字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('IN', $request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }

        $result = D($request['model'])->where($where)->delete();

        if($result) {
            //行为日志
            api('Manager/ActionLog/actionLog', array('remove',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterRemove($result,$request)) { return false; }
            $this->setLogicSuccess('删除成功！'); return true;
        } else {
            $this->setLogicError('删除失败！'); return false;
        }
    }
}