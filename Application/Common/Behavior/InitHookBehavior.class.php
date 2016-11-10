<?php
namespace Common\Behavior;
use Think\Behavior;
use Think\Hook;
defined('THINK_PATH') or exit();

// 初始化钩子信息
class InitHookBehavior extends Behavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content) {
        if(isset($_GET['m']) && $_GET['m'] === 'Install') return;
        
        $data = S('Hooks_Cache');
        if(!$data) {
            $hooks = M('Hooks')->getField('name,plugins');
            foreach ($hooks as $key => $value) {
                if($value) {
                    $map['status']  =   1;
                    $names          =   explode(',',$value);
                    $map['name']    =   array('IN',$names);
                    $data = M('Plugins')->where($map)->getField('id,name');
                    if($data){
                        $plugins = array_intersect($names, $data);
                        Hook::add($key,$plugins);
                    }
                }
            }
            S('Hooks_Cache',Hook::get());
        } else {
            Hook::import($data,false);
        }
    }
}