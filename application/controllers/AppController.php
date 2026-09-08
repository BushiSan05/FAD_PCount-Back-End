<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AppController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('AppModel');
	}

	public function checkConnection()
	{
		$start = microtime(true);

		echo json_encode([
			'status' => 'success',
			'message' => 'Server is reachable',
			'time' => microtime(true) - $start,
		]);
	}

	public function getCsv($csvFilename)
	{
		$filePath = FCPATH . 'csv/' . basename($csvFilename);

		// Check if file exists and is a CSV
		if (!file_exists($filePath) || pathinfo($filePath, PATHINFO_EXTENSION) !== 'csv') {
			show_404();
			return;
		}

		// Set headers for CSV download
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
		header('Content-Length: ' . filesize($filePath));

		readfile($filePath);
		exit;
	}

	public function listCsv()
	{
		$folder = FCPATH . 'csv';
		$files = array_diff(scandir($folder), array('.', '..'));

		$csvFiles = [];
		foreach ($files as $file) {
			if (pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
				$csvFiles[] = $file;
			}
		}

		header('Content-Type: application/json');
		echo json_encode($csvFiles);
	}



	public function uploadCsv()
	{
		// -------------------------
		// 0️⃣ Validate upload
		// -------------------------
		if (empty($_FILES['file']['name'])) {
			echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
			return;
		}

		$uploader   = $this->input->post('uploader');
		$fullname 	= trim($this->input->post('fullname'));
		$department = $this->input->post('department');

		if (empty($uploader) || empty($department)) {
			echo json_encode(['status' => 'error', 'message' => 'Uploader or department missing']);
			return;
		}

		$file = $_FILES['file'];
		$uploadPath = FCPATH . 'pcountdata/';

		if (!is_dir($uploadPath)) {
			mkdir($uploadPath, 0777, true);
		}

		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
		if (strtolower($ext) !== 'csv') {
			echo json_encode(['status' => 'error', 'message' => 'Only CSV files allowed']);
			return;
		}

		$csvFilename   = $file['name'];
		$targetPath = $uploadPath . $csvFilename;

		// -------------------------
		// 1️⃣ Open and lock existing CSV
		// -------------------------
		$fp = fopen($targetPath, 'c+');
		if (!$fp) {
			echo json_encode(['status' => 'error', 'message' => 'Unable to open CSV']);
			return;
		}

		if (!flock($fp, LOCK_EX)) {
			fclose($fp);
			echo json_encode(['status' => 'error', 'message' => 'File is busy, try again']);
			return;
		}

		// -------------------------
		// 2️⃣ Read existing CSV
		// -------------------------
		rewind($fp);
		$existingMap = [];
		$header = fgetcsv($fp);

		if ($header !== false) {
			$barcodeIdx  = array_search('barcode', $header);
			$scannedIdx  = array_search('scanned', $header);
			$datetimeIdx = array_search('datetime_scanned', $header);

			if ($barcodeIdx === false || $scannedIdx === false || $datetimeIdx === false) {
				flock($fp, LOCK_UN);
				fclose($fp);
				echo json_encode(['status' => 'error', 'message' => 'Existing CSV missing required columns']);
				return;
			}

			while (($row = fgetcsv($fp)) !== false) {
				$barcode = trim($row[$barcodeIdx]);
				$existingMap[$barcode] = $row;
			}
		} else {
			$header = [];
		}

		// -------------------------
		// 3️⃣ Read uploaded CSV
		// -------------------------
		$uploadedMap = [];
		$uploadedFp = fopen($file['tmp_name'], 'r');
		$uploadedHeader = fgetcsv($uploadedFp);

		if (empty($header)) {
			$header = $uploadedHeader;
			$barcodeIdx  = array_search('barcode', $header);
			$scannedIdx  = array_search('scanned', $header);
			$datetimeIdx = array_search('datetime_scanned', $header);

			if ($barcodeIdx === false || $scannedIdx === false || $datetimeIdx === false) {
				fclose($uploadedFp);
				flock($fp, LOCK_UN);
				fclose($fp);
				echo json_encode(['status' => 'error', 'message' => 'Uploaded CSV missing required columns']);
				return;
			}
		}

		while (($row = fgetcsv($uploadedFp)) !== false) {
			$barcode = trim($row[$barcodeIdx]);
			$uploadedMap[$barcode] = $row;
		}
		fclose($uploadedFp);

		// -------------------------
		// 4️⃣ Merge uploaded CSV into existing CSV
		// -------------------------
		foreach ($uploadedMap as $barcode => $row) {
			if (isset($existingMap[$barcode])) {
				$existingScanned  = $existingMap[$barcode][$scannedIdx];
				$existingDatetime = $existingMap[$barcode][$datetimeIdx];

				$uploadedScanned  = $row[$scannedIdx];
				$uploadedDatetime = $row[$datetimeIdx];

				$existingFullyScanned =
					$existingScanned == '1' &&
					!empty($existingDatetime) &&
					$existingDatetime !== '0';

				$uploadedFullyScanned =
					$uploadedScanned == '1' &&
					!empty($uploadedDatetime) &&
					$uploadedDatetime !== '0';

				if (!$existingFullyScanned && $uploadedFullyScanned) {
					$existingMap[$barcode][$scannedIdx]  = $uploadedScanned;
					$existingMap[$barcode][$datetimeIdx] = $uploadedDatetime;
				}
			} else {
				$existingMap[$barcode] = $row;
			}
		}

		// -------------------------
		// 5️⃣ Write merged CSV safely
		// -------------------------
		ftruncate($fp, 0);
		rewind($fp);

		// Write header
		fputcsv($fp, $header);

		// Write rows
		foreach ($existingMap as $row) {
			fputcsv($fp, $row); // ✅ handles desc with commas properly
		}

		fflush($fp);
		flock($fp, LOCK_UN);
		fclose($fp);

		// -------------------------
		// 6️⃣ Return success
		// -------------------------

		// Insert record into DB
		$this->db->insert('uploaded_csvs', [
			'filename' => $csvFilename,
			'uploader' => $uploader,
			'fullname' => $fullname,
			'department' => $department,
			'uploaded_at' => date('Y-m-d H:i:s'),
		]);

		echo json_encode([
			'status'     => 'success',
			'filename'   => $csvFilename,
			'uploader'   => $uploader,
			'fullname'	 => $fullname,
			'department' => $department
		]);
	}


	public function addUser()
	{
		$input = json_decode(file_get_contents('php://input'), true);

		if (empty($input['username']) || empty($input['password'])) {
			echo json_encode([
				'status' => false,
				'message' => 'Missing parameters'
			]);
			return;
		}

		// Prevent duplicates
		$exists = $this->db
			->where('username', $input['username'])
			->get('users')
			->row();

		if ($exists) {
			echo json_encode([
				'status' => false,
				'message' => '❌ Username already exists'
			]);
			return;
		}

		$data = [
			'username' => $input['username'],
			'password' => md5($input['password']),
		];

		if ($this->db->insert('users', $data)) {
			echo json_encode([
				'status' => true
			]);
		} else {
			echo json_encode([
				'status' => false,
				'message' => 'Insert failed'
			]);
		}
	}


	public function addLogs()
	{
		$data = json_decode($this->input->raw_input_stream, true);

		if (!isset($data['logs']) || !is_array($data['logs'])) {
			echo json_encode(['status' => false, 'message' => 'No logs received']);
			return;
		}

		$uploadDateTime = date('Y-m-d h:i:s'); // server datetime

		foreach ($data['logs'] as &$log) {
			$log['uploaded'] = $uploadDateTime;
		}

		$this->db->insert_batch('logs', $data['logs']);

		echo json_encode([
			'status' => true,
			'message' => 'Logs uploaded successfully'
		]);
	}


	public function getItemMasterfileCount()
	{
		$itemCount = $this->AppModel->getItemMasterfileCount();
		echo json_encode($itemCount);
	}

	public function getItemMasterfileOffset()
	{
		$offset = $this->security->xss_clean($this->input->post('offset'));
		// $offset = 2;
		$result = $this->AppModel->getItemMasterfileOffset_mod($offset);
		echo json_encode($result);
	}

	public function getAssetTypesOffset()
	{
		$offset = $this->security->xss_clean($this->input->post('offset'));
		// $offset = 2;
		$result = $this->AppModel->getAssetTypesOffset_mod($offset);
		echo json_encode($result);
	}

	public function getCatTypesOffset()
	{
		$offset = $this->security->xss_clean($this->input->post('offset'));
		// $offset = 2;
		$result = $this->AppModel->getCatTypesOffset_mod($offset);
		echo json_encode($result);
	}

	public function getSourceOffset()
	{
		$offset = $this->security->xss_clean($this->input->post('offset'));
		// $offset = 2;
		$result = $this->AppModel->getSourceOffset_mod($offset);
		echo json_encode($result);
	}


	public function getUsersCount()
	{
		$itemCount = $this->AppModel->getUsersCount();
		echo json_encode($itemCount);
	}


	public function getUsers()
	{
		$result = $this->AppModel->getUsers();
		echo json_encode($result);
	}


	public function csvStatus()
	{
		header('Content-Type: application/json');

		$dir = FCPATH . 'csv/';
		$latestTime = 0;
		$latestFile = '';

		foreach (glob($dir . '*.csv') as $file) {
			$mtime = filemtime($file);
			if ($mtime > $latestTime) {
				$latestTime = $mtime;
				$latestFile = basename($file);
			}
		}

		if ($latestTime === 0) {
			echo json_encode([
				'last_updated' => null,
				'timestamp' => 0,
				'latest_file' => null
			]);
			return;
		}

		echo json_encode([
			'last_updated' => date('Y-m-d H:i:s', $latestTime),
			'timestamp'    => (int) $latestTime,
			'latest_file'  => $latestFile
		]);
	}

	public function csvManifest()
	{
		header('Content-Type: application/json');

		$dir = FCPATH . 'csv/';
		$files = [];

		foreach (glob($dir . '*.csv') as $file) {
			$files[] = [
				'filename'      => basename($file),
				'last_modified' => filemtime($file) // INT
			];
		}

		echo json_encode([
			'files' => $files
		]);
	}


	public function uploadNfCsv()
	{
		// -------------------------
		// 0. Validate upload
		// -------------------------
		if (empty($_FILES['file']['name'])) {
			echo json_encode([
				'status' => 'error',
				'message' => 'No file uploaded'
			]);
			return;
		}

		$uploader   = trim($this->input->post('uploader'));
		$fullname   = trim($this->input->post('fullname'));
		$department = trim($this->input->post('department'));

		if (empty($uploader) || empty($department)) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Uploader or department missing'
			]);
			return;
		}

		$file = $_FILES['file'];

		// -------------------------
		// Paths
		// -------------------------
		$uploadPath = FCPATH . 'nfitems/';

		// -------------------------
		// Create folder if needed
		// -------------------------
		if (!is_dir($uploadPath)) {

			if (!mkdir($uploadPath, 0775, true)) {
				echo json_encode([
					'status' => 'error',
					'message' => 'Unable to create nfitems folder'
				]);
				return;
			}
		}

		// -------------------------
		// Validate extension
		// -------------------------
		$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

		if (strtolower($ext) !== 'csv') {
			echo json_encode([
				'status' => 'error',
				'message' => 'Only CSV files allowed'
			]);
			return;
		}

		// -------------------------
		// Validate temporary file
		// -------------------------
		if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Invalid uploaded file'
			]);
			return;
		}

		$csvFilename = basename($file['name']);
		$targetPath  = $uploadPath . $csvFilename;

		// -------------------------
		// Open existing CSV
		// -------------------------
		$fp = fopen($targetPath, 'c+');

		if (!$fp) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Unable to open CSV. Check folder permissions.'
			]);
			return;
		}

		// -------------------------
		// Lock CSV
		// -------------------------
		if (!flock($fp, LOCK_EX)) {

			fclose($fp);

			echo json_encode([
				'status' => 'error',
				'message' => 'File is busy, try again'
			]);

			return;
		}

		// -------------------------
		// Read existing CSV
		// -------------------------
		rewind($fp);

		$existingMap = [];

		$header = fgetcsv($fp);

		$descIdx = false;
		$remarksIdx = false;
		$datetimeIdx = false;

		if ($header !== false) {

			$descIdx = array_search('desc', $header);
			$remarksIdx = array_search('remarks', $header);
			$imageIdx = array_search('image', $header);
			$datetimeIdx = array_search('datetime_saved', $header);

			if (
				$descIdx === false ||
				$remarksIdx === false ||
				$imageIdx === false ||
				$datetimeIdx === false
			) {
				flock($fp, LOCK_UN);
				fclose($fp);

				echo json_encode([
					'status' => 'error',
					'message' => 'Existing CSV missing required columns'
				]);

				return;
			}

			while (($row = fgetcsv($fp)) !== false) {

				if (
					!isset($row[$descIdx]) ||
					!isset($row[$remarksIdx])
				) {
					continue;
				}

				$image = trim($row[$imageIdx]);
				$datetime = trim($row[$datetimeIdx]);

				$key = strtolower($image . '|' . $datetime);

				$existingMap[$key] = $row;
			}
		} else {

			$header = [];
		}

		// -------------------------
		// Read uploaded CSV
		// -------------------------
		$uploadedFp = fopen($file['tmp_name'], 'r');

		if (!$uploadedFp) {

			flock($fp, LOCK_UN);
			fclose($fp);

			echo json_encode([
				'status' => 'error',
				'message' => 'Unable to read uploaded CSV'
			]);

			return;
		}

		$uploadedHeader = fgetcsv($uploadedFp);

		if (empty($header)) {

			$header = $uploadedHeader;

			if ($header === false) {

				fclose($uploadedFp);
				flock($fp, LOCK_UN);
				fclose($fp);

				echo json_encode([
					'status' => 'error',
					'message' => 'Uploaded CSV is empty'
				]);

				return;
			}

			$descIdx = array_search('desc', $header);
			$remarksIdx = array_search('remarks', $header);
			$imageIdx = array_search('image', $header);
			$datetimeIdx = array_search('datetime_saved', $header);

			if (
				$descIdx === false ||
				$remarksIdx === false ||
				$datetimeIdx === false
			) {

				fclose($uploadedFp);
				flock($fp, LOCK_UN);
				fclose($fp);

				echo json_encode([
					'status' => 'error',
					'message' => 'Uploaded CSV missing required columns'
				]);

				return;
			}
		}

		// -------------------------
		// Read uploaded rows
		// -------------------------
		$uploadedMap = [];

		while (($row = fgetcsv($uploadedFp)) !== false) {

			if (
				!isset($row[$descIdx]) ||
				!isset($row[$remarksIdx])
			) {
				continue;
			}

			$image = trim($row[$imageIdx]);
			$datetime = trim($row[$datetimeIdx]);

			$key = strtolower($image . '|' . $datetime);

			$uploadedMap[$key] = $row;
		}

		fclose($uploadedFp);

		// -------------------------
		// Merge uploaded CSV
		// -------------------------
		// foreach ($uploadedMap as $key => $row) {

		// 	if (isset($existingMap[$key])) {

		// 		$existingDatetime =
		// 			isset($existingMap[$key][$datetimeIdx])
		// 				? trim($existingMap[$key][$datetimeIdx])
		// 				: '';

		// 		$uploadedDatetime =
		// 			isset($row[$datetimeIdx])
		// 				? trim($row[$datetimeIdx])
		// 				: '';

		// 		$existingFullySaved =
		// 			!empty($existingDatetime) &&
		// 			$existingDatetime !== '0';

		// 		$uploadedFullySaved =
		// 			!empty($uploadedDatetime) &&
		// 			$uploadedDatetime !== '0';

		// 		if (
		// 			!$existingFullySaved &&
		// 			$uploadedFullySaved
		// 		) {
		// 			$existingMap[$key][$datetimeIdx] =
		// 				$uploadedDatetime;
		// 		}

		// 	} else {

		// 		$existingMap[$key] = $row;
		// 	}
		// }
		foreach ($uploadedMap as $key => $row) {
			$existingMap[$key] = $row;
		}

		// -------------------------
		// Write merged CSV
		// -------------------------
		ftruncate($fp, 0);
		rewind($fp);

		fputcsv($fp, $header);

		foreach ($existingMap as $row) {
			fputcsv($fp, $row);
		}

		fflush($fp);

		flock($fp, LOCK_UN);
		fclose($fp);

		// -------------------------
		// Insert upload record
		// -------------------------
		$this->db->insert('uploaded_nfitems', [
			'filename'    => $csvFilename,
			'uploader'    => $uploader,
			'fullname'    => $fullname,
			'department'  => $department,
			'uploaded_at' => date('Y-m-d H:i:s')
		]);

		// -------------------------
		// Return JSON
		// -------------------------
		echo json_encode([
			'status'     => 'success',
			'filename'   => $csvFilename,
			'uploader'   => $uploader,
			'fullname'   => $fullname,
			'department' => $department,
			'message'    => 'CSV uploaded successfully'
		]);
	}

	public function uploadLogs()
	{
		$json = file_get_contents('php://input');
		$data = json_decode($json, true);

		if (!isset($data['logs']) || !is_array($data['logs'])) {
			echo json_encode([
				'success' => false,
				'message' => 'No logs received'
			]);
			return;
		}

		$uploadDateTime = date('Y-m-d H:i:s');

		$inserted = 0;
		$skipped  = 0;
		$errors   = 0;

		foreach ($data['logs'] as $log) {

			// Safe extraction (PHP 5.6 compatible)
			$date    = isset($log['date']) ? trim($log['date']) : '';
			$time    = isset($log['time']) ? trim($log['time']) : '';
			$user    = isset($log['user']) ? trim($log['user']) : '';
			$details = isset($log['details']) ? trim($log['details']) : '';

			$fullname   = isset($log['fullname']) ? $log['fullname'] : '';
			$location   = isset($log['location']) ? $log['location'] : '';
			$department = isset($log['department']) ? $log['department'] : '';

			// validate
			if ($date === '' || $time === '' || $user === '' || $details === '') {
				$errors++;
				continue;
			}

			// create unique hash
			$logHash = sha1($date . '|' . $time . '|' . $user . '|' . $details);

			// STEP 1: CHECK IF EXISTS
			$this->db->from('logs');
			$this->db->where('log_hash', $logHash);
			$exists = $this->db->count_all_results();

			if ($exists > 0) {
				$skipped++;
				continue;
			}

			// STEP 2: INSERT IF NOT EXISTS
			$insertData = [
				'date'       => $date,
				'time'       => $time,
				'user'       => $user,
				'fullname'   => $fullname,
				'location'   => $location,
				'department' => $department,
				'details'    => $details,
				'uploaded'   => $uploadDateTime,
				'log_hash'   => $logHash
			];

			$this->db->insert('logs', $insertData);

			if ($this->db->affected_rows() > 0) {
				$inserted++;
			} else {
				$errors++;
			}
		}

		echo json_encode([
			'success'  => true,
			'inserted' => $inserted,
			'skipped'  => $skipped,
			'errors'   => $errors,
			'message'  => 'Upload completed'
		]);
	}

	public function getCsvFiles()
	{
		$path = FCPATH . 'pcountdata/';

		$files = glob($path . '*.csv');

		$result = [];

		foreach ($files as $file) {
			$result[] = [
				'filename' => basename($file),
				'size' => filesize($file),
				'modified' => date(
					'Y-m-d H:i:s',
					filemtime($file)
				),
				'url' => base_url(
					'pcountdata/' . basename($file)
				)
			];
		}

		echo json_encode([
			'status' => 'success',
			'files' => $result
		]);
	}


	public function getAllCsvFiles()
	{
		$result = [];

		// ============================================
		// Normal CSV files
		// ============================================
		$csvFiles = glob(FCPATH . 'pcountdata/*.csv');

		foreach ($csvFiles as $file) {

			$result[] = [
				'type' => 'csv',
				'filename' => basename($file),
				'size' => filesize($file),
				'modified' => date(
					'Y-m-d H:i:s',
					filemtime($file)
				),
				'url' => base_url(
					'pcountdata/' . basename($file)
				),
				'images' => []
			];
		}

		// ============================================
		// NF CSV files
		// ============================================
		$nfFiles = glob(FCPATH . 'nfitems/*.csv');

		foreach ($nfFiles as $file) {

			$images = [];

			if (($fp = fopen($file, 'r')) !== false) {

				$header = fgetcsv($fp);

				$imageIdx = array_search('image', $header);

				if ($imageIdx !== false) {

					while (($row = fgetcsv($fp)) !== false) {

						if (
							isset($row[$imageIdx]) &&
							trim($row[$imageIdx]) != ''
						) {

							$images[] = [
								'filename' => trim($row[$imageIdx]),
								'url' => base_url(
									'nfitems_images/' .
										rawurlencode(trim($row[$imageIdx]))
								)
							];
						}
					}
				}

				fclose($fp);
			}

			$result[] = [
				'type' => 'nf',
				'filename' => basename($file),
				'size' => filesize($file),
				'modified' => date(
					'Y-m-d H:i:s',
					filemtime($file)
				),
				'url' => base_url(
					'nfitems/' . basename($file)
				),
				'images' => array_values(
					array_unique($images, SORT_REGULAR)
				)
			];
		}

		echo json_encode([
			'status' => 'success',
			'files' => $result
		]);
	}

	public function checkNfImages()
	{
		$imageUploadPath = FCPATH . 'nfitems_images/';

		if (!isset($_POST['filenames'])) {
			echo json_encode([
				'status' => 'error',
				'message' => 'No filenames provided'
			]);

			return;
		}

		$filenames = json_decode($_POST['filenames'], true);

		if (!is_array($filenames)) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Invalid filenames'
			]);

			return;
		}

		$existing = [];
		$missing = [];

		foreach ($filenames as $filename) {

			$filename = basename($filename);

			if ($filename === '') {
				continue;
			}

			$destination =
				$imageUploadPath . $filename;

			if (file_exists($destination)) {

				$existing[] = $filename;
			} else {

				$missing[] = $filename;
			}
		}

		echo json_encode([
			'status' => 'success',
			'existing' => $existing,
			'missing' => $missing,
			'existing_count' => count($existing),
			'missing_count' => count($missing)
		]);
	}


	public function uploadNfImagesBatch()
	{
		// -------------------------
		// Image folder
		// -------------------------
		$imageUploadPath = FCPATH . 'nfitems_images/';

		// -------------------------
		// Create folder
		// -------------------------
		if (!is_dir($imageUploadPath)) {

			if (!mkdir($imageUploadPath, 0775, true)) {

				echo json_encode([
					'status' => 'error',
					'message' => 'Unable to create nfitems_images folder'
				]);

				return;
			}
		}

		// -------------------------
		// Validate images
		// -------------------------
		if (
			!isset($_FILES['images']) ||
			!isset($_FILES['images']['name']) ||
			!is_array($_FILES['images']['name'])
		) {

			echo json_encode([
				'status' => 'error',
				'message' => 'No images uploaded'
			]);

			return;
		}

		$count = count($_FILES['images']['name']);

		$uploaded = 0;
		$skipped = 0;
		$failed = 0;

		$uploadedFiles = [];
		$skippedFiles = [];
		$failedFiles = [];

		// -------------------------
		// Process batch
		// -------------------------
		for ($i = 0; $i < $count; $i++) {

			$originalName = $_FILES['images']['name'][$i];

			// -------------------------
			// Check upload error
			// -------------------------
			if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {

				$failed++;

				$failedFiles[] = [
					'filename' => $originalName,
					'error' => $_FILES['images']['error'][$i]
				];

				continue;
			}

			// -------------------------
			// Safe filename
			// -------------------------
			$imageFilename = basename($originalName);

			$destination =
				$imageUploadPath . $imageFilename;

			// -------------------------
			// Already exists
			// -------------------------
			if (file_exists($destination)) {

				$skipped++;

				$skippedFiles[] = $imageFilename;

				continue;
			}

			// -------------------------
			// Move file
			// -------------------------
			if (
				move_uploaded_file(
					$_FILES['images']['tmp_name'][$i],
					$destination
				)
			) {

				$uploaded++;

				$uploadedFiles[] = $imageFilename;
			} else {

				$failed++;

				$failedFiles[] = $imageFilename;
			}
		}

		// -------------------------
		// Return result
		// -------------------------
		echo json_encode([
			'status' => 'success',
			'uploaded' => $uploaded,
			'skipped' => $skipped,
			'failed' => $failed,
			'uploaded_files' => $uploadedFiles,
			'skipped_files' => $skippedFiles,
			'failed_files' => $failedFiles
		]);
	}



	public function uploadNfXlsx()
	{
		// ============================================================
		// 0. Validate upload
		// ============================================================

		if (
			!isset($_FILES['file']) ||
			empty($_FILES['file']['name'])
		) {
			echo json_encode([
				'status' => 'error',
				'message' => 'No XLSX file uploaded'
			]);
			return;
		}

		$uploader   = trim($this->input->post('uploader'));
		$fullname   = trim($this->input->post('fullname'));
		$department = trim($this->input->post('department'));

		if (empty($uploader) || empty($department)) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Uploader or department missing'
			]);
			return;
		}

		$file = $_FILES['file'];

		// ============================================================
		// 1. Check upload error
		// ============================================================

		if ($file['error'] !== UPLOAD_ERR_OK) {
			echo json_encode([
				'status' => 'error',
				'message' => 'File upload error: ' . $file['error']
			]);
			return;
		}

		// ============================================================
		// 2. Validate temporary file
		// ============================================================

		if (
			!isset($file['tmp_name']) ||
			!is_uploaded_file($file['tmp_name'])
		) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Invalid uploaded file'
			]);
			return;
		}

		// ============================================================
		// 3. Validate XLSX extension
		// ============================================================

		$originalName = basename($file['name']);

		$ext = strtolower(
			pathinfo($originalName, PATHINFO_EXTENSION)
		);

		if ($ext !== 'xlsx') {
			echo json_encode([
				'status' => 'error',
				'message' => 'Only XLSX files allowed'
			]);
			return;
		}

		// ============================================================
		// 4. Upload path
		// ============================================================

		$uploadPath = FCPATH . 'nfitems/';

		// ============================================================
		// 5. Create folder if needed
		// ============================================================

		if (!is_dir($uploadPath)) {

			if (!mkdir($uploadPath, 0775, true)) {
				echo json_encode([
					'status' => 'error',
					'message' => 'Unable to create nfitems folder'
				]);
				return;
			}
		}

		// ============================================================
		// 6. Final filename
		// ============================================================

		$xlsxFilename = $originalName;

		$targetPath = $uploadPath . $xlsxFilename;

		// ============================================================
		// 7. Save XLSX file
		// ============================================================

		if (!move_uploaded_file(
			$file['tmp_name'],
			$targetPath
		)) {
			echo json_encode([
				'status' => 'error',
				'message' => 'Unable to save XLSX file'
			]);
			return;
		}

		// ============================================================
		// 8. Insert upload record
		// ============================================================

		$this->db->insert('uploaded_nfitems', [
			'filename'    => $xlsxFilename,
			'uploader'    => $uploader,
			'fullname'    => $fullname,
			'department'  => $department,
			'uploaded_at' => date('Y-m-d H:i:s')
		]);

		// ============================================================
		// 9. Return JSON
		// ============================================================

		echo json_encode([
			'status'     => 'success',
			'filename'   => $xlsxFilename,
			'uploader'   => $uploader,
			'fullname'   => $fullname,
			'department' => $department,
			'message'    => 'XLSX uploaded successfully'
		]);
	}
}
