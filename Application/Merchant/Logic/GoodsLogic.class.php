<?php

namespace Merchant\Logic;

/**
 * 商品
 * Class GoodsLogic
 * @package Manager\Logic
 */
class GoodsLogic extends BaseLogic {


   /**
    * [getList 页面展示]
    *
    * @param  array  $request [description]
    *
    * @return [type]          [description]
    */
    function getList($request = array()) {
        if(!empty($request['cn_goods_name'])){
            $param['where']['cn_goods_name'] = array('like','%'.trim($request['cn_goods_name']).'%');
        }
        if(!empty($request['article_number'])){
            $param['where']['article_number'] = array('like','%'.trim($request['article_number']).'%');
        }
        if(!empty($request['cn_goods_brands'])){
            $param['where']['cn_goods_brands'] = array('like','%'.trim($request['cn_goods_brands']).'%');
        }
        if(!empty($request['create_time'])){
            $param['where']['create_time'] = array(array('egt',strtotime(trim($request['create_time']))),array( 'elt',strtotime('+1day',strtotime(trim($request['create_time']))) ),'AND');
        }
        $session =  session('merInfo');
        $param['order']             = 'create_time DESC';   //排序
        $param['where']['merchant_id']       = $session['mer_id']; //商家ID
        $param['where']['status']   = array('lt',9);        //状态
        $param['page_size']         = C('LIST_ROWS');        //页码
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
        $param['where']['status'] = array('lt',9);
        $row = D('Goods')->findRow($param);
        $row['unit'] = M('unit') -> where() -> select();
        $row['fir_classify'] = M('goods_type')->where(array('id'=>$row['fir_g_t_id']))->field('id,type,cn_type_name,parent_id')->find(); // 1级分类
        $row['sec_classify'] = M('goods_type')->where(array('id'=>$row['sec_g_t_id']))->field('id,type,cn_type_name,parent_id')->find(); // 2级分类
        $row['thr_classify'] = M('goods_type')->where(array('id'=>$row['thr_g_t_id']))->field('id,type,cn_type_name,parent_id')->find(); // 3级分类
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
            api('Manager/Goods/setStatus', array('change_status',$request['model'],$ids,AID));
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
    function setAttrStatus($request = array()) {
        //判断参数
        if(empty($request['model']) || empty($request['ids']) || !isset($request['status'])) {
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
            'status'        => $request['status'],
        );

        $result = D($request['model'])->where($where)->data($data)->save();

        if($result) {
           if($request['status'] == 9){
               M('GoodsProduct')->where(array('goods_id'=>$where['id']))->data(array('status'=>9))->save();
           }
            //行为日志
            api('Merchant/Goods/setAttrStatus', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }

/**
     * @param array $request
     * @return bool|mixed
     * 新增 或 修改
     */
    function update($request = array()) {
        //执行前操作
        if(!$this->beforeUpdate($request)) { return false; }
        $model = $request['model'];
        unset($request['model']);
        if(!is_numeric($request['cn_delivery_cost'])){
            $this->setLogicError('请输入正确的配送价格');return false;
        }
        if(!is_numeric($request['wholesale_prices'])){
            $this->setLogicError('请输入正确的批发价格');return false;
        }
        if(!is_numeric($request['cn_price'])){
            $this->setLogicError('请输入正确的出售价格');return false;
        }
        if($request['wholesale_prices'] >= $request['cn_price']){
            $this->setLogicError('批发价格应低于出售价格');return false;
        }
        //获取数据对象
        $data = D($model)->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
            //新增数据
            $result = D($model)->data($data)->add();
            if(!$result) {
                $this->setLogicError('新增时出错！'); return false;
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $result = D($model)->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Merchant/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！'); return true;
    }

}