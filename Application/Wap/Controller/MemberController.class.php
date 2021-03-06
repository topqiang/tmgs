<?php
namespace Wap\Controller;
/**
 * Class MemberController
 * @package Wap\Controller
 * 用户相关
 */
class MemberController extends BaseController
{

    /**
     * 注册协议
     */
    public function agreement()
    {
        $artcle = $this->artcle('2');
        $this->assign('deal',$artcle);
        $this->display();
    }

    /**
     * 登录
     */

    public function login()
    {
        $this->display();
    }

    /**
     * 注册
     */
    public function register()
    {
        $this->display();
    }

    /**
     * 修改密码
     */
    public function resetpwd()
    {
        $this->display();
    }

    /**
     * 收货地址列表
     */
    public function addresslist()
    {
        $this->display();
    }

    /**
     * 收藏列表
     */
    public function collection()
    {
        $this->display();
    }

    /**
     * 收藏商家列表
     */
    public function collectionshop()
    {
        $this->display();
    }

    /**
     * 购物车
     */
    public function shopcart()
    {
        $this->display();
    }

    public function saveMid()
    {
        $m_id = $_POST['m_id'];
        if (isset($m_id)) {
            session("m_id",$m_id);
            $this -> ajaxReturn("success");
        } 
    }

    public function loginout(){
        session("m_id",null);
        $this->ajaxReturn("success");
    }
    /**
     * 我的
     * array(1) {
    ["member_info"] => array(13) {
    ["m_id"] => string(1) "2"
    ["account"] => string(11) "18202229802"
    ["password"] => string(32) "e10adc3949ba59abbe56e057f20f883e"
    ["nickname"] => string(11) "18202229802"
    ["balance"] => string(8) "20000.00"
    ["head_pic"] => string(46) "http://2.taomim.com/Uploads/Member/default.png"
    ["easemob_account"] => string(15) "146924346437912"
    ["easemob_password"] => string(10) "1469243464"
    ["grade"] => string(1) "E"
    ["amount"] => string(4) "0.00"
    ["account_style"] => string(1) "1"
    ["password_style"] => string(1) "1"
    ["un_read_num"] => string(2) "22"
    }
    }
     */
    public function self()
    {
        $this->assign('info',session('member_info'));
        $this->display();
    }

    /**
     * 设置
     */
    public function setting()
    {
        $this->display();
    }

    /**
     * 绑定手机号
     */
    public function bindphone()
    {
        $this->display();
    }

    /**
     * 设置登录密码
     */
    public function setpwd()
    {
        $this->display();
    }

    /**
     * 修改密码
     */
    public function updatepwd()
    {
        $this->display();
    }

    /**
     * 个人资料
     */
    public function selfinfo()
    {
        $this->display();
    }

    /**
     * 我的钱包
     */
    public function mywallet()
    {
        $this->display();
    }

    /**
     * 我的银行卡
     */
    public function banklist()
    {
        $this->display();
    }

    /**
     * 添加银行卡
     */
    public function addbank()
    {
        $this->display();
    }

    /**
     * 添加银行返回
     */
    public function bankresult()
    {
        $this->display();
    }

    /**
     * 充值
     */
    public function recharge()
    {
        $this->display();
    }

    /**
     * 提现
     */
    public function tixian()
    {
        $this->display();
    }

    /**
     * 账单明细
     */
    public function transaction()
    {
        $this->display();
    }

    /**
     * 修改地址
     */
    public function editadd()
    {
        $this->display();
    }

    /**
     * 足迹
     */
    public function history()
    {
        $this->display();
    }

    /**
     * 我的推广/等级
     */
    public function mylevel()
    {
        $this->display();
    }


    public function myinvite()
    {
        $this->display();
    }

    /**
     * 推广获得收益
     */
    public function levelinfo()
    {
        $this->display();
    }

    public function savaMember(){
        $m_id = $_POST['m_id'];
        if (!empty($_POST['nickname'])) {
            $data['nickname'] = $_POST['nickname'];
        }
        if (!empty($_POST['age'])) {
            $data['age'] = $_POST['age'];
        }
        if (!empty($_POST['head_pic'])) {
            $data['head_pic'] = $_POST['head_pic'];
        }
        $where['id'] = $m_id;
        $data['update_time'] = time();
        $res = M('Member')->where($where)->data($data)->save();
        if (isset($res)) {
            $this -> ajaxReturn("success");
        }else{
            $this -> ajaxReturn("error");
        }
    }

    /**
     * 上传图片
     */
    public function uploadPic(){
        $pic       = $_POST['pic'];
        $pic_name      = $_POST['pic_name'];
        $temp = explode('.',$pic_name);
        $ext = uniqid().'.'.end($temp);
        $base64    = substr(strstr($pic, ","), 1);
        $image_res = base64_decode($base64);
        $pic_link  = "Uploads/Member/".date('Y-m-d').'/'.$ext;
        $saveRoot = "Uploads/Member/".date('Y-m-d').'/';
        //检查目录是否存在  循环创建目录
        if(!is_dir($saveRoot)){
            mkdir($saveRoot, 0777, true);
        }
        $res = file_put_contents($pic_link ,$image_res);
        if($res){
            $data['name'] = date('m-d:H:i').'.'.$ext;
            $data['path'] = '/'.$pic_link;
            $data['create_time'] = time();
            $data['status'] = 1;
            $id = M('File')->data($data)->add();
            $ajaxData = array("flag" => "success", "message"=>"上传成功！" );
            if($id){
                $result_data['path'] = $data['path'];
                $result_data['id']   = $id;
                $ajaxData['data'] = $result_data;
                $this->ajaxReturn(json_encode($ajaxData));
            }
        }else{
            $ajaxData = array("flag" => "error", "message"=>"上传头像失败","data" => array());
            $this->ajaxReturn(json_encode($ajaxData));
        }
    }
}