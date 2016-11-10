<?php
namespace Manager\Controller;

/**
 * @author zhouwei
 * Class DivideController
 * @package Home\Controller
 * 分成比例
 */
class DivideController extends BaseController
{
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
                $this->success($Object->getLogicSuccess());
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

}