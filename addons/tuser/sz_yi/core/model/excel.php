<?php
// 模块LTD提供
class Sz_DYi_Excel
{
	protected function column_str($key)
	{
		$array = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ', 'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN', 'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BX', 'BY', 'BZ', 'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG', 'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ');
		return $array[$key];
	}

	protected function column($key, $columnnum = 1)
	{
		return $this->column_str($key) . $columnnum;
	}

	public function export($list, $params = array())
	{
		if (PHP_SAPI == 'cli') {
			exit('This example should only be run from a Web Browser');
		}

		ob_end_clean();
		require_once IA_ROOT . '/addons/sz_yi/core/inc/phpexcel/PHPExcel.php';
		$excel = new PHPExcel();
		$excel->getProperties()->setCreator('微销商城')->setLastModifiedBy('微销商城')->setTitle('Office 2007 XLSX Test Document')->setSubject('Office 2007 XLSX Test Document')->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')->setKeywords('office 2007 openxml php')->setCategory('report file');
		$sheet = $excel->setActiveSheetIndex(0);
		$rownum = 1;

		foreach ($params['columns'] as $key => $column) {
			$sheet->setCellValue($this->column($key, $rownum), $column['title']);

			if (!empty($column['width'])) {
				$sheet->getColumnDimension($this->column_str($key))->setWidth($column['width']);
			}
		}

		++$rownum;

		foreach ($list as $row) {
			$len = count($params['columns']);
			$i = 0;

			while ($i < $len) {
				$value = $row[$params['columns'][$i]['field']];
				$value = @iconv('utf-8', 'gbk', $value);
				$value = @iconv('gbk', 'utf-8', $value);
				$sheet->setCellValueExplicit($this->column($i, $rownum), $value, PHPExcel_Cell_DataType::TYPE_STRING);
				++$i;
			}

			++$rownum;
		}

		$excel->getActiveSheet()->setTitle($params['title']);
		$filename = $params['title'] . '-' . date('Y-m-d H:i', time());
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
		header('Cache-Control: max-age=0');
		$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
		$writer->save('php://output');
		exit();
	}

	public function exportOrder($list, $params = array(), $page = 1, $page_total)
	{
		if (PHP_SAPI == 'cli') {
			exit('This example should only be run from a Web Browser');
		}

		static $excel;

		if (!isset($excel)) {
			require_once IA_ROOT . '/addons/sz_yi/core/inc/phpexcel/PHPExcel.php';
			$excel = new PHPExcel();
		}

		$excel->getProperties()->setCreator('微销商城')->setLastModifiedBy('微销商城')->setTitle('Office 2007 XLSX Test Document')->setSubject('Office 2007 XLSX Test Document')->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')->setKeywords('office 2007 openxml php')->setCategory('report file');
		$sheet = $excel->setActiveSheetIndex(0);
		$rownum = 1;

		foreach ($params['columns'] as $key => $column) {
			$sheet->setCellValue($this->column($key, $rownum), $column['title']);

			if (!empty($column['width'])) {
				$sheet->getColumnDimension($this->column_str($key))->setWidth($column['width']);
			}
		}

		++$rownum;

		foreach ($list as $row) {
			$len = count($params['columns']);
			$i = 0;

			while ($i < $len) {
				$value = $row[$params['columns'][$i]['field']];
				$value = @iconv('utf-8', 'gbk', $value);
				$value = @iconv('gbk', 'utf-8', $value);
				$sheet->setCellValueExplicit($this->column($i, $rownum), $value, PHPExcel_Cell_DataType::TYPE_STRING);
				++$i;
			}

			++$rownum;
		}

		$excel->getActiveSheet()->setTitle($params['title']);
		$filename = $params['title'] . '-' . $page;
		$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');

		if (!file_exists('IA_ROOT . "/addons/sz_yi/data/excel')) {
			mkdir(IA_ROOT . '/addons/sz_yi/data/excel/');
		}

		$writer->save(IA_ROOT . '/addons/sz_yi/data/excel/' . $filename . '.xls');

		if ($page == $page_total) {
			load()->func('file');
			$filename = IA_ROOT . '/addons/sz_yi/data/' . time() . 'down.zip';
			$time = time();
			$zip = new ZipArchive();

			if ($zip->open($filename, ZIPARCHIVE::CREATE) !== true) {
				exit('无法打开文件，或者文件创建失败');
			}

			$_obf_DSYeHxYZDDMtHiMkEVsbGAYqKTw5CyI_ = file_tree(IA_ROOT . '/addons/sz_yi/data/excel');

			foreach ($_obf_DSYeHxYZDDMtHiMkEVsbGAYqKTw5CyI_ as $val) {
				$zip->addFile($val, basename($val));
			}

			$zip->close();

			foreach ($_obf_DSYeHxYZDDMtHiMkEVsbGAYqKTw5CyI_ as $val) {
				file_delete($val);
			}

			$url = 'http://' . $_SERVER['SERVER_NAME'] . '/addons/sz_yi/data/' . $time . 'down.zip';
			$backurl = 'http://' . $_SERVER['SERVER_NAME'] . '/web/index.php?c=site&a=entry&op=display&do=order&m=sz_yi';
			echo '<div style="border: 6px solid #e0e0e0;width: 12%;margin: 0 auto;margin-top: 12%;padding: 26px 100px;box-shadow: 0 0 14px #a2a2a2;color: #616161;"><a style="color:red;text-decorationnone;"  href="' . $url . '">点击获取下载文件</a><a style="color:#616161"  href="' . $backurl . '">返回</a><div>';
		}
	}

	public function import($excefile)
	{
		global $_W;
		require_once IA_ROOT . '/addons/sz_yi/core/inc/phpexcel/PHPExcel.php';
		require_once IA_ROOT . '/addons/sz_yi/core/inc/phpexcel/PHPExcel/IOFactory.php';
		require_once IA_ROOT . '/addons/sz_yi/core/inc/phpexcel/PHPExcel/Reader/Excel5.php';
		$path = IA_ROOT . '/addons/sz_yi/data/tmp/';

		if (!is_dir($path)) {
			load()->func('file');
			mkdirs($path, '0777');
		}

		$file = time() . $_W['uniacid'] . '.xls';
		$filename = $_FILES[$excefile]['name'];
		$tmpname = $_FILES[$excefile]['tmp_name'];

		if (empty($tmpname)) {
			message('请选择要上传的Excel文件!', '', 'error');
		}

		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		if ($ext != 'xls') {
			message('请上传 xls 格式的Excel文件!', '', 'error');
		}

		$uploadfile = $path . $file;
		$result = move_uploaded_file($tmpname, $uploadfile);

		if (!$result) {
			message('上传Excel 文件失败, 请重新上传!', '', 'error');
		}

		$reader = PHPExcel_IOFactory::createReader('Excel5');
		$excel = $reader->load($uploadfile);
		$sheet = $excel->getActiveSheet();
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();
		$_obf_DTsOBigpHhsUAjwXMCgbOT8SLQMGNSI_ = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$values = array();
		$row = 2;

		while ($row <= $highestRow) {
			$rowValue = array();
			$col = 0;

			while ($col < $_obf_DTsOBigpHhsUAjwXMCgbOT8SLQMGNSI_) {
				$rowValue[] = $sheet->getCellByColumnAndRow($col, $row)->getValue();
				++$col;
			}

			$values[] = $rowValue;
			++$row;
		}

		return $values;
	}
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
