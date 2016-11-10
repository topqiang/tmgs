<?php

namespace Manager\Logic;

/**
 * 商品类型
 * Class GoodsTypeLogic
 * @package Manager\Logic
 */
class GoodsTypeLogic extends BaseLogic{


    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态

        //判断是否存在缓存  缓存在分类 修改或添加后清空
            $result = D('GoodsType')->getList($param);
        //将数据转换成树状结构  调用分类api 生成操作html
        $tree_data = list_to_tree($this->handleOperate($result,'GoodsType'));
        //获取某分类下的所有子分类
        //$list[] = D('GoodsType')->findRow(array('where'=>array('id'=>1)));
        //var_dump(api('Tree/getAllChild',array($tree_data, $list)));
        //获取某分类的所有父分类
        //var_dump(api('Tree/getAllParent',array($result, 45)));
        //分类模板
        $template = "<tr>
                        <td>{id}</td>
                        <td>{top_class}{cn_type_name}</td>
                        <td>{ue_type_name}</td>
                        <td>{is_app_show}</td>
                        <td>{operate}</td>
                    </tr>";

        //设置初始数据
        api('Tree/init',array($tree_data,$template,array('id','cn_type_name','ue_type_name','is_app_show','operate')));
        //生成树状页面
        $html = api('Tree/toFormatTree');
        return array('list'=>$html);
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
    function handleOperate($list = array(), $model = '', $prohibit = array(), $only_allow = array('add_child','edit','set_status','delete')) {
        //空数组返回空
        if(empty($list)) { return array(); }
        foreach($list as $key => $value) {
                //添加子分类操作的html
            if( $value['type'] != 3){
                if($value['type'] == 1){
                    $add_child = "<a href='".U(''.MODULE_NAME.'/'.$model.'/add/id/'.$value['id'].'/type/2')."' title='添加子分类' class='tip-bottom'>
                            <span class='label label-warning'>+子分类</span></a>&nbsp";
                }
                if($value['type'] == 2){
                    $add_child = "<a href='".U(''.MODULE_NAME.'/'.$model.'/add/id/'.$value['id'].'/type/3')."' title='添加子分类' class='tip-bottom'>
                            <span class='label label-warning'>+子分类</span></a>&nbsp";
                }


            }else{
                $add_virtue = "<a href='".U(''.MODULE_NAME.'/Attribute/index/id/'.$value['id'].'')."' title='分类属性列表' class='tip-bottom'>
                            <span class='label label-info'>分类属性列表</span></a>&nbsp";
                $add_child = '';
            }

            if($value['is_app_show'] == 0){
                $list[$key]['is_app_show'] = '不显示';
            }else if($value['is_app_show'] == 1){
                $list[$key]['is_app_show'] = '显示';
            }else{
                $list[$key]['is_app_show'] = '未知';
            }

            //编辑分类的HTML
            $edit =  "<a href='".U(''.MODULE_NAME.'/'.$model.'/update/parent_id/'.$value['parent_id'].'/id/'.$value['id'].'')."' title='编辑' class='tip-bottom'>
                        <span class='label label-success'>编辑</span></a>&nbsp;";
            //判断当前状态
            if($value['is_app_show'] == 0) {
                //启用html
                $set_status = "<a href='".U(''.MODULE_NAME.'/'.$model.'/resume/model/'.$model.'/ids/'.$value['id'].'/is_app_show/1')."' title='显示' class='tip-bottom ajax-get'>
                                <span class='label label-info'>显示</span></a>&nbsp;";
            } elseif($value['is_app_show'] == 1) {
                //禁用html
                $set_status = "<a href='".U(''.MODULE_NAME.'/'.$model.'/forbid/model/'.$model.'/ids/'.$value['id'].'/is_app_show/0')."' title='不显示' class='tip-bottom ajax-get'>
                                <span class='label label-inverse'>不显示</span></a>&nbsp";
            }
            //删除操作的html
            $delete = "<a href='".U(''.MODULE_NAME.'/'.$model.'/remove/model/'.$model.'/ids/'.$value['id'])."' title='删除' class='tip-bottom confirm ajax-get'>
                            <span class='label label-important'>删除</span></a>";

            $html = '';
            //判断是否存在 禁止的ID  并当前ID是否在禁止的数组中  并存在只允许的操作
            if($prohibit && in_array($value['id'],$prohibit) && $only_allow) {
                foreach($only_allow as $rep) {
                    $html .= $$rep;
                }
            } else {
                if($value['type'] != 3){
                    $html = $add_child . $edit . $set_status . $delete;
                }else {
                    $html = $add_virtue . $edit . $set_status . $delete;
                }
            }

            $list[$key]['operate'] = $html;
        }

        return $list;
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
        $param['status']   = array('lt',9);
        $param['type'] = array('neq',3);
        $result = D('GoodsType') -> where($param) -> select();
        return $this -> setSelect($result,$field_name,$index_value,$index_field);
    }

