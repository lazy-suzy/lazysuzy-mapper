<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Products extends CI_Controller {

   public function __construct() {
      parent::__construct();
      $this->load->library(array('form_validation'));
      $this->load->helper(array('url', 'language'));
      $this->load->library('pagination');

      $this->currency_symbol = '$';
   }

   public function jj() {
      echo "I work";
      die();
   }

   public function filter($session_name, $session_value) {

      if ('clear_filter' == $session_name && 'all' == $session_value) {$this->session->sess_destroy();}

      if ('department' == $session_name) {
         if ($this->session->userdata('product_category') != '') {
            $this->session->unset_userdata('product_category');
         }
      }

      if ($session_value) {
         $this->session->set_userdata($session_name, $session_value);
      } else {
         $this->session->unset_userdata($session_name);
      }

      redirect('products_page', 'refresh');
   }

   public function index() {
      $getpage = $this->input->get('page');

      if ($getpage > 0) {
         $getpage = $getpage;
      } else {
         $getpage = 0;
      }

      $per_row = 15;
      $start   = $getpage * $per_row;
      $end     = $per_row;

      if ('' == $getpage) {
         $start = 0;
      } else {
         $start = $start;
      }

      if ($start < 0) {
         $start = 0;
      }

      $search_value = $this->input->get('search_value');

      if ('' != $search_value) {
         $search_value_like = " AND product_name like '" . urldecode($search_value) . "%' OR product_sku like '" . urldecode($search_value) . "%' OR product_category like '" . urldecode($search_value) . "%' OR price like '" . urldecode($search_value) . "%'";
      } else {
         $search_value_like = '';
      }

      if ($this->session->userdata('department') != '') {
         $department_name   = $this->session->userdata('department');
         $deptname          = $this->db->get_where('dsb_department', array('name' => $department_name))->row();
         $search_department = "AND department like '" . $deptname->department_name . "%'";
      }

      if ($this->session->userdata('product_category') != '') {
         $product_category                = urldecode($this->session->userdata('product_category'));
         $search_pro_category_cb2         = "AND product_category like '" . $product_category . "%'";
         $search_product_category         = "AND parent_category like '" . $product_category . "%'";
         $data['search_product_category'] = $product_category;
      }

      if ($this->session->userdata('price_filter') != '') {
         $price_filter = $this->session->userdata('price_filter');

         if (1 == $price_filter) {
            $price_filter_query = 'order by CAST(price AS DECIMAL) asc';
         } else {
            $price_filter_query = 'order by CAST(price AS DECIMAL) desc';
         }

         $data['price_filter'] = $price_filter;
      }

      if ($this->session->userdata('price_range') != '') {
         $range       = $this->session->userdata('price_range');
         $price_range = explode(',', $range);
         $from        = $price_range[0];
         $to          = $price_range[1];

         $price_range = 'AND price BETWEEN ' . $from . ' AND ' . $to;
      }

      $data['allsitedetails'] = $this->common_model->getTableData('fl_site_list', array('site_id!=' => ''))->result();

      $data['department'] = $this->db->query("SELECT * FROM `dsb_department` WHERE name IN ('Accent', 'Living', 'Bed', 'Dining', 'Hall', 'Office', 'Bath', 'Outdoor', 'Pet' ) GROUP BY name ORDER BY FIELD (name, 'Accent', 'Living', 'Bed', 'Dining', 'Hall', 'Office', 'Bath', 'Outdoor', 'Pet' )")->result();

      if ($this->session->userdata('site_name') != '') {
         $site_name = $this->session->userdata('site_name');
      }
      $data['site_name'] = $site_name;

      //echo $site_name; echo '<br>';

      //get all products count
      if ('' == $site_name) {
         $total_counts = $this->db->query('SELECT

			(SELECT COUNT(*) FROM `cb2_products` WHERE id > 0 ' . $search_department . ' ' . $search_pro_category_cb2 . '  ' . $price_range . ' ' . $price_filter_query . ' ' . $search_value_like . ') as table1Count,

			(SELECT COUNT(*) FROM `pier1_products` WHERE id > 0 ' . $search_department . ' ' . $search_product_category . '  ' . $price_range . ' ' . $price_filter_query . ' ' . $search_value_like . ') as table2Count,

			(SELECT COUNT(*) FROM `potterybarn_products` WHERE id > 0 ' . $search_department . ' ' . $search_product_category . '  ' . $price_range . ' ' . $price_filter_query . ' ' . $search_value_like . ') as table3Count ')->result();

         $total_records = ($total_counts[0]->table1Count + $total_counts[0]->table2Count + $total_counts[0]->table3Count);

         $data['total_records'] = $total_records;

         //get all products details
         $data['products'] = $this->db->query('SELECT product_name,was_price,price,main_product_images,site_name FROM cb2_products WHERE id > 0 ' . $search_department . ' ' . $search_pro_category_cb2 . ' ' . $search_value_like . ' ' . $price_range . ' group by product_sku

			UNION SELECT product_name,was_price,price,main_product_images,site_name FROM pier1_products WHERE id > 0 ' . $search_department . ' ' . $search_product_category . ' ' . $search_value_like . ' ' . $price_range . '

			UNION SELECT product_name,was_price,price,main_product_images,site_name FROM potterybarn_products WHERE id > 0 ' . $search_department . ' ' . $search_product_category . '  ' . $search_value_like . ' ' . $price_range . ' ' . $price_filter_query . '
			 LIMIT ' . $start . ',' . $end)->result();
         //echo $this->db->last_query();
      } else {
         $explode = explode(',', $site_name);
         $i       = 1;
         foreach ($explode as $sitename) {
            $sitename = strtolower($sitename);

            if ('cb2' == $sitename) {
               $before_total_counts .= '(SELECT COUNT(*) FROM `' . $sitename . '_products` WHERE id > 0 ' . $search_department . ' ' . $search_pro_category_cb2 . '  ' . $search_value_like . ' ' . $price_range . ' ' . $price_filter_query . ') as table' . $i . 'Count, ';

               $before_total_records .= 'SELECT product_name,was_price,price,main_product_images,site_name FROM ' . $sitename . '_products WHERE id > 0 ' . $search_department . ' ' . $search_value_like . ' ' . $search_pro_category_cb2 . ' ' . $price_range . 'group by product_sku  UNION ';
            } else {
               $before_total_counts .= '(SELECT COUNT(*) FROM `' . $sitename . '_products` WHERE id > 0 ' . $search_department . ' ' . $search_product_category . '  ' . $search_value_like . ' ' . $price_range . ' ' . $price_filter_query . ') as table' . $i . 'Count, ';

               $before_total_records .= 'SELECT product_name,was_price,price,main_product_images,site_name FROM ' . $sitename . '_products WHERE id > 0 ' . $search_department . ' ' . $search_product_category . ' ' . $search_value_like . ' ' . $price_range . ' UNION ';
            }
            $i++;
         }

         $before_total_counts1 = substr(trim($before_total_counts), 0, -1);
         $total_counts         = $this->db->query('SELECT ' . $before_total_counts1)->result();

         $total_records         = ($total_counts[0]->table1Count + $total_counts[0]->table2Count + $total_counts[0]->table3Count);
         $data['total_records'] = $total_records;

         $befor_total_records1 = substr(trim($before_total_records), 0, -5);

         $data['products'] = $this->db->query($befor_total_records1 . ' ' . $price_filter_query . ' LIMIT ' . $start . ',' . $end)->result();
      }
      //echo $this->db->last_query();exit;
      $data['department_name'] = $department_name;

      //get sub category details
      if ('' != $department_name) {
         $get_sub_categories = $this->db->query('SELECT * from `dsb_department` where `name` ="' . $data['department_name'] . '" AND status=1 group by product_category')->result();
      }

      $get_sub_categories = $this->db->query('select * from dsb_department where name ="' . $data['department_name'] . '" AND status=1 group by product_category')->result();

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

      if ('' == $getpage) {
         $this->load->view('user/products', $data);
      } else {
         $this->load->view('user/ajax_products', $data);
      }
   }

   public function load_all_Record() {
      $rowno   = (!empty($_POST['pagno'])) ? $_POST['pagno'] : 0;
      $proname = (!empty($_POST['product_name'])) ? $_POST['product_name'] : '';
      $type    = (!empty($_POST['type'])) ? $_POST['type'] : '';

      $rowperpage = 16;

      if (0 != $rowno) {
         $rowno = ($rowno - 1) * $rowperpage;
      }

      if ('filter' == $type) {
         $allcount     = $this->common_model->getall_recordCount_front($proname);
         $users_record = $this->common_model->get_allData_front($rowno, $rowperpage, $proname);
      } else {
         $allcount     = $this->common_model->getall_recordCount();
         $users_record = $this->common_model->get_allData($rowno, $rowperpage);
      }

      // All records count

      // Pagination Configuration
      $config['base_url']         = base_url() . 'load_all_Record';
      $config['use_page_numbers'] = true;
      $config['total_rows']       = $allcount;
      $config['per_page']         = $rowperpage;
      $config['next_link']        = 'Next';
      $config['prev_link']        = 'Previous';

      // Initialize
      $this->pagination->initialize($config);

      // Initialize $data Array
      $data['pagination']    = $this->pagination->create_links();
      $data['result']        = $users_record;
      $data['row']           = $rowno;
      $data['total_rows']    = $allcount;
      $data['total_records'] = $allcount;

      echo json_encode($data);
   }
}
