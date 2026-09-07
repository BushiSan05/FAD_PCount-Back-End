
<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Appm extends CI_Model
{
	function __construct()
	{
		parent::__construct();
	}

	//PCOUNT
	public function getFilteredItemMasterfile()
	{
		$this->db->select('id,byCategory,categoryName,byVendor,vendorName,type as ctype,location_id, countType, batchDate');
		$this->db->from('tbl_nav_count');
		// $this->db->where('byCategory',true);
		$query = $this->db->get();
		$res = $query->result_array();
		if (isset($res)) {
			return $res;
		}
	}

	public function getItemMasterfileCount()
	{
		$count = $this->db->from("tbl_item_masterfile")->count_all_results();
		// $count = $this->db->from("item_masterfile_bardcode_test")->count_all_results();
		return $count;
	}

	public function getNavDataMasterfileCount()
	{
		$count = $this->db->from("tbl_nav_upload")->count_all_results();
		return $count;
	}

	public function getNavCountMasterfileCount()
	{
		$count = $this->db->from("tbl_nav_countdata")->count_all_results();
		return $count;
	}

	public function getItemMasterfileOffset_mod($offset)
	{
		// $this->db->select('item_no, barcode_no, show_item, description, variant_code, uom');
		// $this->db->select('item_code, barcode, desc, extended_desc, uom, vendor_name,category,group as ggroup,conversion_qty,variant_code,conversion_uom,price');
		$this->db->select('item_code, barcode, desc, extended_desc, uom, vendor_name, vendor_code, category, group as ggroup, conversion_qty, variant_code, price, variant_description');
		$this->db->from('tbl_item_masterfile');
		$this->db->offset($offset);
		$this->db->limit(50000);
		$this->db->order_by('tbl_item_masterfile.barcode', 'asc');
		$query = $this->db->get();
		$res = $query->result_array();
		if (isset($res)) {
			return $res;
		}
	}

	public function getNavDataMasterfileOffset_mod($offset)
	{
		$this->db->select('id, company, business_unit, department, section, type');
		$this->db->from('tbl_nav_upload');
		$this->db->offset($offset);
		$this->db->limit(50000);
		$this->db->order_by('tbl_nav_upload.id', 'asc');
		$query = $this->db->get();
		$res = $query->result_array();
		if (isset($res)) {
			return $res;
		}
	}

	public function getNavCountMasterfileOffset_mod($offset)
	{
		$this->db->select('id, itemcode, variant_code, description, uom, qty, batch_id');
		$this->db->from('tbl_nav_countdata');
		$this->db->offset($offset);
		$this->db->limit(50000);
		$this->db->order_by('tbl_nav_countdata.id', 'asc');
		$query = $this->db->get();
		$res = $query->result_array();
		if (isset($res)) {
			return $res;
		}
	}

	public function getUnit_mod($haveFilter, $filters)
	{
		$this->db->select('uom');
		$this->db->from('tbl_item_masterfile');
		$this->db->group_by('tbl_item_masterfile.uom');
		//	$this->db->order_by('tbl_item_masterfile.barcode','asc');
		if ($haveFilter == 'true') {
			$this->db->where('group', $filters);
		}
		$query = $this->db->get();
		$res = $query->result_array();
		return $res;
	}

	public function getVendor_mod($haveFilter, $filters)
{
    $this->db->select('vendor_name, vendor_code'); // Select vendor_name and vendor_code
    $this->db->from('tbl_item_masterfile'); // From tbl_item_masterfile
    $this->db->group_by(['vendor_name', 'vendor_code']); // Group by both vendor_name and vendor_code
    // $this->db->order_by('vendor_name', 'asc'); // Uncomment if you want to order results by vendor_name

    if ($haveFilter == 'true') {
        $this->db->where('group', $filters); // Apply filter if needed
    }

    $query = $this->db->get(); // Execute the query
    $res = $query->result_array(); // Get the results as an array
    return $res; // Return the result
}


	public function getUserMasterfile()
	{
		$this->db->select('uappid, emp_id, emp_no, emp_pin, name, position, location_id, done, locked');
		$this->db->from('tbl_app_user');
		$this->db->order_by('tbl_app_user.uappid', 'asc');
		$query = $this->db->get();
		$res = $query->result_array();
		if (isset($res)) {
			return $res;
		}
	}

	public function getAdmin($haveFilter, $filters)
	{
		if ($haveFilter == 'True') {
			$this->db->select('id, emp_id, emp_no, emp_pin, usertype, emp_name, business_unit');
			$this->db->from('tbl_app_admin');
			$this->db->order_by('tbl_app_admin.id', 'asc');
			$this->db->where('usertype', $filters);
			$query = $this->db->get();
			$res = $query->result_array();
			if (isset($res)) {
				return $res;
			}
		}else{
			return "No Data";
		}
	}

	public function CheckUpdate($version)
	{
		$apkVersion = array(
			'version' => "1.1",
			'url'     => "http://172.16.43.154:88/application/MyNet_PCount_V1.1.apk"
		);
		$previousVersion = (float)$version;
		$latestVersion = (float)$apkVersion['version'];

		if ($version != '') {
			if($latestVersion > $previousVersion){
				return $apkVersion;
			}else{
				return "Uptodate";
			}
		}else{
			return "No Data";
		}
	}

	public function getAuditMasterifle()
	{
		$this->db->select('auappid, emp_id, emp_no, emp_pin, name, position, location_id');
		$this->db->from('tbl_app_audit');
		$this->db->order_by('tbl_app_audit.auappid', 'asc');
		$query = $this->db->get();
		$res = $query->result_array();
		if (isset($res)) {
			return $res;
		}
	}

	public function getLocationMasterfile()
	{
		$this->db->select('location_id, company, business_unit, department, section, rack_desc');
		$this->db->from('tbl_location');
		$this->db->order_by('tbl_location.location_id', 'asc');
		$query = $this->db->get();
		$res = $query->result_array();
		if (isset($res)) {
			return $res;
		}
	}

	public function checkIfFloorPlanIsCreated()
	{
		$count = $this->db->from("tbl_location_floor_plan")->count_all_results();
		return $count;
	}

	public function insertCountData($itemcode, $barcode, $description, $uom, $qty, $business_unit, $department, $section, $rack_desc, $empno, $datetime_scanned, $datetime_saved)
	{
		$data =  (object) array(
			'itemcode' 		    => $itemcode,
			'barcode'           => $barcode,
			'description'       => $description,
			'uom'               => $uom,
			'qty'		        => $qty,
			'business_unit'	    => $business_unit,
			'department'        => $department,
			'section'           => $section,
			'rack_desc'         => $rack_desc,
			'empno'             => $empno,
			'datetime_scanned'  => $datetime_scanned,
			'datetime_saved'    => $datetime_saved,
			'datetime_exported' => date("Y-m-d h:i:s")
		);

		$this->db->insert('tbl_app_countdata', $data);

		$query = $this->db->affected_rows();
		if ($query == 1) {
			return $data;
		} else {
			return "failed insert";
		}
	}

	public function insertFloorPlanData($location_id, $company, $business_unit, $department, $section, $rack_desc, $xP, $yP, $he, $we, $isLandscape)
	{
		$data =  (object) array(
			'location_id' 		 => $location_id,
			'company'        	 => $company,
			'business_unit'      => $business_unit,
			'department'         => $department,
			'section'		     => $section,
			'rack_desc'	 		 => $rack_desc,
			'xP'    		     => $xP,
			'yP'        		 => $yP,
			'he'      			 => $he,
			'we'          		 => $we,
			'isLandscape'        => $isLandscape
		);

		$this->db->truncate('tbl_location_floor_plan');
		$this->db->insert('tbl_location_floor_plan', $data);

		$query = $this->db->affected_rows();
		if ($query == 1) {
			return $data;
		} else {
			return "failed insert";
		}
	}

	public function updateFloorPlanStatus($department, $section, $rack_desc)
	{
		$data	= array(
			"isDone" => 1,
		);
		$this->db->where('department', $department);
		$this->db->where('section', $section);
		$this->db->where('rack_desc', $rack_desc);
		$res = $this->db->update('tbl_location_floor_plan', $data);
		if ($res) {
			return "done";
		}
	}

	public function getFloorPlanData()
	{
		$this->db->select('*');
		$this->db->from('tbl_location_floor_plan');
		$this->db->order_by('tbl_location_floor_plan.location_id', 'asc');
		$query = $this->db->get();
		$res = $query->result_array();
		if (isset($res)) {
			return $res;
		}
	}

	public function updateUserStatus_mod($locationId)
	{
		$this->db->set('done', 'true');
		$this->db->set('locked', 'true');
		$this->db->where('location_id', $locationId);
		$this->db->update('tbl_app_user');
	}

	public function updateAuditStatus_mod($locationId)
	{

		$this->db->set('done', 'true');
		$this->db->where('location_id', $locationId);
		$this->db->update('tbl_app_audit');
	}

	public function updateLocationStatus_mod($locationId)
	{
		
		$this->db->set('done', 'true');
		$this->db->where('location_id', $locationId);
		$this->db->update('tbl_location');
	}
	//kulang
	// public function updateSignature($locationid, $usersig, $auditsig)
	// {
	// 	$this->db->set('user_signature', $usersig);
	// 	$this->db->set('audit_sognature', $auditsig);
	// 	$this->db->where('location_id', $locationid);
	// 	$this->db->update('tbl_app_countdata');
	// }
	
	// public function updateSignature($locationId, $user_signature)
	// {
	// 	$this->db->set('user_signature', $user_signature);
	// 	$this->db->where('location_id', $locationId);
	// 	$this->db->update('tbl_app_user');
	
	// 	$this->db->set('audit_signature', $user_signature); 
	// 	$this->db->where('location_id', $locationId);
	// 	$this->db->update('tbl_app_audit');
	// }

		public function updateSignature($locationId, $user_signature)
	{
		// Update the user_signature in the tbl_app_user table
		$this->db->set('user_signature', $user_signature);
		$this->db->where('location_id', $locationId);
		if (!$this->db->update('tbl_app_user')) {
			// Handle error if the update fails in tbl_app_user
			log_message('error', 'Failed to update user_signature in tbl_app_user for locationId: ' . $locationId);
			return false;
		}

		// Update the audit_signature in the tbl_app_audit table
		$this->db->set('audit_signature', $user_signature); 
		$this->db->where('location_id', $locationId);
		if (!$this->db->update('tbl_app_audit')) {
			// Handle error if the update fails in tbl_app_audit
			log_message('error', 'Failed to update audit_signature in tbl_app_audit for locationId: ' . $locationId);
			return false;
		}

		return true; // Success
	}


	public function insertAdvanceCount(){

	}

	public function insertNFAdvanceCount(){
		
	}

	public function checkDuplicateAndDelete($id)
	{
		//$this->db->query("DELETE FROM tbl_app_countdata_alturas WHERE id = $id");
		$this -> db -> where('id', $id);
    	$this -> db -> delete('tbl_app_countdata');
	}
}
