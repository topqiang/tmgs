<?php
namespace Api\Logic;

/**
 * Class ArticleLogic
 * @package Api\Logic
 */
class ArticleLogic extends BaseLogic{

    /**
     * @param array $request
     * 用户协议
     */
    public function userAgreement($request = array()){

        $article_info = M('Article')->where(array('id'=>2))->find();

        //用户协议处理处理
        preg_match_all('/src=\"\/?(.*?)\"/',$article_info['cn_content'],$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $article_info['cn_content'] = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$article_info['cn_content']);
            }
        }
        if($article_info){
            $result_data['content'] = $article_info['cn_content'];
        }else{
            $result_data['content'] = '';
        }
        apiResponse('success','',$result_data);
    }


    /**
     * @param array $request
     * 帮助中心列表
     * 语言：language ue英文，cn中文
     * 用户id：m_id
     * type：1关于买家，2关于卖家
     */
    public function helpList($request = array()){

        if(!in_array($request['type'],array('1','2'))){
            apiResponse('error','参数有误');
        }

        //获取未读消息的条数
        if($request['m_id']){
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        }else{
            $un_read_num = 0;
        }
        $result_data['un_read_num'] = ''.$un_read_num;

        $where['type'] = $request['type'];
        $where['status'] = array('neq',9);

        $list = M('Help')->where($where)->field('id as help_id,cn_question as question')->order('sort desc')->select();

        if($list){
            $result_data['list'] = $list;
        }else{
            $result_data['list'] = array();
        }
        apiResponse('success','',$result_data);
    }

    /**
     * @param array $request
     * 帮助中心详情
     */
    public function helpInfo($request = array()){

        if(empty($request['help_id'])){
            apiResponse('error','参数不完整');
        }

        //获取未读消息的条数
        if($request['m_id']){
            $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        }else{
            $un_read_num = 0;
        }
        $result_data['un_read_num'] = ''.$un_read_num;

        $where['id'] = $request['help_id'];

        $info = M('Help')->where($where)->field('cn_question as question,cn_answer as answer')->find();

        preg_match_all('/src=\"\/?(.*?)\"/',$info['answer'],$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $info['answer'] = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$info['answer']);
            }
        }
        $result_data['info'] = $info;

        apiResponse('success','',$result_data);
    }

    /**
     * @param array $request
     * 设置页面
     */
    public function setPage($request = array()){

        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }

        $un_read_num = $this->getUnReadMessageNum($request['m_id']);

        $result_data['un_read_num'] = ''.$un_read_num;

        apiResponse('success','',$result_data);
    }

    /**
     * @param array $request
     * 关于我们
     */
    public function aboutUs($request = array()){

        if(empty($request['m_id'])){
            apiResponse('error','用户ID不能为空');
        }

        $un_read_num = $this->getUnReadMessageNum($request['m_id']);

        $result_data['un_read_num'] = ''.$un_read_num;

        $config = $this->getConfig();
        $result_data['company_name'] = $config['CN_COMPANY_NAME'];

        $result_data['copyright']    = $config['COPYRIGHT'];

        apiResponse('success','',$result_data);
    }
    /**
     * 等级特权
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function rankPrivilege($request = array()){
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        $article_info = M('Article')->where(array('id'=>11))->find();
        if($article_info){
            $result_data['content'] = $article_info['cn_content'];
        }else{
            $result_data['content'] = '';
        }
        preg_match_all('/src=\"\/?(.*?)\"/',$result_data['content'],$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $result_data['content'] = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$result_data['content']);
            }
        }
        apiResponse('success','',$result_data);
    }
    /**
     * 升级规则
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function exlimitRules($request = array()){
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        $article_info = M('Article')->where(array('id'=>12))->find();
        if($article_info){
            $result_data['content'] = $article_info['cn_content'];
        }else{
            $result_data['content'] = '';
        }
        preg_match_all('/src=\"\/?(.*?)\"/',$result_data['content'],$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $result_data['content'] = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$result_data['content']);
            }
        }
        apiResponse('success','',$result_data);
    }
    /**
     * 联系我们
     * 传递参数的方式：post
     * 需要传递的参数：
     * 语言版本：language：cn 中文  ue英文
     * 用户id：m_id
     * 未取消息条数： $un_read_num
     * 客服账号：$service['account']
     * 客服头像： $service['head_pic']
     * 客服电话：$config['SERVICE_LINE']
     */
    public function onlineService($request = array()){
        /*搜索用户m_id*/
        if (empty($request['m_id'])) {
            apiResponse('error',  '用户id不能为空');
        }
        //获取未读消息的条数
        $un_read_num = $this->getUnReadMessageNum($request['m_id']);
        $result['un_read_num'] = ''.$un_read_num;
        /*取客服的账号*/
        $service['account']  = '8001';
        $service['head_pic'] = C('API_URL').'/Uploads/Member/service.png';
        $result['head_pic']  =  $service['head_pic'];
        $config = D('Config')->parseList();
        $result['service_line'] = $config['SERVICE_LINE'];
        $result['service_account'] = '8001';
        apiResponse('success','操作成功',$result);
    }
    /**
     * 商户协议
     * 传递参数的方式：post
     * 需要传递的参数：
     */
    public function merchantRules(){
        $article_info = M('Article') -> where(array('id'=>15)) ->find();
        if($article_info){
            $result_data['content'] = $article_info['cn_content'];
        }else{
            $result_data['content'] = '';
        }
        preg_match_all('/src=\"\/?(.*?)\"/',$result_data['content'],$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $result_data['content'] = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$result_data['content']);
            }
        }
        apiResponse('success','',$result_data);
    }
    /**
     * 保证金问题
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function securityRules(){
        $article_info = M('Article')->where(array('id'=>14))->find();
        if($article_info){
            $result_data['content'] = $article_info['cn_content'];
        }else{
            $result_data['content'] = '';
        }
        preg_match_all('/src=\"\/?(.*?)\"/',$result_data['content'],$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $result_data['content'] = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$result_data['content']);
            }
        }
        apiResponse('success','',$result_data);
    }
}