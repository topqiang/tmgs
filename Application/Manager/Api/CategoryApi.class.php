<?php
namespace Manager\Api;

/**
 * Class CategoryApi
 * @package Manager\Api
 */
class CategoryApi {

    /**
     * @param array $list       分类数据
     * @param string $model     模型名称
     * @param array $prohibit   禁止某些操作的ID
     * @param array $only_allow  只允许的操作
     * @return array
     * 生成分类的操作html
     * $prohibit设置部分操作禁止的ID数组
     * $only_allow 和$prohibit共同使用  $prohibit中的ID记录只显示$only_allow中的操作方法
     */
    function handleOperate($list = array(), $model = '', $prohibit = array(), $only_allow = array('add_child','edit','set_status','delete')) {
        //空数组返回空
        if(empty($list)) { return array(); }
        //
        foreach($list as $key => $value) {
            //添加子分类操作的html
            $add_child = "<a href='".U(''.MODULE_NAME.'/'.$model.'/add/id/'.$value['id'].'')."' title='添加子分类' class='tip-bottom'>
                            <span class='label label-warning'>+子分类</span></a>&nbsp";
            //编辑分类的HTML
            $edit =  "<a href='".U(''.MODULE_NAME.'/'.$model.'/update/parent_id/'.$value['parent_id'].'/id/'.$value['id'].'')."' title='编辑' class='tip-bottom'>
                        <span class='label label-success'>编辑</span></a>&nbsp;";
            //判断当前状态
            if($value['status'] == 0) {
                //启用html
                $set_status = "<a href='".U(''.MODULE_NAME.'/'.$model.'/resume/model/'.$model.'/ids/'.$value['id'].'/status/'.abs(1-$value['status']).'')."' title=".show_status_name($value['status'])." class='tip-bottom ajax-get'>
                                <span class='label label-info'>".show_status_name($value['status'])."</span></a>&nbsp;";
            } elseif($value['status'] == 1) {
                //禁用html
                $set_status = "<a href='".U(''.MODULE_NAME.'/'.$model.'/forbid/model/'.$model.'/ids/'.$value['id'].'/status/'.abs(1-$value['status']).'')."' title=".show_status_name($value['status'])." class='tip-bottom ajax-get'>
                                <span class='label label-inverse'>".show_status_name($value['status'])."</span></a>&nbsp";
            }
            //删除操作的html
            $delete = "<a href='".U(''.MODULE_NAME.'/'.$model.'/delete/model/'.$model.'/ids/'.$value['id'].'/status/9')."' title='删除' class='tip-bottom confirm ajax-get'>
                            <span class='label label-important'>删除</span></a>";

            $html = '';
            //判断是否存在 禁止的ID  并当前ID是否在禁止的数组中  并存在只允许的操作
            if($prohibit && in_array($value['id'],$prohibit) && $only_allow) {
                foreach($only_allow as $rep) {
                    $html .= $$rep;
                }
            } else {
                $html = $add_child.$edit.$set_status.$delete;
            }

            $list[$key]['operate'] = $html;
        }

        return $list;
    }


    /**
     * @param array $list       分类数据
     * @param string $model     模型名称
     * @param array $prohibit   禁止某些操作的ID
     * @param array $only_allow  只允许的操作
     * @return array
     * 生成分类的操作html
     * $prohibit设置部分操作禁止的ID数组
     * $only_allow 和$prohibit共同使用  $prohibit中的ID记录只显示$only_allow中的操作方法
     */
    function handleOperate1($list = array(), $model = '', $prohibit = array(), $only_allow = array('add_child','edit','set_status','delete')) {
        //空数组返回空
        if(empty($list)) { return array(); }
        //
        foreach($list as $key => $value) {
            //添加子分类操作的html
            $add_child = "<a href='".U(''.MODULE_NAME.'/'.$model.'/add/id/'.$value['id'].'')."' title='添加子分类' class='tip-bottom'>
                            <span class='label label-warning'>+子分类</span></a>&nbsp";
            //编辑分类的HTML
            //判断是否可以编辑
            if($value['is_edit'] == 1){
                $edit =  "<a href='".U(''.MODULE_NAME.'/'.$model.'/update/parent_id/'.$value['parent_id'].'/id/'.$value['id'].'')."' title='编辑' class='tip-bottom'>
                        <span class='label label-success'>编辑</span></a>&nbsp;";
            }else{
                $edit = '';
            }
            //删除操作的html
            $delete = "<a href='".U(''.MODULE_NAME.'/'.$model.'/delete/model/'.$model.'/ids/'.$value['id'].'/status/9')."' title='删除' class='tip-bottom confirm ajax-get'>
                            <span class='label label-important'>删除</span></a>";

            $html = '';
            //判断是否存在 禁止的ID  并当前ID是否在禁止的数组中  并存在只允许的操作
            if($prohibit && in_array($value['id'],$prohibit) && $only_allow) {
                foreach($only_allow as $rep) {
                    $html .= $$rep;
                }
            } else {
                $html = $add_child.$edit.$delete;
            }

            $list[$key]['operate'] = $html;
        }

        return $list;
    }

    /**
     * @param array $list   分类数据
     * @param string $field_name 隐藏文本框name名称
     * @param string $index_value  默认选中的值
     * @param string $index_field  默认选中的字段索引
     * @return string
     * 获取树状下拉菜单
     */
    function getSelect($list = array(), $field_name = '', $index_value = '', $index_field = 'id') {
        //判空
        if(empty($list)) return '';
        //将数据转换成树状结构
        $tree_data = list_to_tree($list);
        //下拉模板
        $template = "<li data-value='{id}' data-title='{top_class}{name}'><a href='javascript:void(0)'>{top_class}{name}</a></li>";
        //设置初始数据
        api('Tree/init',array($tree_data,$template,array('id','name')));
        //生成树状下拉菜单
        $html = api('Tree/toSelectFormatTree',array($index_value, $index_field));
        //html结构
        $select = "<div class='btn-group'>
                        <button type='button' class='btn checked' data-default='--选择分类--'></button>
                        <button class='btn dropdown-toggle' data-toggle='dropdown'><span class='caret'></span></button>
                        <ul class='dropdown-menu'>".$html."</ul>
                    </div>
                    <input type='hidden' name='".$field_name."' value='".$index_value."' class='{target-form}'>";

        return $select;
    }


    /**
     * @param array $list   分类数据
     * @param string $field_name 隐藏文本框name名称
     * @param string $index_value  默认选中的值
     * @param string $index_field  默认选中的字段索引
     * @return string
     * 获取树状下拉菜单
     */
    function getSelect1($list = array(), $field_name = '', $index_value = '', $index_field = 'id') {
        //判空
        if(empty($list)) return '';
        //将数据转换成树状结构
        $tree_data = list_to_tree($list);
        //下拉模板
        $template = "<li data-value='{id}' data-title='{top_class}{s_t_name}'><a href='javascript:void(0)'>{top_class}{s_t_name}</a></li>";
        //设置初始数据
        api('Tree/init',array($tree_data,$template,array('id','s_t_name')));
        //生成树状下拉菜单
        $html = api('Tree/toSelectFormatTree',array($index_value, $index_field));
        //html结构
        $select = "<div class='btn-group'>
                        <button type='button' class='btn checked' data-default='--选择分类--'></button>
                        <button class='btn dropdown-toggle' data-toggle='dropdown'><span class='caret'></span></button>
                        <ul class='dropdown-menu'>".$html."</ul>
                    </div>
                    <input type='hidden' name='".$field_name."' value='".$index_value."' class='{target-form}'>";

        return $select;
    }
}