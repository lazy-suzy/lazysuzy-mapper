<?php
class Common_model extends CI_Model 
{
 	// Constructor 
 	function __construct()
	{
		parent::__construct();
	}
	/**
	 * INSERT data into table model
	 * 
	 * @access Public
	 * @param $tableName - Name of the table(required)
	 * @param $data - Specifies the insert data(required)
	 * @return Last insert ID
	 */
	public function insertTableData($tableName = '', $data = array())
	{
		$this->db->insert($tableName, $data);
		return $this->db->insert_id();
	}
	/**
	 * DELETE data from table
	 * 
	 * @access Public
	 * @param $tableName - Name of the table(required)
	 * @param $where - Specifies the which row will be delete(optional)
	 * @return Affected rows
	 */
	public function deleteTableData($tableName = '', $where = array())
	{
		if ((is_array($where)) && (count($where) > 0)) {
			$this->db->where($where);
		}
		$this->db->delete($tableName);
		return $this->db->affected_rows();
	}
	/**
	 * UPDATE data to table
	 * 
	 * @access Public
	 * @param $tableName - Name of the table(required)
	 * @param $where - Specifies the where to update(optional)
	 * @param $data - Modified data(required) 
	 * @return Affected rows
	 */
	public function updateTableData($tableName = '', $where = array(), $data = array())
	{
		if ((is_array($where)) && (count($where) > 0)) {
			$this->db->where($where);
		}
		return $this->db->update($tableName, $data);
	}
	/**
	 * SELECT data from table
	 * 
	 * @access Public
	 * @param $tableName - Name of the table(required)
	 * @param $where - Specifies the where to update(optional)
	 * @param $data - Modified data(required) 
	 * @return Affected rows
	 */
	public function getTableData($tableName = '', $where = array(), $selectFields = '', $like = array(), $where_or = array(), $like_or = array(), $offset = '', $limit = '', $orderBy = array(), $groupBy = array(), $where_not = array(), $where_in = array())
	{
		// WHERE AND conditions
		if ((is_array($where)) && (count($where) > 0)) {
			$this->db->where($where);
		}
		// WHERE NOT conditions
		if ((is_array($where_not)) && (count($where_not) > 0)) {
			//echo "<pre>";
			//print_r($where_not);die;
			$this->db->where_not_in($where_not[0],$where_not[1]);
		}
		// WHERE IN conditions
		if ((is_array($where_in)) && (count($where_in) > 0)) {
			$this->db->where_in($where_in[0],$where_in[1]);
		}
		// WHERE OR conditions
		if ((is_array($where_or)) && (count($where_or) > 0)) {
			$this->db->or_where($where_or);
		}
		// $this->db->group_start();
		//LIKE AND 
		if ((is_array($like)) && (count($like) > 0)) {
			$this->db->like($like);
		}
		//LIKE OR 
		if ((is_array($like_or)) && (count($like_or) > 0)) {
			$this->db->or_like($like_or);
		}
		// $this->db->group_end();
		//SELECT fields
		if ($selectFields != '') {
			$this->db->select($selectFields);
		}
		//Group By
		if (is_array($groupBy) && (count($groupBy) > 0)) {
			$this->db->group_by($groupBy[0]);
		}
		//Order By
		if (is_array($orderBy) && (count($orderBy) > 0)) {
			if(count($orderBy) > 2)
			{
				$this->db->order_by($orderBy[0].' '.$orderBy[1].','.$orderBy[2].' '.$orderBy[3]);
			}
			else
			{
				$this->db->order_by($orderBy[0], $orderBy[1]);
			}
		}
		//OFFSET with LIMIT
		if($limit != '' && $offset != ''){
			$this->db->limit($limit, $offset);
		}
		// LIMIT
		if($limit != '' && $offset == ''){
			$this->db->limit($limit);
		}
		
		return $this->db->get($tableName);
		
	} 


	public function getData($rowno,$rowperpage='',$product_name='',$product_sku='',$product_category_src='') 
	{
	    $site_id      = $this->session->userdata('site_id');

	    $sitedetails  = $this->getTableData('fl_site_list',array('site_id'=>$site_id))->result();
		$table_name   = $sitedetails[0]->product_tb_name;

		if($product_name!='')
		{
			$this->db->like('product_name', urldecode($product_name));
		}
		if($product_sku!='')
		{
			$this->db->like('product_sku', urldecode($product_sku));
		}
		if($product_category_src!='')
		{
			$this->db->like('product_category', urldecode($product_category_src));
		}

	    $this->db->select('*');
	    $this->db->from($table_name);
	    $this->db->limit($rowperpage, $rowno);  
	    $query = $this->db->get();

	    //echo $this->db->last_query();exit;
	 
	    return $query->result_array();
	}

