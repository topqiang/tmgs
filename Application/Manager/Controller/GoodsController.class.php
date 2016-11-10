<?php

namespace Manager\Controller;

/**
 * 商品
 * Class GoodsController
 * @package Manager\Controller
 */
class GoodsController extends BaseController {


    /**
     * 修改
     */
    function update() {
        $this->checkRule(self::$rule);
        if(!IS_POST) {
            if ($_GET['id']) {
                $Object = D(CONTROLLER_NAME,'Logic');
                $row = $Object->findRow(I('get.'));
                if ($row) {
                    $this->getUpdateRelation();
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
            $this->display('update');
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

    /**
     * 操作修改 好服务 好店铺
     */
    function handleGood()
    {
        $where['id'] = $_POST['id'];
        $status = $_POST['status'] == 1 ? 0 : 1;
        $data[$_POST['type']] = $status;
        $M = M('Goods') -> where($where) -> data($data) -> save();
        if($M){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }


    /**
     * 禁用操作
     */
    function forbidAudit() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->setStatusAudit(I('request.'));
        if($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

       /**
     * 频道列表页
     */
    function notIndex() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->getNotList(I('request.'));
        if($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('notIndex');
    }

}
