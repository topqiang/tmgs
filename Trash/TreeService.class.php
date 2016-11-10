<?php
namespace Common\Service;

/**
 * Class TreeService
 * @package Common\Service
 * 分类及其他树状结构操作  页面树状结构  select树状结构
 * 分类父、子级ID获取
 */
class TreeService {

    /**
     * @var array
     * 树状结构数据
     */
    protected $treeData     =   array();
    /**
     * @var string
     * 子数组键名
     */
    protected $child        =   '_child';
    /**
     * @var string
     * 显示模板
     */
    protected $template     =   '';
    /**
     * @var array
     * 要替换赋值的字段名称
     */
    protected $fields       =   array();
    /**
     * @var string
     * 根树前的层级符号
     */
    protected $rootPre      =   '├&nbsp;';
    /**
     * @var string
     * 子树前的层级符号
     */
    protected $childPre     =   '└&nbsp;';
    /**
     * @var string
     * 缩行填充 全角空格(分类缩进) 或 带有宽度的html(无限回复)
     */
    protected $indentation  =   '　';


    /**
     * @param array $tree_data
     * @param string $child
     * @param string $template
     * @param array $fields
     * @param string $root_pre
     * @param string $child_pre
     * @param string $indentation
     */
    public function __init($tree_data = array(), $template = '', $fields = array(), $root_pre = '├&nbsp;', $child_pre = '└&nbsp;', $indentation = '　', $child = '_child') {
        $this->treeData     =   $tree_data;
        $this->template     =   $template;
        $this->fields       =   $fields;
        $this->rootPre      =   $root_pre;
        $this->childPre     =   $child_pre;
        $this->indentation  =   $indentation;
        $this->child        =   $child;
    }

    /**
     * @param array $tree
     * @param int $level
     * @return mixed|string
     */
    function toFormatTree($tree = array(), $level = 0) {
        //判断是否传值 递归时
        $treeData = empty($tree) ? $this->treeData : $tree;
        //树状HTML
        $tree_html = '';
        //计算当前的层级
        $top_class = '';
        //判断层级
        if($level > 0) {
            //子层级情况
            for ($i = 0; $i < ($level * 2); $i++) {
                $top_class .= $this->indentation;
            }
            $top_class .= $this->childPre;
        } else {
            //父层级
            $top_class = $this->rootPre;
        }
        //遍历外部提供的树状数组
        foreach ($treeData as $data) {

            $template = $this->template;

            //正则替换
            foreach($this->fields as $field) {
                //动态匹配数组下标名称
                $template = preg_replace("/{".$field."}/i", $data[$field], $template);
            }
            //拼接html
            $tree_html .= $template;
            //层级位置替换 层级标识
            $tree_html = preg_replace("/(\{top_class})/is", $top_class, $tree_html);
            //判断是否有子级
            if(!empty($data[$this->child])) {
                $tree_html .= $this->toFormatTree($data[$this->child], $level+1);
            }
        }
        return $tree_html;
    }

    /**
     * @param string $index_value  默认选中的值
     * @param string $index_field  默认选中索引字段
     * @param array $tree 数据
     * @param int $level 层级
     * @return mixed|string
     * 下拉层级菜单
     */
    function toSelectFormatTree($index_value = '', $index_field = '', $tree = array(), $level = 0){
        //判断是否传值 递归时
        $treeData = empty($tree) ? $this->treeData : $tree;
        //树状HTML
        $tree_html = '';
        //计算当前的层级
        $top_class = '';
        //判断层级
        if($level > 0) {
            //子层级情况
            for ($i = 0; $i < ($level * 2); $i++) {
                $top_class .= $this->indentation;
            }
            $top_class .= $this->childPre;
        } else {
            //父层级
            $top_class = $this->rootPre;
        }

        foreach ($treeData as $data){
            //标签组合
            $template = $this->template;
            //正则替换 动态循环数组下标
            foreach($this->fields as $field){
                //根据要替换的标签 匹配数据
                $template = preg_replace("/{".$field."}/i",$data[$field],$template);
                //如果满足参数要求 给设置默认选择
                if($data[$index_field] == $index_value && !preg_match('/selected/',$tree) && $index_field != ''){
                    $template = preg_replace('/>/','class="selected" selected="selected">',$template,1);
                }
            }
            //拼接html
            $tree_html .= $template;
            //层级位置替换 层级标识
            $tree_html = preg_replace("/(\{top_class\})/is", $top_class, $tree_html);
            //判断是否存在子
            if(!empty($data[$this->child])){
                //递归调用
                $tree_html .= $this->toSelectFormatTree($index_value, $index_field, $data[$this->child], $level+1);
            }
        }

        return $tree_html;
    }

    /**
     * @var array
     * 存放父级分类
     */
    public $single_tree = array();

    /**
     * @param $list  分类列表
     * @param int $root  最低根节点
     * @param string $pk 主键名称
     * @param string $pid 上级名称
     * @return array
     * 获取某分类的所有父分类
     */
    function getAllParent($list, $root = 0, $pk = 'id', $pid = 'parent_id') {
        if(is_array($list)) {
            foreach ($list as $key => $data) {
                //如果键值等于根节点
                if($data[$pk] == $root) {
                    //记录
                    $this->single_tree[] = $data;
                    //修改根节点为当前分类的父节点
                    $root = $data[$pid];
                    //删除当前分类
                    unset($list[$key]);
                    //递归判断
                    $this->getAllParent($list,$root);
                    //跳出循环
                    break;
                }
            }
        }
        return array_reverse($this->single_tree);
    }

    /**
     * @param array $tree_data 树状数据
     * @param array $list 记录父根节点
     * @param string $pk 主键名称
     * @return array
     * 获取数据的所有子孙数据的id值
     */
    function getAllChild($tree_data = array(), $list = array(), $pk = 'id') {
        //树状结构转换成列表
        $list = tree_to_list($tree_data, $list);
        //获取ID数组
        foreach($list as $value) {
            $ids[] = $value[$pk];
        }
        //
        return array('ids' => $ids, 'list' => $list);
    }
}