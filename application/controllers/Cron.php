<?php
defined('BASEPATH') or exit('No direct script access allowed');
ini_set('memory_limit', '-1');
class Cron extends CI_Controller
{
   public $CLEAN_SYMBOLS = ['.'];
   public $DIMS = [
      'w' => 'width',
      'h' => 'height',
      'd' => 'depth',
      'l' => 'length',
      'dia' => 'diameter',
      'diam' => 'diameter'
   ];

   public function make_searchable()
   {
      $products  = $this->db->select("product_name, product_sku")
         ->from("master_data")
         ->get()->result();

      foreach ($products as $product) {
         $str = $product->product_name;
         $sku = $product->product_sku;

         $str_arr = explode(" ", $str);

         $size = sizeof($str_arr);

         $text_t = "";
         for ($i = 0; $i < (1 << $size); $i++) {
            $text = "";
            for ($j = 0; $j < $size; $j++) {
               if (($i & (1 << $j)) > 0) {
                  $text .= $str_arr[$j] . " ";
               }
            }
            if (strlen(trim($text)) > 0)
               $text_t .= "," . trim($text);
         }
         $text_arr = explode(",", trim($text_t));
         unset($text_arr[0]);
         //var_dump($text_arr);
         echo implode(",", $text_arr) . "SKU: " . $sku . "\n";
         $this->db->set("product_name_ES", implode(",", $text_arr))
            ->where("product_sku", $sku)
            ->update("master_data");
      }
   }

