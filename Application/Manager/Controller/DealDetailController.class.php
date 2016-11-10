<?php

namespace Manager\Controller;

/**
 * 交易明细
 */
class DealDetailController extends BaseController
{

    /**
     * 频道列表页
     */
    function index()
    {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->getList(I('request.'));
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
            $this->assign('deposit', $result['deposit']); /*存入*/
            $this->assign('expend', $result['expend']); /*支出*/
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('index');
    }


    /**
     * [exportExcel 导出EXCEL]
     *
     * @return [type] [description]
     */
    function exportExcel()
    {
        vendor("PHPExcel.PHPExcel");//如果这里提示类不存在，肯定是你文件夹名字不对。
        $objPHPExcel = new \PHPExcel();//这里要注意‘\’ 要有这个。因为版本是3.2了。
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);//设置保存版本格式
        $list = M('pay_log')->where(array('id' => array('in', $_GET['ids'])))->order('type,create_time DESC')->select();
        $objPHPExcel->getActiveSheet()->setCellValue('A1', '操作账号');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', '用户类型');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', '操作用户');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', '日志标题');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', '日志内容');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', '操作金额');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', '生成时间');

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);

        foreach ($list as $key => $value) {
            $i = $key + 2;//表格是从1开始的
            if ($value['type'] == 1) {
                $account = M('Member')->where(array('id' => $value['object_id']))->field('account,nickname')->find();
            } else if ($value['type'] == 2) {
                $account = M('Merchant')->where(array('id' => $value['object_id']))->field('account,merchant_name as nickname')->find();
            }
            $symbol = $value['symbol'] == 1 ? '+' : '-';
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $account['account']); // 操作账号
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $value['type'] == 1 ? '用户' : '商家'); // 提现类型
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $account['nickname']); // 操作用户
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $value['title']); // 标题
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $value['content']); // 内容
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $i, $symbol.$value['money'].''); //银行类型
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $i, date('Y-m-d H:i:s',$value['create_time'])); // 银行卡号
            //以此类推，可以设置C D E F G看你需要了。
        }

        $objPHPExcel->getProperties()->setCreator("淘米公社");
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename=收支记录数据表' . date('YmdHis') . '.xls');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

}
