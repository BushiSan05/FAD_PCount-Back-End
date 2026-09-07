<?php
defined('BASEPATH') or exit('No direct script access allowed');
date_default_timezone_set('Asia/Manila');
class Appc extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('Appm');
		$this->load->library('form_validation');
		$this->load->library('upload');
	}


	//PCOUNT
	public function checkConnection()
	{
		echo 'Connected';
	}
	public function getFilteredItemMasterfile()
	{
		$res = $this->Appm->getFilteredItemMasterfile();
		if ($res[0]['byCategory'] == 'False' && $res[0]['byVendor'] == 'False') {
			echo json_encode($res);
		} elseif ($res[0]['byCategory'] == 'True' && $res[0]['byVendor'] == 'True') {
			echo json_encode($res);
		} elseif ($res[0]['byCategory'] == 'True' && $res[0]['byVendor'] == 'False') {
			echo json_encode($res);
		} elseif ($res[0]['byCategory'] == 'False' && $res[0]['byVendor'] == 'True') {
			echo json_encode($res);
		}
	}

	public function getUnit()
	{
		$haveFilter = $this->security->xss_clean($this->input->post('haveFilter'));
		$filters = $this->security->xss_clean($this->input->post('filters'));
		// $haveFilter = 'True';
		//$filters ="'HAIR CARE'";
		$unit = $this->Appm->getUnit_mod($haveFilter, $filters);
		echo json_encode($unit);
	}

	public function getVendor()
	{
		$haveFilter = $this->security->xss_clean($this->input->post('haveFilter'));
		$filters = $this->security->xss_clean($this->input->post('filters'));
		// $haveFilter = 'True';
		//$filters ="'HAIR CARE'";
		$unit = $this->Appm->getVendor_mod($haveFilter, $filters);
		echo json_encode($unit);
	}

	public function insertNFItemList()
	{

		$where = array();
		$nfitems	 		=  $this->security->xss_clean($this->input->post('nfitems'));
		$user_signature	 	=  $this->security->xss_clean($this->input->post('user_signature'));
		// $audit_signature	=  $this->security->xss_clean($this->input->post('audit_signature'));
		$locationId  	 	=  $this->security->xss_clean($this->input->post('locationid'));

		$nfjson = str_replace('&quot;', '"', $nfitems);
		$decodedNfItems = json_decode($nfjson, true);

		$nffinal_ress = array();
		foreach ($decodedNfItems as $nfress) :
			$nffinal_ress = array(
				"barcode" 			=> $nfress['barcode'],
				"inputted_desc"	 	=> $nfress['inputted_description'],
				"itemcode"			=> $nfress['item_code'],
				"vendor_code"		=> $nfress['supplier_code'],
				"uom" 				=> $nfress['uom'],
				"price"				=> $nfress['price'],
				"qty" 				=> $nfress['qty'],
				"location_id" 		=> $nfress['location'],
				"datetime_scanned" 	=> $nfress['datetimecreated'],
				"business_unit" 	=> $nfress['business_unit'],
				"department" 		=> $nfress['department'],
				"section" 			=> $nfress['section'],
				"empno" 			=> $nfress['empno'],
				"rack_desc" 		=> $nfress['rack_desc'],
				// "audit_signature" 	=> $nfress['audit_signature'],
				// "user_signature" 	=> $nfress['user_signature'],
				"datetime_exported"	=> date("Y-m-d H:i:s"),
				"item_image"		=> $nfress['front_pic'],
				"item_image_back"	=> $nfress['back_pic'],
				// "itemcode"			=> $nfress['itemcode'],
			);

			$where = array(
				"barcode" 			=> $nfress['barcode'],
				"inputted_desc" 	=> $nfress['inputted_description'],
				"itemcode"			=> $nfress['item_code'],
				"vendor_code"		=> $nfress['supplier_code'],
				"uom" 				=> $nfress['uom'],
				"price"				=> $nfress['price'],
				"qty" 				=> $nfress['qty'],
				"location_id" 		=> $nfress['location'],
				"datetime_scanned" 	=> $nfress['datetimecreated'],
				"business_unit" 	=> $nfress['business_unit'],
				"department" 		=> $nfress['department'],
				"section" 			=> $nfress['section'],
				"empno" 			=> $nfress['empno'],
				"rack_desc" 		=> $nfress['rack_desc'],
				"item_image"		=> $nfress['front_pic'],
				"item_image_back"	=> $nfress['back_pic'],
				// "itemcode"			=> $nfress['itemcode'],
			);

			$this->db->select('id, ,barcode, inputted_desc, itemcode, vendor_code, uom, price, qty, location_id, datetime_scanned, business_unit, department, section, empno, rack_desc, item_image, item_image_back');
			$this->db->from('tbl_app_nfitem');
			$this->db->where($where);
			$query = $this->db->get();
			$result = $query->result_array();
			if (isset($result)) {
				if(sizeof($result) > 0){
					//$nfres = true;
					$this->db->where('id', $result[0]['id']);
					$nfres = $this->db->update('tbl_app_nfitem', $nffinal_ress);
				}else{
					$nfres = $this->db->insert('tbl_app_nfitem', $nffinal_ress);
				}
			}
		endforeach;

		$this->Appm->updateUserStatus_mod($locationId);
		$this->Appm->updateAuditStatus_mod($locationId);
		$this->Appm->updateLocationStatus_mod($locationId);
		$this->Appm->updateSignature($locationId, $user_signature); 

		echo json_encode($nfres);
	}


	public function getItemMasterfileCount()
	{
		$itemCount = $this->Appm->getItemMasterfileCount();
		echo json_encode($itemCount);
	}

	public function getNavDataMasterfileCount()
	{
		$NavDataCount = $this->Appm->getNavDataMasterfileCount();
		echo json_encode($NavDataCount);
	}

	public function getNavCountMasterfileCount()
	{
		$NavCount = $this->Appm->getNavCountMasterfileCount();
		echo json_encode($NavCount);
	}

	public function getItemMasterfileOffset()
	{
		$offset = $this->security->xss_clean($this->input->post('offset'));
		// $offset = 2;
		$result = $this->Appm->getItemMasterfileOffset_mod($offset);
		echo json_encode($result);
	}

	public function getNavDataMasterfileOffset()
	{
		$offset = $this->security->xss_clean($this->input->post('offset'));
		$result = $this->Appm->getNavDataMasterfileOffset_mod($offset);
		echo json_encode($result);
	}

	public function getNavCountMasterfileOffset()
	{
		$offset = $this->security->xss_clean($this->input->post('offset'));
		$result = $this->Appm->getNavCountMasterfileOffset_mod($offset);
		echo json_encode($result);
	}

	public function getUserMasterfile()
	{
		$result = $this->Appm->getUserMasterfile();
		echo json_encode($result);
	}

	public function getAdmin()
	{
		$haveFilter = $this->security->xss_clean($this->input->post('haveFilter'));
		$filters = $this->security->xss_clean($this->input->post('filters'));
		$result = $this->Appm->getAdmin($haveFilter, $filters);
		echo json_encode($result);
	}

	public function CheckUpdate()
	{
		$version = $this->security->xss_clean($this->input->post('version'));
		$result = $this->Appm->CheckUpdate($version);
		echo json_encode($result);
	}

	public function getAuditMasterifle()
	{
		$result = $this->Appm->getAuditMasterifle();
		echo json_encode($result);
	}

	public function getLocationMasterfile()
	{
		$result = $this->Appm->getLocationMasterfile();
		echo json_encode($result);
	}

	public function insertCountData()
	{
		$this->form_validation->set_rules('itemcode', 'itemcode', 'trim');
		$this->form_validation->set_rules('barcode', 'barcode', 'trim');
		$this->form_validation->set_rules('description', 'description', 'trim');
		$this->form_validation->set_rules('uom', 'uom', 'trim');
		$this->form_validation->set_rules('qty', 'qty', 'trim');
		$this->form_validation->set_rules('business_unit', 'business_unit', 'trim');
		$this->form_validation->set_rules('department', 'department', 'trim');
		$this->form_validation->set_rules('section', 'section', 'trim');
		$this->form_validation->set_rules('rack_desc', 'rack_desc', 'trim');
		$this->form_validation->set_rules('empno', 'empno', 'trim');
		$this->form_validation->set_rules('datetime_scanned', 'datetime_scanned', 'trim');
		$this->form_validation->set_rules('datetime_saved', 'datetime_saved', 'trim');

		if ($this->form_validation->run() == FALSE) {
			$data	=	array(
				"error"	=> "errror",
				"msg"	=> "Pleas check the fields required."
			);
		} else {
			$itemcode 		  = $this->security->xss_clean($this->input->post('itemcode'));
			$barcode 		  = $this->security->xss_clean($this->input->post('barcode'));
			$description	  = $this->security->xss_clean($this->input->post('description'));
			$uom 		 	  = $this->security->xss_clean($this->input->post('uom'));
			$qty			  = $this->security->xss_clean($this->input->post('qty'));
			$business_unit	  = $this->security->xss_clean($this->input->post('business_unit'));
			$department		  = $this->security->xss_clean($this->input->post('department'));
			$section		  = $this->security->xss_clean($this->input->post('section'));
			$rack_desc		  = $this->security->xss_clean($this->input->post('rack_desc'));
			$empno			  = $this->security->xss_clean($this->input->post('empno'));
			$datetime_scanned = $this->security->xss_clean($this->input->post('datetime_scanned'));
			$datetime_saved   = $this->security->xss_clean($this->input->post('datetime_saved'));

			$result = $this->Appm->insertCountData($itemcode, $barcode, $description, $uom, $qty, $business_unit, $department, $section, $rack_desc, $empno, $datetime_scanned, $datetime_saved);

			echo json_encode($result);
		}
	}

	public function insertCountDataList_ctrl()
	{

		$where = array();
		$items       	 =  $this->security->xss_clean($this->input->post('items'));
		$empno       	 =  $this->security->xss_clean($this->input->post('empno'));
		$user_signature  =  $this->security->xss_clean($this->input->post('user_signature'));
		// $audit_signature =  $this->security->xss_clean($this->input->post('audit_signature'));
		$locationId  	 =  $this->security->xss_clean($this->input->post('locationid'));

		$json = str_replace('&quot;', '"', $items);
		$decodedItems = json_decode($json, true);

		$final_ress = array();
		foreach ($decodedItems as $ress) :
			$final_ress = array(
				"itemcode" 				=> $ress['itemcode'],
				"barcode" 				=> $ress['barcode'],
				"description" 			=> $ress['description'],
				"desc" 					=> $ress['desc'],
				"uom" 					=> $ress['uom'],
				"nav_qty"				=> $ress['pqty'],
				"qty" 					=> $ress['qty'],
				"conversion_qty" 		=> $ress['conqty'],
				"vendor_code"			=> $ress['supplier_code'],
				"vendor"				=> $ress['supplier'],
				"variant_code"			=> $ress['variant_code'],
				"variant_description"	=> $ress['variant_description'],
				"price"					=> $ress['price'],
				"nego_price"			=> $ress['nego_price'],
				"location_id" 			=> $ress['location_id'],
				"business_unit" 		=> $ress['business_unit'],
				"department" 			=> $ress['department'],
				"section" 				=> $ress['section'],
				"rack_desc" 			=> $ress['rack_desc'],
				"empno" 				=> $empno,
				"datetime_scanned" 		=> $ress['datetimecreated'],
				"datetime_saved" 		=> $ress['datetimesaved'],
				"datetime_exported"		=> date("Y-m-d H:i:s"),
				// "date_expiry" 		=> $ress['expiry'],
				// "user_signature"	=> $user_signature,
				// "audit_signature"	=> $audit_signature,
			);


			$where = array(
				"itemcode" 				=> $ress['itemcode'],
				"barcode" 				=> $ress['barcode'],
				"description" 			=> $ress['description'],
				"desc" 					=> $ress['desc'],
				"uom" 					=> $ress['uom'],
				"nav_qty"				=> $ress['pqty'],
				"qty" 					=> $ress['qty'],
				"conversion_qty" 		=> $ress['conqty'],
				"vendor_code"			=> $ress['supplier_code'],
				"vendor"				=> $ress['supplier'],
				"variant_code"			=> $ress['variant_code'],
				"variant_description"	=> $ress['variant_description'],
				"price"					=> $ress['price'],
				"nego_price"			=> $ress['nego_price'],
				"location_id" 			=> $ress['location_id'],
				"business_unit" 		=> $ress['business_unit'],
				"department" 			=> $ress['department'],
				"section" 				=> $ress['section'],
				"rack_desc" 			=> $ress['rack_desc'],
				"empno" 				=> $empno,
				"datetime_scanned" 		=> $ress['datetimecreated'],
				"datetime_saved" 		=> $ress['datetimesaved'],
			);

			$this->db->select('id, itemcode, barcode, desc, description, uom, nav_qty, qty, conversion_qty, vendor_code, vendor, variant_code, variant_description, price, nego_price, location_id, business_unit, department, section, rack_desc, empno, datetime_scanned, datetime_saved');
			$this->db->from('tbl_app_countdata');
			$this->db->where($where);
			$query = $this->db->get();
			$result = $query->result_array();
			if (isset($result)) {
				if(sizeof($result) > 0){
					//$res = true;
					$this->db->where('id', $result[0]['id']);
					$res = $this->db->update('tbl_app_countdata', $final_ress);
				}else{
					$res = $this->db->insert('tbl_app_countdata', $final_ress);
				}
			}
		endforeach;

		$this->Appm->updateUserStatus_mod($locationId);
		$this->Appm->updateAuditStatus_mod($locationId);
		$this->Appm->updateLocationStatus_mod($locationId);
		$this->Appm->updateSignature($locationId, $user_signature); 

		echo json_encode($res);
	}

	public function checkIfFloorPlanIsCreated()
	{
		$floor = $this->Appm->checkIfFloorPlanIsCreated();
		echo json_encode($floor);
	}

	public function test()
	{
		echo 'test';
	}

	public function insertFloorPlanData()
	{
		$this->form_validation->set_rules('location_id', 'location_id', 'trim');
		$this->form_validation->set_rules('company', 'company', 'trim');
		$this->form_validation->set_rules('business_unit', 'business_unit', 'trim');
		$this->form_validation->set_rules('department', 'department', 'trim');
		$this->form_validation->set_rules('section', 'section', 'trim');
		$this->form_validation->set_rules('rack_desc', 'rack_desc', 'trim');
		$this->form_validation->set_rules('xP', 'xP', 'trim');
		$this->form_validation->set_rules('yP', 'yP', 'trim');
		$this->form_validation->set_rules('he', 'he', 'trim');
		$this->form_validation->set_rules('we', 'we', 'trim');
		$this->form_validation->set_rules('isLandscape', 'isLandscape', 'trim');

		if ($this->form_validation->run() == FALSE) {
			$data	=	array(
				"error"	=> "errror",
				"msg"	=> "Pleas check the fields required."
			);
		} else {
			$location_id 	= $this->security->xss_clean($this->input->post('location_id'));
			$company 		= $this->security->xss_clean($this->input->post('company'));
			$business_unit 	= $this->security->xss_clean($this->input->post('business_unit'));
			$department 	= $this->security->xss_clean($this->input->post('department'));
			$section 		= $this->security->xss_clean($this->input->post('section'));
			$rack_desc 		= $this->security->xss_clean($this->input->post('rack_desc'));
			$xP 			= $this->security->xss_clean($this->input->post('xP'));
			$yP 			= $this->security->xss_clean($this->input->post('yP'));
			$he 			= $this->security->xss_clean($this->input->post('he'));
			$we				= $this->security->xss_clean($this->input->post('we'));
			$isLandscape 	= $this->security->xss_clean($this->input->post('isLandscape'));

			$result = $this->Appm->insertFloorPlanData($location_id, $company, $business_unit, $department, $section, $rack_desc, $xP, $yP, $he, $we, $isLandscape);

			echo json_encode($result);
		}
	}

	//kulang ug update signature model
	// public function updateSignature()
	// {
	// 	$locationid = $this->security->xss_clean($this->input->post('location_id'));
	// 	$usersig 	= $this->security->xss_clean($this->input->post('user_sig'));
	// 	$auditsig 	= $this->security->xss_clean($this->input->post('audit_sig'));

	// 	$result = $this->Appm->updateSignature($locationid, $usersig, $auditsig);
	// 	echo json_encode($result);
	// }

	public function updateFloorPlanStatus()
	{
		$department = $this->security->xss_clean($this->input->post('department'));
		$section 	= $this->security->xss_clean($this->input->post('section'));
		$rack_desc 	= $this->security->xss_clean($this->input->post('rack_desc'));
		$result 	= $this->Appm->updateFloorPlanStatus($department, $section, $rack_desc);
		echo json_encode($result);
	}

	public function getFloorPlanData()
	{
		$result = $this->Appm->getFloorPlanData();
		echo json_encode($result);
	}

	public function getServer_ctrl()
	{
		echo 'http://172.16.161.100/pcount/pcount/';
		// echo 'sfsdf';
	}

	public function insertAuditTrail()
	{
		
		$where = array();
		$logs =  $this->security->xss_clean($this->input->post('logs'));
		$logsjson = str_replace('&quot;', '"', $logs);
		$decodedlogs = json_decode($logsjson, true);

		$logs_array = array();

		foreach ($decodedlogs as $value) {


			$logs_array = array(
				"date"		=> $value['date'],
				"time"		=> $value['time'],
				"device" 	=> $value['device'],
				"user" 		=> $value['user'],
				"empid" 	=> $value['empid'],
				"details" 	=> $value['details'],
			);

			$where = array(
				"date"		=> $value['date'],
				"time"		=> $value['time'],
				"device" 	=> $value['device'],
				"user" 		=> $value['user'],
				"empid" 	=> $value['empid'],
				"details" 	=> $value['details'],
			);

			$this->db->select('id, date, time, device, user, empid, details');
			$this->db->from('tbl_audit_trail');
			$this->db->where($where);
			$query = $this->db->get();
			$result = $query->result_array();
			if (isset($result)) {
				if(sizeof($result) > 0){
					//$res = true;
					$this->db->where('id', $result[0]['id']);
					$res = $this->db->update('tbl_audit_trail', $logs_array);
				}else{
					$res = $this->db->insert('tbl_audit_trail', $logs_array);
				}
			}

			// $this->db->insert('tbl_audit_trail', $logs_array);
		}
		echo json_encode($res);
	}

	public function insertAdvanceCount()
	{

		$where = array();

		$items       	 =  $this->security->xss_clean($this->input->post('items'));
		$empno       	 =  $this->security->xss_clean($this->input->post('empno'));
		$user_signature  =  $this->security->xss_clean($this->input->post('user_signature'));
		$audit_signature =  $this->security->xss_clean($this->input->post('audit_signature'));
		$locationId  	 =  $this->security->xss_clean($this->input->post('locationid'));

		$json = str_replace('&quot;', '"', $items);
		$decodedItems = json_decode($json, true);

		$final_ress = array();
		foreach ($decodedItems as $ress) :
			$final_ress = array(
				"itemcode" 			=> $ress['itemcode'],
				"barcode" 			=> $ress['barcode'],
				"description" 		=> $ress['description'],
				"desc" 				=> $ress['desc'],
				"uom" 				=> $ress['uom'],
				// "lot_number" 		=> $ress['lot_number'],
				// "batch_number" 		=> $ress['batch_number'],
				// "expiry" 			=> $ress['expiry'],
				"qty" 				=> $ress['qty'],
				"conversion_qty" 	=> $ress['conqty'],
				"location_id" 		=> $ress['location_id'],
				"business_unit" 	=> $ress['business_unit'],
				"department" 		=> $ress['department'],
				"section" 			=> $ress['section'],
				"rack_desc" 		=> $ress['rack_desc'],
				"empno" 			=> $empno,
				"datetime_scanned" 	=> $ress['datetimecreated'],
				"datetime_saved" 	=> $ress['datetimesaved'],
				"datetime_exported"	=> date("Y-m-d H:i:s"),
				// "date_expiry" 		=> $ress['expiry'],
				// "user_signature"	=> $user_signature,
				// "audit_signature"	=> $audit_signature,
			);

			$where = array(
				"itemcode" 			=> $ress['itemcode'],
				"barcode" 			=> $ress['barcode'],
				"description" 		=> $ress['description'],
				"desc" 				=> $ress['desc'],
				"uom" 				=> $ress['uom'],
				// "lot_number" 		=> $ress['lot_number'],
				// "batch_number" 		=> $ress['batch_number'],
				// "expiry" 			=> $ress['expiry'],
				"qty" 				=> $ress['qty'],
				"conversion_qty" 	=> $ress['conqty'],
				"location_id" 		=> $ress['location_id'],
				"business_unit" 	=> $ress['business_unit'],
				"department" 		=> $ress['department'],
				"section" 			=> $ress['section'],
				"rack_desc" 		=> $ress['rack_desc'],
				"empno" 			=> $empno,
				"datetime_scanned" 	=> $ress['datetimecreated'],
				"datetime_saved" 	=> $ress['datetimesaved'],
			);

			$this->db->select('id, itemcode, barcode, description, uom, qty, location_id, business_unit, department,section, rack_desc, empno, datetime_scanned, datetime_saved');
			$this->db->from('tbl_advance_count');
			$this->db->where($where);
			$query = $this->db->get();
			$result = $query->result_array();
			if (isset($result)) {
				if(sizeof($result) > 0){
					//$res = true;
					$this->db->where('id', $result[0]['id']);
					$res = $this->db->update('tbl_advance_count', $final_ress);
				}else{
					$res = $this->db->insert('tbl_advance_count', $final_ress);
				}
			}
		endforeach;

		$this->Appm->updateUserStatus_mod($locationId);
		$this->Appm->updateAuditStatus_mod($locationId);
		$this->Appm->updateLocationStatus_mod($locationId);
		$this->Appm->updateSignature($locationId, $user_signature, $audit_signature); 
		
		echo json_encode($res);
		//echo json_encode($res);
	}

	public function insertNFAdvanceCount()
	{

		$where = array();
		$nfitems	 		=  $this->security->xss_clean($this->input->post('nfitems'));
		$auditSignature	 	=  $this->security->xss_clean($this->input->post('audit_signature'));
		$userSignature	 	=  $this->security->xss_clean($this->input->post('user_signature'));

		$nfjson = str_replace('&quot;', '"', $nfitems);
		$decodedNfItems = json_decode($nfjson, true);

		$nffinal_ress = array();
		foreach ($decodedNfItems as $nfress) :
			$nffinal_ress = array(
				"barcode" 			=> $nfress['barcode'],
				"inputted_desc"	 	=> $nfress['inputted_description'],
				"uom" 				=> $nfress['uom'],
				// "lot_number" 		=> $nfress['lot_number'],
				// "batch_number" 		=> $nfress['batch_number'],
				// "expiry" 			=> $nfress['expiry'],
				"qty" 				=> $nfress['qty'],
				"location_id" 		=> $nfress['location'],
				"datetime_scanned" 	=> $nfress['datetimecreated'],
				//Added 
				"business_unit" 	=> $nfress['business_unit'],
				"department" 		=> $nfress['department'],
				"section" 			=> $nfress['section'],
				"empno" 			=> $nfress['empno'],
				"rack_desc" 		=> $nfress['rack_desc'],
				// "audit_signature" 	=> $auditSignature,
				// "user_signature" 	=> $userSignature,
				"datetime_exported"	=> date("Y-m-d H:i:s"),
				"itemcode"			=> $nfress['itemcode'],
			);

			$where = array(
				"barcode" 			=> $nfress['barcode'],
				"inputted_desc"	 	=> $nfress['inputted_description'],
				"uom" 				=> $nfress['uom'],
				// "lot_number" 		=> $nfress['lot_number'],
				// "batch_number" 		=> $nfress['batch_number'],
				// "expiry" 			=> $nfress['expiry'],
				"qty" 				=> $nfress['qty'],
				"location_id" 		=> $nfress['location'],
				"datetime_scanned" 	=> $nfress['datetimecreated'],
				//Added 
				"business_unit" 	=> $nfress['business_unit'],
				"department" 		=> $nfress['department'],
				"section" 			=> $nfress['section'],
				"empno" 			=> $nfress['empno'],
				"rack_desc" 		=> $nfress['rack_desc'],
				"itemcode"			=> $nfress['itemcode'],
			);

			$this->db->select('id, empno, datetime_scanned');
			$this->db->from('tbl_app_advnfitem');
			$this->db->where($where);
			$query = $this->db->get();
			$result = $query->result_array();
			if (isset($result)) {
				if(sizeof($result) > 0){
					//$nfres = true;
					$this->db->where('id', $result[0]['id']);
					$nfres = $this->db->update('tbl_app_advnfitem', $nffinal_ress);
				}else{
					$nfres = $this->db->insert('tbl_app_advnfitem', $nffinal_ress);
				}
			}
		endforeach;

		echo json_encode($nfres);
	}

	public function insertFreeGoodsCount()
	{
		$items       	 =  $this->security->xss_clean($this->input->post('items'));
		$empno       	 =  $this->security->xss_clean($this->input->post('empno'));
		$user_signature  =  $this->security->xss_clean($this->input->post('user_signature'));
		$audit_signature =  $this->security->xss_clean($this->input->post('audit_signature'));
		$locationId  	 =  $this->security->xss_clean($this->input->post('locationid'));

		$json = str_replace('&quot;', '"', $items);
		$decodedItems = json_decode($json, true);

		$final_ress = array();
		foreach ($decodedItems as $ress) :
			$final_ress = array(
				"itemcode" 			=> $ress['itemcode'],
				"barcode" 			=> $ress['barcode'],
				"description" 		=> $ress['description'],
				"uom" 				=> $ress['uom'],
				"qty" 				=> $ress['qty'],
				"conversion_qty" 	=> $ress['conqty'],
				"location_id" 		=> $ress['location_id'],
				"business_unit" 	=> $ress['business_unit'],
				"department" 		=> $ress['department'],
				"section" 			=> $ress['section'],
				"rack_desc" 		=> $ress['rack_desc'],
				"empno" 			=> $empno,
				"datetime_scanned" 	=> $ress['datetimecreated'],
				"datetime_saved" 	=> $ress['datetimesaved'],
				"datetime_exported"	=> date("Y-m-d H:i:s"),
				"date_expiry" 		=> $ress['expiry'],
				"user_signature"	=> $user_signature,
				"audit_signature"	=> $audit_signature,
			);
			$res = $this->db->insert('tbl_freegoods_countdata', $final_ress);

			$this->Appm->updateUserStatus_mod($locationId);
			$this->Appm->updateAuditStatus_mod($locationId);
			$this->Appm->updateLocationStatus_mod($locationId);
		endforeach;

		echo json_encode($res);
	}

	public function insertNFFreeGoods()
	{

		$nfitems	 		=  $this->security->xss_clean($this->input->post('nfitems'));
		$auditSignature	 	=  $this->security->xss_clean($this->input->post('audit_signature'));
		$userSignature	 	=  $this->security->xss_clean($this->input->post('user_signature'));

		$nfjson = str_replace('&quot;', '"', $nfitems);
		$decodedNfItems = json_decode($nfjson, true);

		$nffinal_ress = array();
		foreach ($decodedNfItems as $nfress) :
			$nffinal_ress = array(
				"barcode" 			=> $nfress['barcode'],
				"uom" 				=> $nfress['uom'],
				"qty" 				=> $nfress['qty'],
				"location_id" 		=> $nfress['location'],
				"datetime_scanned" 	=> $nfress['datetimecreated'],
				//Added 
				"business_unit" 	=> $nfress['business_unit'],
				"department" 		=> $nfress['department'],
				"section" 			=> $nfress['section'],
				"empno" 			=> $nfress['empno'],
				"rack_desc" 		=> $nfress['rack_desc'],
				"audit_signature" 	=> $auditSignature,
				"user_signature" 	=> $userSignature,
				"datetime_exported"	=> date("Y-m-d H:i:s"),
				"itemcode"			=> $nfress['itemcode'],
				"barcode"			=> $nfress['barcode'],
			);
			$nfres = $this->db->insert('tbl_freegoods_nfitem', $nffinal_ress);
		endforeach;
		echo json_encode($nfres);
		
	}

	public function checkDuplicates(){

		$query = $this->db->query('SELECT * FROM tbl_app_countdata_alturas GROUP BY datetime_scanned, empno
			HAVING count(datetime_scanned) > 1 ');
		$result = $query->result_array();
		if (isset($result)) {
			echo json_encode($result);
		} else {
			return "No transaction found!";
		}
	}

	public function delDuplicate(){
		$query = $this->db->query('DELETE tbl1 FROM tbl_app_countdata_alturas_copy tbl1
				INNER JOIN tbl_app_countdata_alturas_copy tbl2 
				WHERE
    			tbl1.datetime_scanned = tbl2.datetime_scanned 
	 			AND tbl1.empno = tbl2.empno ');
		$result = $query->result_array();
		if (isset($result)) {
			return 'true';
		} else {
			return 'false';
		}
	}


	public function insertDuplicate(){
		/*$query = $this->db->query('SELECT * FROM tbl_app_countdata_alturas');*/
		$query = $this->db->query('SELECT * FROM tbl_app_countdata GROUP BY datetime_scanned, empno
			HAVING count(datetime_scanned) > 1 ');
		$result = $query->result_array();
		//$this->delDuplicate();
		$final_ress = array();
		for($i = 0; $i < 41; $i++){
			foreach ($result as $ress) :
			$final_ress = array(
				"itemcode" 			=> $ress['itemcode'],
				"barcode" 			=> $ress['barcode'],
				"description" 		=> $ress['description'],
				"uom" 				=> $ress['uom'],
				"qty" 				=> $ress['qty'],
				"conversion_qty" 	=> $ress['conversion_qty'],
				"location_id" 		=> $ress['location_id'],
				"business_unit" 	=> $ress['business_unit'],
				"department" 		=> $ress['department'],
				"section" 			=> $ress['section'],
				"rack_desc" 		=> $ress['rack_desc'],
				"empno" 			=> $ress['empno'],
				"datetime_scanned" 	=> $ress['datetime_scanned'],
				"datetime_saved" 	=> $ress['datetime_saved'],
				"datetime_exported"	=> $ress['datetime_exported'],
				"date_expiry" 		=> $ress['date_expiry'],
				"user_signature"	=> $ress['user_signature'],
				"audit_signature"	=> $ress['audit_signature'],
			);
			$res = $this->db->insert('tbl_app_countdata_alturas_copy', $final_ress);
			endforeach;
		}
		echo json_encode($res);

	}

	public function updateDuplicateCountItem($table){
		$id_array = array();
		$query = $this->db->query('SELECT id, qty, conversion_qty, empno, datetime_scanned, datetime_saved FROM '.$table.' WHERE qty != 0 AND conversion_qty != 0 GROUP BY qty, conversion_qty, empno, datetime_scanned, datetime_saved HAVING count(datetime_scanned) > 1 ');
		$result = $query->result_array();
		if (isset($result)) {
			if(sizeof($result) > 0){
				$final_ress = array();
				foreach ($result as $ress) :
					$final_ress = array(
						"id !="				=> $ress['id'],
						"qty" 				=> $ress['qty'],
						"conversion_qty" 	=> $ress['conversion_qty'],
						"empno" 			=> $ress['empno'],
						"datetime_scanned" 	=> $ress['datetime_scanned'],
						"datetime_saved" 	=> $ress['datetime_saved'],
					);
					echo json_encode($ress['id']);
					$this->db->set('qty', '0');
					$this->db->set('conversion_qty', '0');
					$this->db->where($final_ress);
					$this->db->update($table);
					//UPDATE ONLY
					/*$this->db->where($final_ress);
					$this->db->delete('tbl_app_countdata_alturas');*/
				endforeach;
			}else{
				echo "No duplicate";
			}	
		} else {
			echo "No transaction found!";
		}
	}

	public function updateDuplicateNFItem($table){
		$id_array = array();
		$query = $this->db->query('SELECT id, barcode, itemcode, description, uom, qty, location_id, business_unit, department, section, empno, datetime_scanned, rack_desc FROM '.$table.' WHERE qty != 0 GROUP BY barcode, itemcode, description, uom, qty, location_id, business_unit, department, section, empno, datetime_scanned, rack_desc HAVING count(datetime_scanned) > 1 ');
		$result = $query->result_array();
		if (isset($result)) {
			if(sizeof($result) > 0){
				$final_ress = array();
				foreach ($result as $ress) :
					$final_ress = array(
						"id !=" 			=> $ress['id'],
						"barcode" 			=> $ress['barcode'],
						"itemcode" 			=> $ress['itemcode'],
						"description" 		=> $ress['description'],
						"uom" 				=> $ress['uom'],
						"qty" 				=> $ress['qty'],
						"location_id" 		=> $ress['location_id'],
						"business_unit" 	=> $ress['business_unit'],
						"department" 		=> $ress['department'],
						"section" 			=> $ress['section'],
						"empno" 			=> $ress['empno'],
						"datetime_scanned" 	=> $ress['datetime_scanned'],
						"rack_desc"			=> $ress['rack_desc'],
					);

					echo json_encode($ress['id']);
					$this->db->set('qty', '0');
					$this->db->where($final_ress);
					$this->db->update($table);
					//UPDATE ONLY
					/*$this->db->where($final_ress);
					$this->db->delete('tbl_app_countdata_alturas');*/
				endforeach;
			}else{
				echo "No duplicate";
			}	
		} else {
			echo "No transaction found!";
		}
	}


	public function updateDuplicateCountItemAD()
	{
		$this->updateDuplicateCountItem("tbl_app_countdata");
	}
	public function updateDuplicateCountItemAC()
	{
		$this->updateDuplicateCountItem("tbl_advance_count");
	}
	public function updateDuplicateNFItemAD()
	{
		$this->updateDuplicateNFItem("tbl_app_advNfitem");
	}
	public function updateDuplicateNFItemAC()
	{
		$this->updateDuplicateNFItem("tbl_app_nfitem");
	}

	public function Testingta()
	{
		$where = array("id"  => "199");				
		$this->db->select('empno, datetime_scanned');
		$this->db->from('tbl_app_advnfitem');
		$this->db->where($where);
		$query = $this->db->get();
		$result = $query->result_array();
		if (isset($result)) {
			/*if(sizeof($result) > 0){
				$nfres = true;
			}else{
				$nfres = $this->db->insert('tbl_app_advnfitem', $nffinal_ress);
			}*/
			echo json_encode($result);
		}else{
			echo "no data";
		}
	}
}