   public function multiple_download($urls, $save_path = '/tmp')
   {
      $multi_handle  = curl_multi_init();
      $file_pointers = array();
      $curl_handles  = array();
      $file_paths    = array();

      // Add curl multi handles, one per file we don't already have
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

   public function is_word_match($a, $b)
   {
      $a = strtolower($a);
      $b = strtolower($b);

      $words = explode(" ", $a);

      if (in_array($b, $words)) {
         return true;
      }

      return false;
   }

   public function mapLS_IDs()
   {
      // get mapping info
      $multi_map  = $this->db->select("*")
         ->from("cb2_mapping_multi_dept")
         ->order_by("product_sec_category", "ASC")
         ->get()->result();

      $ultra_direct_map = $this->db->query("SELECT * FROM cb2_mapping_direct")->result();

      $direct_map = $this->db->select("*")
         ->from("cb2_mapping_keyword_category")
         ->order_by("product_category")
         ->get()->result();

      // get products to map.
      $products = $this->db->select("*")
         ->from("cb2_products_new")
         ->get()->result();

      $default_depts = array('outdoor-furniture', 'bedroom-furniture', 'living-room-furniture', 'dining-room-furniture', 'office-furniture');

      foreach ($products as $key => $pro) {
         $LS_ID = array();
         $product_categories = explode(",", $pro->product_category);
         $product_all_cat = $product_categories; // copy for ultra direct mapping.
         $product_depts = array();

         foreach ($product_categories as $key => $val) {
            if (in_array($val, $default_depts)) {
               array_push($product_depts, $val);
               unset($product_categories[$key]);
            }
         }

         foreach ($ultra_direct_map as $key => $val) {
            $product_cat = preg_replace('/\s+/', '-', strtolower(trim($val->product_category)));
            if (in_array(trim($product_cat), $product_all_cat)) {
               if (!in_array($val->LS_ID, $LS_ID))
                  array_push($LS_ID, $val->LS_ID);
            }
         }

         // direct mapping.
         foreach ($direct_map as $key => $val) {
            $product_cat = preg_replace('/\s+/', '-', strtolower(trim($val->product_category)));
            if (in_array($product_cat, $product_all_cat)) {
               // department matched. 
               // give the LS_ID to product for department.
               // match for keywords
               if (strlen($val->product_key) == 0) {
                  if (!in_array($val->LS_ID, $LS_ID))
                     array_push($LS_ID, $val->LS_ID);
               }

               if ($this->is_word_match($pro->product_name, $val->product_key)) {
                  // keyword matched 
                  // give product the LS_ID
                  if (!in_array($val->LS_ID, $LS_ID))
                     array_push($LS_ID, $val->LS_ID);
               }
            }
         }

         // multi department mapping.
         foreach ($multi_map as $key => $val) {
            foreach ($product_depts as $index => $dept) {
               // try to match the department
               $prod_s_dept  = preg_replace('/\s+/', '-', strtolower($val->product_sec_category));
               if (trim($dept) == trim($prod_s_dept)) {
                  // department matched now match category.
                  // add the department LS_ID to the product;
                  foreach ($product_categories as $ind => $cat) {
                     $prod_s_cat = preg_replace('/\s+/', '-', strtolower($val->product_category));

                     if (trim($cat) == trim($prod_s_cat)) {
                        // catgeory matched. now search the keyword.
                        // give department and category LS_ID for the product.
                        if (strlen($val->product_key) == 0) {
                           if (!in_array($val->LS_ID, $LS_ID))
                              array_push($LS_ID, $val->LS_ID);
                        }
                        if ($this->is_word_match($pro->product_name, $val->product_key)) {
                           // keyword matched. now give the ls_id to the product. 
                           if (!in_array($val->LS_ID, $LS_ID))
                              array_push($LS_ID, $val->LS_ID);
                        }
                     }
                  }
               }
            }
         }
         echo "Product Name: " . $pro->product_name . "LS_ID: " . implode(",", $LS_ID) . "\n";
         $this->db->set("LS_ID", implode(",", $LS_ID))
            ->where("product_sku", $pro->product_sku)
            ->update("cb2_products_new");
      }

      echo "\n == MAPPING COMPLETED == \n";
   }

   public function update_variations()
   {
      // update variations field
      $dis_SKU = $this->db->distinct()->select('product_sku')->from('cb2_products_new')->get()->result();
      $dis_variation_SKU = $this->db
         ->select('product_sku, variation_sku')
         ->from('cb2_products_variations')
         ->get()->result();
      $dis_skus = array();
      $dis_variation_skus = array();

      foreach ($dis_SKU as $key => $val) {
         array_push($dis_skus, $val->product_sku);
      }

      foreach ($dis_variation_SKU as $key => $val) {
         array_push(
            $dis_variation_skus,
            array($val->product_sku, $val->variation_sku)
         );
      }

      foreach ($dis_variation_skus as $key => $sku) {
         if (in_array($sku[1], $dis_skus)) {
            $this->db->set('has_parent_sku', 1)
               ->where('product_sku', $sku[0])
               ->where('variation_sku', $sku[1])
               ->update('cb2_products_variations');
         }
      }

      echo "\n========= UPDATED VARIATIONS `has_parent_sku` FIELD ==========\n";
   }

   public function save_variations($variations, $product_sku)
   {

      $origin_sku = $product_sku;
      // echo print_r($variations, true);
      if (sizeof($variations) > 0) {
         foreach ($variations as $key => $variation) {

            if ($product_sku != $variation['SKU']) {

               $variation_fields = array(
                  'product_sku'      => $origin_sku,
                  'variation_sku'    => $variation['SKU'],
                  'variation_name'   => $variation['choiceName'],
                  'choice_code'      => isset($variation['choiceCode']) ? $variation['choiceCode'] : null,
                  'option_code'      => isset($variation['optionCode']) ? $variation['optionCode'] : null,
                  'swatch_image'       => isset($variation['ColorImage']) ? $this->multiple_download(array($variation['ColorImage']), '/var/www/html/cb2/images') : null,
                  'variation_image'  => isset($variation['Image']) ? $this->multiple_download(array($variation['Image']), '/var/www/html/cb2/images') : null,
               );


               if ($variation['SKU'] != NULL) {
                  echo "Variations " . "\n";
                  var_dump($variation_fields);
                  $this->db->insert('cb2_products_variations', $variation_fields);
               }
            }
         }
      }
   }

   public function mapCABLS_IDs()
   {
      // get mapping info

      $ultra_direct_map = $this->db->query("SELECT * FROM cab_mapping_direct")->result();

      $direct_map = $this->db->select("*")
         ->from("cab_mapping_keyword_category")
         ->order_by("product_category")
         ->get()->result();

      // get products to map.
      $products = $this->db->select("*")
         ->from("crateandbarrel_products")
         ->get()->result();

      $default_depts = array('living-room-furniture', 'dining-kitchen-furniture', 'storage-and-modular-furniture', 'bedroom-furniture', 'home-office-furniture', 'entryway-furniture');

      foreach ($products as $key => $pro) {
         $LS_ID = array();
         $product_categories = explode(",", $pro->product_category);
         $product_all_cat = $product_categories; // copy for ultra direct mapping.
         $product_depts = array();

         foreach ($product_categories as $key => $val) {
            if (in_array($val, $default_depts)) {
               array_push($product_depts, $val);
               unset($product_categories[$key]);
            }
         }

         foreach ($ultra_direct_map as $key => $val) {
            $product_cat = preg_replace('/\s+/', '-', strtolower(trim($val->product_category)));
            if (in_array(trim($product_cat), $product_all_cat)) {
               if (!in_array($val->LS_ID, $LS_ID))
                  array_push($LS_ID, $val->LS_ID);
            }
         }

         // direct mapping.
         foreach ($direct_map as $key => $val) {
            $product_cat = preg_replace('/\s+/', '-', strtolower(trim($val->product_category)));
            if (in_array($product_cat, $product_all_cat)) {
               // department matched. 
               // give the LS_ID to product for department.
               // match for keywords
               if (strlen($val->product_key) == 0) {
                  if (!in_array($val->LS_ID, $LS_ID))
                     array_push($LS_ID, $val->LS_ID);
               }

               if ($this->is_word_match($pro->product_name, $val->product_key)) {
                  // keyword matched 
                  // give product the LS_ID
                  if (!in_array($val->LS_ID, $LS_ID))
                     array_push($LS_ID, $val->LS_ID);
               }
            }
         }


         echo "Product Name: " . $pro->product_name . "LS_ID: " . implode(",", $LS_ID) . "\n";
         $this->db->set("LS_ID", implode(",", $LS_ID))
            ->where("product_sku", $pro->product_sku)
            ->update("crateandbarrel_products");
      }

      echo "\n == MAPPING COMPLETED == \n";
   }


   public function clean_str($str)
   {
      return str_replace(Dimension::$CLEAN_SYMBOLS, '', $str);
   }


   public function format_cb2($str)
   {
      $json_string = $str;
      if ($json_string === "null") return [];
      $dim = json_decode($json_string);
      if (json_last_error()) return [];

      $d_arr = [];
      $i = 1;
      foreach ($dim as $d) {
         if ($d->hasDimensions) {
            $d_arr['dimension_' . $i++] =  $d;
         }
      }

      //return $json_string;
      return $d_arr;
   }

   public function format_cab($str)
   {
      return Dimension::format_cb2($str);
   }

   public function format_pier1($str)
   {

      $str = Dimension::clean_str($str);

      $dim_arr = explode(",", $str);
      $i = 1;
      $dims = [];
      $dim_seq = ['Width', 'Depth', 'Height', 'Diameter'];
      foreach ($dim_arr as $dim) {
         $dim_values = [];
         $d = explode(":", $dim);
         $d_label = isset($d[0]) ? $d[0] : null;
         $d_val = isset($d[1]) ? $d[1] : null;

         if ($d_val == null) $d_val = $d[0];

         $d_val_arr = explode("x", strtolower($d_val));

         $x = 0;

         foreach ($d_val_arr as $val) {

            $val_pair = explode("\"", trim($val));
            if (isset($val_pair[0]) && isset($val_pair[1])) {
               $val = $val_pair[0];

               if (isset(Dimension::$DIMS[$val_pair[1]])) {
                  $label = Dimension::$DIMS[$val_pair[1]];
                  $x++;
               } else $label = $val_pair[1];

               if (strlen($val_pair[1]) == 0 || !isset($val_pair[1])) $label = $dim_seq[$x];

               $dim_values[$label] = $val;
               $x++;
            }
         }

         if (isset($d[1])) $dim_values['label'] = $d_label;
         $dim_values['filter'] = 1;
         array_push($dims, [
            'dimension_' . $i++ => $dim_values
         ]);
      }

      return $dims;
   }

   public function format_westelm($str)
   {
      return Dimension::format_pier1(Dimension::clean_str($str));
   }

   public function get_dims($product)
   {
      $dims = [];
      switch ($product->site_name) {

         case 'cb2':
            $dims = $this->format_cb2($product->product_dimension);
            break;
            case 'cab':
            $dims = $this->format_cab($product->product_dimension);
            break;
            case 'pier1':
            $dims = $this->format_pier1($product->product_dimension);
            break;
            case 'westelm':
            $dims = $this->format_westelm($product->product_dimension);
            break;
            /*case 'nw':
				$dims = $this->format_nw($product->product_dimension);
				break;*/
            default:
            $dims = null;
            break;
         }
         

      $dims_str = [
         'length',
         'width',
         'height',
         'depth',
         'diameter',
         'square'
      ];

      $dims_val = [];
      foreach($dims_str as $str) {
         $dims_val[$str] = [];
      }
      
      foreach ($dims_str as $value) {
         $i = 1;
         foreach ($dims as $key => $dim) {
            if (isset($dims['dimension_' . $i]->$value)) {
               if (!in_array($dims['dimension_' . $i]->$value, $dims_val[$value]) && $dims['dimension_' . $i]->$value) {
                  array_push($dims_val[$value], $dims['dimension_' . $i]->$value);
                  $i++;
               }
               
            }
         }
      }

       foreach($dims_val as $key => $val) {
         $dims_val[$key] = implode(",", $dims_val[$key]);
      } 
    
      return $dims_val;
   }

   public function merge()
   {
      $product_tables = array(
         'cb2_products_new',
         'nw_products',
         'pier1_products',
         'westelm_products_parents',
         'crateandbarrel_products'
      );

      $offset_limit = 600;
      $batch = 0;
      $offset = 0;
      $master_table = 'master_data';

      $this->db->query("TRUNCATE " . $master_table);

      foreach ($product_tables as $key => $table) {
         // get count of rows in the table
         $this->db->from($table);
         $this->db->where('product_status', 'active');
         $num_rows = $this->db->count_all_results(); // number

         $batch = 0;
         $processed = 0;
         $offset = 0;
         echo $table . "\n";

         while ($processed < $num_rows) {

            $offset = $batch * $offset_limit;

            echo "Batch: " . $batch . "\n";

            $products = $this->db->select("*")
               ->from($table)
               ->where('product_status', 'active')
               ->limit($offset_limit, $offset)
               ->get()->result();

            $batch++;
            $processed += count($products);

            foreach ($products as $key => $product) {
               $price = explode("-", $product->price);
               $min_price = -1;
               $max_price = -1;

               if (sizeof($price) > 1) {
                  $min_price = $price[0];
                  $max_price = $price[1];
               } else {
                  $min_price = $max_price = $price[0];
               }

               $pop_index = 0;
               if (isset($product->rating) && isset($product->reviews)) {
                  $pop_index = ((float) $product->rating / 2) + (2.5 * (1 - exp(-((float) $product->reviews) / 200)));
                  $pop_index = $pop_index * 1000000;
                  $pop_index = (int) $pop_index;
               }

               $dim = $this->get_dims($product);

               if ($product->site_name !== "westelm") {
                  $fields = $this->get_master_data($product, $min_price, $max_price, $pop_index, $dim);
               } else {
                  $fields = $this->get_westelm_master_data($product, $min_price, $max_price, $pop_index, $dim);
               }

               $this->db->insert($master_table, $fields);
            }

            echo "Processed: " . $processed . "\n";
         }
      }
   }


   public function index()
   {

      //Store the get request
      $status = $this->input->get();

      //Initialize CB2 Module
      $this->load->library('CB2', array(
         'proxy' => '5.79.66.2:13010',
         'debug' => false,
      ));

      if (isset($status['category'])) {
         header('Content-Type: application/json');
         echo json_encode($this->cb2->get_category($status['category']));
      } else if (isset($status['product'])) {
         header('Content-Type: application/json');
         echo json_encode($this->cb2->get_product($status['product']));
      } else {
         // change accessibility for this statement. 
         $this->db->query("TRUNCATE cb2_products_new");
         $this->db->query("TRUNCATE cb2_products_variations");

         // get product data urls from.
         $default_depts = array('all-bedroom-furniture', 'all-living-room-furniture', 'all-dining-room-furniture', 'office-furniture', 'all-outdoor-furniture');
         //$urls          = $this->db->query("SELECT * FROM cb2_categories")->result();
         //Take relevent action
         // loop here on $urls
         $db_skus = $this->db->select("product_sku")
            ->from('cb2_products_new')
            ->get()->result();
         $harveseted_SKU  = array();


         foreach ($db_skus as $sku) {
            if ($sku->product_sku != null) array_push($harveseted_SKU, $sku->product_sku);
         }


         $empty_categories = [];
         $empty_products = [];
         $urls = [
            "/furniture/all-bedroom-furniture/1",
            "/outdoor/outdoor-sofas/1",
            "/furniture/all-living-room-furniture/1",
            "/furniture/all-dining-room-furniture/1",
            "/furniture/office-furniture/",
            "/outdoor/all-outdoor-furniture/1",
            "/furniture/sectionals/1",
            "/furniture/sleepers-daybeds/1",
            "/furniture/accent-chairs/1",
            "/furniture/benches/1",
            "/furniture/ottomans-poufs-stools/1",
            "/furniture/coffee-tables/1",
            "/furniture/side-tables/1",
            "/furniture/console-tables/1",
            "/furniture/bar-counter-stools/1",
            "/furniture/beds/1",
            "/furniture/nightstands/1",
            "/furniture/dressers-chests/1",
            "/furniture/wardrobes-cabinets/1",
            "/furniture/media-consoles/1",
            "/furniture/storage-cabinets/1",
            "/furniture/bookcases/1",
            "/outdoor/outdoor-sofas/1",
            "/outdoor/outdoor-chairs/1",
            "/outdoor/outdoor-tables/1",
            "/dining/dinnerware-collections/1",
            "/bed-and-bath/all-bedding/1",
         ];
         $harveseted_prod = array();
         foreach ($urls as $key => $url) {
            $data_retry = 40;
            echo "\n || " . $url . " || \n";
            $data        = $this->cb2->get_category($url);
            $parts       = explode('/', $url);
            $product_cat = strtolower($parts[2]);
            $department  = strtolower($parts[1]);

            if (in_array($product_cat, $default_depts)) {
               $department = $product_cat;
            }

            while ((sizeof($data) <= 0) && $data_retry--) {
               // echo '\n' . sizeof($data) . "\n";
               $data = $this->cb2->get_category($url->category_url);
               echo " || DATA RETRY || " . $data_retry . "\n\n";
               sleep(20);
               if ($data_retry == 0) {
                  array_push($empty_categories, $url->category_url);
               }
            }

            if (isset($data)) {
               foreach ($data as $key => $product) {
                  $product_retry = 20;
                  $has_variations = 0;

                  $product_details = $this->cb2->get_product($product['BaseURL']);
                  while ((sizeof($product_details) <= 0) && $product_retry--) {
                     $product_details = $this->cb2->get_product($product['BaseURL']);
                     if ($data_retry == 0) {
                        array_push($empty_products, $product['BaseURL']);
                     }
                  }

                  if (isset($product_details)) {
                     $image_links   = $this->multiple_download($product_details['SecondaryImages'], '/var/www/html/cb2/images');
                     $img           = "https://cb2.scene7.com/is/image/CB2/" . $product_details['PrimaryImage'];
                     $primary_image = $this->multiple_download(array($img), '/var/www/html/cb2/images');

                     if ($product_details['Variations'] && $product['BaseSKU'] != NULL) {
                        if (sizeof($product_details['Variations']) >= 1) {
                           echo "Size of: " . sizeof($product_details['Variations']) . "\n";
                           echo "SKU: " . $product['BaseSKU'] . "\n";
                           $has_variations = 1;
                           $this->save_variations($product_details['Variations'], $product['BaseSKU']);
                        }
                     }
                  } else {
                     $image_links = NULL;
                  }
                  if (!isset($product_details['familyID'])) {
                     $product_details['familyID'] = '0000';
                  }

                  echo "\n" . $product_details['Name'] . " || " . $product_cat . " || " . $department . " || " . $LS_ID . "\n";

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
                     'LS_ID'               => 0,
                     'has_variations'      => $has_variations
                  );

                  echo "Product SKU: " . $product_details['SKU'] . "\n";
                  if (!in_array($product_details['SKU'], $harveseted_SKU)) {
                     if (NULL != $product_details['SKU']) {
                        array_push($harveseted_SKU, $product_details['SKU']);
                        $sql = $this->db->insert_string('cb2_products_new', $fields);

                        if (!$this->db->query($sql)) {
                           $log = fopen("cb2-error-log.txt", "w") or die("Unable to open file!");
                           fwrite($log, $sql . "\n\n");
                           fclose($log);
                           die('error! could not enter data in database');
                        }
                     }
                  } else {
                     $x  = $product_details['SKU'];
                     $ss = $this->db->query("SELECT product_category, LS_ID FROM cb2_products_new WHERE product_sku = '$x'")->result();

                     $product_categories_exists = explode(",", $ss[0]->product_category);

                     // only update the catgeory field if there is a new category.
                     if (!in_array($product_cat, $product_categories_exists)) {

                        $aa = array(
                           'product_category' => $ss[0]->product_category . "," . $product_cat,
                           'price'            => $product_details['CurrentPrice'],
                        );

                        $this->db->where('product_sku', $product_details['SKU']);
                        $this->db->update('cb2_products_new', $aa);
                        echo "\n\n\n || PRODUCT UPDATE FOUND || " . $ss[0]->product_category . "," . $product_cat . "\n\n\n";
                     }
                  }
                  $product_details = NULL;
               }
            }
         }
         $this->update_variations();
         var_dump($empty_categories);
         $this->mapLS_IDs();
         //$this->merge();
      }
   }

