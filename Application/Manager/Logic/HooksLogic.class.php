<?php
namespace Manager\Logic;

/**
 * Class HooksLogic
 * @package Manager\Logic
 * 钩子逻辑处理类
 */
class HooksLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     */
    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Hooks')->getList($param);
        return $result;
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
        $row = D('Hooks')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param int $result
     * @param array $request
     * @return boolean
     * 修改状态后执行 清除钩子缓存
     */
    protected function afterSetStatus($result = 0, $request = array()) {
        S('Hooks_Cache', null);
        return true;
    }

    /**
     * @param $result
     * @param array $request
     * @return boolean
     * 更新后执行
     */
    protected function afterUpdate($result, $request = array()) {
        S('Hooks_Cache', null);
        return true;
    }

    /**
     * @param string $plugin_name
     * @return bool
     * 更新所有钩子对应的该插件
     */
    function updateHooks($plugin_name = '') {
        //获取插件类名
        $plugin_class = get_plugin_class($plugin_name);
        //判断类名是否存在
        if(!class_exists($plugin_class)) {
            $this->setLogicError("未实现{$plugin_name}插件的入口文件"); return false;
        }
        //获取插件类中的方法名称
        $methods    =   get_class_methods($plugin_class);
        //获取数据库中所有钩子名称
        $hooks      =   D('Hooks')->getField('name', true);
        //取交集
        $common     =   array_intersect($hooks, $methods);
        //是否存在钩子方法
        if(!empty($common)) {
            //循环钩子 修改钩子对应的插件信息
            foreach ($common as $hook) {
                //修改钩子对应的插件信息
                $flag = $this->_updatePlugins($hook, array($plugin_name));
                if(false === $flag) {
                    $this->removeHooks($plugin_name);
                    return false;
                }
            }
        } else {
            $this->setLogicError("插件未实现任何钩子"); return false;
        }
        return true;
    }

    /**
     * @param $hook_name
     * @param $plugin_name
     * @return bool
     * 更新单个钩子处的插件
     */
    private function _updatePlugins($hook_name, $plugin_name) {
        //原有插件信息
        $o_plugins = D('Hooks')->where(array('name'=>$hook_name))->getField('plugins');
        if($o_plugins) {
            //转化成数组
            $o_plugins = str2arr($o_plugins);
        }
        //存在原有插件信息
        if($o_plugins) {
            //合并数组 去掉重复
            $plugins = array_merge($o_plugins, $plugin_name);
            $plugins = array_unique($plugins);
        } else {
            //新插件信息未当前插件名称
            $plugins = $plugin_name;
        }
        //修改插件信息
        $flag = D('Hooks')->where(array('name'=>$hook_name))->data(array('plugins'=>arr2str($plugins)))->save();
        return $flag;
    }

    /**
     * @param $plugin_name
     * @return bool
     * 去除所有钩子里对应的该插件数据
     */
    function removeHooks($plugin_name) {
        //获取插件类名
        $plugin_class = get_plugin_class($plugin_name);
        //判断类名是否存在
        if(!class_exists($plugin_class)) {
            return false;
        }
        //获取插件类中存在的方法 与钩子名称对应
        $methods = get_class_methods($plugin_class);
        //获取钩子名称
        $hooks = D('Hooks')->getField('name', true);
        //取交集
        $common = array_intersect($hooks, $methods);
        //存在钩子方法
        if($common) {
            //循环钩子
            foreach ($common as $hook) {
                //移除钩子对应的插件信息
                $flag = $this->_removePlugins($hook, array($plugin_name));
                if(false === $flag) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param $hook_name
     * @param $plugin_name
     * @return bool
     * 去除单个钩子里对应的该插件数据
     */
    private function _removePlugins($hook_name, $plugin_name) {
        //获取原插件信息
        $o_plugins = D('Hooks')->where(array('name'=>$hook_name))->getField('plugins');
        //转化成数组
        $o_plugins = str2arr($o_plugins);
        //存在原有插件信息
        if($o_plugins) {
            //去掉重复值
            $plugins = array_diff($o_plugins, $plugin_name);
        } else {
            return true;
        }
        //更新
        $flag = D('Hooks')->where(array('name'=>$hook_name))->data(array('plugins'=>arr2str($plugins)))->save();
        return $flag;
    }
}