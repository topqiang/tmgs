<?php
namespace Common\Service;

/**
 * Class FileService
 * @package Common\Service
 * 文件表 数据服务层
 */
class FileService extends BaseService{

    /**
     * @param array $custom_param
     * @return array
     * 获取文件列表
     */
    function select($custom_param = array()) {
        //表别名
        $param['alias']                 = 'file';
        //状态
        $param['where']['file.status']  = array('lt',9);
        //关联的表 file_extend
//        $param['join']                  = array(
//            'LEFT JOIN '.C('DB_PREFIX').'file_extend file_ext ON file_ext.file_id = file.id',
//        );

        //查询的字段
        $param['field']                 = 'file.*';   //排序

        //是否有外部其他自定义条件  如果有替换条件
        if(!empty($custom_param)) {
            $param = $this->customParam($param, $custom_param);
        }

        $result = D('File')->getCustomList($param);
        return $result;
    }

    /**
     * @param array $custom_param
     * @return mixed
     */
    function find($custom_param = array()) {
        $param['where'] = array();
        //是否有外部其他自定义条件
        if(!empty($custom_param)) {
            $param = $this->customParam($param, $custom_param);
        }
        return D('File')->getCustomRow($param);
    }
}