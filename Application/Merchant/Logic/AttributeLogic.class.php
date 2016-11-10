<?php

namespace Merchant\Logic;

class AttributeLogic extends BaseLogic {


    /**
     * @param array $request
     * @return array
     * 获取日志列表
     */
    function getList($request = array()) {
        $param['where']['attr.is_merchant'] =  array('in',('0,'.$request['is_merchant']));
        $param['where']['attr.status'] = array('neq',9);
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('Attribute')->getList($param);

        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }

        $param['where']['status'] = array('lt',9);
        $row = D('Attribute')->getCustomRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
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
            $thr_g_t_id_parent_id = D('goods_type') -> where(array('id'=>$data['thr_g_t_id'])) -> getField('parent_id');
            $data['sec_g_t_id'] = D('goods_type') -> where(array('id'=>$thr_g_t_id_parent_id)) -> getField('id'); // sec_id
            $parent_id = D('goods_type') -> where(array('id'=>$data['sec_g_t_id'])) -> getField('parent_id');
            $data['fir_g_t_id'] = D('goods_type') -> where(array('id'=>$parent_id)) -> getField('id');
            $result = D($model)->data($data)->add();
            if(!$result) {
                $this->setLogicError('新增时出错！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $result = D($model)->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！'); return true;
    }

    /**
     * @param array $request
     * @return bool
     * 彻底删除记录
     */
    function remove($request = array()) {
        //判断参数
        if(empty($request['model']) || empty($request['ids'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        if(M('attribute_content')->where(array('status'=>array('neq',9),'attr_id'=>$request['ids']))->count() > 0){
            $this->setLogicError('请先删除该分类下的属性值！'); return false;
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