  	// Select total records
  	public function getrecordCount($product_name='',$product_sku='',$product_category_src='') 
  	{	

  		$site_id      = $this->session->userdata('site_id');
  		$sitedetails = $this->getTableData('fl_site_list',array('site_id'=>$site_id))->result();
		$table_name   = $sitedetails[0]->product_tb_name;

		if($product_name!='')
		{
			$this->db->like('product_name', urldecode($product_name));
		}
		if($product_sku!='')
		{
			$this->db->like('product_sku', $product_sku);
		}
		if($product_category_src!='')
		{
			$this->db->like('product_category', urldecode($product_category_src));
		}

	    $this->db->select('count(*) as allcount');
	    $this->db->from($table_name);
	    $query = $this->db->get();
	    $result = $query->result_array();
	 	//echo $this->db->last_query();exit; 
	    return $result[0]['allcount'];
	}


	public function newgetData($rowno,$rowperpage,$product_sku='',$product_category_src='') 
	{
 
	    $site_url_id    = $this->session->userdata('site_url_id');
  		$sitedetails 	= $this->getTableData('fl_site_list',array('site_id'=>$site_url_id))->result();
		$table_name   	= $sitedetails[0]->url_tb_name;

		 
		if($product_sku!='')
		{
			$this->db->or_like('product_name', urldecode($product_sku));
			$this->db->or_like('product_sku', urldecode($product_sku));
			$this->db->or_like('product_url', urldecode($product_sku));
		}
		if($product_category_src!='')
		{
			$this->db->like('product_category', urldecode($product_category_src));
		}


	    $this->db->select('*');
	    $this->db->from($table_name);
	    $this->db->limit($rowperpage, $rowno);  
	    $query = $this->db->get();
	 	//echo $this->db->last_query(); exit;
	    return $query->result_array();
	}

  	// Select total records
  	public function newgetrecordCount($product_sku='',$product_category_src='') 
  	{
	    $site_url_id    = $this->session->userdata('site_url_id');
  		$sitedetails 	= $this->getTableData('fl_site_list',array('site_id'=>$site_url_id))->result();
		$table_name   	= $sitedetails[0]->url_tb_name;

		if($product_sku!='')
		{
			$this->db->or_like('product_name', urldecode($product_sku));
			$this->db->or_like('product_sku',  urldecode($product_sku));
			$this->db->or_like('product_url',  urldecode($product_sku));
		}
		if($product_category_src!='')
		{
			$this->db->like('product_category', urldecode($product_category_src));
		}

	    $this->db->select('count(*) as allcount');
	    $this->db->from($table_name);
	    $query = $this->db->get();
	    $result = $query->result_array();
	 
	    return $result[0]['allcount'];
	}  


	//get all product details using filter 9-11-18
	public function getall_recordCount($product_name='',$product_sku='',$product_category_src='',$table_name='') 
  	{	
  		$sitedetails  = $this->getTableData('fl_site_list',array('site_id!='=>''))->result();
		//$table_name   = $sitedetails[0]->product_tb_name;

		if($product_name!='')
		{
			$product_name_like = "AND product_name like '%".urldecode($product_name)."%' ";
		}

		if($product_sku!='')
		{
			$product_sku_like .= "AND product_sku like '%".urldecode($product_sku)."%' ";
		}

		if($product_category_src!='')
		{	
			$product_category_like .= "AND product_category like '%".urldecode($product_category_src)."%' ";
		}

		if($table_name)
		{
			 $query = $this->db->query("SELECT (SELECT COUNT(*) FROM ".$table_name." where id > 0 ".$product_name_like." ".$product_sku_like." ".$product_category_like.") as table1Count")->result_array();

			 $result = $query;
	    	$total_count = $result[0]['table1Count'];
		}
		else
		{
			 $query = $this->db->query("SELECT (SELECT COUNT(*) FROM ".$sitedetails[0]->product_tb_name." where id > 0
			 ".$product_name_like." ".$product_sku_like." ".$product_category_like.") as table1Count, (SELECT COUNT(*) FROM ".$sitedetails[1]->product_tb_name." where id > 0 ".$product_name_like." ".$product_sku_like." ".$product_category_like.") as table2Count, (SELECT COUNT(*) FROM ".$sitedetails[2]->product_tb_name." where id > 0 ".$product_name_like." ".$product_sku_like." ".$product_category_like.") as table3Count")->result_array();
			 $result = $query;
	    	$total_count = ($result[0]['table1Count'] + $result[0]['table2Count'] + $result[0]['table3Count']);
		}
	    
	  

	    
	    return $total_count;
	}

