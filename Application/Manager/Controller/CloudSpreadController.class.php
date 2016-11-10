<?php

namespace Manager\Controller;

/**
 * [云推广-控制器]
 * @author zhouwei
 * Class CloudSpreadController
 * @package Manager\Controller
 */
class CloudSpreadController extends BaseController {
    /**
     * 频道列表页
     */
    function details() {
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->getDetail(I('request.'));
        if($result) {
//         	dump($result['list']);
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('detail');
    }
}
