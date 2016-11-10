<?php
namespace Common\Api;

/**
 * @author zhouwei
 * Class TreeApi
 * @package Common\Api
 * 文件上传、下载服务
 */
class UpPicApi {
    /**
     * @var string
     * 接收错误信息
     */
    protected static $apiError = '';

    /**
     * @var string
     * 接收成功信息
     */
    protected static $apiSuccess = '';

    /**
     * @param array $request
     * @return array
     * 上传图片
     */
    public static function upload($request = array()) {
        //TODO: 用户登录检测
        //返回标准数据
        $response  = array('status' => 1, 'info' => '上传成功', 'data' => '', 'url' => '');

        //判断上传函数
        $type   = strtoupper('picture');

        //上传驱动类型
        $driver = C(''.$type.'_UPLOAD_DRIVER');

        //执行上传 返回上传信息
        $info = self::_doUpload(
            $_FILES,                            //所有文件
            C(''.$type.'_UPLOAD'),              //上传配置信息
            C(''.$type.'_UPLOAD_DRIVER'),       //上传驱动类型
            C("UPLOAD_{$driver}_CONFIG"),        //上传驱动配置
            $request
        ); //TODO:上传到远程服务器

        ///判断成功还是失败
        if(is_array($info)) {
            foreach ($info as $key => &$value) {
                //处理图片路径 在模板里的url路径
                $value['path'] = substr(C('PICTURE_UPLOAD.rootPath'), 1).$value['savepath'].$value['savename'];
                //绝对路径
                $value['abs_url'] = empty($_SERVER['REQUEST_SCHEME']) ? 'http' . '://' . $_SERVER['HTTP_HOST'] . $value['path'] : $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $value['path'];

                //已经存在文件记录
                if(isset($value['id']) && is_numeric($value['id'])) {
                    //文件下载路径
                    $value['download_url'] = U('UpDownLoad/download',array('id'=>$value['id']));
                    continue;
                }
                //文件类型
                $value['mime'] = $value['type'];
                //创建时间
                $value['create_time'] = time();
                //记录文件信息
                if($id = M('File')->add($value)) {
                    $value['id'] = $id;
                    //文件下载路径
                    $value['download_url'] = U('UpDownLoad/download',array('id'=>$value['id']));
                } else {
                    //TODO: 文件上传成功，但是记录文件信息失败，需记录日志
                    unset($info[$key]);
                }
            }

            //合并上传信息和标准信息
//            $response           = array_merge($info['fileData'], $response);
            $response           = $info;
        } else {
            $response['status'] = 0;
            //错误信息
            $response['info']   = $info;
        }

        return $response;
    }

    /**
     * 文件上传
     * @param  array  $files   要上传的文件列表（通常是$_FILES数组）
     * @param  array  $setting 文件上传配置
     * @param  string $driver  上传驱动名称
     * @param  array  $config  上传驱动配置
     * @param  array  $request  其他信息
     * @return array           文件上传成功后的信息
     */
    private static function _doUpload($files, $setting, $driver = 'Local', $config = null, $request = array()) {

        //回调函数 判断文件数据库记录是否存在 如果存在，判断文件是否存在 如果存在直接返回数据库记录 不存在就上传 返回上传信息
        //$setting['callback'] = array(new \Common\Api\UpDownLoadApi(), 'isFile');
        //删除垃圾文件记录 本地文件不存在数据库记录存在
        //$setting['removeTrash'] = array(new \Common\Api\UpDownLoadApi(), 'removeTrash');
        //上传配置信息
        $setting = self::_returnSetting($request, $setting);
        //初始化上传类
        $Upload = new \Think\Upload($setting, $driver, $config);
        //执行上传
        $info   = $Upload->upload($files);
        //判断是否上传成功
        if($info) {
            //文件上传成功，返回文件信息
            // "name"   :   "2014-08-12_162223.png",
            // "type"   :   "application/octet-stream",
            // "size"   :   64483,
            // "key"    :   "download",
            // "ext"    :   "png",
            // "md5"    :   "dc113e5f7a34ee0f4410877cd9132522",
            // "sha1"   :   "f06bb91a1edf648770efb1e54c7e9d11e3a83a0b",
            // "savename"   :   "55f66b9a82544.png",
            // "savepath"   :   "2015-09-14/",
            return $info;
        } else {
            return $Upload->getError();
        }
    }

    /**
     * @param $request
     * @param $setting
     * 配置信息 处理每个上传不同的配置信息
     */
    private function _returnSetting($request, $setting) {
        //保存路径
        !empty($request['save_path'])   ? $setting['savePath']  = $request['save_path'].'/' : '';
        //后缀名
        !empty($request['exts'])        ? $setting['exts']      = $request['exts']          : '';
        //文件大小
        !empty($request['max_size'])    ? $setting['maxSize']   = $request['max_size']      : '';

        return $setting;
    }


    /**
     * @param $file 文件信息
     * @return boolean  文件信息， false - 不存在该文件
     * @throws \Exception
     * 检测当前上传的文件是否存在记录
     */
    public static function isFile($file) {
        if(empty($file['md5'])) {
            throw new \Exception('缺少参数:md5');
        }
        //查找文件
        $where = array('md5' => $file['md5'],'sha1'=>$file['sha1']);
        return M('File')->where($where)->find();
    }

    /**
     * 清除数据库存在但本地不存在的数据
     * @param $data
     */
    public static function removeTrash($data) {
        M('File')->where(array('id'=>$data['id']))->delete();
    }

    /**
     * @param string $error
     * @return string
     * 设置错误信息
     */
    final protected static function setApiError($error = '') {
        self::$apiError = $error;
    }

    /**
     * @param string $success
     * @return string
     * 设置成功信息
     */
    final protected static function setApiSuccess($success = '') {
        self::$apiSuccess = $success;
    }

    /**
     * @return string
     * 获取错误信息
     */
    final public static function getApiError() {
        return self::$apiError;
    }

    /**
     * @return string
     * 获取成功提示信息
     */
    final public static function getApiSuccess() {
        return self::$apiSuccess;
    }
}