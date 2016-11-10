<?php
namespace Api\Logic;

/**
 * Class RegisterLogLogic
 * @package Api\Logic
 */
class RegisterLogLogic extends BaseLogic{

//    //调用方法 获取省市信息
//$ip_address = $this->getAddress($request['ip']);
    /**
     * @param array $request
     * 发送验证码
     * 用户手机号   account
     * 验证码类型  activity  注册  bind  绑定  reset  找回
     */
    public function sendVerify($request = array()){
        //手机号码不能为空
        if(empty($request['account'])){
            apiResponse('error','手机号码不能为空');
        }
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //判断发送验证码类型
        if(!in_array($request['type'],array('activity','bind','reset'))){
            apiResponse('error','类型有误');
        }
        if($request['type'] == 'activity') {
            $where['account'] = $request['account'];
            $where['status'] = array('neq', 9);
            $member_info = M('Member')->where($where)->find();
            if ($member_info) {
                apiResponse('error', '该手机号已被注册');
            }
        }
        if($request['type'] == 'reset') {
            $where['account'] = $request['account'];
            $where['status'] = array('neq', 9);
            $member_info = M('Member')->where($where)->find();
            if (!$member_info) {
                apiResponse('error', '该手机号尚未注册，不能执行找回密码功能');
            }
        }
        $result = D('Sms')->sendVerify($request['account'],$request['type']);
        if($result['success']){
            apiResponse('success',$result['success']);
        }else{
            apiResponse('error',$result['error']);
        }
    }