   public function get_master_data($product, $min_price, $max_price, $pop_index, $dim)
   {
      return  array(
         'product_sku'         => $product->product_sku,
         'sku_hash'            => $product->product_sku,
         'model_code'          => $product->model_code,
         'product_url'         => $product->product_url,
         'model_name'          => $product->model_name,
         'images'              => $product->images,
         'thumb'               => $product->thumb,
         'product_dimension'   => $product->product_dimension,
         'dim_width' => $dim['width'],
         'dim_height' => $dim['height'],
         'dim_depth' => $dim['depth'],
         'dim_length' => $dim['length'],
         'dim_diameter' => $dim['diameter'],
         'dim_square' => $dim['square'],
         'color'               => $product->color,
         'price'               => $product->price,
         'min_price'           => $min_price,
         'max_price'           => $max_price,
         'was_price'           => $product->was_price,
         'product_name'        => $product->product_name,
         'product_feature'     => $product->product_feature,
         'collection'          => $product->collection,
         'product_set'         => $product->product_set,
         'product_condition'   => $product->product_condition,
         'product_description' => $product->product_description,
         'created_date'        => $product->created_date,
         'updated_date'        => $product->updated_date,
         'product_images'      => $product->product_images,
         'main_product_images' => $product->main_product_images,
         'site_name'           => $product->site_name,
         'reviews'             => $product->reviews,
         'rating'              => $product->rating,
         'master_id'           => $product->master_id,
         'LS_ID'               => $product->LS_ID,
         'popularity'          => $pop_index
      );
   }

