<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

	public function __construct()
	{	
		parent::__construct();		
		$this->load->library(array('form_validation','csvimport'));
		$this->load->helper(array('url', 'language'));	
		$this->load->library('pagination');
		$this->load->library('upload');
	}

	public function index()
	{
		$this->login();
	}


	function login()
	{
		//If Already logged in
		$sessionvar=$this->session->userdata('loggeduser');
		if($sessionvar!="")
		{	
			admin_redirect('dashboard', 'refresh');
		}

		// When Post
		if ($this->input->post()) 
		{ 
			// Login credentials
			$username = $this->input->post('username');
			$password = $this->input->post('password');
			$remember = $this->input->post('remember');
			
			// Login using Ion Auth
			$login = $this->common_model->getTableData('fl_users',array('username'=>$username,'password'=>md5($password)));
			//echo $this->db->last_query();die;
			if ($login->num_rows()==1) 
			{ 
				$session_data = array('loggeduser'=> $login->row('userid'));
				$this->session->set_userdata($session_data);
			    $this->session->set_flashdata('success', 'You\'re logged in successfully!');
				admin_redirect('dashboard', 'refresh');
			} 
			else 
			{ 	
				$this->session->set_flashdata('error', 'Invalid email or password!');
				admin_redirect('', 'refresh');
			}
		

		}
		$data['action'] = admin_url() . 'login';
		$data['title'] = 'Admin Login';
		$data['type']='login';
		$data['meta_keywords'] = 'Admin Login Keywords';
		$data['meta_description'] = 'Admin Login Description';
		$this->load->view('admin/login', $data);
	}

	function dashboard()
	{
		//If login
		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		else {
			
            $user           				= $this->common_model->getTableData('fl_users', array());
            $user_details   				= $user->result();
            $data['username'] 				= $user_details[0]->username;
            
			$data['site_details'] 			= $this->common_model->getTableData('fl_site_list','','','');
			$data['varWelcomeHeaderTitle'] 	= "Dashboard";
			$data['varSmallHeaderTitle'] 	= "";
			$data['arrBreadCrumbInfo'] 		= array("Home"=>"#","Dashboard"=>"");

			$data['title'] 					= 'Dashboard';
			$data['meta_keywords'] 			= 'Dashboard';
			$data['meta_description'] 		= 'Dashboard';
			$data['main_content'] 			= 'admin/dashboard';
			$this->load->view('admin/dashboard', $data); 
		}
	}

	function logout() {
		$this->session->sess_destroy();
		admin_redirect('', 'refresh');
	}

	function forget_password() 
	{
		
		$sessionvar=$this->session->userdata('loggeduser');
		if($sessionvar!="")
		{	
			admin_redirect('admin/dashboard', 'refresh');
		}
		 
		if($this->input->post()) 
		{
			$email 	  = $this->input->post('email');
			$identity = $this->common_model->getTableData('fl_users',array('username'=>$email));
			
			if($identity->num_rows()==0) 
			{
				$this->session->set_flashdata('error', 'It looks like you doesn\'t in our database.');
				admin_redirect('forget_password', 'refresh');
			}
			else
			{
				$to      		= $email;
				$email_template = 1;
				$special_vars 	= array(						
				'###USERNAME###' => $identity->row('admin_name'),
				'###PASSWORD###' => decryptIt($identity->row('password'))
				);	

				if ($this->email_model->sendMail($to, '', '', $email_template, $special_vars)) 
				{
					$this->session->set_flashdata('success', 'We have send you an email for reset your password');
					admin_redirect('', 'refresh');
				} 
				else 
				{
					$this->session->set_flashdata('error', 'Problem occure with your forget password');
					admin_redirect('forget_password', 'refresh');
				}
			}
		}

		$data['action'] = admin_url() . 'forget_password';
		$data['title'] = 'Forget Password';
		$data['type']='forget';
		$data['meta_keywords'] = 'Forget Password Keywords';
		$data['meta_description'] = 'Forget Password Description';
		$this->load->view('admin/forget_password', $data);
	}

	function category($type='')
	{
		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		else 
		{
			if($type == 'add')
			{
				if($this->input->post('category_submit')) 
				{	
					//echo "<pre>";print_r($_POST); exit;

					$insertData=array();
					
					$insertData['category_name'] = ucfirst($this->input->post('cat_name')); 
					$insertData['category_slug'] = $this->input->post('cat_name'); 
					$insertData['parent_id'] 	 = $this->input->post('cat_root_name');
					$insertData['category_code'] = md5($this->input->post('cat_name'));
					$insertData['created_on'] 	 = date('Y-m-d H:i:s'); 


					$insert = $this->common_model->insertTableData('dsb_category', $insertData);
					if ($insert) {
						$this->session->set_flashdata('success', 'New Category has been added successfully!');
						admin_redirect('category', 'refresh');
					} else {
						$this->session->set_flashdata('error', 'Unable to add the new Category!');
						admin_redirect('category/add', 'refresh');
					}
				}
				 

				$cat_details   	 	= $this->common_model->getTableData('dsb_category', array());
            	$newcat_details   	= $cat_details->result();
            	$data['category'] 	= $newcat_details;
				$data['view'] = 'add';
			}
			else if($type == 'edit')
			{	
				$id = $this->uri->segment(4);
				$condition = array('category_id' => $id);
				$updateData=array();

				if($id == '') 
				{
					$this->session->set_flashdata('error', 'Invalid request');
					admin_redirect('category');
				}
				$isValid = $this->common_model->getTableData('dsb_category', array('category_id' => $id));

				if($isValid->num_rows() == 0)
				{
					$this->session->set_flashdata('error', 'Unable to find this page');
					admin_redirect('category');
				}
				//echo '<pre>';print_r($isValid->row()); exit;

				if($this->input->post('update_category_submit')) 
				{	
					$updateData['category_name'] = ucfirst($this->input->post('edit_cat_name')); 
					$updateData['category_slug'] = $this->input->post('edit_cat_name'); 
					$updateData['parent_id'] 	 = $this->input->post('edit_cat_root_name');
					$updateData['category_code'] = md5($this->input->post('edit_cat_name'));
					$updateData['created_on'] 	 = date('Y-m-d H:i:s'); 


					$update = $this->common_model->updateTableData('dsb_category',$condition,$updateData);
					if ($update) {
						$this->session->set_flashdata('success', ' Category has been Updated successfully!');
						admin_redirect('category', 'refresh');
					} else {
						$this->session->set_flashdata('error', 'Unable to Edit the Category!');
						admin_redirect('category/edit', 'refresh');
					}
				}
				        
				$cat_details   	 		= $this->common_model->getTableData('dsb_category', array());
            	$newcat_details   		= $cat_details->result();
            	$data['category'] 		= $newcat_details;
				$data['edit_category']  = $isValid->row();
				$data['view'] 			= 'edit';

			}
			else
			{
				$data['view'] = 'view_all';
				$category_id=0;
				$argLimit=false;
				$order='';
				$varWhere = "";
			
				$varLimit ='';
				if($argLimit == true){
					$varLimit = ' LIMIT '.$this->_startIndex.','.$this->_resultIndex;
				}

				$varOrder = "";
				if(!empty($this->_order_by) && !empty($this->_sort_by)){
					$varOrder .= " ORDER BY ".$this->_order_by." ".strtoupper($this->_sort_by);
				} else {
					$varOrder=' ORDER BY ca.category_id DESC ';
				}
			
				$varQuery = "select ca.*,ca1.category_name as parent_name from dsb_category as ca left join dsb_category as ca1 on ca1.category_id=ca.parent_id ".$varWhere.$varOrder.$varLimit;

				$result = $this->db->query($varQuery);

				$data['cat_details'] = $result->result();
			}
			
            $user           	= $this->common_model->getTableData('fl_users', array());
            $user_details   	= $user->result();
            $data['username'] 	= $user_details[0]->username;
            $data['site_details'] = $this->common_model->getTableData('fl_site_list','','','');
			$data['varWelcomeHeaderTitle'] = "Category Management";
			$data['varSmallHeaderTitle'] = "";
			$data['arrBreadCrumbInfo'] = array("Home"=>"#","Category Management"=>"");

			$data['title'] = 'Category Management';
			$data['meta_keywords'] = 'Category Management';
			$data['meta_description'] = 'Category Management';
			$data['main_content'] = 'admin/dashboard';
			$data['cancel_url'] = admin_url().'category';
			
			$this->load->view('admin/category', $data); 
		}
	}

	function delete_category($id)
	{
		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		// Is valid
		if ($id == '') {
			$this->session->set_flashdata('error', 'Invalid request');
			admin_redirect('category');
		}
		$isValid = $this->common_model->getTableData('dsb_category', array('category_id' => $id))->num_rows();
		if ($isValid > 0) { // Check is valid 
			$condition = array('category_id' => $id);
			$delete = $this->common_model->deleteTableData('dsb_category', $condition);
			if ($delete) { // True // Delete success
				$this->session->set_flashdata('success', 'Category deleted successfully');
				admin_redirect('category');
			} else { //False
				$this->session->set_flashdata('error', 'Problem occure with Category deletion');
				admin_redirect('category');	
			}
		} else {
			$this->session->set_flashdata('error', 'Unable to find this page');
			admin_redirect('category');
		}
	}


	function current_url()
	{
    	$CI =& get_instance();
    	$url = $CI->config->site_url($CI->uri->uri_string());
    	return $url;
	}


	function product_list($id)
	{
		$session_data = array('site_id'=> $id);
		$this->session->set_userdata($session_data);

		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		else 
		{	
			$cat_details   	 	= $this->common_model->getTableData('dsb_category', array());
    		$newcat_details   	= $cat_details->result();
    		$data['category'] 	= $newcat_details; 


			$sitedetails 	= $this->common_model->getTableData('fl_site_list',array('site_id'=>$id))->result();
			$table_name   	= $sitedetails[0]->product_tb_name;
			$site_name   	= $sitedetails[0]->site_name;

			$data['table_name'] = $table_name;
			$data['site_name'] = $site_name;


			$data['pro_id'] = $id;
			$data['view'] = 'view_all';
			/*$product_details    		= $this->common_model->getTableData($table_name, array());
        	$newproduct_details			= $product_details->result();
        	$data['product_details'] 	= $newproduct_details;*/
			 
			
			$user           	= $this->common_model->getTableData('fl_users', array());
            $user_details   	= $user->result();
            $data['username'] 	= $user_details[0]->username;
            $data['site_details'] = $this->common_model->getTableData('fl_site_list','','','');

            //echo '<pre>';print_r($sitedetails);exit;

            $data['varWelcomeHeaderTitle'] = "Products";
			$data['varSmallHeaderTitle'] = "";
			$data['arrBreadCrumbInfo'] = array("Home"=>"#","Products"=>"");


			$data['title'] = $site_name.' Product Details';
			$data['meta_keywords'] = $site_name.' Product Details';
			$data['meta_description'] = $site_name.' Product Details';

			$total_counts = $this->db->query("SELECT count(*) as counts FROM $table_name")->row();
			$total_records = $total_counts->counts;


			//$getpage = (!empty($this->input->get('page'))) ? $this->input->get('page') : 0;
			$getpage = $this->input->get('page');

			if($getpage > 0)
				$getpage = $getpage;
			else
				$getpage = 0;
			


			
			$per_row = 15;

			$start 	= $getpage * $per_row;
			$end 	= $per_row;
			$First	= 0;
			$Next 	= $getpage + 1;
			$Prev 	= $getpage - 1;
			$Last 	= $total_records - $per_row;


			if($this->input->get('page') == 'first')
			{
				$start 	= 0;
			}
			else if($this->input->get('page') == 'last')
			{
				$start 	= $Last;
			}

			$result = $this->db->query("SELECT * FROM $table_name LIMIT $start, $end")->result();
			
			$data['result'] = $result;
			$data['first'] = '<a href="'.$this->current_url().'?page=first" class="active">First</a>';
			$data['last'] = '<a href="'.$this->current_url().'?page=last" class="active">Last</a>';
			
			
			if($Prev > 0)
				$data['previous'] = '<a href="'.$this->current_url().'?page='.$Prev.'"> ← Prev </a>';
			else
				$data['previous'] = '<a href="'.$this->current_url().'"> ← Prev  </a>';


			$data['next'] = '<a href="'.$this->current_url().'?page='.$Next.'"> Next →  </a>';

			$this->load->view('admin/product', $data);
		}		
	}

	function product_list_1($id)
	{
		
		$session_data = array('site_id'=> $id);
		$this->session->set_userdata($session_data);

		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		else 
		{	
			$cat_details   	 	= $this->common_model->getTableData('dsb_category', array());
    		$newcat_details   	= $cat_details->result();
    		$data['category'] 	= $newcat_details; 


			$sitedetails 	= $this->common_model->getTableData('fl_site_list',array('site_id'=>$id))->result();
			$table_name   	= $sitedetails[0]->product_tb_name;


			$data['pro_id'] = $id;
			$data['view'] = 'view_all';
			$product_details    		= $this->common_model->getTableData($table_name, array());
        	$newproduct_details			= $product_details->result();
        	$data['product_details'] 	= $newproduct_details;
			 
			
			$user           	= $this->common_model->getTableData('fl_users', array());
            $user_details   	= $user->result();
            $data['username'] 	= $user_details[0]->username;
            $data['site_details'] = $this->common_model->getTableData('fl_site_list','','','');

            $data['varWelcomeHeaderTitle'] = "Products";
			$data['varSmallHeaderTitle'] = "";
			$data['arrBreadCrumbInfo'] = array("Home"=>"#","Products"=>"");


			$data['title'] = 'Product Details';
			$data['meta_keywords'] = 'Product Details';
			$data['meta_description'] = 'Product Details';
			
			$this->load->view('admin/product', $data); 
		}
	}


	function get_table_name($id)
	{
		$sitedetails = $this->common_model->getTableData('fl_site_list',array('site_id'=>$id))->row();
		return $sitedetails->product_tb_name;
	}

	function get_site_name($id)
	{
		$sitedetails = $this->common_model->getTableData('fl_site_list',array('site_id'=>$id))->row();
		return $sitedetails->site_name;
	}

	function products($type,$id,$page='')
	{
		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		else 
		{
			$this->load->library('upload');

			if($type == 'add')
			{
				if($this->input->post('category_submit')) 
				{	
					 
					$insertData=array();

					$site_id 			= $this->input->post('site_id');
					$site_name 			= $this->get_site_name($site_id);
					
					$files    = $_FILES;
	        		 
	        		$total_img_count 	= count($_FILES['pro_image']['name']); 

					for($i=0; $i<$total_img_count; $i++)
				    {           
	 					$_FILES['pro_image']['name'] 	= $files['pro_image']['name'][$i];
			            $_FILES['pro_image']['type'] 	= $files['pro_image']['type'][$i];
			            $_FILES['pro_image']['tmp_name']= $files['pro_image']['tmp_name'][$i];
			            $_FILES['pro_image']['error'] 	= $files['pro_image']['error'][$i];
			            $_FILES['pro_image']['size']    = $files['pro_image']['size'][$i];

			            $config['upload_path']          = '../'.$site_name;
		                $config['allowed_types']        = 'gif|jpg|jpeg|png'; //|pdf|doc

				        $this->upload->initialize($config);

					    $uploads 	= $this->upload->do_upload('pro_image');
				        $fileName 	= $_FILES['pro_image']['name'];
				        $images[] 	= $fileName;
				    }

	                if(!$uploads)
	                {
	                    $this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	                    $error = array('error' => $this->upload->display_errors());
	                    $this->session->set_flashdata('error', $this->upload->display_errors());
	                    admin_redirect('products/add/'.$site_id, 'refresh');
	                }
	                else
	                {
	                	$imagenames = implode(',',$images);

	                	//echo "<pre>";print_r($imagenames);exit;

	                	$insertData['product_images'] 	= $imagenames;

						$insertData['product_name'] 	= $this->input->post('pro_name'); 
						$insertData['product_sku'] 		= $this->input->post('pro_sku'); 
						$insertData['model_code'] 	 	= $this->input->post('model_code');
						$insertData['color'] 			= $this->input->post('color');
						$insertData['product_url'] 		= $this->input->post('pro_url'); 
						$insertData['product_category'] = $this->input->post('cat_name'); 
						$insertData['price'] 	 		= $this->input->post('pro_price');
						$insertData['created_date'] 	= date('Y-m-d H:i:s'); 

						$table_name = $this->get_table_name($site_id);

						if($site_id == 3)
							unset($insertData['model_code']); 


						$insert = $this->common_model->insertTableData($table_name, $insertData);
						if ($insert) {
							$this->session->set_flashdata('success', 'New Product has been added successfully!');
							admin_redirect('product_list/'.$site_id, 'refresh');
						} else {
							$this->session->set_flashdata('error', 'Unable to add the new Product details!');
							admin_redirect('products/add/'.$site_id, 'refresh');
						}
					}	
				}
				 
				$cat_details   	 	= $this->common_model->getTableData('dsb_category', array());
	        	$newcat_details   	= $cat_details->result();
	        	$data['category'] 	= $newcat_details;
				$data['view'] 		= 'add';
			}
			else if($type == 'edit')
			{	
				if($this->input->post('update_product_submit')) 
				{	
					$id 			= $this->input->post('edit_pro_id');
					$site_id 		= $this->input->post('edit_site_id');
					$edit_site_id 	= $this->input->post('edit_site_id');
					$table_name 	= $this->get_table_name($site_id);
					$site_name 		= $this->get_site_name($site_id);


					$condition  = array('id' => $id);
					$updateData = array();


					if($site_id != 3)
						$updateData['model_code'] 	 = $this->input->post('edit_model_code');

					$newfiles = $_FILES;

					if(!empty($_FILES['edit_pro_image']['name']))
					{
		        		$total_img_count = count($_FILES['edit_pro_image']['name']);

						for($i=0; $i<$total_img_count; $i++)
					    {           
		 						
		 					$imgname = $id.'_'.$i.'.jpg';

		 					$_FILES['edit_pro_image']['name'] 		= $imgname;
			            	$_FILES['edit_pro_image']['type'] 		= $newfiles['edit_pro_image']['type'][$i];
			            	$_FILES['edit_pro_image']['tmp_name']	= $newfiles['edit_pro_image']['tmp_name'][$i];
			            	$_FILES['edit_pro_image']['error'] 		= $newfiles['edit_pro_image']['error'][$i];
			            	$_FILES['edit_pro_image']['size']    	= $newfiles['edit_pro_image']['size'][$i];
		 				 
				            
				            $config['upload_path']          = '../'.$site_name.'/product_images/';
			                $config['allowed_types']        = 'gif|jpg|jpeg|png';
			                $config['overwrite'] 			= TRUE;

					        $this->upload->initialize($config);

						    $uploads 	= $this->upload->do_upload('edit_pro_image');
					        $fileName 	= $_FILES['edit_pro_image']['name'];
					        $images[] 	= $fileName;
					    }
					}    

	                	 
					$updateData['product_name'] 	= $this->input->post('edit_pro_name'); 
					$updateData['product_sku'] 		= $this->input->post('edit_pro_sku'); 
					$updateData['color'] 			= $this->input->post('edit_color');
					$updateData['product_url'] 		= $this->input->post('edit_pro_url'); 
					$updateData['product_category'] = $this->input->post('edit_cat_name'); 
					$updateData['price'] 	 		= $this->input->post('edit_pro_price');
					$updateData['created_date'] 	= date('Y-m-d H:i:s'); 

					if($page=='all')
					{
						$admin_redirect = 'view_all_products';
					}
					else
					{
						$admin_redirect  = 'product_list/'.$edit_site_id;
						$admin_redirect1 = 'products/edit/'.$edit_site_id.'/'.$id;
					}

					$update = $this->common_model->updateTableData($table_name,$condition,$updateData);
					if($update) 
					{
						$this->session->set_flashdata('success', ' Product details has been Updated successfully!');
						admin_redirect($admin_redirect, 'refresh');
					} else {
						$this->session->set_flashdata('error', 'Unable to Edit the Product details!');
						admin_redirect($admin_redirect1, 'refresh');
					}
				}
				else
				{  
					
					$id 		= $this->uri->segment(5); 
					$site_id 	= $this->uri->segment(4);
					$table_name = $this->get_table_name($site_id);
					$condition  = array('id' => $id);

					if($id == '') 
					{
						$this->session->set_flashdata('error', 'Invalid request');
						admin_redirect('category');
					}

					$isValid = $this->common_model->getTableData($table_name, $condition);

					if($isValid->num_rows() == 0)
					{
						$this->session->set_flashdata('error', 'Unable to find this page');
						admin_redirect('category');
					}


					$cat_details   	 		= $this->common_model->getTableData('dsb_category', array());
		        	$newcat_details   		= $cat_details->result();
		        	$data['category'] 		= $newcat_details;
					$data['edit_product']   = $isValid->row();
					$data['site_id']  		= $site_id;
					$data['view'] 			= 'edit';
				}

			}
			else
			{
				$data['view'] = 'view_all';
				// Row per page
			    $rowperpage = 15;

			    // Row position
			    if($rowno != 0){
			      $rowno = ($rowno-1) * $rowperpage;
			    }
			 
			    // All records count
			    $allcount = $this->Common_model->getrecordCount();

			    // Get records
			    $users_record = $this->Common_model->getData($rowno,$rowperpage);
			 
			    // Pagination Configuration
			    $config['base_url'] 		= base_url().'index.php/User/loadRecord';
			    $config['use_page_numbers'] = TRUE;
			    $config['total_rows'] 		= $allcount;
			    $config['per_page'] 		= $rowperpage;

			    // Initialize
			    $this->pagination->initialize($config);

			    // Initialize $data Array
			    $data['pagination'] = $this->pagination->create_links();
			    $data['result'] = $users_record;
			    $data['row'] = $rowno;

			    echo json_encode($data);

				$data['cat_details'] = $result->result();
			}


			$user           	= $this->common_model->getTableData('fl_users', array());
	        $user_details   	= $user->result();
	        $data['username'] 	= $user_details[0]->username;
	        $data['site_details'] = $this->common_model->getTableData('fl_site_list','','','');

	        $data['varWelcomeHeaderTitle'] = "Products";
			$data['varSmallHeaderTitle'] = "";
			$data['arrBreadCrumbInfo'] = array("Home"=>"#","Products"=>"");


			$data['title'] = 'Product Details';
			$data['meta_keywords'] = 'Product Details';
			$data['meta_description'] = 'Product Details';
			$data['cancel_url'] 		= admin_url().'product_list/'.$id;
			
			$this->load->view('admin/product', $data);  
		}	
	}


	function delete_product($site_id,$id,$page='')
	{
		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		// Is valid
		if ($id == '') {
			$this->session->set_flashdata('error', 'Invalid request');
			admin_redirect($admin_redirect.'/'.$site_id);
		}

		$sitedetails 	= $this->common_model->getTableData('fl_site_list',array('site_id'=>$site_id))->result();
		$table_name   	= $sitedetails[0]->product_tb_name;
		$site_name   	= $sitedetails[0]->site_name;

		if($page!='')
		{
			$admin_redirect = 'view_all_products';
		}
		else
		{
			$admin_redirect = 'product_list/'.$site_id;
		}


		$isValid = $this->common_model->getTableData($table_name, array('id' => $id))->num_rows();
		if($isValid > 0) 
		{
			$condition = array('id' => $id);
			$delete = $this->common_model->deleteTableData($table_name, $condition);
			if($delete) 
			{
				$this->session->set_flashdata('success', $site_name.' Product details deleted successfully');
				admin_redirect($admin_redirect);
			} 
			else 
			{
				$this->session->set_flashdata('error', 'Problem occure with '.$site_name.' Product deletion');
				admin_redirect($admin_redirect);	
			}
		} 
		else 
		{
			$this->session->set_flashdata('error', 'Unable to find this page');
			admin_redirect($admin_redirect);
		}
	}

	function brand($type='')
	{
		$sessionvar = $this->session->userdata('loggeduser');
		if (!$sessionvar) 
		{
			admin_redirect('', 'refresh');
		}
		else 
		{
			if($type == 'add')
			{
				if($this->input->post('brand_submit')) 
				{	
					$insertData=array();
					
					$insertData['brand_name'] 	 = ucfirst($this->input->post('brand_name')); 
					$insertData['brand_slug'] 	 = $this->input->post('brand_name'); 
					$insertData['created_on'] 	 = date('Y-m-d H:i:s'); 


					$insert = $this->common_model->insertTableData('dsb_brand', $insertData);
					if ($insert) {
						$this->session->set_flashdata('success', 'New Brand details has been added successfully!');
						admin_redirect('brand', 'refresh');
					} else {
						$this->session->set_flashdata('error', 'Unable to add the new Category!');
						admin_redirect('brand/add', 'refresh');
					}
				}
				$data['view'] = 'add';
			}
			else if($type == 'edit')
			{	
				$id = $this->uri->segment(4);
				$condition = array('brand_id' => $id);
				$updateData=array();

				if($id == '') 
				{
					$this->session->set_flashdata('error', 'Invalid request');
					admin_redirect('brand');
				}
				$isValid = $this->common_model->getTableData('dsb_brand', array('brand_id' => $id));

				if($isValid->num_rows() == 0)
				{
					$this->session->set_flashdata('error', 'Unable to find this page');
					admin_redirect('brand');
				}


				if($this->input->post('update_brand_submit')) 
				{	
					$updateData['brand_name'] = ucfirst($this->input->post('edit_brand_name')); 
					$updateData['brand_slug'] = $this->input->post('edit_brand_name'); 
					$updateData['created_on'] = date('Y-m-d H:i:s'); 


					$update = $this->common_model->updateTableData('dsb_brand',$condition,$updateData);
					if ($update) {
						$this->session->set_flashdata('success', 'Brand details has been Updated successfully!');
						admin_redirect('brand', 'refresh');
					} else {
						$this->session->set_flashdata('error', 'Unable to Edit the Brand details!');
						admin_redirect('brand/edit', 'refresh');
					}
				}

				$data['edit_brand']  = $isValid->row();
				$data['view'] 			= 'edit';

			}
			else
			{
				$brand_details    		= $this->common_model->getTableData('dsb_brand', array());
	        	$newbrand_details		= $brand_details->result();
	        	$data['brand_details'] 	= $newbrand_details; 
	        	$data['view'] 			= 'view_all';
			}
			
            $user           				= $this->common_model->getTableData('fl_users', array());
            $user_details   				= $user->result();
            $data['username'] 				= $user_details[0]->username;
            $data['site_details'] 			= $this->common_model->getTableData('fl_site_list','','','');
			$data['varWelcomeHeaderTitle'] 	= "Brand Management";
			$data['varSmallHeaderTitle'] 	= "";
			$data['arrBreadCrumbInfo'] 		= array("Home"=>"#","Brand"=>"");

			$data['title'] 					= 'Brand Management';
			$data['meta_keywords'] 			= 'Brand Management';
			$data['meta_description'] 		= 'Brand Management';
			$data['main_content'] 			= 'admin/brand';
			$data['cancel_url'] 			= admin_url().'brand';

			$this->load->view('admin/brand', $data); 
		}
	}

	function delete_brand($id)
	{
		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		// Is valid
		if ($id == '') 
		{
			$this->session->set_flashdata('error', 'Invalid request');
			admin_redirect('brand');
		}
		$isValid = $this->common_model->getTableData('dsb_brand', array('brand_id' => $id))->num_rows();
		if ($isValid > 0) { // Check is valid 
			$condition = array('brand_id' => $id);
			$delete = $this->common_model->deleteTableData('dsb_brand', $condition);
			if ($delete) { // True // Delete success
				$this->session->set_flashdata('success', 'Brand deleted successfully');
				admin_redirect('brand');
			} else { //False
				$this->session->set_flashdata('error', 'Problem occure with Brand deletion');
				admin_redirect('brand');	
			}
		} else {
			$this->session->set_flashdata('error', 'Unable to find this page');
			admin_redirect('brand');
		}
	}

	function manufacturer($type='')
	{
		$sessionvar = $this->session->userdata('loggeduser');
		if (!$sessionvar) 
		{
			admin_redirect('', 'refresh');
		}
		else 
		{
			if($type == 'add')
			{
				if($this->input->post('manufacturer_submit')) 
				{	
					$insertData=array();
					
					$insertData['manufacturer_name'] 	= ucfirst($this->input->post('manufacturer_name')); 
					$insertData['manufacturer_slug'] 	= $this->input->post('manufacturer_name'); 
					$insertData['created_on'] 	 		= date('Y-m-d H:i:s'); 


					$insert = $this->common_model->insertTableData('dsb_manufacturer', $insertData);
					if ($insert) {
						$this->session->set_flashdata('success', 'New Manufacturer details has been added successfully!');
						admin_redirect('manufacturer', 'refresh');
					} else {
						$this->session->set_flashdata('error', 'Unable to add the new Manufacturer details!');
						admin_redirect('manufacturer/add', 'refresh');
					}
				}
				$data['view'] = 'add';
			}
			else if($type == 'edit')
			{	
				$id = $this->uri->segment(4);
				$condition = array('manufacturer_id' => $id);
				$updateData=array();

				if($id == '') 
				{
					$this->session->set_flashdata('error', 'Invalid request');
					admin_redirect('manufacturer');
				}
				$isValid = $this->common_model->getTableData('dsb_manufacturer', array('manufacturer_id' => $id));

				if($isValid->num_rows() == 0)
				{
					$this->session->set_flashdata('error', 'Unable to find this page');
					admin_redirect('manufacturer');
				}


				if($this->input->post('update_manufacturer_submit')) 
				{	
					$updateData['manufacturer_name'] = ucfirst($this->input->post('edit_manufacturer_name')); 
					$updateData['manufacturer_slug'] = $this->input->post('edit_manufacturer_name'); 
					$updateData['created_on'] 		 = date('Y-m-d H:i:s'); 


					$update = $this->common_model->updateTableData('dsb_manufacturer',$condition,$updateData);
					if ($update) {
						$this->session->set_flashdata('success', 'Manufacturer details has been Updated successfully!');
						admin_redirect('manufacturer', 'refresh');
					} else {
						$this->session->set_flashdata('error', 'Unable to Edit the Manufacturer details!');
						admin_redirect('manufacturer/edit', 'refresh');
					}
				}

				$data['edit_manufacturer']  = $isValid->row();
				$data['view'] 			= 'edit';

			}
			else
			{
				$manufacturer_details			= $this->common_model->getTableData('dsb_manufacturer', array());
	        	$newmanufacturer_details		= $manufacturer_details->result();
	        	$data['manufacturer_details'] 	= $newmanufacturer_details; 
	        	$data['view'] 					= 'view_all';
			}
			
            $user           				= $this->common_model->getTableData('fl_users', array());
            $user_details   				= $user->result();
            $data['username'] 				= $user_details[0]->username;
            $data['site_details'] 			= $this->common_model->getTableData('fl_site_list','','','');
			$data['varWelcomeHeaderTitle'] 	= "Manufacturer Management";
			$data['varSmallHeaderTitle'] 	= "";
			$data['arrBreadCrumbInfo'] 		= array("Home"=>"#","Manufacturer"=>"");

			$data['title'] 					= 'Manufacturer Management';
			$data['meta_keywords'] 			= 'Manufacturer Management';
			$data['meta_description'] 		= 'Manufacturer Management';
			$data['main_content'] 			= 'admin/manufacturer';
			$data['cancel_url'] 			= admin_url().'manufacturer';

			$this->load->view('admin/manufacturer', $data); 
		}
	}

	function delete_manufacturer($id)
	{
		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		// Is valid
		if ($id == '') 
		{
			$this->session->set_flashdata('error', 'Invalid request');
			admin_redirect('manufacturer');
		}
		$isValid = $this->common_model->getTableData('dsb_manufacturer', array('manufacturer_id' => $id))->num_rows();
		if ($isValid > 0) { // Check is valid 
			$condition = array('manufacturer_id' => $id);
			$delete = $this->common_model->deleteTableData('dsb_manufacturer', $condition);
			if ($delete) { // True // Delete success
				$this->session->set_flashdata('success', 'Manufacturer deleted successfully');
				admin_redirect('manufacturer');
			} else { //False
				$this->session->set_flashdata('error', 'Problem occure with Manufacturer deletion');
				admin_redirect('manufacturer');	
			}
		} else {
			$this->session->set_flashdata('error', 'Unable to find this page');
			admin_redirect('manufacturer');
		}
	}


	function product_url_list($id)
	{
		$cat_details   	 	= $this->common_model->getTableData('dsb_category', array());
    		$newcat_details   	= $cat_details->result();
    		$data['category'] 	= $newcat_details; 

		$session_data = array('site_url_id'=> $id);
		$this->session->set_userdata($session_data);

		$sessionvar = $this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		else 
		{	
			
			$sitedetails = $this->common_model->getTableData('fl_site_list',array('site_id'=>$id))->result();
			$table_name   = $sitedetails[0]->url_tb_name;

			
			 
			$data['pro_id'] 			= $id;
			$data['view'] 				= 'view_all';

			/*$product_details    		= $this->common_model->getTableData($table_name, array());
        	$newproduct_details			= $product_details->result();
        	$data['product_url_details']= $newproduct_details;*/
			

        	$total_counts  = $this->db->query("SELECT count(*) as counts FROM $table_name")->row();
			$total_records = $total_counts->counts;
			$data['total_records'] = $total_records;
			$getpage = $this->input->get('page');

			if($getpage > 0)
				$getpage = $getpage;
			else
				$getpage = 0;
			
			$per_row = 15;
			$start 	 = $getpage * $per_row;
			$end 	 = $per_row;
			$First	 = 0;
			$Next 	 = $getpage + 1;
			$Prev 	 = $getpage - 1;
			$Last 	 = $total_records - $per_row;


			if($this->input->get('page') == 'first')
			{
				$start 	= 0;
			}
			else if($this->input->get('page') == 'last')
			{
				$start 	= $Last;
			}

			$result = $this->db->query("SELECT * FROM $table_name LIMIT $start, $end")->result();
			
			$data['result'] = $result;
			$data['first'] = '<a href="'.$this->current_url().'?page=first" class="active">First</a>';
			$data['last'] = '<a href="'.$this->current_url().'?page=last" class="active">Last</a>';
			
			
			if($Prev > 0)
				$data['previous'] = '<a href="'.$this->current_url().'?page='.$Prev.'"> ← Prev </a>';
			else
				$data['previous'] = '<a href="'.$this->current_url().'"> ← Prev  </a>';

			$data['next'] = '<a href="'.$this->current_url().'?page='.$Next.'"> Next →  </a>';



			
			$user           				= $this->common_model->getTableData('fl_users', array());
            $user_details   				= $user->result();
            $data['username'] 				= $user_details[0]->username;
            $data['site_details'] 			= $this->common_model->getTableData('fl_site_list','','','');

            $data['varWelcomeHeaderTitle'] 	= "Products URL details";
			$data['varSmallHeaderTitle'] 	= "";
			$data['arrBreadCrumbInfo'] 		= array("Home"=>"#","Product URL details"=>"");
			$data['title'] 					= 'Product URL Details';
			$data['meta_keywords'] 			= 'Product URL Details';
			$data['meta_description'] 		= 'Product URL Details';
			
			$this->load->view('admin/product_url_list', $data); 
		}
	}

	 

	function changeStatus($site_id,$id)
	{
		$sitedetails = $this->common_model->getTableData('fl_site_list',array('site_id'=>$site_id))->result();
		$site_status = $sitedetails[0]->status;
		$site_name   = $sitedetails[0]->site_name;

		if($site_name!='')
		{
			$siteid    = $sitedetails[0]->site_id;

			$condition = array('site_id' => $siteid);
			$updateData= array();

			$updateData['status'] 		= $id; 
			$updateData['modified_on'] 	= date('Y-m-d H:i:s'); 

			$update = $this->common_model->updateTableData('fl_site_list',$condition,$updateData);
			if ($update) {
				$this->session->set_flashdata('success', 'Site status has been Updated successfully!');
				admin_redirect('dashboard', 'refresh');
			} else {
				$this->session->set_flashdata('error', 'Unable to Edit the Site status!');
				admin_redirect('dashboard', 'refresh');
			}
		}	
	}


	public function loadRecord()
	{	
		//echo "<Pre>";print_r($_REQUEST); exit;
		$rowno 		=  (!empty($_POST['pagno'])) ? $_POST['pagno'] : 0;
		$proname 	=  (!empty($_POST['product_name'])) ? $_POST['product_name'] : '';
		$type 	 	=  (!empty($_POST['type'])) ? $_POST['type'] : '';

		$pro_sku 	=  (!empty($_POST['product_sku'])) ? $_POST['product_sku'] : '';
		$pro_cat 	=  (!empty($_POST['product_category_src'])) ? $_POST['product_category_src'] : '';


	    $rowperpage = 15;

	    if($rowno != 0){
	      $rowno = ($rowno-1) * $rowperpage;
	    }


	    if($type == 'filter')
	    {
	    	$allcount 		= $this->common_model->getrecordCount($proname,$pro_sku,$pro_cat);
		    $users_record 	= $this->common_model->getData($rowno,$rowperpage,$proname,$pro_sku,$pro_cat);
		    //echo "<pre>";print_r($users_record);exit;
	    }
	    else
	    {
	    	$allcount 		= $this->common_model->getrecordCount();
		    $users_record 	= $this->common_model->getData($rowno,$rowperpage);
	    }
	 	
	    // All records count
	    
	 
	    // Pagination Configuration
	    $config['base_url'] 		= admin_url().'loadRecord';
	    $config['use_page_numbers'] = TRUE;
	    $config['total_rows'] 		= $allcount;
	    $config['per_page'] 		= $rowperpage;
	    $config['next_link'] 		= 'Next';
		$config['prev_link'] 		= 'Previous';

	    // Initialize
	    $this->pagination->initialize($config);

	    // Initialize $data Array
	    $data['pagination'] = $this->pagination->create_links();
	    $data['result'] = $users_record;
	    $data['row'] = $rowno;

	    echo json_encode($data); 
	}

	public function newloadRecord()
	{
		//echo "<Pre>";print_r($_REQUEST); exit;
		$rowno 		=  (!empty($_POST['pagno'])) ? $_POST['pagno'] : 0;
		$proname 	=  (!empty($_POST['common_search'])) ? $_POST['common_search'] : '';
		$pro_cat 	=  (!empty($_POST['product_category'])) ? $_POST['product_category'] : '';
		$type 	 	=  (!empty($_POST['type'])) ? $_POST['type'] : '';

	    $rowperpage = 15;

	    // Row position
	    if($rowno != 0){
	      $rowno = ($rowno-1) * $rowperpage;
	    }
	 	

	    if($type == 'filter')
	    {
	    	$allcount 		= $this->common_model->newgetrecordCount($proname,$pro_cat);
		    $users_record 	= $this->common_model->newgetData($rowno,$rowperpage,$proname,$pro_cat);
		    //echo "<pre>";print_r($users_record);exit;
	    }
	    else
	    {
	    	$allcount 		= $this->common_model->newgetrecordCount();
		    $users_record 	= $this->common_model->newgetData($rowno,$rowperpage);
		    //echo "<pre>";print_r($allcount);exit;
	    }
	 	


	    // All records count
	    //$allcount = $this->common_model->newgetrecordCount();

	    // Get records
	    //$users_record = $this->common_model->newgetData($rowno,$rowperpage);
	 
	    // Pagination Configuration
	    $config['base_url'] 		= admin_url().'newloadRecord';
	    $config['use_page_numbers'] = TRUE;
	    $config['total_rows'] 		= $allcount;
	    $config['per_page'] 		= $rowperpage;
	    $config['next_link'] 		= 'Next';
		$config['prev_link'] 		= 'Previous';

	    // Initialize
	    $this->pagination->initialize($config);

	    // Initialize $data Array
	    
	    $data['pagination'] = $this->pagination->create_links();
	    $data['result'] = $users_record;
	    $data['total_rows'] = $allcount;

	    $data['row'] = 1;

	    echo json_encode($data); 
	}

	function profile()
	{
		//If login
		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		else {
			
            $user           	= $this->common_model->getTableData('fl_users', array());
            $user_details   	= $user->result();
            $data['username'] 	= $user_details[0]->username;
            
			$data['site_details'] = $this->common_model->getTableData('fl_site_list','','','');
			$data['varWelcomeHeaderTitle'] = "Profile";
			$data['varSmallHeaderTitle'] = "";
			$data['arrBreadCrumbInfo'] = array("Home"=>"#","Profile"=>"");

			$data['title'] = 'Profile';
			$data['meta_keywords'] = 'Profile';
			$data['meta_description'] = 'Profile';
			$data['main_content'] = 'admin/profile';
			// echo "<pre>";
			// print_r($data);die;
			$this->load->view('admin/profile', $data); 
		}
	}

  	
  	function image_scrapping($site_id)
  	{
  		$table_name 	= $this->get_table_name($site_id);
		$site_name 		= $this->get_site_name($site_id);
		
		if($site_name == 'Pier1') { $site_name = 'Pier-1';}
		if($site_name == 'Potterybarn') { $site_name = 'potterybarn';} 

  		if($table_name!='')
  		{

  			if($site_name == 'CB2')
  			{
  				$select = 'thumb,images,id,product_images,main_product_images';
  			}
  			else
  			{
  				$select = 'images,id,product_images,main_product_images';
  			}
  			$get_images = $this->db->query("SELECT ".$select." FROM ".$table_name. " WHERE 1 = 1 and product_images = ''")->result_array(); // limit 1 images WHERE 1 = 1
  			//echo "<pre>";print_r($get_images); exit;  //and product_images = ''   limit 0,2000 
			if(count($get_images))
			{	
				 
				foreach($get_images as $key => $imagedata)
				{	
					
					if($imagedata['product_images'] == '')
					{
						$product_id	= $imagedata['id'];
						//$image_url 	= $imagedata['images'];
						if($site_name == 'CB2')
						{ 
							$image_url  = $imagedata['thumb']; 
						}
						else
						{
							$image_url 	= $imagedata['images'];
						}
						

						$explode 	= explode('https://',$image_url);
						$count		= count($explode);
						
						if(empty($count)) {
							$explode = explode('http://',$image_url);
						}

						//echo "<pre>";print_r($explode); exit;
						if(count($explode) > 0) 
						{
							$i=0;
							$images = array();
							
							foreach($explode as $row)
							{
								if(!empty($row))
								{	
									$row 		= str_replace(array('http://','https://'), '', $row);
									$img_url 	= 'https://'.$row;

									if($site_name == 'CB2')
									{
										$img_url    = strtok($img_url,'&');
										$string 	= "$web_zoom_furn_hero$";
										$img_url    = str_replace('web_Lineitem', 'web_zoom_furn_hero', $img_url);
									}

									if(strpos($img_url, '[US]') !== false ) 
	    							{
	    								$img_url = str_replace('[US]','',$img_url);
	    							}
	    							else
	    							{
	    								$img_url = $img_url;
	    							}
	    							 
									$image 		= file_get_contents($img_url);
									$imgname 	= $product_id.'_'.$i.'.jpg';
									file_put_contents('./'.$site_name.'/product_images/'.$imgname, $image);
									$images[] 	= $imgname;
								
									$condition 	= array('id' => $product_id);
									$update    	= array();

									$imagenames 				= implode(',',$images);
									$update['product_images'] 	= $imagenames;
									$update 					= $this->common_model->updateTableData($table_name,$condition,$update);
									
									if($update)
									{
										echo "Scrapped image successfully stored into table ".$i; echo "<br>";
									}
									else
									{
										echo "Error!";
									}

									$i++;
								}
							}

						}
					}
					else
					{
						echo "Scrapping thumb image details completed for all products";echo "<br>";
					}

					/*if($imagedata['main_product_images'] == '')
					{	
						$product_ids	= $imagedata['id'];

						if($site_name == 'CB2')
			  			{
			  				$filepath = 'cb2';
			  			}
			  			if($site_name == 'Pier-1')
			  			{
			  				$filepath = 'pi';
			  			}
			  			if($site_name == 'potterybarn')
			  			{
			  				$filepath = 'pb';
			  			}
						
						if(strpos($imagedata['images'], '[US]') == true) 
			            {
			                $imagess 	= explode('[US]', $imagedata['images']);
			                $new_images = $imagess[0];
			            }
			            else
			            {
			                $new_images = $imagedata['images'];
			            }
			            $iii 		 = rand();
						$image 		 = file_get_contents($new_images);
						$imgname 	 = $filepath.'_'.$product_ids.'.jpg';
					
						file_put_contents("./".$site_name.'/main_product_images/'.$imgname, $image);

						$conditions = array('id' => $product_ids);
						$updates   	= array();

						$updates['main_product_images'] 	= $imgname;
						$update = $this->common_model->updateTableData($table_name,$conditions,$updates);
						if($update)
						{
							echo "Scrapped main product image successfully stored into table ".$product_ids; echo "<br>";
						}
						else
						{
							echo "Error!";
						}
					}
					else
					{
						echo "Scrapping details completed for all products";
					}*/	
				}
			}
			else
			{
				echo "Image Scrapping process completed.";
			}
		}	
  	}

  	function update_cron_frequency()
  	{
  		if($this->input->post('submit')) 
		{	
			$site_id = $this->input->post('site_id');
			$condition = array('site_id' => $site_id);
			$updateData=array();

			$tmrw_date 	 = date("Y-m-d h:i:s a", strtotime('tomorrow'));
			//echo $tmrw_date; exit;

			$cron_frequency 			=  $this->input->post('cron_frequency_number');
			$format 					=  $this->input->post('cron_frequency_days');
			$cron_start_date 			=  $tmrw_date;
			$cron_end_date 				=  date("Y-m-d h:i:s a", strtotime("$tmrw_date +$cron_frequency $format"));

			 
			$updateData['cron_frq'] 		 = $cron_frequency; 
			$updateData['cron_format'] 		 = $format; 
			$updateData['cron_start_date'] 	 = $cron_start_date; 
			$updateData['cron_end_date'] 	 = $cron_end_date; 


			$update = $this->common_model->updateTableData('fl_site_list',$condition,$updateData);
			if ($update) {
				$this->session->set_flashdata('success', 'Cron details has been Updated successfully!');
				admin_redirect('dashboard', 'refresh');
			} else {
				$this->session->set_flashdata('error', 'Unable to Edit the Cron details!');
				admin_redirect('dashboard', 'refresh');
			}
		}

  	}

  	function view_all_products()
  	{
		$sessionvar=$this->session->userdata('loggeduser');
		if (!$sessionvar) {
			admin_redirect('', 'refresh');
		}
		else 
		{	
			$sitedetails 	= $this->common_model->getTableData('fl_site_list',array('site_id!='=>''))->result();
			$table_name   	= $sitedetails[0]->product_tb_name;
			$site_name   	= $sitedetails[0]->site_name;


			$cat_details   	 	= $this->common_model->getTableData('dsb_category', array());
    		$newcat_details   	= $cat_details->result();
    		$data['category'] 	= $newcat_details; 
			 
			$user           				= $this->common_model->getTableData('fl_users', array());
            $user_details   				= $user->result();
            $data['username'] 				= $user_details[0]->username;
            $data['site_details'] 			= $this->common_model->getTableData('fl_site_list','','','')->result();
            
            $data['varWelcomeHeaderTitle'] 	= "All Product details";
			$data['varSmallHeaderTitle'] 	= "";
			$data['arrBreadCrumbInfo'] 		= array("Home"=>"#","All Product details"=>"");
			$data['title'] 					= 'All Product list';
			$data['meta_keywords'] 			= 'All Product list';
			$data['meta_description'] 		= 'All Product list';

			
			$total_counts = $this->db->query("SELECT (SELECT COUNT(*) FROM ".$sitedetails[0]->product_tb_name.") as table1Count, (SELECT COUNT(*) FROM ".$sitedetails[1]->product_tb_name.") as table2Count, (SELECT COUNT(*) FROM ".$sitedetails[2]->product_tb_name.") as table3Count")->result();

			$first_table = $total_counts[0]->table1Count;
			$sec_table 	 = $total_counts[0]->table2Count;
			$third_table = $total_counts[0]->table3Count;

			$total_records 	= ($first_table + $sec_table + $third_table);
			$data['total_records'] = $total_records;

			$getpage = $this->input->get('page');

			if($getpage > 0)
				$getpage = $getpage;
			else
				$getpage = 0;
			

			$per_row 	= 15;
			$start 		= $getpage * $per_row;
			$end 		= $per_row;
			$First		= 0;
			$Next 		= $getpage + 1;
			$Prev 		= $getpage - 1;
			$Last 		= $total_records - $per_row;


			if($this->input->get('page') == 'first')
			{
				$start 	= 0;
			}
			else if($this->input->get('page') == 'last')
			{
				$start 	= $Last;
			}

			$result = $this->db->query("SELECT product_name,product_sku,color,product_url,product_category,price,id FROM ".$sitedetails[0]->product_tb_name." UNION SELECT product_name,product_sku,color,product_url,product_category,price,id FROM ".$sitedetails[1]->product_tb_name." UNION SELECT product_name,product_sku,color,product_url,product_category,price,id FROM ".$sitedetails[2]->product_tb_name." LIMIT $start, $end")->result();
			 
			//echo $this->db->last_query();exit;

			//$this->db->query("SELECT * FROM $table_name LIMIT $start, $end")->result();
			
			$data['result'] = $result;
			$data['first'] 	= '<a href="'.$this->current_url().'?page=first" class="active">First</a>';
			$data['last'] 	= '<a href="'.$this->current_url().'?page=last" class="active">Last</a>';
			
			
			if($Prev > 0)
				$data['previous'] = '<a href="'.$this->current_url().'?page='.$Prev.'">← Prev </a>';
			else
				$data['previous'] = '<a href="'.$this->current_url().'"> ← Prev  </a>';


			$data['next'] = '<a href="'.$this->current_url().'?page='.$Next.'"> Next →  </a>';




			$this->load->view('admin/all_product_list', $data);
		}		
  	}


  	public function load_all_Record()
	{	
		$rowno 		=  (!empty($_POST['pagno'])) ? $_POST['pagno'] : 0;
		$proname 	=  (!empty($_POST['product_name'])) ? $_POST['product_name'] : '';
		$type 	 	=  (!empty($_POST['type'])) ? $_POST['type'] : '';
		$pro_sku 	=  (!empty($_POST['product_sku'])) ? $_POST['product_sku'] : '';
		$pro_cat 	=  (!empty($_POST['product_category_src'])) ? $_POST['product_category_src'] : '';
		$table_name	=  (!empty($_POST['site_id'])) ? $_POST['site_id'] : '';

	    $rowperpage = 15;

	    if($rowno != 0){
	      $rowno = ($rowno-1) * $rowperpage;
	    }

	    if($type == 'filter')
	    {
	    	$allcount 		= $this->common_model->getall_recordCount($proname,$pro_sku,$pro_cat,$table_name);
		    $users_record 	= $this->common_model->get_allData($rowno,$rowperpage,$proname,$pro_sku,$pro_cat,$table_name);
	    }
	    else
	    {
	    	$allcount 		= $this->common_model->getall_recordCount();
		    $users_record 	= $this->common_model->get_allData($rowno,$rowperpage);
	    }
	 	
	    // All records count
	    
	 
	    // Pagination Configuration
	    $config['base_url'] 		= admin_url().'load_all_Record';
	    $config['use_page_numbers'] = TRUE;
	    $config['total_rows'] 		= $allcount;
	    $config['per_page'] 		= $rowperpage;
	    $config['next_link'] 		= 'Next';
		$config['prev_link'] 		= 'Previous';

	    // Initialize
	    $this->pagination->initialize($config);

	    // Initialize $data Array
	    $data['pagination'] = $this->pagination->create_links();
	    $data['result'] = $users_record;
	    $data['row'] = $rowno;
	    $data['total_rows'] = $allcount;

	    echo json_encode($data); 
	}

	//10-11-18
	function bulk_upload()
	{
		$sessionvar=$this->session->userdata('loggeduser');
		if(!$sessionvar) 
		{
			admin_redirect('', 'refresh');
		}
		else 
		{
			if($this->input->post('import_csv_submit')) 
			{	
				
				$product_id					= $this->input->post('product_id');
				$site_name 					= $this->get_site_name($product_id);
				$csv_fileName 				= $_FILES['upload_csv']['name'];
				
	            $config['upload_path'] 		= './upload/';
		        $config['allowed_types'] 	= 'csv';
		        
		        //$this->load->library('upload');
		        $this->upload->initialize($config);

			    $uploads 	= $this->upload->do_upload('upload_csv');
		        
                if(!$uploads)
                {
                    $this->form_validation->set_error_delimiters('<p class="error">', '</p>');
                    $error = array('error' => $this->upload->display_errors());
                    $this->session->set_flashdata('error', $this->upload->display_errors());
                    admin_redirect('bulk_upload', 'refresh');
                }
                else
                {
                	  
                	$file_data  = $this->upload->data();
		            $file_path  =  './upload/'.$file_data['file_name'];
		            $table_name = $this->get_table_name($product_id);

		            if($this->csvimport->get_array($file_path)) 
		            {	 
		                $csv_array = $this->csvimport->get_array($file_path);

		                foreach($csv_array as $row) 
		                {
		                    $insert_data = array(
		                        'product_sku'			=>$row['product_sku'],
		                        'sku_hash'   			=>$row['sku_hash'],
		                        'model_code' 			=>$row['model_code'],
		                        'product_url' 			=>$row['product_url'],
		                        'images'     			=>$row['images'],
		                        'thumb'      			=>$row['thumb'],
		                        'product_diemension'	=>$row['product_diemension'],
		                        'color'      			=>$row['color'],
		                        'price'      			=>$row['price'],
		                        'was_price'  			=>$row['was_price'],
		                        'product_category'		=>$row['product_category'],
		                        'department' 			=>$row['department'],
		                        'product_sub_category'	=>$row['product_sub_category'],
		                        'product_name'			=>$row['product_name'],
		                        'all_category'			=>$row['all_category'],
		                        'product_feature'		=>$row['product_feature'],
		                        'product_description'	=>$row['product_description'],
		                        'product_status'		=>$row['product_status'],
		                        'product_condition'		=>$row['product_condition'],
		                        'created_date'			=>$row['created_date'],
		                        'updated_date'			=>$row['updated_date'],
		                        'is_moved'				=>$row['is_moved'],
		                        'update_status'			=>$row['update_status'],
		                    );

		                    if($product_id == 2)
		                    {
		                    	/*$insert_data = array(
		                    		'model_name'      => $row['model_name'],
		                    		'parent_category' => $row['parent_category'],
		                    		'collection'  	  => $row['collection'],
		                    		'product_set'     => $row['product_set'],

		                    	);*/
		                    	unset($insert_data['product_sub_category']);
		                    	unset($insert_data['all_category']); 

		                    }

		                    if($product_id == 3)
		                    {
		                    	unset($insert_data['model_code']); 
		                    	unset($insert_data['all_category']);
		                    	unset($insert_data['thumb']);
		                    	unset($insert_data['product_feature']);	
		                    }
							
							$insert = $this->common_model->insertTableData($table_name, $insert_data);
		                }
		                if($insert) 
		                {	
		                	//$condition  = array('site_id' => $product_id);
		                	//$updateData = array('product_count' => $insert);
		                	//$update = $this->common_model->updateTableData('fl_site_list',$condition,$updateData);
							$this->session->set_flashdata('success', 'New Product has been added successfully!');
							admin_redirect('bulk_upload', 'refresh');
						} 
						else 
						{
							$this->session->set_flashdata('error', 'Unable to add the new Product details!');
							admin_redirect('bulk_upload', 'refresh');
						}
		            } 
		            else
		            {	 
		            	$this->session->set_flashdata('error', 'Error occured!');
						admin_redirect('bulk_upload', 'refresh');
		            } 
				}	
			}
			 
			$user           	= $this->common_model->getTableData('fl_users', array());
	        $user_details   	= $user->result();
	        $data['username'] 	= $user_details[0]->username;
	        $data['site_details'] = $this->common_model->getTableData('fl_site_list','','','')->result();

	        $data['varWelcomeHeaderTitle'] = "Bulk upload Products";
			$data['varSmallHeaderTitle'] = "";
			$data['arrBreadCrumbInfo'] = array("Home"=>"#","Bulk upload Products"=>"");


			$data['title'] 			  = 'Bulk upload Products';
			$data['meta_keywords']    = 'Bulk upload Products';
			$data['meta_description'] = 'Bulk upload Products';
			$data['cancel_url'] 	  = admin_url().'dashboard';
			
			$this->load->view('admin/bulk_upload', $data);
		}
	}

}
