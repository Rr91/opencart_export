<?php

class ControllerModuleImport extends Controller {
	public function index() {
		$this->language->load('module/import');
		// ???

		$this->getModule();
	}

	public function getModule() {
		$this->language->load('module/import');

		$this->data['test'] = 'this is test';

		$this->data['heading_title'] = strip_tags($this->language->get('heading_title'));

		$this->data['import_url'] = $this->url->link('module/import/import', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['export_url'] = $this->url->link('module/import/export', 'token=' . $this->session->data['token'], 'SSL');

		$this->load->model('design/layout');

        $this->data['layouts'] = $this->model_design_layout->getLayouts();

        $this->template = 'module/import.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
	}

	public function import() {

		$this->data['form_action'] = $this->url->link('module/import/load', 'token=' . $this->session->data['token'], 'SSL');

		$this->template = 'module/import/import.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
	}

	public function load() {

		$file = $_FILES['xls'];
		$excelFile = $file['tmp_name'];

		require_once($_SERVER['DOCUMENT_ROOT'] . '/system/PHPExcel/Classes/PHPExcel.php');

		try {
		    $objReader = PHPExcel_IOFactory::createReader('Excel2007');
			$objPHPExcel = $objReader->load($excelFile);
		} catch(Exception $e) {
		    die('Error loading file "'.pathinfo($excelFile,PATHINFO_BASENAME).'": '.$e->getMessage());
		}
		// $sheet = $objPHPExcel->getSheet(0);
		// $data = $sheet->toArray();

		$sheet = $objPHPExcel->getSheet(0);
		$highestRow = $sheet->getHighestRow();
		$highestColumn = $sheet->getHighestColumn();

		//  Loop through each row of the worksheet in turn
		for ($row = 1; $row <= $highestRow; $row++){
		    //  Read a row of data into an array
		    $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
		                                    NULL,
		                                    TRUE,
		                                    FALSE);
		    $data[] = $rowData;
		    //  Insert row data array into your database of choice here
		}

		$updated = $missed = array();

		$head = array('SKU', 'количество', 'цена', 'скидка 1', 'скидка 2');

		$xls_head = $data[0][0];
		unset($data[0]);

		if ($head[0] == $xls_head[0] && $head[1] == $xls_head[1] && $head[2] == $xls_head[2] && $head[3] == $xls_head[3] && $head[4] == $xls_head[4]) {
			foreach($data as $row) {
				$row = $row[0];
				// var_dump($row); exit;
				if ($row[0] == null) {
					continue;
				}
				if ($pid = $this->findProduct($row[0])) {
					$this->updateProduct($row, $pid);
					$updated[] = array('sku' => $row[0], 'name' => '');
				} else {
					$missed[] = array('sku' => $row[0], 'name' => '');
				}
			}
		} else {
			$this->data['errors'] = 'Неверный формат данных в файле';
		}


		$this->data['updated']     = $updated;
		$this->data['missed']      = $missed;
		$this->data['xls_content'] = $data;

		$this->template = 'module/import/load.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
	}

	protected function findProduct($sku) {
		$this->load->model('module/import');

		$res = $this->model_module_import->findProduct($sku);
		if (count($res)) {
			return $res[0]['id'];
		}
		return false;
	}

	protected function updateProduct($data, $pid) {
		$this->load->model('module/import');

		$this->model_module_import->updateProduct(array(
				'sku' => $data[0],
				'quantity' => $data[1],
				'price' => $data[2],
				'disc1' => $data[3],
				'disc2' => $data[4],
				'product_id' => $pid,
			));
	}

	public function export() {

		$file = $_SERVER['DOCUMENT_ROOT'].'/export/price_template.xlsx';

		$file_type = 'Excel2007';

		require_once($_SERVER['DOCUMENT_ROOT'] . '/system/PHPExcel/Classes/PHPExcel.php');

		$objReader   = PHPExcel_IOFactory::createReader($file_type);
		$objPHPExcel = $objReader->load($file);
		$objPHPExcel->setActiveSheetIndex(0);
		$sheet       = $objPHPExcel->getActiveSheet();

		$max_row = $sheet->getHighestRow()+1;

		$this->load->model('module/import');
		$data = $this->model_module_import->getExportData();

		$columns = array('A', 'B', 'C', 'D', 'E');

		// var_dump($data); exit;

		foreach($data as $row) {
			$key = 0;
			foreach ($row as $item) {
				// var_dump($key, $max_row, $item);
				if ($key > 1) {
					$sheet->setCellValueByColumnAndRow($key, $max_row, (int)$item);
				} else {
					$sheet->setCellValueByColumnAndRow($key, $max_row, $item);
				}
				$sheet->getStyle($columns[$key].$max_row)->applyFromArray(array(
					'borders' => array(
						'bottom' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
							),
						'right' => array(
							'style' => PHPExcel_Style_Border::BORDER_THIN
							)
						)
               		)
               	);
               	if ($key > 1) {
               		$sheet->getStyle($columns[$key].$max_row)->getNumberFormat()->setFormatCode("#,##0.00р");
               	}
               	$key++;
			}
			// echo '<hr>';
			$max_row++;
		}

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $file_type);

		// // Tell excel not to precalculate any formulas
		$objWriter->setPreCalculateFormulas(false);

		$exc_filename=$_SERVER['DOCUMENT_ROOT'].'/export/export.xlsx';

		// // Save the file
		$objWriter->save($exc_filename);
		// // This must be called before unsetting to prevent memory leaks
		$objPHPExcel->disconnectWorksheets();
		// // Again, unset variables to free up memory
		unset($file, $file_type, $objReader, $objPHPExcel);

		$this->template = 'module/import/export.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->data['download_url'] = '/export/export.xlsx';

        $this->response->setOutput($this->render());
	}
}