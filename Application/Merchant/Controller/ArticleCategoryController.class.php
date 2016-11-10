<?php
namespace Manager\Controller;

/**
 * Class ArticleCategoryController
 * @package Manager\Controller
 * 文章分类 控制器
 */
class ArticleCategoryController extends BaseController {

    /**
     * 添加时关联数据
     */
    function getAddRelation() {
        $this->assign('select',D('ArticleCategory','Logic')->getSelect('parent_id',I('get.id')));
    }

    /**
     * 修改时关联数据
     */
    function getUpdateRelation() {
        $this->assign('select',D('ArticleCategory','Logic')->getSelect('parent_id',I('get.parent_id')));
    }

    function breadcrumbNav() { }
}
