<?php
namespace Common\Api;

/**
 * Class FileService
 * @package Common\Service
 * excel表格操作  读取、导出
 */
class ExcelApi {

    /**
     * 导出EXCEL表格
     * @param array $fields_data  $fields_data数据格式 表头汉字||数据库字段名称
     * @param array $list  要导出的数据列表
     * @param string $name  表格文件导出名称
     */
    public static function exportToExcel($fields_data = array(), $list = array(), $name = 'Export') {
        if(empty($list)) {
            return false;
        }
        //头部字段汉字解释
        $heads = array();
        //导出内容字段数据库名称
        $fields = array();
        //拆分字段数据  分别为头部和字段名称列表赋值
        foreach($fields_data as $value) {
            $arr = explode('||',$value);
            $heads[]    = $arr[0];
            $fields[]   = $arr[1];
        }
        //存储导出表格的数据
        $data = array();
        //标题赋值
        $data[1] = $heads;
        //数据赋值
        foreach($list as $value) {
            //数据内容  循环字段
            $tmp = array();
            //每列赋值
            foreach($fields as $field) {
                $tmp[] = $value[$field];
            }
            $data[] = $tmp;
        }
        //导出
        $xls = new \Think\Excel_XML('UTF-8', false, $name);
        $xls->addArray($data);
        $xls->generateXML($name);
    }


    /**
     * @param int $file_id  导入文件记录ID
     * @return array
     * 读取excel文件 生成数组
     * 导入EXCEL文件格式  第一行：给用户的一些提示语（例如：请严格按照格式填写等...）
     *                  第二行：该列对应的数据库字段名称 （用户不可更改）
     *                  第三行：该列对应的数据库字段名称汉字解释 （枚举类型 提供给用户可选值，或者其他一些提示）
     *                  第四行：将导入的数据
     */
    public static function readExcelToData($file_id = 0) {
        //判断是否传入了文件ID
        if(empty($file_id)) {
            return false;
        }
        //查询文件信息
        $file = M('File')->where(array('id'=>$file_id))->find();
        //未查到文件信息
        if(!$file) {
            return false;
        }
        //生成文件本地路径
        $file_url = C('ATTACHMENT_UPLOAD.rootPath').$file['savepath'].$file['savename'];
        //utf-8字符编码转换
        header ('Content-Type:text/html; charset=utf-8');
        vendor('ExcelReader.class#Spreadsheet_Excel_Reader');
        //初始化转换方法 xls文件转换为数组  该类还可以读取表格大部分信息 例如：字体颜色、背景色等 如需使用 自行研究
        $ExcelReader = new \Vendor\ExcelReader\Spreadsheet_Excel_Reader($file_url);
        //输出表格内容到html页面
        //echo $excel->dump(true,true);exit;
        //设置文本输出编码
        $ExcelReader->setOutputEncoding('UTF-8');
        //获取表格内容数组
        $excels = $ExcelReader->sheets[0];
        //总行数  记录的是存在内容的行
        $numRows = $excels['numRows'];
        //总列数  记录的是存在内容的列
        $numCols = $excels['numCols'];
        //数据库字段名称在第二行
        $fields = $excels['cells'][2];
        //从文件行数大于3开始 即第四行开始为导入数据
        if($numRows > 3) {
            //创建可存入数据库的data数组
            //循环行数添加到data中几行数据  第四行开始
            for($i = 4; $i <= $numRows; $i++) {
                //TODO 是否加入验证行数最大值
                //循环列数为每行数据的字段赋值
                for($j = 1; $j <= $numCols; $j++) {
                    //该列是否正确存在字段名称  不存在直接返回失败 不然插入时也是失败
                    if(empty($fields[$j])) {
                        return false;
                    }
                    //判断数组该键值下是否存在值
                    $data[$i][$fields[$j]] = empty($excels['cells'][$i][$j]) ? '' : $excels['cells'][$i][$j];
                }
            }
        } else {
            $data = array();
        }
        //返回data数组
        return array_merge($data);
    }
}