<?php
namespace Manager\Model;

/**
 * 微信文章
 */
class WechatArticleModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array ();
    
    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

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
        $count[]= $this->where(array('wxa_type'=>0))->count(); 
        $count[]= $this->where(array('wxa_type'=>1))->count(); 
        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '','count'=>$count);
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        $row['wxa_picurl'] = api('System/getFiles',array($row['wxa_picurl']));
        return $row;
    }
}