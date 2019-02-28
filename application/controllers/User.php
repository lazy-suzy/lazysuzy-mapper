<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

   private $perPage = 20;

   public function __construct() {
      parent::__construct();
      $this->load->library(array('form_validation'));
      $this->load->helper(array('url', 'language'));
      $this->load->library('pagination');
      $this->currency_symbol = '$';
   }

   public function index() {

      $data['departments'] = $this->get_departments();
      //  $data['facebook_login_url'] = $this->facebook->login_url();
      $data['google_login_url'] = $this->googleplus->loginURL();
      $this->load->view('user/home_page', $data);
      //$this->load->view('user/index');
   }

   public function home_page() {

      $data['departments']        = $this->get_departments();
      $data['facebook_login_url'] = $this->facebook->login_url();
      $data['google_login_url']   = $this->googleplus->loginURL();
      $this->load->view('user/home_page', $data);
      //$this->load->view('user/index');
   }

   public function get_departments() {
      $data = $this->db->query("SELECT department AS name, LS_ID AS id FROM mapping_core WHERE LENGTH(product_category) = 0 and LENGTH(product_sub_category) = 0")->result();

      return $data;
   }

   public function get_categories($department) {
      $data = $this->db->query("SELECT product_category, LS_ID AS id FROM mapping_core WHERE department = '$department' and length(product_sub_category) = 0 AND length(product_category) != 0")->result();

      return $data;
   }

   public function get_sub_categories($department, $category) {
      $data = $this->db->query("SELECT product_sub_category, LS_ID AS id FROM mapping_core WHERE product_category = '$category' and department = '$department' and length(product_sub_category) != 0")->result();

      return $data;
   }

   public function getNavBar() {
      $navbar_data = $this->db->query("SELECT * FROMmapping_core")->result();
      $navbar      = array();
      $this->get_sub_departments("Living");
   }

   public function current_url() {
      $CI  = &get_instance();
      $url = $CI->config->site_url($CI->uri->uri_string());
      return $url;
   }

   public function get_LS_IDs($department, $category = NULL) {

      if (NULL == $category) {
         $data = $this->db->select('LS_ID')
            ->where(array('department' => $department))
            ->get('mapping_core')
            ->result();

         $LS_IDs = array();
         // var_dump($data);
         foreach ($data as $key => $val) {
            array_push($LS_IDs, $val->LS_ID);
         }
         // echo " < pre > " . print_r($LS_IDs, TRUE);
         return $LS_IDs;
      } else {
         $data = $this->db->select('LS_ID')
            ->where(array('department' => $department, 'product_category' => $category))
            ->get('mapping_core')
            ->result();

         $LS_IDs = array();
         // var_dump($data);
         foreach ($data as $key => $val) {
            array_push($LS_IDs, $val->LS_ID);
         }
         //echo " < pre > " . print_r($LS_IDs, TRUE);
         return $LS_IDs;
      }

      return null;
   }

   public function get_department_products($department, $id, $category = NULL) {
      $department = urldecode($department);
      if (NULL != $category) {
         $category       = urldecode($category);
         $data['LS_IDs'] = json_encode($this->get_LS_IDs($department, $category));
      } else {
         $data['LS_IDs'] = json_encode($this->get_LS_IDs($department));
      }

      $count = $this->db->get('master_data')->num_rows();
      //  $LS_IDs = $this->get_LS_IDs()
      $data['allsitedetails']  = $this->common_model->getTableData('fl_site_list', array('site_id!=' => ''))->result();
      $data['department']      = $this->get_departments();
      $data['department_name'] = $department;
      $data['categories']      = $this->get_categories($department);

      if (null != $category) {
         $data['sub_categories']    = $this->get_sub_categories($department, $category);
         $data['sub_category_name'] = $category;
      }
      //echo "page is  :" . $this->input->get("page");
      //die();
      if ($this->input->get("page") > 0) {
         $start = ceil($this->input->get("page") * $this->perPage);
         if (NULL == $category) {
            $query = $this->db
               ->where_in('LS_ID', $this->get_LS_IDs($department))
               ->limit($this->perPage, $start)
               ->get('master_data');
         } else {
            $query = $this->db
               ->where_in('LS_ID', $this->get_LS_IDs($department, $category))
               ->limit($this->perPage, $start)
               ->get('master_data');
         }

         $data['products'] = $query->result();
         $result           = $this->load->view('user/ajax_products', $data);

         echo $result;
      } else {

         if (NULL == $category) {
            $query = $this->db
               ->where_in("LS_ID", $this->get_LS_IDs($department))
               ->limit(20)
               ->get('master_data');
         } else {
            $query = $this->db
               ->where_in("LS_ID", $this->get_LS_IDs($department, $category))
               ->limit(20)
               ->get('master_data');
         }

         $data['products'] = $query->result();
         $this->load->view('user/products', $data);
      }
   }

   public function filter_products() {

      $brand_filters = $this->input->post('brandFilters');
      $page          = $this->input->post('page');
      $min_val       = $this->input->post('minPrice');
      $max_val       = $this->input->post('maxPrice');
      $ls_ids        = json_decode($this->input->post('ls_ids'));
      if (sizeof($brand_filters) > 0) {
         $start            = $page * $this->perPage;
         $data['products'] = $this->db
            ->where('price >=', $min_val)
            ->where('price <=', $max_val)
            ->where_in('site_name', $brand_filters)
            ->where_in('LS_ID', $ls_ids)
            ->limit($this->perPage, $start)
            ->get('master_data')->result();
         $result = $this->load->view('user/ajax_products', $data);
         echo $result;
      } else {
         // load products without filter
         $start            = $page * $this->perPage;
         $data['products'] = $this->db
            ->where('price >=', $min_val)
            ->where('price <=', $max_val)
            ->where_in('LS_ID', $ls_ids)
            ->limit($this->perPage, $start)
            ->get('master_data')->result();
         $result = $this->load->view('user/ajax_products', $data);
         echo $result;
      }
   }
};