   public function get_westelm_master_data($product, $min_price, $max_price, $pop_index, $dim)
   {
      return  array(
         'product_sku'         => $product->product_id,
         'sku_hash'            => $product->product_id_hash,
         'model_code'          => null,
         'product_url'         => $product->product_url,
         'model_name'          => null,
         'images'              => $product->product_images_path,
         'thumb'               => $product->thumb_path,
         'product_dimension'   => $product->product_dimension,
         'dim_width' => $dim['width'],
         'dim_height' => $dim['height'],
         'dim_depth' => $dim['depth'],
         'dim_length' => $dim['length'],
         'dim_diameter' => $dim['diameter'],
         'dim_square' => $dim['square'],
         'color'               => null,
         'price'               => $product->price,
         'min_price'           => $min_price,
         'max_price'           => $max_price,
         'was_price'           => $product->was_price,
         'product_name'        => $product->product_name,
         'product_feature'     => $product->description_details,
         'collection'          => $product->collection,
         'product_set'         => null,
         'product_condition'   => $product->description_shipping,
         'product_description' => $product->description_overview,
         'created_date'        => $product->created_date,
         'updated_date'        => $product->updated_date,
         'product_images'      => $product->product_images_path,
         'main_product_images' => $product->main_image_path,
         'site_name'           => $product->site_name,
         'reviews'             => 0,
         'rating'              => 0,
         'master_id'           => null,
         'LS_ID'               => $product->LS_ID,
         'popularity'          => $pop_index
      );
   }
   public function mapPBLS_IDs()
   { 

   }
}


