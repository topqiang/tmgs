<?php
namespace Manager\Model;

/**
 * Class ConfigModel
 * @package Manager\Model
 * 系统配置 模型
 */
class ConfigModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('name', 'require', '配置名称必须', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '/^[a-zA-Z]\w{0,39}$/', '配置名称不合法', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('name', '1,30', '配置名称长度不能超过30个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('name', 'checkUnique', '该配置名称已经存在', self::MUST_VALIDATE, 'callback', self::MODEL_BOTH, array('name')),
        array('title', 'require', '配置标题必须', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('title', '1,30', '配置标题长度不能超过30个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('type', 'require', '配置类型未选择', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        //array('config_group', 'require', '配置分组未选择', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('value', 'require', '配置值未填写', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param $param
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->where($param['where'])->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        return $row;
    }

    /**
     * 获取数据库中的配置列表
     * @return array 配置数组
     * 返回 批量导入C函数的数据
     */
    function parseList() {
        //获取可用的配置列表
        $param['where']['status'] = array('lt',9);
        $result = $this->getList($param);
        $config = array();
        //给配置名称赋值
        if($result['list'] && is_array($result['list'])) {
            foreach ($result['list'] as $value) {
                $config[$value['name']] = self::parse($value['type'], $value['value']);
            }
        }
        return $config;
    }

    /**
     * @param $type
     * @param $value
     * @return array
     * 根据配置类型解析配置
     */
    private function parse($type, $value) {
        switch ($type) {
            //如果是数组类型 解析数组
            case 4:
                //根据换行拆分
                $array = preg_split('/[,;\r\n]+/', trim($value, ",;\r\n"));
                //是否存在冒号
                if(strpos($value,':')) {
                    //存在的情况 冒号前边的值为键值 后边为显示值
                    $value  = array();
                    foreach ($array as $val) {
                        list($k, $v) = explode(':', $val);
                        $value[$k]   = $v;
                    }
                } else {
                    //不存在冒号
                    $value = $array;
                }
                break;
        }
        return $value;
    }
}