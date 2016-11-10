<?php
namespace Manager\Model;

/**
 * 列表
 */
class PayLogModel extends BaseModel
{

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array();

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param array $param 综合条件参数
     * @return array
     */
    function getList($param = array())
    {
        if (!empty($param['page_size'])) {
            $total = $this->where($param['where'])->count();
            $Page = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show = $Page->show();
        }

        $model = $this->where($param['where'])->order($param['order']);

        //是否分页
        !empty($param['page_size']) ? $model = $model->limit($Page->firstRow, $Page->listRows) : '';

        $list = $model->select();
        foreach ($list as $key => $value) {
            if ($value['type'] == 1) {
                $list[$key]['object_id'] = M('Member')->where(array('id' => $value['object_id']))->field('nickname,account')->find();
            } else {
                $list[$key]['object_id'] = M('Merchant')->where(array('id' => $value['object_id']))->field('merchant_name as nickname,account')->find();
            }
        }
        $expend = $this->where(array('symbol' => 0))->getField('SUM(money)'); /*支出 */
        $deposit = $this->where(array('symbol' => 1))->getField('SUM(money)'); /*存入*/
        return array('deposit'=>$deposit,'expend'=>$expend,'list' => $list, 'page' => !empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array())
    {
        $row = M('pay_log')->where($param['where'])->find();
        return $row;
    }
}