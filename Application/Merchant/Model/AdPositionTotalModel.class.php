<?php
namespace Merchant\Model;

/**
 * [广告办理]
 * @author zhouwei
 * Class AdPositionModel
 * @package Merchant\Model
 */
class AdPositionTotalModel extends BaseModel {

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
        $row['pic'] = api('System/getFiles',array($row['pic']));
        $row['region'] = M('region')->where(array('id'=>$row['region']))->getField('region_name');
        $time = explode(',',trim($row['time'],','));
        sort($time);
        $row['time'] = '';
        foreach($time as $k =>$v){
            if(strtotime($v) > strtotime('-1 day')){
                $row['time'] .= '<div class="" style="padding:1px;height: 20px;width: 74px;border:1px solid #CCCCCC;float: left;margin-left: 5px;margin-bottom:3px;" >'.$v.'</div>';
            }else{
                $row['time'] .= '<div class="" style="color:#CCCCCC;padding:1px;height: 20px;width: 74px;border:1px solid #CCCCCC;float: left;margin-left: 5px;margin-bottom:3px;" >'.$v.'</div>';
            }
        }
        return $row;
    }
}