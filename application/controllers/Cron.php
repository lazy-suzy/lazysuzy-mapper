<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cron extends CI_Controller {

   public function multiple_download($urls, $save_path = '/tmp') {
      $multi_handle  = curl_multi_init();
      $file_pointers = array();
      $curl_handles  = array();
      $file_paths    = array();
      // Add curl multi handles, one per file we don't already have
      //echo gettype($urls);
      //die();
      if (sizeof($urls) > 0) {
         foreach ($urls as $key => $url) {
            $file   = $save_path . '/' . basename($url);
            $s_file = "/cb2/images/" . basename($url);
            array_push($file_paths, $s_file);
            if (!is_file($file)) {
               $curl_handles[$key]  = curl_init($url);
               $file_pointers[$key] = fopen($file, "w");
               curl_setopt($curl_handles[$key], CURLOPT_FILE, $file_pointers[$key]);
               curl_setopt($curl_handles[$key], CURLOPT_HEADER, 0);
               curl_setopt($curl_handles[$key], CURLOPT_CONNECTTIMEOUT, 60);
               curl_multi_add_handle($multi_handle, $curl_handles[$key]);
            }
         }
      }
      // Download the files
      do {
         curl_multi_exec($multi_handle, $running);
      } while ($running > 0);
      // Free up objects
      foreach ($file_pointers as $key => $url) {
         curl_multi_remove_handle($multi_handle, $curl_handles[$key]);
         curl_close($curl_handles[$key]);
         fclose($file_pointers[$key]);
      }
      curl_multi_close($multi_handle);
      return implode(",", $file_paths);
   }

   public function is_word_match($a, $b) {
      $a = strtolower($a);
      $b = strtolower($b);

      $words = explode(" ", $a);

      if (in_array($b, $words)) {
         return true;
      }

      return false;
   }

   public function getLS_ID($url, $data) {
      //echo json_encode($data);

      $multi_map  = $this->db->query("SELECT * FROM cb2_mapping_multi_dept")->result();
      $direct_map = $this->db->query("SELECT * FROM cb2_mapping_utltra_direct")->result();
      $single_map = $this->db->query("SELECT * FROM cb2_mapping_direct")->result();

      $parts       = explode('/', $url);
      $department  = strtolower($parts[1]);
      $product_cat = strtolower($parts[0]);
      //echo "<pre>" . print_r($data, true) . "</pre>";
      //a loop here! on $data
      $LSID            = null;
      $product['Name'] = $data['BaseName']; //$this->cb2->get_product($data[208873]['BaseURL']);
      $product_LSIDS   = array();

      // getting LSD on the basic of ultra-direct-mapping/just `$product_cat` mapping;

      foreach ($direct_map as $key => $val) {
         $prod_cat_template = preg_replace('/\s+/', '-', strtolower($val->product_category));
         // echo "||DIRECT MAPPING MATCHING|| " . $prod_cat_template . " == " . $product_cat . "\n";
         if ($product_cat == $prod_cat_template) {
            //echo "\n\n|DIRECT MAPPING MATCHED|\n\n";
            return $val->LS_ID;
         }
      }
      $LSIDs = array();
      foreach ($multi_map as $key => $val) {
         if ((strlen($product['Name']) > 1) && (strlen($val->product_key) > 1)) {
            //echo "|| MULTY DEPT KEYWORD MATCHING ||" . $product['Name'] . " == " . $val->product_key . "\n";
            if ($this->is_word_match($product['Name'], $val->product_key)) {
               // if (strpos(strtolower($product['Name']), strtolower($val->product_key)) !== false) {
               $prod_cat_template  = preg_replace('/\s+/', '-', strtolower($val->product_sec_category));
               $prod_cat_template1 = preg_replace('/\s+/', '-', strtolower($val->product_category));
               // echo "|| MULTY DEPT MATCHING ||" . $prod_cat_template . " == " . $product_cat . "|| OR ||" . $prod_cat_template1 . " == " . $product_cat . "\n";

               if ($prod_cat_template == $product_cat || $prod_cat_template1 == $product_cat) {
                  //  echo "|| MULTI DEPT MATCHED ||" . $product['Name'] . " got matched with LS_ID" . $val->LS_ID . "\n";
                  if (!in_array($val->LS_ID, $LSIDs)) {
                     array_push($LSIDs, $val->LS_ID);
                  }
               }
            }
         }
      }
      if (sizeof($LSIDs) > 0) {
         return implode(",", $LSIDs);
      }

      // do the direct mapping check here.

      // get the mapping LS_ID from the direct mapping script;
      // if there are two or more LS_IDs present for a product in multi_dept map
      // then take the first match
      foreach ($single_map as $key => $val) {
         if ((strlen($product['Name']) > 1) && (strlen($val->product_key) > 1)) {
            //echo "|| DIRECT DEPT KEYWORD MATCHING ||" . $product['Name'] . " == " . $val->product_key . "\n";
            if ($this->is_word_match($product['Name'], $val->product_key)) {
//            if (strpos(strtolower($product['Name']), strtolower($val->product_key)) !== false) {
               $prod_cat_template1 = preg_replace('/\s+/', '-', strtolower($val->product_category));
               //echo "|| DIRECT DEPT MATCHING ||" . $prod_cat_template1 . " == " . $product_cat . "\n";
               if ($prod_cat_template1 == $product_cat) {
                  //   echo "||DIRECT DEPT MATHCED ||" . $product['Name'] . " got matched with LS_ID" . $val->LS_ID . "\n";
                  return $val->LS_ID;
               }
            }
         }
      }
      return 0;
   }

// loop end.

   public function index() {

      //Store the get request
      $status = $this->input->get();
      //$this->db->query("TRUNCATE cb2_products_new");

      //Initialize CB2 Module
      $this->load->library('CB2', array(
         'proxy' => '5.79.66.2:13010',
         'debug' => false,
      ));

      if (isset($status['category'])) {
         echo json_encode($this->cb2->get_category($status['category']));
      } else if (isset($status['product'])) {
         echo json_encode($this->cb2->get_product($status['product']));
      } else {
         // get product data urls from.
         $default_depts = array('outdoor-furniture', 'bedroom-furniture', 'living-room-furniture', 'dining-room-furniture', 'office-furniture');
         $urls          = $this->db->query("SELECT * FROM cb2_categories")->result();
         //Take relevent action
         // loop here on $urls
         $harveseted_prod = array();
         $harveseted_SKU  = array();
         foreach ($urls as $key => $url) {
            $data_retry = 20;
            echo "\n || " . $url->category_url . " || \n";
            $data        = $this->cb2->get_category($url->category_url);
            $parts       = explode('/', $url->category_url);
            $product_cat = strtolower($parts[0]);
            $department  = strtolower($parts[1]);

            if (in_array($product_cat, $default_depts)) {
               $department = $product_cat;
            }

            while ((sizeof($data) <= 0) && $data_retry--) {
               // echo '\n' . sizeof($data) . "\n";
               $data = $this->cb2->get_category($url->category_url);
               echo " || DATA RETRY || " . $data_retry . "\n\n";
            }

            if (isset($data)) {
               foreach ($data as $key => $product) {
                  //  echo "\n\n\n || NEW PRODUCT || " . $product['BaseURL'] . " - " . $key . "\n\n\n";
                  $product_retry = 20;
                  $LS_ID         = $this->getLS_ID($url->category_url, $product);
                  //$LS_ID           = '0000';
                  $product_details = $this->cb2->get_product($product['BaseURL']);
                  while ((sizeof($product_details) <= 0) && $product_retry--) {
                     //echo '\n' . sizeof($product_details) . "\n";
                     $product_details = $this->cb2->get_product($product['BaseURL']);
                     //   echo " || PRODUCT RETRY || " . $product_retry . "\n\n";
                  }
                  // echo $product['BaseURL'];
                  // echo " < pre > " . print_r($product_details, true) . " <  / pre > ";

                  if (isset($product_details)) {
                     $image_links   = $this->multiple_download($product_details['SecondaryImages'], '/var/www/html/cb2/images');
                     $img           = "https://cb2.scene7.com/is/image/CB2/" . $product_details['PrimaryImage'];
                     $primary_image = $this->multiple_download(array($img), '/var/www/html/cb2/images');
                  } else {
                     $image_links = NULL;
                  }
                  if (!isset($product_details['familyID'])) {
                     $product_details['familyID'] = '0000';
                  }

                  echo "\n" . $product_details['SKU'] . " || " . $product_cat . " || " . $department . " || " . $LS_ID . "\n";

                  //echo "\n" . $image_links . "\n";
                  $fields = array(
                     'product_sku'         => $product_details['SKU'],
                     'sku_hash'            => md5($product_details['SKU']),
                     'model_code'          => '',
                     'product_url'         => 'https://cb2.com' . $product['BaseURL'],
                     'model_name'          => '',
                     'images'              => implode(",", $product_details['SecondaryImages']),
                     'thumb'               => 'https://www.cb2.com/is/image/CB2/' . $product_details['PrimaryImage'],
                     'product_dimension'   => json_encode($product_details['Dimentions'][0]['productDimensions']),
                     'color'               => '',
                     'price'               => $product_details['CurrentPrice'],
                     'was_price'           => $product_details['RegularPrice'],
                     'parent_category'     => $product_details['familyID'],
                     'product_category'    => $product_cat,
                     'product_name'        => $product_details['Name'],
                     'department'          => $department,
                     'product_feature'     => implode('<br>', $product_details['Features']),
                     'collection'          => '',
                     'product_set'         => '',
                     'product_condition'   => '',
                     'product_description' => $product_details['Description'],
                     'product_status'      => 'active',
                     'created_date'        => gmdate('Y-m-d h:i:s \G\M\T'),
                     'updated_date'        => gmdate('Y-m-d h:i:s \G\M\T'),
                     'is_moved'            => '0',
                     'update_status'       => '',
                     'product_images'      => $image_links,
                     'main_product_images' => $primary_image,
                     'site_name'           => 'cb2',
                     'reviews'             => '',
                     'rating'              => '',
                     'master_id'           => '',
                     'reviews'             => $product_details['Reviews']['ReviewCount'],
                     'rating'              => $product_details['Reviews']['ReviewRating'],
                     'LS_ID'               => $LS_ID,
                  );
                  //  echo "\n\n" . $product_details['SKU'] . "\n\n";
                  if (!in_array($product_details['SKU'], $harveseted_SKU)) {
                     if (NULL != $product_details['SKU']) {
                        array_push($harveseted_SKU, $product_details['SKU']);
                     }

                     $sql = $this->db->insert_string('cb2_products_new', $fields);
                     if (!$this->db->query($sql)) {
                        $log = fopen("cb2-error-log.txt", "w") or die("Unable to open file!");
                        fwrite($log, $sql . "\n\n");
                        fclose($log);
                        die('error! could not enter data in data');
                     }
                  } else {
                     $x  = $product_details['SKU'];
                     $ss = $this->db->query("SELECT LS_ID FROM cb2_products_new WHERE product_sku = '$x'")->result();

                     $aa = array(
                        'LS_ID'            => $ss['LS_ID'] . "," . $LS_ID,
                        'product_category' => $product_cat,
                        'price'            => $product_details['CurrentPrice'],
                     );

                     $this->db->where('product_sku', $product_details['SKU']);
                     $this->db->update('cb2_products_new', $aa);
                     echo "\n\n\n || PRODUCT UPDATE FOUND! || " . $LS_ID . "\n\n\n";
                  }

                  $sql = $this->db->insert_string('cb2_products_new', $fields);

                  //array_push($harveseted_prod, $sql);

                  //echo "<pre>" . print_r($fields, true);

                  $product_details = NULL;
               }
            }
         }

         //foreach ($harveseted_prod as $key => $value) {

         //}
      }
   }
}