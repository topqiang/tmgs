<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 模型基类
 */
abstract class BaseModel extends Model {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array();

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array();

    /**
     * 文件模型字段映射
     * @var array
     */
    protected $_map = array();


    abstract public function getList($param = array());

    abstract public function findRow($param = array());

    /**
     * @param array $param
     * @return array
     * 获取列表
     */
    final public function getCustomList($param = array()) {
        //分页对象
        $Page = null;
        //是否分页
        if(!empty($param['page_size'])) {
            //数据总数
            $total = $this->_getCURD($param, null)->count();
            //获取分类对象
            $Page = $this->getPage($total, $param['page_size'], $param['parameter']);
            //分页信息
            $page_show = $Page->show();
        }
        //数据列表
        $list  = $this->_getCURD($param, $Page)->select();
        //返回列表和分页信息
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param $param
     * @param $Page
     * @return BaseModel
     * 获取数据库连贯操作
     */
    final private function _getCURD($param = array(), $Page = null) {
        $model  = $this;
        //表别名 字符串
        !empty($param['alias'])         ? $model = $model->alias($param['alias']) : '';
        //去掉重复记录 true/false
        !empty($param['distinct'])      ? $model = $model->distinct($param['distinct']) : '';
        //指定当前数据表 字符串/数组 可用于多表操作  'think_user user,think_role role'  array('think_user'=>'user','think_role'=>'role')
        !empty($param['table'])         ? $model = $model->table($param['table']) : '';
        //查询字段  两个参数  字符串/数组 ,true/false  true代表过滤
        !empty($param['field'])         ? $model = $model->field($param['field'],$param['field_filter']) : '';
        //查询条件 数组
        !empty($param['where'])         ? $model = $model->where($param['where']) : '';
        //左、右、并 连接查询  统一使用数组 array('','')
        !empty($param['join'])          ? $model = $model->join($param['join']) : '';
        //归并 两个参数 合并两个或多个 SELECT 语句的结果集。 统一使用数组array('SELECT name FROM think_user_1','SELECT name FROM think_user_2')  array,true/false true代表unionall
        !empty($param['union'])         ? $model = $model->union($param['union'],$param['union_all']) : '';
        //排序 字符串
        !empty($param['order'])         ? $model = $model->order($param['order']) : '';
        //分页
        !empty($param['page_size']) && !($Page == null)  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        //分组查询 字符串 支持多字段分组  'name,score'
        !empty($param['group'])         ? $model = $model->group($param['group']) : '';
        //和group共用  查询条件  字符串
        !empty($param['having'])        ? $model = $model->having($param['having']) : '';
        //只返回SQL语句不执行 true/false  默认false,  为true时只返回sql语句
        $param['fetchSql']              ? $model = $model->fetchSql($param['fetchSql']) : '';

        return $model;
    }

    /**
     * @param array $param
     * @return mixed
     * 一行数据
     */
    final public function getCustomRow($param = array()) {
        return $this->_getCURD($param, null)->find();
    }

    /**
     * @param $value 验证字段的值
     * @param $field 验证字段的名称 该名称在“验证时间”后设置 数组的形式
     * @return bool
     * 自动验证时 验证是否已存在记录  若表中删除为假删除 则验证名称、标题等是否已存在则 使用该方法
     */
    protected function checkUnique($value, $field) {
        //获取主键名称
        $pk = $this->getPk();
        //判断提交数据中是否存在主键值 如果存在则是修改的情况
        if(!empty($_REQUEST[$pk])) {
            //获取原该字段的值
            $old = $this->where(array($pk => $_REQUEST[$pk]))->getField($field);
            //如果原值等于现值  不在进行下一步判断
            if($old == $value) {
                return true;
            }
        }
        //判断现值是否存在
        $where[$field]      = $value;
        $where['status']    = array('lt',9);
        $count = $this->where($where)->count();
        if($count > 0) {
            return false;
        }
        return true;
    }

    /**
     * @param int $total 总数
     * @param int $page_size 每页记录数
     * @param array $parameter 拼接参数
     * @return \Think\Page 分页对象
     */
    protected function getPage($total = 0, $page_size = 0, $parameter = array()) {
        $Page = new \Think\Page($total, $page_size, $parameter);
        //分页样式配置
        if($total>$page_size) {
            $Page->setConfig('theme','%UP_PAGE% %FIRST% %LINK_PAGE% %END% %DOWN_PAGE% %HEADER%');
        }
        return $Page;
    }
}