<?php

namespace Manager\Controller;
/**
 * @author zhouwei
 * Class RechargeController
 * @package Manager\Controller
 * 财务管理-充值记录-查询
 */
class RechargeController extends BaseController
{
	function getIndexRelation(){
	}

	/**
	 * [exportExcel 导出EXCEL]
	 *
	 * @return [type] [description]
	 */
 	function exportExcel(){
        vendor("PHPExcel.PHPExcel");//如果这里提示类不存在，肯定是你文件夹名字不对。
        $objPHPExcel = new \PHPExcel();//这里要注意‘\’ 要有这个。因为版本是3.2了。
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);//设置保存版本格式
        $list =M('recharge') -> where(array('id'=>array('in',$_GET['ids']))) -> order('create_time DESC')->select();
		$objPHPExcel->getActiveSheet()->setCellValue('A1','订单号'  );
		$objPHPExcel->getActiveSheet()->setCellValue('B1','用户昵称');
		$objPHPExcel->getActiveSheet()->setCellValue('C1','充值方式');
		$objPHPExcel->getActiveSheet()->setCellValue('D1','充值金额');
		$objPHPExcel->getActiveSheet()->setCellValue('E1','创建时间');
		$objPHPExcel->getActiveSheet()->setCellValue('F1','充值状态');
        foreach ($list as $key => $value) {
          $i=$key+2;//表格是从1开始的
          $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$value['order_sn'].' ');
          $objPHPExcel->getActiveSheet()->setCellValue('B'.$i, M('Member')->where(array('id'=>$value['m_id']))->getField('nickname'));
          $objPHPExcel->getActiveSheet()->setCellValue('C'.$i, rechargeType($value['recharge']));
          $objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $value['money']);
          $objPHPExcel->getActiveSheet()->setCellValue('E'.$i, date('Y-m-d H:i:s',$value['create_time']));
          $objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $value['status'] == 1 ? '充值成功' : '充值失败');
          //以此类推，可以设置C D E F G看你需要了。
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
		$objPHPExcel->getProperties()->setCreator("淘米公社");
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");;
		header('Content-Disposition:attachment;filename=充值记录数据表'.date('YmdHis').'.xls');
		header("Content-Transfer-Encoding:binary");
		// $objWriter->save('充值记录数据表'.date('YmdHis').'.xls');
		$objWriter->save('php://output');
    }
}