<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->library(array('form_validation'));
		$this->load->helper(array('url', 'language'));
		$this->load->library('pagination');
		$this->currency_symbol = '$';
	}

	function index() {
		$data['department'] = $this->db->query("SELECT * FROM `dsb_department` WHERE name IN ('Accent', 'Living', 'Bed', 'Dining', 'Hall', 'Office', 'Bath', 'Outdoor', 'Pet' ) GROUP BY name ORDER BY FIELD (name, 'Accent', 'Living', 'Bed', 'Dining', 'Hall', 'Office', 'Bath', 'Outdoor', 'Pet' )")->result();

		//$this->load->view('user/index',$data);
		$this->load->view('user/index');
	}

	function home_page() {
		$data['department'] = $this->db->query("SELECT * FROM `dsb_department` WHERE name IN ('Accent', 'Living', 'Bed', 'Dining', 'Hall', 'Office', 'Bath', 'Outdoor', 'Pet' ) GROUP BY name ORDER BY FIELD (name, 'Accent', 'Living', 'Bed', 'Dining', 'Hall', 'Office', 'Bath', 'Outdoor', 'Pet' )")->result();
		$this->load->view('user/home_page', $data);
		//$this->load->view('user/index');
	}

	function current_url() {
		$CI = &get_instance();
		$url = $CI->config->site_url($CI->uri->uri_string());
		return $url;
	}

	//New product page functionalities 1-12-18
	function filter($session_name, $session_value) {
		//echo $session_name .' == '. $session_value;
		if ($session_name == 'clear_filter' && $session_value == 'all') {$this->session->sess_destroy();}

		if ($session_name == 'department') {
			if ($this->session->userdata('product_category') != '') {
				$this->session->unset_userdata('product_category');
			}
			if ($this->session->userdata('product_sub_category') != '') {
				$this->session->unset_userdata('product_sub_category');
			}
		}

		if ($session_name == 'product_category') {
			if ($this->session->userdata('product_sub_category') != '') {
				$this->session->unset_userdata('product_sub_category');
			}
		}

		if ($session_name == 'product_sub_category_all') {
			if ($this->session->userdata('product_sub_category') != '') {
				$this->session->set_userdata('product_sub_category', $session_value); //'all'
			}
		}

		if ($session_value) {
			$this->session->set_userdata($session_name, $session_value);
		} else {
			$this->session->unset_userdata($session_name);
		}

		redirect('products', 'refresh');
	}

	function products() {
		$getpage = $this->input->get('page');

		if ($getpage > 0) {
			$getpage = $getpage;
		} else {
			$getpage = 0;
		}

		$per_row = 15;
		$start = $getpage * $per_row;
		$end = $per_row;

		if ($getpage == '') {
			$start = 0;
		} else {
			$start = $start;
		}

		if ($start < 0) {
			$start = 0;
		}

		$search_post_value = $this->input->post('search-prod');

		if ($search_post_value != '') {
			$search_post_value_like = " AND product_name like '%" . urldecode($search_post_value) . "%' OR product_sku like '%" . urldecode($search_post_value) . "%' OR product_category like '%" . urldecode($search_post_value) . "%' OR price like '%" . urldecode($search_post_value) . "%'";
		} else {
			$search_post_value_like = '';
		}

		$search_value = $this->input->get('search_value');
		if ($search_value != '') {
			$search_value_like = " AND product_name like '%" . urldecode($search_value) . "%' OR product_sku like '%" . urldecode($search_value) . "%' OR product_category like '%" . urldecode($search_value) . "%' OR price like '%" . urldecode($search_value) . "%'";
		} else {
			$search_value_like = '';
		}

		if ($this->session->userdata('department') != '') {
			$department_name = $this->session->userdata('department');
			$deptname = $this->db->get_where('dsb_department', array('name' => $department_name))->row();
			$search_department = "AND department like '%" . $deptname->department_name . "%'";
		}

		if ($this->session->userdata('product_category') != '') {
			$product_category = urldecode($this->session->userdata('product_category'));

			if ($product_category == 'Misc') //Accessories
			{
				$product_category = 'Accessories';
			}

			$search_pro_category_cb2 = "AND product_category like '%" . $product_category . "%'";
			$search_pro_category_pb = "AND product_category like '%" . $product_category . "%'";
			$search_pro_category_pier = "AND parent_category like '%" . $product_category . "%'";
			$data['search_product_category'] = $product_category;
		}

		//New code for product_sub_category filter 7-12-18
		if ($this->session->userdata('product_sub_category') != '') {
			$product_sub_category = urldecode($this->session->userdata('product_sub_category'));
			$product_sub_category = "'" . str_replace(",", "','", $product_sub_category) . "'";

			if ($product_sub_category != "'all'") //|| strpos($product_sub_category,"'all',") !== true
			{
				$search_pro_sub_category_cb2 = "AND product_sub_category IN (" . $product_sub_category . ")";
				$search_pro_sub_category_pb = "AND product_sub_category IN (" . $product_sub_category . ")";
				$search_pro_sub_category_pier = "AND product_category IN (" . $product_sub_category . ")";

				/*$explode_sub_category = explode(',',$product_sub_category);
					                $ii = 1;
					                foreach ($explode_sub_category as  $product_sub_category)
					                {
					                    $search_pro_sub_category_cb2   .= "AND product_sub_category  like '%".$product_sub_category."%'";
					                    $search_pro_sub_category_pb    .= "AND product_sub_category like '%".$product_sub_category."%'";
					                    $search_pro_sub_category_pier  .= "AND product_category like '%".$product_sub_category."%'";
					                    $ii++;
				*/

			} else {
				$search_pro_sub_category_cb2 = "";
				$search_pro_sub_category_pb = "";
				$search_pro_sub_category_pier = "";
			}

			//$data['search_product_category']= $product_category;
		}
		//End 7-12-18

		if ($this->session->userdata('price_filter') != '') {
			$price_filter = $this->session->userdata('price_filter');

			if ($price_filter == 1) {
				$price_filter_query = 'order by CAST(price AS DECIMAL) asc';
			} else {
				$price_filter_query = 'order by CAST(price AS DECIMAL) desc';
			}

			$data['price_filter'] = $price_filter;
		}

		if ($this->session->userdata('price_range') != '') {
			$range = $this->session->userdata('price_range');
			$price_range = explode(',', $range);
			$from = $price_range[0];
			$to = $price_range[1];

			$price_range = 'AND price BETWEEN ' . $from . ' AND ' . $to;
		}

		$data['allsitedetails'] = $this->common_model->getTableData('fl_site_list', array('site_id!=' => ''))->result();

		$data['department'] = $this->db->query("SELECT * FROM `dsb_department` WHERE name IN ('Accent', 'Living', 'Bed', 'Dining', 'Hall', 'Office', 'Bath', 'Outdoor', 'Pet' ) GROUP BY name ORDER BY FIELD (name, 'Accent', 'Living', 'Bed', 'Dining', 'Hall', 'Office', 'Bath', 'Outdoor', 'Pet' )")->result();

		if ($this->session->userdata('site_name') != '') {
			$site_name = $this->session->userdata('site_name');
		}

		$data['site_name'] = $site_name;

		//get all products count
		if ($site_name == '') {
			if ($product_category != '') {
				$data['all_product_sub_category'] = $this->db->query('SELECT product_sub_category FROM cb2_products WHERE id > 0 ' . $search_department . ' ' . $search_pro_category_cb2 . ' AND  product_sub_category!=""  group by product_sub_category
                UNION SELECT product_category FROM pier1_products WHERE id > 0 ' . $search_department . ' ' . $search_pro_category_pier . ' AND  product_category!="" group by product_category
                UNION SELECT product_sub_category FROM potterybarn_products WHERE id > 0 ' . $search_department . ' ' . $search_pro_category_pb . ' AND product_sub_category!="" group by product_sub_category')->result();
				//AND product_sub_category!="'.$product_category.'"
			}

			//get all products details
			$products_query = 'SELECT product_name,was_price,price,main_product_images,site_name,id,product_images,product_url,product_description,product_feature,product_diemension FROM cb2_products WHERE id > 0 AND product_name!="" ' . $search_department . ' ' . $search_pro_category_cb2 . ' ' . $search_pro_sub_category_cb2 . ' ' . $search_value_like . ' ' . $search_post_value_like . '  ' . $price_range . ' group by product_sku

            UNION SELECT product_name,was_price,price,main_product_images,site_name,id,product_images,product_url,product_description,product_feature,product_diemension FROM pier1_products WHERE id > 0 AND product_name!="" ' . $search_department . ' ' . $search_pro_category_pier . ' ' . $search_pro_sub_category_pier . ' ' . $search_value_like . ' ' . $search_post_value_like . '  ' . $price_range . ' group by product_name

            UNION SELECT product_name,was_price,price,main_product_images,site_name,id,product_images,product_url,product_description,product_condition as product_feature,product_diemension FROM potterybarn_products WHERE id > 0 AND product_name!="" ' . $search_department . ' ' . $search_pro_category_pb . '  ' . $search_pro_sub_category_pb . '  ' . $search_value_like . ' ' . $search_post_value_like . '  ' . $price_range . ' group by product_name ';

			$data['products'] = $this->db->query($products_query . ' ' . $price_filter_query . ' LIMIT ' . $start . ',' . $end)->result();
			$data['products_counts'] = $this->db->query($products_query . ' ' . $price_filter_query)->num_rows();
			//echo $this->db->last_query();

		} else {
			$explode = explode(',', $site_name);
			$i = 1;
			foreach ($explode as $sitename) {
				$sitename = strtolower($sitename);

				if ($sitename == 'cb2') {

					$before_total_records .= 'SELECT product_name,was_price,price,main_product_images,site_name,id,product_images,product_url,product_description,product_feature,product_diemension FROM ' . $sitename . '_products WHERE id > 0 AND product_name!="" ' . $search_department . ' ' . $search_value_like . ' ' . $search_post_value_like . ' ' . $search_pro_category_cb2 . ' ' . $search_pro_sub_category_cb2 . '   ' . $price_range . ' group by product_sku  UNION ';

					$before_total_sub_categories .= 'SELECT product_sub_category FROM ' . $sitename . '_products WHERE id > 0 ' . $search_department . ' ' . $search_pro_category_cb2 . ' AND product_sub_category!="" group by product_sub_category  UNION ';

				}
				if ($sitename == 'potterybarn') {
					$before_total_records .= 'SELECT product_name,was_price,price,main_product_images,site_name,id,product_images,product_url,product_description,product_condition as product_feature,product_diemension FROM ' . $sitename . '_products WHERE id > 0 AND product_name!="" ' . $search_department . ' ' . $search_value_like . ' ' . $search_post_value_like . ' ' . $search_pro_category_pb . ' ' . $search_pro_sub_category_pb . ' ' . $price_range . ' group by product_name  UNION ';

					$before_total_sub_categories .= 'SELECT product_sub_category FROM ' . $sitename . '_products WHERE id > 0 ' . $search_department . ' ' . $search_pro_category_pb . ' AND product_sub_category!="" group by product_sub_category  UNION ';
				}
				if ($sitename == 'pier1') {
					$before_total_records .= 'SELECT product_name,was_price,price,main_product_images,site_name,id,product_images,product_url,product_description,product_feature,product_diemension FROM pier1_products WHERE id > 0 AND product_name!="" ' . $search_department . ' ' . $search_pro_category_pier . ' ' . $search_pro_sub_category_pier . '  ' . $search_value_like . ' ' . $search_post_value_like . ' ' . $price_range . ' group by product_name UNION ';

					$before_total_sub_categories .= 'SELECT product_category as product_sub_category FROM pier1_products WHERE id > 0 ' . $search_department . ' ' . $search_pro_category_pier . ' AND  product_category!=""  group by product_category UNION ';
				}
				$i++;

			}

			$all_pro_sub_category = substr(trim($before_total_sub_categories), 0, -5);

			if ($product_category != '') {
				$data['all_product_sub_category'] = $this->db->query($all_pro_sub_category)->result();
			}

			$befor_total_records1 = substr(trim($before_total_records), 0, -5);
			$data['products'] = $this->db->query($befor_total_records1 . ' ' . $price_filter_query . ' LIMIT ' . $start . ',' . $end)->result();
			$data['products_counts'] = $this->db->query($befor_total_records1 . ' ' . $price_filter_query)->num_rows();

			//echo $this->db->last_query();
		}

		//New code for get sub category filter name details 11-12-18
		if ($product_category != '') {

			$data['all_product_sub_category_new'] = $this->db->query("SELECT `product_sub_category` from dsb_product_sub_category where sub_category_id > 0 AND department like '%" . $department_name . "%' AND product_category like '%" . $product_category . "%' AND product_sub_category!=''")->result();
		}
		//End 11-12-18

		$data['department_name'] = $department_name;

		//get sub category details
		if ($department_name != '') {
			$get_sub_categories = $this->db->query('SELECT * from `dsb_department` where `name` ="' . $data['department_name'] . '" AND status=1 group by product_category')->result();
		}

		if ($data['department_name'] == 'Bed') {
			$get_sub_categories = $this->db->query('select * from dsb_department where name ="' . $data['department_name'] . '" AND status=1 group by product_category order by product_category="Bedding" asc')->result();
		} else {
			$get_sub_categories = $this->db->query('select * from dsb_department where name ="' . $data['department_name'] . '" AND status=1 group by product_category')->result();
		}

		$data['sub_categories'] = $get_sub_categories;

		//New code 29-11-18
		$get_departments = $this->db->query("select id,name from department_table where status=1")->result_array();
		foreach ($get_departments as $departments) {
			$all_departments .= "WHEN '" . $departments['name'] . "' THEN " . $departments['id'] . ' ';
			$dept_namess .= "'" . $departments['name'] . "',";
		}
		$dept_names = rtrim($dept_namess, ',');
		//End 29-11-18

		$query = $this->db->query("select * from `dsb_department` where `status`=1 AND `name` IN (" . $dept_names . ") group by `name` ORDER BY CASE `name` " . $all_departments . " END");

		$data['get_categories'] = $query->result_array();

		if ($getpage != '') {
			$this->load->view('user/ajax_products', $data);
		} else {
			$this->load->view('user/products', $data);
		}
	}
	//end 1-12-18

	function load_all_Record() {
		$rowno = (!empty($_POST['pagno'])) ? $_POST['pagno'] : 0;
		$proname = (!empty($_POST['product_name'])) ? $_POST['product_name'] : '';
		$type = (!empty($_POST['type'])) ? $_POST['type'] : '';

		$rowperpage = 16;

		if ($rowno != 0) {
			$rowno = ($rowno - 1) * $rowperpage;
		}

		if ($type == 'filter') {
			$allcount = $this->common_model->getall_recordCount_front($proname);
			$users_record = $this->common_model->get_allData_front($rowno, $rowperpage, $proname);
		} else {
			$allcount = $this->common_model->getall_recordCount();
			$users_record = $this->common_model->get_allData($rowno, $rowperpage);
		}

		// All records count

		// Pagination Configuration
		$config['base_url'] = base_url() . 'load_all_Record';
		$config['use_page_numbers'] = TRUE;
		$config['total_rows'] = $allcount;
		$config['per_page'] = $rowperpage;
		$config['next_link'] = 'Next';
		$config['prev_link'] = 'Previous';

		// Initialize
		$this->pagination->initialize($config);

		// Initialize $data Array
		$data['pagination'] = $this->pagination->create_links();
		$data['result'] = $users_record;
		$data['row'] = $rowno;
		$data['total_rows'] = $allcount;
		$data['total_records'] = $allcount;

		echo json_encode($data);
	}
	//14-11-18
	function unset_session() {
		$this->session->sess_destroy();
		redirect('products', 'refresh');
	}

}
