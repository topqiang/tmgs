<?php
namespace Common\Controller;

/**
 * 插件类
 */
abstract class Plugin {
    /**
     * 视图实例对象
     * @var view
     * @access protected
     */
    protected $view = null;

    /**
     * $info = array(
     *  'name'          =>  'Editor',
     *  'title'         =>  '编辑器',
     *  'description'   =>  '用于增强整站长文本的输入和显示',
     *  'status'        =>  1,
     *  'author'        =>  'thinkphp',
     *  'version'       =>  '0.1'
     *  )
     */
    public $info                =   array();
    public $plugin_path         =   '';
    public $config_file         =   '';
    public $access_url          =   array();

    public function __construct() {
        //获取视图实例对象
        $this->view         =   \Think\Think::instance('Think\View');
        //某插件路径
        $this->plugin_path  =   PLUGIN_PATH.$this->getName().'/';
        //模板替换变量
        $TMPL_PARSE_STRING  = C('TMPL_PARSE_STRING');
        $TMPL_PARSE_STRING['__PLUGIN_ROOT__'] = __ROOT__ . '/Plugins/'.$this->getName();
        //动态配置赋值
        C('TMPL_PARSE_STRING', $TMPL_PARSE_STRING);
        //存在配置文件 给属性赋值
        if(is_file($this->plugin_path.'config.php')){
            $this->config_file = $this->plugin_path.'config.php';
        }
    }

    /**
     * 模板主题设置
     * @access protected
     * @param string $theme 模版主题
     * @return Action
     */
    final protected function theme($theme) {
        $this->view->theme($theme);
        return $this;
    }

    /**
     * @param string $template
     * @throws \Exception
     * 显示方法
     */
    final protected function display($template = '') {
        if($template == '')
            $template = CONTROLLER_NAME;
        echo ($this->fetch($template));
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return Action
     */
    final protected function assign($name, $value = '') {
        $this->view->assign($name,$value);
        return $this;
    }


    /**
     * @param mixed|string $templateFile
     * @return mixed
     * @throws \Exception
     * 用于显示模板的方法
     */
    final protected function fetch($templateFile = CONTROLLER_NAME) {
        if(!is_file($templateFile)) {
            $templateFile = $this->plugin_path.$templateFile.C('TMPL_TEMPLATE_SUFFIX');
            if(!is_file($templateFile)) {
                throw new \Exception("模板不存在:$templateFile");
            }
        }
        return $this->view->fetch($templateFile);
    }

    /**
     * @return string
     * 获取插件名称
     */
    final public function getName() {
        $class = get_class($this);
        return substr($class,strrpos($class, '\\')+1, -6);
    }

    /**
     * @return bool
     * 检查插件是否有配置信息
     */
    final public function checkInfo() {
        $info_check_keys = array('name','title','description','status','author','version');
        foreach ($info_check_keys as $value) {
            if(!array_key_exists($value, $this->info))
                return FALSE;
        }
        return TRUE;
    }

    /**
     * @param string $name
     * @return array|mixed
     * 获取插件的配置数组
     */
    final public function getConfig($name = '') {
        static $_config = array();
        if(empty($name)) {
            $name = $this->getName();
        }
        if(isset($_config[$name])) {
            return $_config[$name];
        }
        $map['name']    =   $name;
        $map['status']  =   1;
        $config         =   D('Plugins')->where($map)->getField('config');
        if($config) {
            $config   =   json_decode($config, true);
        } else {
            $temp_arr = include $this->config_file;
            foreach ($temp_arr as $key => $value) {
                if($value['type'] == 'group') {
                    foreach ($value['options'] as $g_key => $g_value) {
                        foreach ($g_value['options'] as $i_key => $i_value) {
                            $config[$i_key] = $i_value['value'];
                        }
                    }
                } else {
                    $config[$key] = $temp_arr[$key]['value'];
                }
            }
        }
        $_config[$name]     =   $config;
        return $config;
    }

    /**
     * @return mixed
     * 必须实现安装
     */
    abstract public function install();

    /**
     * @return mixed
     * 必须卸载插件方法
     */
    abstract public function uninstall();
}
