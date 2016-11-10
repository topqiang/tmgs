<?php

namespace Manager\Controller;

/**
 * @author zhouwei
 * Class WithdrawController
 * @package Manager\Controller
 * 财务管理 - 提现管理 - 控制器
 */
class WithdrawController extends BaseController{

    /**
     * 频道列表页
     */
    function notIndex() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->getNotList(I('request.'));
        if($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('notIndex');
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
        $list =M('Withdraw') -> where(array('id'=>array('in',$_GET['ids']))) -> order('create_time DESC')->select();
		$objPHPExcel->getActiveSheet()->setCellValue('A1','提现账号'  );
		$objPHPExcel->getActiveSheet()->setCellValue('B1','提现类型'  );
		$objPHPExcel->getActiveSheet()->setCellValue('C1','持卡人姓名'  );
		$objPHPExcel->getActiveSheet()->setCellValue('D1','持卡人手机号');
		$objPHPExcel->getActiveSheet()->setCellValue('E1','持卡人身份证号'  );
		$objPHPExcel->getActiveSheet()->setCellValue('F1','银行类型'  );
		$objPHPExcel->getActiveSheet()->setCellValue('G1','银行卡号'  );
		$objPHPExcel->getActiveSheet()->setCellValue('H1','提现金额'  );
		$objPHPExcel->getActiveSheet()->setCellValue('I1','提现日期'  );
		$objPHPExcel->getActiveSheet()->setCellValue('J1','提现状态'  );

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(12);

        foreach ($list as $key => $value) {
          $i=$key+2;//表格是从1开始的
          if($value['type'] == 1){
          	$account = M('Member') -> where(array('id'=>$value['object_id']))->getField('account');
          }else if($value['type'] == 2){
          	$account = M('Merchant') -> where(array('id'=>$value['object_id']))->getField('account');
          }
          $m_c_id = M('member_card') -> where(array('id'=>$value['m_c_id'])) -> find();
          $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$account); // 提现账号
          $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$value['type'] == 1 ? '用户' : '商家'); // 提现类型
          $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$m_c_id['name']); // 开户姓名
          $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$m_c_id['phone']); // 持卡人手机号
          $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$m_c_id['id_card'].' '); // 持卡人身份证号
          $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,M('support_bank')->where(array('id'=>$m_c_id['bank_id']))->getField('bank_name')); //银行类型
          $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$m_c_id['card_number'].' '); // 银行卡号
          $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$value['money']); // 提现金额
          $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,date('Y-m-d H:i:s',$value['create_time']));// 提现日期
          $objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $value['status'] == 1 ? '已打款' : '未打款'); // 提现状态
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
		header('Content-Disposition:attachment;filename=提现记录数据表'.date('YmdHis').'.xls');
		header("Content-Transfer-Encoding:binary");
		$objWriter->save('php://output');
    }

}