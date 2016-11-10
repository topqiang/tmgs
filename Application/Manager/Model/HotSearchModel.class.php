<?php

namespace Manager\Model;

/**
 * 热搜词
 * Class HotSearchModel
 * @package Manager\Controller
 */
class HotSearchModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('cn_search_word', 'require', '中文热门搜索词不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('cn_search_word', '1,50', '中文热门搜索词不能超过50个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array();

    /**
     * @param array $param  综合条件参数
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
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        return $row;
    }
}
