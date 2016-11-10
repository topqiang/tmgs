<?php
namespace Common\Service;

/**
 * Class FileService
 * @package Common\Service
 * 文件表 数据服务层
 */
class ExcelService {

    /**
     * 导入数据
     */
    public function importMember(){
        if(empty($_FILES['excel']['name'])){
            $this->display('importMember');
        }else{
            //判断文件类型 是不是xls
            if($_FILES['excel']['type'] != 'application/vnd.ms-excel'){
                $this->error('该文件类型错误，请确认是xls文件再导入',U('Member/importMember'));
            }
            //上传文件
            $res = uploadFile('','Excel','',array('xls'));
            //上传成功
            if(empty($res['error'])){
                //字段名称数组
                $field_arr = array( 1=>'m_account',
                    2=>'m_password',
                    3=>'m_nickname',
                    4=>'m_sex',
                    5=>'m_email',
                    6=>'m_wchart',
                    7=>'m_mobile',
                    8=>''
                );
                //文件路径
                $file = "./Uploads/Excel/".$res['success'];
                //获取可添加数据
                $data = D('Util','Service')->doImport($file,$field_arr);
                //默认数据
                foreach($data as $key => $value){
                    $data[$key]['m_head']     = 'default.jpg';                 //默认头像
                    $data[$key]['ctime']      = time();                        //添加时间
                    $data[$key]['m_type']     = 1;                             //会员类型
                    $data[$key]['m_password'] = MD5($value['m_password']);     //密码加密
                }
                //存入数据库
                $res = $this->member_obj->addMemberAll($data);
                if($res){
                    $this->success('批量导入成功',U('Member/memberList'));
                }else{
                    $this->error('导入失败',U('Member/importMember'));
                }
            }else{
                //上传失败
                $this->error($res['error'],U('Member/importMember'));
            }
        }
    }

    /**
     * @param $file
     * @param $field_arr
     * @return null
     */
    public function doImport($file,$field_arr) {
        //utf-8字符编码转换
        header ( "Content-Type:text/html; charset=utf-8" );
        vendor('ExcelReader.class#Spreadsheet_Excel_Reader');
        //初始化转换方法 xls文件转换为数组
        $excel = new \Vendor\ExcelReader\Spreadsheet_Excel_Reader($file);
        //输出表格内容到html页面
        //echo $excel->dump(true,true);exit;
        //设置文本输出编码
        $excel->setOutputEncoding('UTF-8');
        //获取表格内容数组
        $excels = $excel->sheets[0];

        $numRows = $excels['numRows'];//行数
        $numCols = $excels['numCols'];//列数

        //文件行数大于一 即存在数据
        if($numRows > 1) {
            //创建可存入数据库的data数组
            //循环行数添加到data中几行数据
            for($i = 2; $i <= $numRows; $i++) {
                //循环列数为每行数据的字段赋值
                for($j = 1; $j <= $numCols; $j++) {
                    //判断数组该键值下是否存在值
                    if(!empty($excels['cells'][$i][$j])) {
                        //存在值添加到data数组
                        $data[$i-2][$field_arr[$j]] = $excels['cells'][$i][$j];
                    } else {
                        //不存在值
                        $data[$i-2][$field_arr[$j]] = '';
                    }
                }
            }
        } else {
            $data = null;
        }
        //返回data数组
        return $data;
    }
}