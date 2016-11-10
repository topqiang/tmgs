<?php
namespace Api\Logic;
/**
 * Class BaseLogic
 * @package Api\Logic
 * 逻辑层基类
 */
class BaseLogic{
    /**
     * 初始化
     */
    public function _initialize(){
    }

    /**
     * 获取配置文件
     */
    public function getConfig(){
        $config = D('Config')->parseList();
        return $config;
    }

    /**
     * @param $m_id
     * @return int
     * 获取用户未读信息条数
     */
    public function getUnReadMessageNum($m_id){
        $where['m_id']   = $m_id;
        $where['status'] = array('eq',0);
        $num = M('Message')->where($where)->count();
        $num = $num?$num:0;
        return $num;
    }


    /**
     * @param int $num
     * @return array
     * 环信注册
     */
    public function easemobRegister(){
        $i = 0;
        while($i < 8){
            $i = $i+1;
            //生成环信账号
            $username = time().rand(00001,99999);
            $res = M('Member')->where(array('easemob_account'=>$username))->count();
            if($res>0){
                continue;
            }
            //生成环信密码
            $password = time();

            //调用环信注册账号
            $register_res = D('Easemob','Service')->createUser(array('username'=>$username,'password'=>$password));
            //判断环信注册结果，如果注册失败，继续调用循环,如果成功直接跳出循环
            if($register_res['error']){
                continue;
            }else{
                break;
            }
        }
        //如果循环8次依旧没有得到想要的结果，返回error,如果成功返回success
        if($register_res['error']){
            return array('flag' => 'error');
        }else{
            return array('flag' => 'success', 'easemob_account' => $username, 'easemob_password' => $password);
        }
    }

    /**
     * @param string $account
     * @param int $type
     * @return bool
     * 检查账号是否重复
     * $account是需要被检测的账号，$type=1表示检查用户名，$type=2表示检查账号
     */
    public function checkAccount($account = '',$type = 1){
        if($type==1){
            $where['nickname'] = $account;
        }else{
            $where['account']  = $account;
        }
        $res = M('Member')->where($where)->count();
        if($res>0){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 获取百度地址返回
     * @param $data
     * @return mixed
     */
    public function getAddress($data)
    {
        $ak = 'EFa3o1aWFce9qO041CNjNYG6b4H7qcX1';
        $coor = 'bd09ll'; // coor=bd09ll时，返回为百度经纬度坐标
        $ip = $data;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.map.baidu.com/location/ip?ak=" . $ak . "&ip=" . $ip . "&coor=" . $coor);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $res = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($res, true);
        if ($json['status'] == 0) {
            // 当存在地址
            $return['register_city'] = $json['content']['address_detail']['city'];
            $return['register_province'] = $json['content']['address_detail']['province'];
        } else {
            // 当不存在地址
            $return['register_city'] = '未知';
            $return['register_province'] = '未知';
        }
        return $return;
    }
}