    /**
     * @param array $request
     * 注册
     * 用户手机号    account
     * 账号密码      password
     * 确认密码      sec_password
     * 验证码        verify
     */
    public function register($request = array()){
        //用户账号不能为空
        if(empty($request['account'])){
            apiResponse('error','用户账号不能为空');
        }
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //账号密码不能为空
        if(empty($request['password'])){
            apiResponse('error','账号密码不能为空');
        }
        //确认密码不能为空
        if(empty($request['sec_password'])){
            apiResponse('error','请再次输入密码');
        }
        //验证码不能为空
        if(empty($request['verify'])){
            apiResponse('error','请输入验证码');
        }
        //如果邀请码不为空
        if(!empty($request['invite'])){
            $parent = M('Member') ->where(array('account'=>$request['invite'])) ->find();
            if(!$parent){
                apiResponse('error','邀请码输入有误');
            }
        }
        $password = strlen($request['password']);
        if($password<6){
            apiResponse('error','密码长度不得小于6位');
        }
        if($request['password'] != $request['sec_password']){
            apiResponse('error','两次密码输入不一致');
        }

        //检测该账号是否已经被注册
        $where['account'] = $request['account'];
        $res = M('Member')->where($where)->count();
        if($res>0){
            apiResponse('error','该手机号已被注册');
        }
        //检查验证码输入是否正确
        $this ->_checkVerify($request['account'],$request['verify'],'activity');

        //注册环信
        $easemob_reg_info = $this->easemobRegister();
        if($easemob_reg_info['flag'] == 'error'){
            apiResponse('error','注册失败');
        }

        //后台注册
        $data['account']         = $request['account'];
        $data['password']        = md5($request['password']);
        $data['easemob_account'] = $easemob_reg_info['easemob_account'];
        $data['easemob_password']= $easemob_reg_info['easemob_password'];
        $data['nickname']        = $request['account'];
        $data['invite_code']     = get_vc(8,0);
        $data['create_time']     = time();
        $data['status']          = 1;
        $member_res = M('Member') ->data($data) ->add();
        if(!$member_res){
            apiResponse('error','注册失败');
        }
        $result_data['m_id']     = $member_res;
        $result_data['account']  = $request['account'];
        $result_data['password'] = md5($request['password']);
        $result_data['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
        $result_data['nickname'] = $data['nickname'];
        $result_data['un_read_num'] = '0';
        $result_data['easemob_account'] = ''.$easemob_reg_info['easemob_account'];
        $result_data['easemob_password'] = ''.$easemob_reg_info['easemob_password'];
        $result_data['account_style'] = '1';
        $result_data['password_style'] = '1';
        $result_data['balance']  = '0.00';
        $result_data['grade']    = 'E';
        $result_data['amount']   = '0.00';

        unset($where);
        unset($data);
        if($parent){
            $data['parent_id'] = $parent['id'];
        }else{
            $data['parent_id'] = 0;
        }
        $data['m_id']      = $member_res;
        $data['create_time'] = time();
        $res = M('Relation') ->add($data);

        unset($where);
        unset($data);
        //计算父级ID的B级人数和人员

        if($parent['id']){
            $B_grade = M('Relation') ->where(array('parent_id'=>$parent['id'])) ->select();
            $B_count = M('Relation') ->where(array('parent_id'=>$parent['id'])) ->count();
            $count  = 0;
            $count1 = 0;
            $count2 = 0;
            //计算父级ID的各级别人数
            foreach($B_grade as $k =>$v){
                $D = M('Member') ->where(array('id'=>$v['m_id'],'grade'=>'D')) ->count();
                $C = M('Member') ->where(array('id'=>$v['m_id'],'grade'=>'C')) ->count();
                $B = M('Member') ->where(array('id'=>$v['m_id'],'grade'=>'B')) ->count();
            }
            //计算父级ID的各级别人数
            foreach($B_grade as $k =>$v){
                $C_grade = $this ->dean($v['m_id']);
                $count = $count + $C_grade['count'];
                foreach($C_grade['relation'] as $key => $val){
                    $D_grade = $this ->dean($val['m_id']);
                    $count1 = $count1 + $D_grade['count'];
                    foreach($D_grade['relation'] as $keys => $value){
                        $E_grade = $this ->dean($value['m_id']);
                        $count2 = $count2 + $D_grade['count'];
                    }
                }
            }
            //根据各级别人数更改人员表数据库
            if($B_count >= 1000&& $count >= 10000&& $B >= 500){
                $grade = 'A';
            }elseif($B_count >= 200&& $count >= 1000 && $C >= 125){
                $grade = 'B';
            }elseif($B_count >= 100&& $count >= 400 && $D >= 50){
                $grade = 'C';
            }elseif($B_count >= 10){
                $grade = 'D';
            }else{
                $grade = 'E';
            }
            unset($where);
            unset($data);
            $where['id']   = $parent['id'];
            $data['grade'] = $grade;
            $data['update_time'] = time();
            $result = M('Member') ->where($where) ->data($data) ->save();
        }
        apiResponse('success','注册成功',$result_data);
    }

    /**
     * @param array $request
     * 用户账号登录
     * 账号：account
     * 密码：password
     */
    public function login($request = array()){
        //用户账号不能为空
        if(empty($request['account'])){
            apiResponse('error','账号不能为空');
        }
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //用户密码不能为空
        if(empty($request['password'])){
            apiResponse('error','密码不能为空');
        }
        //用户账号不能为空
        $where['account']  = $request['account'];
        $where['password'] = md5($request['password']);
        //查询用户信息
        $member_info = M('Member')
            ->where($where)
            ->field('id as m_id, account, password, nickname, balance, head_pic, easemob_account, easemob_password, grade')
            ->find();

        if(empty($member_info)){
            apiResponse('error','用户账号或密码错误');
        }
        //操作用户头像
        $path = M('File')->where(array('id'=>$member_info['head_pic']))->getField('path');
        $member_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';

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
        apiResponse('success','登陆成功',$member_info);
    }

    /**
     * 三方登陆
     * 传递参数的方式：post
     * 需要传递的参数：
     * 三方账号    open_id
     * 三方类型    type  1  QQ  2  微信  3  微博
     */
    public function threeLogin($request = array()){
        //三方账号不能为空
        if(!$request['open_id']){
            apiResponse('error','三方登录账号不能为空');
        }
        //三方类型  1  QQ  2  微信  3  微博
        if($request['type']!=1&&$request['type']!=2&&$request['type']!=3){
            apiResponse('error','三方账号类型有误');
        }

        if($request['nickname']){
            $nickname = $request['nickname'];
        }

        //查询三方信息  没注册现注册   注册了直接返回
        $where['open_id'] = $request['open_id'];
        $result = M('Member')
            ->where($where)
            ->field('id as m_id, account, password, nickname, head_pic, easemob_account, easemob_password, balance, grade')
            ->find();
        if($result){
            $path = M('File') ->where(array('id'=>$result['head_pic'])) ->getField('path');
            $result['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';

            //获取结算中金额
            unset($where);
            $where['m_id'] = $result['m_id'];
            $where['status'] = array('IN',array(0,1,2,3));
            $amount = M('Order') ->where($where) ->getField('SUM(totalprice) as totalprice');
            $result['amount'] = $amount?$amount:'0.00';
            if($result['account'] == ''){
                $result['account_style'] = 0;
            }else{
                $result['account_style'] = 1;
            }
            if($result['password'] == ''){
                $result['password_style'] = 0;
            }else{
                $result['password_style'] = 1;
            }
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($result['m_id']);
            $result['un_read_num'] = ''.$un_read_num;
            apiResponse('success','登陆成功',$result);
        }else{
            //注册环信
            $easemob_reg_info = $this->easemobRegister();
            if($easemob_reg_info['flag'] == 'error'){
                apiResponse('error','注册失败');
            }
            $data['open_id'] = $request['open_id'];
            $data['type']    = $request['type'];
            $data['nickname'] = $nickname?$nickname:get_vc(11,0);
            if($_FILES['head_pic']['name']){
                $res= api('UploadPic/upload',array(array('save_path'=>'Member')));
                $head_pic = '';
                foreach($res as $value){
                    $head_pic = $value['id'];
                    $path = $value['path'];
                }
            }
            $data['head_pic'] = $head_pic?$head_pic:'0';
            $data['easemob_account']  = $easemob_reg_info['easemob_account'];
            $data['easemob_password'] = $easemob_reg_info['easemob_password'];
            $data['create_time']      = time();
            $data['invite_code']      = get_vc(8,0);
            $res = M('Member') ->add($data);
            if(!$res){
                apiResponse('error','三方登陆失败');
            }
            $result['m_id']             = $res;
            $result['account']          = '';
            $result['password']         = '';
            $result['easemob_account']  = $data['easemob_account'];
            $result['easemob_password'] = $data['easemob_password'];
            if($head_pic){
                $path = M('File') ->where(array('id'=>$data['head_pic'])) ->getField('path');
                $result['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
            }else{
                $result['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
            }
            $result['nickname']         = $data['nickname'];
            $result['balance']          = '0.00';
            //返回是否设置了账号和密码
            if($result['account']){
                $result['account_type'] = '1';
            }else{
                $result['account_type'] = '0';
            }
            if($result['password']){
                $result['password_style'] = '1';
            }else{
                $result['password_style'] = '0';
            }

            $result['grade']            = 'E';
            $result['amount']           = '0.00';
            //获取未读消息的条数
            $un_read_num = $this->getUnReadMessageNum($result['m_id']);
            $result['un_read_num'] = ''.$un_read_num;

            unset($where);
            unset($data);
            $data['parent_id'] = 0;
            $data['m_id']      = $res;
            $data['create_time'] = time();
            $res_data = M('Relation') ->add($data);
            if(!$res_data){
                apiResponse('error','三方登录失败');
            }
            apiResponse('success','三方登陆成功',$result);
        }
    }

    /**
     * @param array $request
     *  忘记密码接口
     * 邮箱账号：account
     * 验证码：verify
     * 新密码：new_password
     * 第二个密码：sec_password
     */
    public function resetPassword($request = array()){
        //手机账号不能为空
        if(empty($request['account'])){
            apiResponse('error','手机账号不能为空');
        }
        if(!preg_match(C('MOBILE'),$request['account'])) {
            apiResponse('error', '手机号码格式错误');
        }
        //验证码不能为空
        if(empty($request['verify'])){
            apiResponse('error','验证码不能为空');
        }
        //新密码不能为空
        if(empty($request['new_password'])){
            apiResponse('error','请输入密码');
        }
        //再次输入密码
        if(empty($request['sec_password'])){
            apiResponse('error','请再次输入密码');
        }
        //密码不能小于6位
        $new_password = strlen($request['new_password']);
        if($new_password<6){
            apiResponse('error','密码长度不能小于6位');
        }

        if($request['new_password'] != $request['sec_password']){
            apiResponse('error','两次密码输入不一致');
        }

        //检查账号是否正确
        $res_count = M('Member')->where(array('account'=>$request['account'],'status'=>array('neq',9)))->count();
        if($res_count <= 0 ){
            apiResponse('error','用户信息不存在');
        }

        //检测验证码
        $this->_checkVerify($request['account'],$request['verify'],'reset');

        $where['account'] = $request['account'];
        $data['password']    = md5($request['new_password']);
        $data['update_time'] = time();
        $res = M('Member') -> where($where) ->data($data)->save();
        if($res){
            apiResponse('success','找回密码成功');
        }else{
            apiResponse('error',' 找回密码失败');
        }
    }

    /**
     * @param $verify
     * @param $type
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


    public function dean($m_id){
        $result['relation'] = M('Relation')->where(array('parent_id'=>$m_id)) ->select();
        $result['count']    = M('Relation')->where(array('parent_id'=>$m_id)) ->count();
        return  $result;
    }
}