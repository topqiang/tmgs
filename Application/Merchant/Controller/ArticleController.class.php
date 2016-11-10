<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 文章咨询 控制器
 */
class ArticleController extends BaseController {

    function getIndexRelation() {
        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('request.cate_id')));
    }

    function getUpdateRelation() {
        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
    }

    function getAddRelation() {
        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
    }

    /**
     * 移动文章
     */
    function move() {
        $this->checkRule(self::$rule);
        $Object = D('Article','Logic');
        $result = $Object->move(I('request.'));
        if($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }
}
