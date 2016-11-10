<?php

namespace Manager\Logic;

/**
 * Class WechatMenu
 * @package Manager\Logic
 * 微信菜单
 */
class WechatMenuLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = 40;        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('WechatMenu')->getList($param);

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
        $row = D('WechatMenu')->findRow($param);

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
            if($data['parent_id'] == 0){
                $count = M('WechatMenu')->where(array('parent_id'=>0))->count();
                if($count >= 3){
                    $this->setLogicError('当前顶级菜单达到上限'); return false;
                }
            }else{
                $count = M('WechatMenu')->where(array('parent_id'=>$data['parent_id']))->count();
                if($count >= 5){
                    $this->setLogicError('该分类下的子菜单达到上限'); return false;
                }
            }
            //新增数据
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
        //执行前操作
        if(!$this->beforeRemove($request)){ return false; }
        //判断数组ID 字符ID
        $where['id'] = $request['ids'];
        $ids = $request['ids'];

        $result = D($request['model'])->where($where)->delete();

        if($result) {
            D($request['model'])->where(array('parent_id'=>$ids))->delete();
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