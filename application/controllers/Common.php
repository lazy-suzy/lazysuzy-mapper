<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Common extends CI_Controller {

   public function __construct() {
      parent::__construct();
      $this->load->library(array('form_validation'));
      $this->load->helper(array('url', 'language'));
      $this->load->library('pagination');
   }

   public function insert_data($array, $site_id) {
      foreach ($array as $department => $category) {
         foreach ($category as $key => $product_category) {
            echo $department . ' = ' . $product_category . ' = ' . $site_id;
            echo '<br>';

            $department = str_replace('&amp;', '&', $department);

            $product_category = str_replace('&amp;', '&', $product_category);

            $this->db->insert('dsb_department', array('department_name' => $department, 'product_category' => $product_category, 'site_id' => $site_id, 'name' => $department));
         }
      }
   }

   public function categories() {
      $this->db->query("TRUNCATE dsb_department");

      $category = array();

      $table_name = 'cb2_products';

      $Query  = $this->db->query("select department from $table_name products where department != '' group by department ");
      $Result = $Query->result();

      if ($Result) {
         foreach ($Result as $Row) {
            $Department = addslashes($Row->department);

            $Query1  = $this->db->query("select product_category from $table_name products where department = '" . $Department . "' group by product_category ");
            $Result1 = $Query1->result();

            if ($Result1) {
               foreach ($Result1 as $Row1) {
                  $product_category = $Row1->product_category;

                  $category[$Department][$product_category] = $product_category;
               }
            }
         }
      }

      $this->insert_data($category, 1);

      $category = array();

      $table_name = 'pier1_products';

      $Query  = $this->db->query("select department from $table_name products where department != '' group by department ");
      $Result = $Query->result();

      if ($Result) {
         foreach ($Result as $Row) {
            $Department = addslashes($Row->department);

            $Query1  = $this->db->query("select parent_category from $table_name products where department = '" . $Department . "' group by parent_category "); //product_category
            $Result1 = $Query1->result();

            if ($Result1) {
               foreach ($Result1 as $Row1) {
                  $product_category = $Row1->parent_category;

                  $category[$Department][$product_category] = $product_category;
               }
            }
         }
      }

      $this->insert_data($category, 2);

      $category = array();

      $table_name = 'potterybarn_products';

      $Query  = $this->db->query("select department from $table_name products where department != '' group by department ");
      $Result = $Query->result();

      if ($Result) {
         foreach ($Result as $Row) {
            $Department = addslashes($Row->department);

            $Query1  = $this->db->query("select product_category from $table_name products where department = '" . $Department . "' group by product_category "); //parent_category
            $Result1 = $Query1->result();

            if ($Result1) {
               foreach ($Result1 as $Row1) {
                  $product_category = $Row1->product_category;

                  $category[$Department][$product_category] = $product_category;
               }
            }
         }
      }

      $this->insert_data($category, 3);

      echo '<pre>';
      print_r($category);
      echo '<pre>';
      print_r($Result);exit;
   }

   public function update_desc() {
      $Query  = $this->db->query("SELECT id,product_sku,product_url FROM pier1_products WHERE product_description=''");
      $Result = $Query->result();

      if ($Result) {
         foreach ($Result as $Row) {
            $product_sku = $Row->product_sku;
            $product_url = $Row->product_url;

            if ('' != $product_sku && '' != $product_url) {
               $Query_old = $this->db->query("SELECT * FROM `pier1_products_old` WHERE `product_sku`='" . $product_sku . "'"); // AND `product_url`='".$product_url."'

               $Result_old = $Query_old->result();

               if ($Result_old) {
                  $description = $Result_old[0]->product_description;
                  if (NULL != $description) {
                     $condition                         = array('product_sku' => $product_sku, 'product_url' => $product_url);
                     $updateData                        = array();
                     $updateData['product_description'] = $description;
                     $update                            = $this->common_model->updateTableData('pier1_products', $condition, $updateData);
                     if ($update) {
                        echo "Description details updated successfully into " . $Row->product_sku;
                        echo "<br>";
                     } else {
                        echo "error" . $Row->product_sku;
                        echo "<br>";
                     }
                  } else {
                     echo "NULL description found " . $Row->product_sku;
                     echo "<br>";
                  }
               }
            }
         }
      } else {
         echo "Records not found";
      }
   }
}

?>