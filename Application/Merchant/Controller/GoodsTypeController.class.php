<?php
namespace Manager\Controller;

/**
 * 商品类型
 * Class GoodsTypeController
 * @package Manager\Controller
 */
class GoodsTypeController extends BaseController {

    function getIndexRelation() {
        if(session('thr_g_t_id')){
            $s = session('thr_g_t_id');
            unset($s);
        }

    }
    /**
     * 添加时关联数据
     */
    function getAddRelation() {
        if(!empty($_GET['type']) && !empty($_GET['id'])){
            $this->assign('type',$_GET['type']);
            $this->assign('select',D('GoodsType','Logic')->getAddSelect(I('get.id')));
        }else{
            $this->assign('select',D('GoodsType','Logic')->getAddAllSelect('parent_id',I('get.parent_id')));
        }
    }

    /**
     * 修改时关联数据
     */
    function getUpdateRelation() {
        $this->assign('parent_id',$_GET['parent_id']);
        $this->assign('select',D('GoodsType','Logic')->getSelect('parent_id',I('get.parent_id')));
    }
    /**
     * 添加
     */
    function add() {
        $this->checkRule(self::$rule);
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
}
