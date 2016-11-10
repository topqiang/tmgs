<?php

namespace Manager\Logic;

/**
 * Class ArticleCategoryLogic
 * @package Manager\Logic
 * 文章分类 逻辑处理
 */
class ArticleCategoryLogic extends BaseLogic{

    /**
     * @param array $request
     * @return array
     * 获取文章分类列表
     */
    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态

        //判断是否存在缓存  缓存在分类 修改或添加后清空
        $result = S('ArticleCategory_Cache');
        if(!$result) {
            $result = D('ArticleCategory')->getList($param);
            //设置缓存
            S('ArticleCategory_Cache', $result);
        }

        //将数据转换成树状结构  调用分类api 生成操作html
        $tree_data = list_to_tree(api('Manager/Category/handleOperate',array($result,'ArticleCategory')));

        //获取某分类下的所有子分类
        //$list[] = D('ArticleCategory')->findRow(array('where'=>array('id'=>1)));
        //var_dump(api('Tree/getAllChild',array($tree_data, $list)));
        //获取某分类的所有父分类
        //var_dump(api('Tree/getAllParent',array($result, 45)));

        //分类模板
        $template = "<tr>
                        <td>{id}</td>
                        <td>{top_class}{name}</td>
                        <td class='single-edit' data-model='ArticleCategory' data-id='{id}' data-field='sort'>{sort}</td>
                        <td>{description}</td>
                        <td>{operate}</td>
                    </tr>";

        //设置初始数据
        api('Tree/init',array($tree_data,$template,array('id','name','sort','description','operate')));
        //生成树状页面
        $html = api('Tree/toFormatTree');

        return array('list'=>$html);
    }

    /**
     * @param string $field_name 隐藏文本框name名称
     * @param string $index_value 默认选中的值
     * @param string $index_field 默认选中字段
     * @return string
     * 获取分类树状下拉菜单
     */
    function getSelect($field_name = '', $index_value = '', $index_field = 'id') {
        //状态
        $param['where']['status']   = array('lt',9);
        $result = D('ArticleCategory')->getList($param);
        return api('Manager/Category/getSelect',array($result,$field_name,$index_value,$index_field));
    }

    /**
     * @param array $request
     * @return bool
     * 删除分类前操作 验证是否该分类下存在文章
     */
    protected function beforeSetStatus($request = array()) {
        if($request['status'] == 9) {
            //判断给分类下是否存在文章
            $where['cate_id'] = $request['ids'];
            $where['status']  = array('lt',9);
            $count = D('Article')->where($where)->count();
            if($count > 0) {
                $this->setLogicError('该分类下存在文章，请先删除该分类下的全部文章！'); return false;
            }
        }
        return true;
    }

    /**
     * @param int $result
     * @param array $request
     * @return boolean
     * 修改状态后执行
     */
    protected function afterSetStatus($result = 0, $request = array()) {
        S('ArticleCategory_Cache', null);
        return true;
    }

    /**
     * @param int $result
     * @param array $request
     * @return boolean
     * 更新后执行
     */
    protected function afterUpdate($result, $request = array()) {
        S('ArticleCategory_Cache', null);
        return true;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['status'] = array('lt',9);
        $row = D('ArticleCategory')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //获取文件
        $row['icon'] = api('System/getFiles',array($row['icon']));
        return $row;
    }
}