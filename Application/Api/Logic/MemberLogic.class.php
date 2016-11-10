<?php
namespace Api\Logic;

/**
 * Class MemberLogic
 * @package Api\Logic
 */
class MemberLogic extends BaseLogic{

    /**
     * @param array $request
     * 个人中心
     * 用户ID    m_id
     */
    public function memberCenter($request = array()){
        //用户ID不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //查询用户信息
        $where['id']      = $request['m_id'];
        $where['status']  = array('neq',9);
        $member_info = M('Member')->where($where)->field('id as m_id,account,password,nickname,easemob_account,easemob_password,head_pic, balance, grade')->find();
        if(empty($member_info)){
            apiResponse('error','该用户不存在');
        }
        if($member_info['head_pic'] == 0){
            $member_info['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
        }else{
            $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
            $member_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
        }
        //获取结算中金额
        unset($where);
        $where['m_id'] = $member_info['m_id'];
        $where['status'] = array('IN',array(0,1,2,3));
        $amount = M('Order') ->where($where) ->getField('SUM(totalprice) as totalprice');
        $member_info['amount'] = $amount?$amount:'0.00';
        if($member_info['account'] == ''){
            $member_info['account_style'] = 0;
        }else{
            $member_info['account_style'] = 1;
        }
        if($member_info['password'] == ''){
            $member_info['password_style'] = 0;
        }else{
            $member_info['password_style'] = 1;
        }
        //获取未读消息的条数
        $un_read_num = $this->getUnReadMessageNum($member_info['m_id']);
        $member_info['un_read_num'] = ''.$un_read_num;

        apiResponse('success','操作成功',$member_info);

    }
    /**
     * @param array $request
     * 修改密码
     * 用户id：m_id
     * 旧密码：old_password
     * 新密码：new_password
     * 第二次密码：sec_password
     */
    public function modifyPassword($request = array()){
        //用户id不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //原密码不能为空
        if(empty($request['old_password'])){
            apiResponse('error','请输入原密码');
        }
        //新密码不能为空
        if(empty($request['new_password'])){
            apiResponse('error','请输入新密码');
        }
        //第二次密码不能为空
        if(empty($request['sec_password'])){
            apiResponse('error','请再次输入新密码');
        }
        //对密码进行验证
        $new_password = strlen($request['new_password']);
        if($new_password<6){
            apiResponse('error','密码长度不能小于6位');
        }

        if($request['new_password'] != $request['sec_password']){
            apiResponse('error','两次密码输入不一致');
        }

        //查询原密码是否正确并修改新密码
        $where['id'] = $request['m_id'];
        $where['password'] = md5($request['old_password']);
        $res = M('Member')->where($where)->count();
        if($res<=0){
            apiResponse('error','原密码错误');
        }
        $res = M('Member')->where(array('id'=>$request['m_id']))->data(array('password'=>md5($request['new_password']),'update_time'=>time()))->save();
        if($res){
            apiResponse('success','修改密码成功');
        }else{
            apiResponse('error','修改密码失败');
        }
    }
    /**
     * @param $verify
     * @param $type
     * @param $language
     * 检查验证码是否正确
     */
    public function _checkVerify($account,$verify,$type){
        $where['way']  = $account;
        $where['vc']   = $verify;
        $where['type'] = $type;
        //检查验证码是否错误
        $sms_info = M('Sms')->where($where)->find();

        if(empty($sms_info)){
            apiResponse('error','验证码错误');
        }

        //检查验证码是否过期
        if($sms_info['expire_time']<time()){
            apiResponse('error','验证码已过期');
        }
    }
    /**
     * 三方绑定手机号//绑定手机号第二步
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 手机账号  account
     * 验证码    verify
     */
    public function threeAccount($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //手机账号不能为空
        if(!$request['account']){
            apiResponse('error','请输入手机号');
        }
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //验证码不能为空
        if(!$request['verify']){
            apiResponse('error','请输入验证码');
        }
        //对验证码进行验证
        $this ->_checkVerify($request['account'],$request['verify'],'bind');

        $where['account'] = $request['account'];
        $where['status']  = array('neq',9);
        $account = M('Member') ->where($where) ->find();
        if($account){
            apiResponse('error','该手机号已被注册');
        }
        //绑定新的手机号
        unset($where);
        $where['id'] = $request['m_id'];
        $data['account'] = $request['account'];
        $data['update_time'] = time();
        $member = M('Member') ->where($where) ->data($data) ->save();
        if(!$member){
            apiResponse('error','绑定手机号失败');
        }
        apiResponse('success','绑定手机号成功');
    }
    /**
     * 三方设置登录密码
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID    m_id
     * 用户密码  password
     * 第二次密码  sec_password
     */
    public function threePassword($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //用户密码不能为空
        if(!$request['password']){
            apiResponse('error','请输入密码');
        }
        //第二次密码不能为空
        if(!$request['sec_password']){
            apiResponse('error','请再次输入密码');
        }
        //对两次密码进行操作
        $password = strlen($request['password']);
        if($password < 6){
            apiResponse('error','密码不能少于6位');
        }
        if($request['password'] != $request['sec_password']){
            apiResponse('error','两次密码输入不一致');
        }
        //对三方账号设置密码
        $where['id'] = $request['m_id'];
        $data['password'] = md5($request['password']);
        $data['update_time'] = time();
        $result = M('Member') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('error','设置密码失败');
        }
        apiResponse('success','设置密码成功');
    }
    /**
     * 修改绑定手机号第一步
     * 用户ID      m_id
     * 手机号码    account
     * 验证码      verify
     */
    public function modifyAccountOne($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //手机号码不能为空
        if(!$request['account']){
            apiResponse('error','请填写手机号码');
        }
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //验证码不能为空
        if(!$request['verify']){
            apiResponse('error','请填写验证码');
        }
        //检查验证码是否正确
        $this ->_checkVerify($request['account'],$request['verify'],'bind');
        $where['id']      = $request['m_id'];
        $where['account'] = $request['account'];
        $result = M('Member') ->where($where) ->find();
        if(!$result){
            apiResponse('error','手机账号有误');
        }
        apiResponse('success','操作成功');
    }
    /**
     * @param array $request
     * 个人资料页
     * 用户ID      m_id
     */
    public function userBaseData($request = array()){
        //用户id不能为空
        if(empty($request['m_id'])){
            apiResponse('error','用户id不能为空');
        }
        //查询用户信息
        $where['id'] = $request['m_id'];
        $member_info = M('Member') ->where($where) ->field('id as m_id,head_pic,nickname,age') ->find();
        if(empty($member_info)){
            apiResponse('error','用户信息不存在');
        }

        $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
        $member_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';

        $result_data = array();
        $result_data['m_id']      = $member_info['m_id'];
        $result_data['head_pic']  = $member_info['head_pic'];
        $result_data['nickname']  = $member_info['nickname']?$member_info['nickname']:'';
        $result_data['age']       = $member_info['age']?$member_info['age']:'';

        //获取未读消息的条数
        $un_read_num = $this->getUnReadMessageNum($member_info['m_id']);
        $result_data['un_read_num'] = ''.$un_read_num;
        apiResponse('success','操作成功',$result_data);
    }
    /**
     * 修改个人资料
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     * 头像：head_pic（可以为空）
     * 姓名: nickname（可以为空）
     * 年龄：age(可以为空)
     */
    public function modifyBaseData($request = array()){
        //用户ID不能为空
        if (empty($request['m_id'])) {
            apiResponse('error', '用户id不能为空');
        }
        //上传头像
        if (!empty($_FILES['head_pic']['name'])) {
            $res = api('UploadPic/upload', array(array('save_path' => 'Member')));
            foreach ($res as $value) {
                $head_pic = $value['id'];
                $path = $value['path'];
                $data['head_pic'] = $head_pic;
            }
        }
        //用户昵称可以为空
        if (!empty($request['nickname'])) {
            $data['nickname'] = $request['nickname'];
        }
        //用户年龄可以为空
        if(!empty($request['age'])){
            $data['age'] = $request['age'];
        }

        $where['id'] = $request['m_id'];
        $data['update_time'] = time();
        $res = M('Member')->where($where)->data($data)->save();
        if ($res) {
            $member_info = M('Member')->where(array('id'=>$request['m_id']))->field('id as m_id,head_pic,nickname,age')->find();
            if(empty($member_info)){
                apiResponse('error','修改个人资料失败');
            }

            $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
            $member_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';

            apiResponse('success', '修改个人资料成功',$member_info);
        } else {
            apiResponse('error', '修改个人资料失败');
        }
    }
    /**
     * 好友列表
     * 用户环信json格式：[{"easemob_account":"146649058649749"}]
     */
    public function conversationList($request = array()){
        //用户的环信账号
        if(empty($_POST['easemob_json'])){
            apiResponse('success','');
        }
        //对获取的环信账号进行处理
        $easemob_arr = json_decode($_POST['easemob_json'],true);
        if(!$easemob_arr){
            apiResponse('error','操作有误');
        }
        $result_data = array();
        $index = 0;
        foreach($easemob_arr as $k =>$v){
            unset($where);
            $where['easemob_account'] = $v['easemob_account'];
            $member_info = M('Member')->where($where)->field('id as m_id,head_pic,nickname,easemob_account,easemob_password')->find();
            if($member_info){
                $result_data[$index]['easemob_account'] = $v['easemob_account'];
                $result_data[$index]['nickname'] = $member_info['nickname']?$member_info['nickname']:'';
                $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
                $result_data[$index]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
                $index = $index+1;
            }
        }
        apiResponse('success','',$result_data);
    }
}