<?php
namespace Common\Service;

/**
 * Class BaseService
 * @package Common\Service
 * 公共服务层 基类
 */
abstract class BaseService {

    /**
     * @var string
     * 接收服务层错误信息
     */
    protected $serviceError = '';

    /**
     * @var string
     * 接收服务层成功信息
     */
    protected $serviceSuccess = '';


    /**
     * @param array $param
     * @param array $custom_param
     * @return array
     * 处理外部自定义条件
     */
    protected function customParam($param = array(), $custom_param = array()) {
        //替换页码条件
        if(isset($custom_param['page_size'])) {
            $param['page_size'] = $custom_param['page_size'];
        }
        //合并查询条件
        if(isset($custom_param['where'])) {
            $param['where'] = array_merge($param['where'], $custom_param['where']);
        }
        //合并、清空关联条件
        if(isset($custom_param['join'])) {
            $param['join'] = empty($custom_param['join']) ? array() : array_merge($param['join'], $custom_param['join']);
        }
        //新关联条件
        if(isset($custom_param['new_join'])) {
            $param['join'] = $custom_param['new_join'];
        }
        //替换排序条件
        if(isset($custom_param['order'])) {
            $param['order'] = $custom_param['order'];
        }
        //拼接字段条件
        if(isset($custom_param['field'])) {
            $mosaic = empty($param['field']) ? '' : ',';
            $param['field'] = is_array($custom_param['field']) ? $custom_param['field'] : $param['field'].$mosaic.$custom_param['field'];
        }
        return $param;
    }


    /**
     * @param string $error
     * @return string
     * 设置错误信息
     */
    final protected function setServiceError($error = '') {
        $this->serviceError = $error;
    }

    /**
     * @param string $success
     * @return string
     * 设置成功信息
     */
    final protected function setServiceSuccess($success = '') {
        $this->serviceSuccess = $success;
    }

    /**
     * @return string
     * 获取错误信息
     */
    final public function getServiceError() {
        return $this->serviceError;
    }

    /**
     * @return string
     * 获取成功提示信息
     */
    final public function getServiceSuccess() {
        return $this->serviceSuccess;
    }

}