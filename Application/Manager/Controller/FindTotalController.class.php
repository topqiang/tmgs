<?php

namespace Manager\Controller;

/**
 * 发现模块
 * @author zhouwei
 * Class FindTotalController
 * @package Manager\Controller
 */
class FindTotalController extends BaseController
{
    /**
     * 添加
     */
    public function add() {
//        $this->checkRule(self::$rule);
        if(!IS_POST) {
            $this->getAddRelation();
            $this->display('add');
        } else {
            $Object = D(CONTROLLER_NAME,'Logic');
            $result = $Object->update(I('post.'));
            if($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

    public function getAddRelation()
    {
        $model = M('MerchantAdvertising')->where(array('type'=>array('in','3,4,5')))->field('money,title,type')->select();
        $this->assign('ma',$model);
        parent::getIndexRelation(); // TODO: Change the autogenerated stub
    }
    /**
     * [getGoodsName 获得商品名称]
     * @author zhouwei
     * @param  [type] $name [商品名称]
     * @return [type]       [description]
     */
    public function getGoodsName($name)
    {
        $session = session('merInfo');
        if($name != ''){
            $model = M() -> query("SELECT cn_goods_name,id FROM __GOODS__ WHERE trim(REPLACE(`cn_goods_name`,' ','')) LIKE trim(REPLACE('%".$name."%',' ','')) AND merchant_id = ".$session['mer_id']." AND status != 9 LIMIT 20");
            // $model =  M('Goods')->where("trim(REPLACE(`cn_goods_name`,' ','')) LIKE trim(REPLACE('%".$name."%',' ','')) AND merchant_id = ".$session['mer_id'])->field('cn_goods_name,id')->limit(20)->select();
        }else{
            $model =  array();
        }
        $this ->ajaxReturn($model);
    }

}