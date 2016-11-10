<?php
namespace Manager\Logic;

/**
 * Class BaseLogic
 * @package Manager\Logic
 * 逻辑层  父类
 *
 */
abstract class BaseLogic {

    /**
     * @var string
     * 接收逻辑层错误信息
     */
    protected $logicError = '';

    /**
     * @var string
     * 接收逻辑层成功信息
     */
    protected $logicSuccess = '';

    /**
     * @param array $request
     * @return array
     * 返回列表
     */
    abstract public function getList($request = array());

    /**
     * @param array $request
     * @return boolean
     * @return array
     * 返回一行数据
     */
    abstract public function findRow($request = array());

    /**
     * @param array $request
     * @return boolean
     * 修改状态前执行方法
     */
    protected function beforeSetStatus($request = array()) { return true; }

    /**
     * @param int $result
     * @param array $request
     * @return boolean
     * 修改状态后执行
     */
    protected function afterSetStatus($result = 0, $request = array()) { return true; }

    /**
     * @param array $request  model 模型  ids操作的主键ID  status要改为的状态
     * @return bool
     * 修改状态
     */
    function setStatus($request = array()) {
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
            'audit_time'    => time(),
            'status'        => $request['status'],
            'update_time'   => time()
        );

        $result = D($request['model'])->where($where)->data($data)->save();

        if($result) {
            //行为日志
            api('Manager/ActionLog/actionLog', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }


    /**
     * @param array $request
     * @return boolean
     * 彻底删除前执行
     */
    protected function beforeRemove($request = array()) { return true; }

    /**
     * @param int $result
     * @param array $request
     * @return boolean
     * 彻底删除后执行
     */
    protected function afterRemove($result = 0, $request = array()) { return true; }


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

    /**
     * @param array $request
     * @return boolean
     * 更新前执行
     */
    protected function beforeUpdate($request = array()) { return true; }
    /**
     * @param array $data
     * @return array
     * 处理提交数据 进行加工或者添加其他默认数据
     */
    protected function processData($data = array()) { return $data; }
    /**
     * @param $result
     * @param array $request
     * @return boolean
     * 更新后执行
     */
    protected function afterUpdate($result, $request = array()) { return true; }

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
     * @param string $error
     * @return string
     * 设置错误信息
     */
    final protected function setLogicError($error = '') {
        $this->logicError = $error;
    }

    /**
     * @param string $success
     * @return string
     * 设置成功信息
     */
    final protected function setLogicSuccess($success = '') {
        $this->logicSuccess = $success;
    }

    /**
     * @return string
     * 获取错误信息
     */
    final public function getLogicError() {
        return $this->logicError;
    }

    /**
     * @return string
     * 获取成功提示信息
     */
    final public function getLogicSuccess() {
        return $this->logicSuccess;
    }
}