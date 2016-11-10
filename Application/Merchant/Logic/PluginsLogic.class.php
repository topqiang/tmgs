<?php

namespace Merchant\Logic;

/**
 * Class PluginsLogic
 * @package Manager\Logic
 * 插件 逻辑层
 */
class PluginsLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取插件列表
     * 读取插件目录下的插件文件夹列表，并查询插件数据表（即已安装的插件） 未安装的插件 读取入口文件中的 $info 信息
     */
    function getList($request = array()) {
        //获取插件目录下子目录的名称 存为数组
        $dirs = array_map('basename',glob(PLUGIN_PATH.'*', GLOB_ONLYDIR));
        //判断插件目录是否存在或可读
        if($dirs === false || !file_exists(PLUGIN_PATH)) {
            $this->setLogicError('插件目录不可读或者不存在！');
            return false;
        }
        //存储所有插件数组
        $plugins = array();
        //查询数据库里的插件记录 即已安装的插件
        $param['where']['name']	= array('IN',$dirs);
        $list = D('Plugins')->getList($param);
        //数据库已有插件
        foreach($list as $plugin) {
            //已安装
            $plugin['uninstall']	    =	0;
            $plugins[$plugin['name']]	=	$plugin;
        }

        //循环插件文件夹下的所有子文件夹名称 即插件名称 plugins表中name
        foreach ($dirs as $value) {
            //判断数据库是否存在该插件 不存在即未安装 判断插件入口是否可用
            if(!isset($plugins[$value])) {
                //获取插件入口文件名称
                $class = get_plugin_class($value);
                // 实例化插件失败忽略执行
                if(!class_exists($class)) {
                    \Think\Log::record('插件'.$value.'的入口文件不存在！');
                    continue;
                }
                //初始化插件入口类
                $PluginClass = new $class;
                //获取插件介绍信息
                $plugins[$value] = $PluginClass->info;
                if($plugins[$value]) {
                    //未安装标志
                    $plugins[$value]['uninstall'] = 1;
                    //插件状态置为未安装
                    unset($plugins[$value]['status']);
                }
            }
        }
        return array('list'=>$plugins,'page'=>$list['page']);
    }

    function findRow($request = array()){}

    /**
     * @param array $request
     * @return bool
     * 安装插件
     * 判断插件固定参数是否正常，插入数据库，修改钩子对应的插件信息
     */
    function install($request = array()) {
        //插件名称
        $plugin_name = trim($request['plugin_name']);
        //根据插件名称获取插件类名
        $class       = get_plugin_class($plugin_name);
        //判断插件类名是否存在
        if(!class_exists($class)) {
            $this->setLogicError('插件不存在！'); return false;
        }
        //插件存在 初始化插件类
        $PluginClass = new $class;
        //获取插件的信息 作者、版本等
        $info = $PluginClass->info;
        //检测信息的正确性
        if(!$info || !$PluginClass->checkInfo()) {
            $this->setLogicError('插件信息缺失！'); return false;
        }
        session('plugins_install_error',null);
        //执行插件中的 预安装操作
        $install_flag = $PluginClass->install();
        if(!$install_flag) {
            $this->setLogicError('执行插件预安装操作失败'.session('plugins_install_error')); return false;
        }
        //初始化插件模型
        $PluginsModel  =   D('Plugins');
        //创建数据
        $data          =   $PluginsModel->create($info);
        if(!$data) {
             $this->setLogicError($PluginsModel->getError()); return false;
        }
        //插件数据写入数据库
        if($PluginsModel->add($data)) {
            //插入成功后 读取默认的配置信息
            $config         =   array('config'=>json_encode($PluginClass->getConfig()));
            //插入默认配置信息
            $PluginsModel->where(array('name'=>$plugin_name))->data($config)->save();
            //修改钩子对应的插件内容
            $hooks_update   =   D('Hooks','Logic')->updateHooks($plugin_name);
            if($hooks_update) {
                //修改成功后 清空钩子缓存
                S('Hooks_Cache', null);
                $this->setLogicSuccess('安装成功'); return true;
            } else {
                //修改失败 删除插件记录
                $PluginsModel->where(array('name'=>$plugin_name))->delete();
                $this->setLogicError('更新钩子处插件失败,请卸载后尝试重新安装'); return false;
            }
        } else {
            $this->setLogicError('写入插件数据失败'); return false;
        }
    }

    /**
     * @param array $request
     * @return bool
     * 卸载插件
     * 删除数据库记录，修改钩子对应的插件信息
     */
    function uninstall($request = array()) {
        //初始化插件模型
        $PluginsModel  =  D('Plugins');
        //获取插件记录信息
        $param['where']['id'] = trim($request['id']);
        $plugin  =  $PluginsModel->findRow($param);
        //获取插件类名
        $class   =  get_plugin_class($plugin['name']);
        //判断插件是否存在
        if(!$plugin || !class_exists($class)) {
            $this->setLogicError('插件不存在'); return false;
        }
        session('plugins_uninstall_error',null);
        //初始化插件类
        $PluginClass   = new $class;
        //预卸载处理
        $uninstall_flag = $PluginClass->uninstall();
        if(!$uninstall_flag) {
            $this->setLogicError('执行插件预卸载操作失败' . session('plugins_uninstall_error'));
            return false;
        }
        //移除钩子对应的该插件信息
        $hooks_update   =   D('Hooks','Logic')->removeHooks($plugin['name']);
        if($hooks_update === false) {
            $this->setLogicError('卸载插件所挂载的钩子数据失败'); return false;
        }
        //清空钩子缓存
        S('Hooks_Cache', null);
        //删除插件记录
        $delete = $PluginsModel->where(array('name'=>$plugin['name']))->delete();
        if($delete === false) {
            $this->setLogicError('卸载插件失败'); return false;
        } else {
            $this->setLogicSuccess('卸载成功'); return true;
        }
    }

    /**
     * @param array $request
     * @return mixed
     * 插件设置页面
     */
    public function config($request = array()) {
        //获取插件记录信息
        $param['where']['id'] = $request['id'];
        $plugin  =  D('Plugins')->findRow($param);
        if(!$plugin) {
            $this->setLogicError('插件未安装'); return false;
        }
        //获取插件类名
        $class = get_plugin_class($plugin['name']);
        //判断插件类名是否存在
        if(!class_exists($class)) {
            trace("插件{$plugin['name']}无法实例化,",'PLUGINS','ERR');
            $this->setLogicError('插件不存在'); return false;
        }
        //初始化插件类
        $PluginClass  =  new $class;
        //插件路径
        $plugin['plugin_path'] = $PluginClass->plugin_path;
        //数据库以存配置信息
        $db_config = $plugin['config'];
        //导入文件中配置信息
        $plugin['config'] = include $PluginClass->config_file;
        /**
         * 配置文件格式
         * return array(
                普通配置格式
                'title' => array(
                    'title' => '显示标题',    //表单的文字
                    'type'  => 'text',		 //表单的类型：text、textarea、checkbox、radio、select等
                    'value' => 'OneThink开发团队',			 //表单的默认值
                    'tip'   => '填写自己登录友言后的uid,填写后可进相应官方后台' 提示
                ),
                'width' => array(
                    'title'     => '显示宽度',
                    'type'      => 'select',
                    'options'   => array(
                        '1' => '1格',
                        '2' => '2格',
                        '4' => '4格'
                    ),
                    'value'     => '2'
                ),
                'display' => array(
                    'title'     => '是否显示',
                    'type'      => 'radio',
                    'options'   => array(
                        '1' => '显示',
                        '0' => '不显示'
                    ),
                    'value'     => '1'
                ),
                'text' => array(
                    'title' => '文字',
                    'type'  => 'textarea',
                    'value' => '文字'
                ),
                'box' => array(
                    'title'     => 'box',
                    'type'      => 'checkbox',
                    'options'   => array(
                        '1' => '显示',
                        '0' => '不显示'
                    ),
                    'value'     => '1'
                ),
                'picture' => array(
                    'title' => '图片',
                    'type'  => 'picture_union',
                    'value' => ''
                ),
                分组配置格式
                'group' => array(
                    'title'     => '分组',
                    'type'      => 'group',
                    'options'   =>  array(
                        'youyan' => array(
                            'title' => '友言配置',
                            'options' => array(
                                同普通配置
                            )
                        ),
                        'duoshuo'=>array(
                            'title'=>'多说配置',
                            'options'=>array(
                                同普通配置
                            )
                        )
                    )
                )
            );
         */
        //数据库中存在配置
        if($db_config) {
            //json转化
            $db_config = json_decode($db_config, true);
            foreach ($plugin['config'] as $key => $value) {
                //不存在分组配置
                if($value['type'] != 'group') {
                    $plugin['config'][$key]['value'] = $db_config[$key];
                    //如果是图片类型 获取图片信息
                    if($plugin['config'][$key]['type'] == 'picture_union') {
                        $plugin['config'][$key]['value'] = api('System/getFiles',array($db_config[$key]));
                    }
                } else {
                    //存在分组配置
                    foreach ($value['options'] as $group => $options) {
                        foreach ($options['options'] as $g_key => $value) {
                            $plugin['config'][$key]['options'][$group]['options'][$g_key]['value'] = $db_config[$g_key];
                            //如果是图片类型 获取图片信息
                            if($plugin['config'][$key]['options'][$group]['options'][$g_key]['type'] == 'picture_union') {
                                $plugin['config'][$key]['options'][$group]['options'][$g_key]['value'] = api('System/getFiles',array($db_config[$g_key]));
                            }
                        }
                    }
                }
            }
        }
        return $plugin;
    }

    /**
     * @param array $data
     * @return array
     * 修改配置时数据处理
     */
    protected function processData($data = array()) {
        $data['config'] = json_encode($data['config']);
        return $data;
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
     * @param null $plugins
     * @param null $controller
     * @param null $action
     * @return bool
     * 执行插件控制器中的方法
     * 用于调度各个扩展的URL访问需求
     */
    function execute($plugins = null, $controller = null, $action = null) {
        if(C('URL_CASE_INSENSITIVE')) {
            $plugins       =   ucfirst(parse_name($plugins, 1));
            $controller    =   parse_name($controller,1);
        }

        if(!empty($plugins) && !empty($controller) && !empty($action)){
            $Plugins = A("Plugins://{$plugins}/{$controller}")->$action();
        } else {
            $this->setLogicError('没有指定插件名称，控制器或操作！'); return false;
        }
    }
}