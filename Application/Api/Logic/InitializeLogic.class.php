<?php
namespace Api\Logic;

/**
 * Class InitializeLogic
 * @package Api\Logic
 * APP启动基本配置
 */
class InitializeLogic extends BaseLogic{

    /**
     * APP启动页
     */
    public function appStart(){

        $config = $this->getConfig();

        $open_page_pic = M('OpenPage')->where(array('id'=>1))->getField('picture');
        if($open_page_pic){
            $path = M('File')->where(array('id'=>$open_page_pic))->getField('path');
        }else{
            $path = '';
        }
        $result_data = array();
        $result_data['android_version'] = ''.$config['ANDROID_VERSION'];
        $result_data['android_link'] = ''.$config['ANDROID_LINK'];
//        $result_data['ios_status'] = ''.$config['IOS_STATUS'];
        $result_data['picture'] = $path?C('API_URL').$path:'';
        
        apiResponse('success','请求成功',$result_data);
    }

    /**
     * 商家端下载判断
     */
    public function merDownload()
    {
        $config = $this->getConfig();
        $result_data = array();
        $result_data['android_mer_version'] = ''.$config['ANDROID_MER_VERSION'];
        $result_data['android_mer_link'] = ''.C('API_URL').$config['ANDROID_MER_LINK'];
        apiResponse('success','请求成功',$result_data);

    }
}