	public function get_allData($rowno,$rowperpage='',$product_name='',$product_sku='',$product_category_src='',$table_name='') 
	{
		if($product_name!='')
		{
			$product_name_like = "AND product_name like '%".urldecode($product_name)."%' ";
		}

		if($product_sku!='')
		{
			$product_sku_like .= "AND product_sku like '%".urldecode($product_sku)."%' ";
		}

		if($product_category_src!='')
		{	
			$product_category_like .= "AND product_category like '%".urldecode($product_category_src)."%' ";
		}


		if($table_name)
		{
			$query = $this->db->query("SELECT product_name,product_sku,color,product_url,product_category,price,id FROM 
			".$table_name."  where id > 0 ".$product_name_like." ".$product_sku_like." ".$product_category_like."
			LIMIT $rowno, $rowperpage")->result_array();
		}
		else
		{
			 $query = $this->db->query("SELECT product_name,product_sku,color,product_url,product_category,price,id FROM ".$sitedetails[0]->product_tb_name." where id > 0 ".$product_name_like." ".$product_sku_like." ".$product_category_like."  UNION SELECT product_name,product_sku,color,product_url,product_category,price,id FROM ".$sitedetails[1]->product_tb_name." where id > 0 ".$product_name_like." ".$product_sku_like." ".$product_category_like." UNION SELECT product_name,product_sku,color,product_url,product_category,price,id FROM ".$sitedetails[2]->product_tb_name." where id > 0 ".$product_name_like." ".$product_sku_like." ".$product_category_like." LIMIT $rowno, $rowperpage")->result_array();
		}

	    
	  
	 
	    //echo $this->db->last_query();exit;
	    return $query;

	}



	//get all product details using filter in frontend page 12-11-18
	public function getall_recordCount_front($product_name='') 
  	{	
  		$sitedetails  = $this->getTableData('fl_site_list',array('site_id!='=>''))->result();

		if($product_name!='')
		{
			$product_name_like = " AND product_name like '%".urldecode($product_name)."%' OR product_sku like '%".urldecode($product_name)."%' OR product_category like '%".urldecode($product_name)."%' OR price like '%".urldecode($product_name)."%'";
		}

		$query = $this->db->query("SELECT product_name,product_sku,color,product_url,product_category,price,id,images,main_product_images,was_price,site_name,product_images,product_description,product_feature,product_diemension FROM ".$sitedetails[0]->product_tb_name." where id > 0 ".$product_name_like." group by product_sku

	  	UNION SELECT product_name,product_sku,color,product_url,product_category,price,id,images,main_product_images,was_price,site_name,product_images,product_description,product_feature,product_diemension FROM ".$sitedetails[1]->product_tb_name." where id > 0 ".$product_name_like." group by product_name 

	  	UNION SELECT product_name,product_sku,color,product_url,product_category,price,id,images,main_product_images,was_price,site_name,product_images,product_description,product_condition as product_feature,product_diemension FROM ".$sitedetails[2]->product_tb_name." where id > 0 ".$product_name_like." group by product_name")->num_rows();
		
		//echo $this->db->last_query(); 
		$total_count = $query;
	    
	    //$total_count = ($result[0]['table1Count'] + $result[0]['table2Count'] + $result[0]['table3Count']);
	   
	    return $total_count;
	}

	public function get_allData_front($rowno,$rowperpage='',$product_name='') 
	{
		$sitedetails  = $this->getTableData('fl_site_list',array('site_id!='=>''))->result();

		if($product_name!='')
		{
			$product_name_like = "AND product_name like '%".urldecode($product_name)."%' OR product_sku like '%".urldecode($product_name)."%' OR product_category like '%".urldecode($product_name)."%' OR price like '%".urldecode($product_name)."%'";
		}

		 
		$query = $this->db->query("SELECT product_name,product_sku,color,product_url,product_category,price,id,images,main_product_images,was_price,site_name,product_images,product_description,product_feature,product_diemension FROM ".$sitedetails[0]->product_tb_name." where id > 0 ".$product_name_like." group by product_sku

	  UNION SELECT product_name,product_sku,color,product_url,product_category,price,id,images,main_product_images,was_price,site_name,product_images,product_description,product_feature,product_diemension FROM ".$sitedetails[1]->product_tb_name." where id > 0 ".$product_name_like." group by product_name 

	  UNION SELECT product_name,product_sku,color,product_url,product_category,price,id,images,main_product_images,was_price,site_name,product_images,product_description,product_condition as product_feature,product_diemension FROM ".$sitedetails[2]->product_tb_name." where id > 0 ".$product_name_like." group by product_name LIMIT $rowno, $rowperpage")->result();
		 
	    return $query;

	}

}