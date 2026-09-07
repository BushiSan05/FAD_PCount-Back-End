<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AppModel extends CI_Model
{

	// public function app_countbcode_mod(){
	// 	$barcode = $this->db->count_all_results('asset_bcode');
	// 	echo $barcode;
	// }

    // public function app_bcode_mod(){
		
	// 	// $result = array();
	// 	$this->db->select('id, barcode, description, adate, ast_type, cat_type, est_life, locname, dept');
	// 	$this->db->from('barcodes.asset_bcode');
	// 	$this->db->order_by('id', 'DESC');

	// 	$query = $this->db->get();
	// 	$data = $query->result_array();
	// 	$post_data = [];

	// 	 foreach($data as $value){   
	// 		$post_data[] = [
			   
	// 			'd_id' => $value['id'],
	// 			'd_barcode' => $value['barcode'],
	// 			'd_description' => $value['description'],
	// 			'd_adate' => $value['adate'],
	// 			'd_ast_type' => $value['ast_type'],
	// 			'd_cat_type' => $value['cat_type'],
	// 			'd_est_life' => $value['est_life'],
	// 			'd_locname' => $value['locname'],
	// 			'd_dept' => $value['dept'],
	// 		];
	// 	}
	// 	$item = array('bcode'=>$post_data);
	// 	echo json_encode($item);
	// }

	
	public function getItemMasterfileCount()
	{
		$count = $this->db->from("masterfile")->count_all_results();
		return $count;
	}


	public function getItemMasterfileOffset_mod($offset, $limit = 10000)
	{
		$this->db->select('id, barcode, desc, acost, adate, cat_type, locname, dept, status, barpost');
		$this->db->from('masterfile');
		$this->db->order_by('id', 'asc'); // stable ordering
		$this->db->limit($limit, $offset); // offset, limit
		$query = $this->db->get();
		return $query->result_array();
	}	

	public function getAssetTypesOffset_mod($offset)
	{
		$this->db->select('id, asset_type');
		$this->db->from('asset_types');
		$this->db->order_by('id', 'asc'); // stable ordering
		$query = $this->db->get();
		return $query->result_array();
	}

	public function getCatTypesOffset_mod($offset)
	{
		$this->db->select('id, cat_type');
		$this->db->from('cat_types');
		$this->db->order_by('id', 'asc'); // stable ordering
		$query = $this->db->get();
		return $query->result_array();
	}

	public function getSourceOffset_mod($offset, $limit = 10000)
	{
		$this->db->select('id, locname, dept, barpost, cas_barpost');
		$this->db->from('source_barpost');
		$this->db->order_by('id', 'asc'); // stable ordering
		$this->db->limit($limit, $offset); // offset, limit
		$query = $this->db->get();
		return $query->result_array();
	}	


	public function getUsersCount()
	{
		$count = $this->db->from("users")->count_all_results();
		return $count;
	}


	public function getUsers()
	{
		$this->db->select('id, username, password, fullname');
		$this->db->from('users');
		$this->db->order_by('id', 'asc');
		$query = $this->db->get();
		$res = $query->result_array();
		if (isset($res)) {
			return $res;
		}
	}
	

}