    /**
     * @param string $index_value 默认选中的值
     * @return string
     * 获取分类树状下拉菜单
     */
    function getAddSelect($index_value = '') {
        $where['id'] = $index_value;
        //状态
        $result = D('GoodsType') -> where($where) -> field('cn_type_name,id gt_id')->find();
        return $result;
    }
    /**
     * @param string $index_value 默认选中的值
     * @return string
     * 获取分类树状下拉菜单
     */
    function getAddAllSelect($field_name = '', $index_value = '', $index_field = 'id') {
        $param['type'] = array('eq',1);
        $result = D('GoodsType') -> where($param) -> select();
        return $this -> setSelect($result,$field_name,$index_value,$index_field);
    }
    /**
     * @param array $list   分类数据
     * @param string $field_name 隐藏文本框name名称
     * @param string $index_value  默认选中的值
     * @param string $index_field  默认选中的字段索引
     * @return string
     * 获取树状下拉菜单
     */
    function setSelect($list = array(), $field_name = '', $index_value = '', $index_field = 'id') {
        //判空
        if(empty($list)) return '';
        //将数据转换成树状结构
        $tree_data = list_to_tree($list);
        //下拉模板
        $template = "<li data-value='{id}' data-title='{top_class}{cn_type_name}'><a href='javascript:void(0)'>{top_class}{cn_type_name}</a></li>";
        //设置初始数据
        api('Tree/init',array($tree_data,$template,array('id','cn_type_name')));
        //生成树状下拉菜单
        $html = api('Tree/toSelectFormatTree',array($index_value, $index_field));
        //html结构
        $select = "<div class='btn-group'>
                        <button type='button' class='btn checked' data-default='--顶级分类--'></button>
                        <button class='btn dropdown-toggle' data-toggle='dropdown'><span class='caret'></span></button>
                        <ul class='dropdown-menu'>".$html."</ul>
                    </div>
                    <input type='hidden' name='".$field_name."' value='".$index_value."' class='{target-form}'>";

        return $select;
    }

    /**
     * @param array $request
     * @return bool
     * 删除分类前操作 验证是否该分类下存在商品
     */
    protected function beforeSetStatus($request = array()) {
        if($request['status'] == 9) {
            //判断给分类下是否存在文章
            $where['parent_id'] = $request['ids'];
            $where['status']  = array('lt',9);
            $count = D('GoodsType')->where($where)->count();
            if($count > 0) {
                $this->setLogicError('该分类下还有分类，请先删除分类'); return false;
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
        S('GoodsType_Cache', null);
        return true;
    }

    /**
     * @param int $result
     * @param array $request
     * @return boolean
     * 更新后执行e
     */
    protected function afterUpdate($result, $request = array()) {
        S('GoodsType_Cache', null);
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
        $row = D('GoodsType')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //获取文件
        $row['app_picture'] = api('System/getFiles',array($row['app_picture']));
        return $row;
    }

    /**
     * @param array $request  model 模型  ids操作的主键ID  status要改为的状态
     * @return bool
     * 修改状态
     */
    function setStatus($request = array())
    {
        //判断参数
        if (empty($request['model']) || empty($request['ids']) || !isset($request['is_app_show'])) {
            $this->setLogicError('参数错误！');
            return false;
        }
        //执行前操作
        if (!$this->beforeSetStatus($request)) {
            return false;
        }
        //判断是数组ID还是字符ID
        if (is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in', $request['ids']);
            $ids = implode(',', $request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }

        $data = array(
            'is_app_show' => $request['is_app_show'],
            'update_time' => time()
        );

        $result = D($request['model'])->where($where)->data($data)->save();

        if ($result) {
            //行为日志
            api('Manager/ActionLog/actionLog', array('change_status', $request['model'], $ids, AID));
            //执行后操作
            if (!$this->afterSetStatus($result, $request)) {
                return false;
            }
            $this->setLogicSuccess('操作成功！');
            return true;
        } else {
            $this->setLogicError('操作失败！');
            return false;
        }
    }


    /**
     * @param array $request
     * @return bool|mixed
     * 新增 或 修改
     */
    function update($request = array()) {
        //执行前操作
        if(!$this->beforeUpdate($request)) { return false; }
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = D($model)->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
            //新增数据
            // 判断类型是否存在 如果不存在 就判断为顶级分类
            if(!$data['type']){
                $data['type'] = 1;
            }
            $result = D($model)->data($data)->add();
            if(!$result) {
                $this->setLogicError('新增时出错！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $result = D($model)->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！'); return true;
    }
}