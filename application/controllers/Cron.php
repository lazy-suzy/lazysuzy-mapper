, <?php
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
            $image_url = str_replace('$', '', $url);
            $path_arr = explode("/", $image_url);
            if (sizeof($path_arr) >= 6) {
               $path_arr_str = implode('', array_slice($path_arr, 6));
               $file   = $save_path . '/' . $path_arr_str . basename($url) ;
               $s_file = "/cb2/images/" . $path_arr_str . basename($url);
               array_push($file_paths, $s_file);           
            }
            else {
               $file   = $save_path . '/'  . basename($url) ;
               $s_file = "/cb2/images/" . basename($url);
               array_push($file_paths, $s_file);
            }

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

   public function is_word_match($name, $keyword)
   {
      if (strlen($name) > 0 && strlen($keyword) > 0) {
         $name = str_replace([",", ":", "&", "'"], "", $name);
         $name_arr = explode(" ", $name);

         if (in_array($keyword, $name_arr)) return true;
         return false;
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
         ->from("cb2_products_new_new")
         ->get()->result();

      $default_depts = array('outdoor-furniture', 'bedroom-furniture', 'living-room-furniture', 'dining-room-furniture', 'office-furniture');

      foreach ($products as $key => $pro) {
         $LS_ID = array();
         $LS_ID_no_key = array();
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
                  if (!isset($LS_ID_no_key[$product_cat]))
                     $LS_ID_no_key[$product_category] = $val->LS_ID;
               }

               if ($this->is_word_match($pro->product_name, $val->product_key)) {
                  // keyword matched 
                  // give product the LS_ID
                  $key = $product_category;
                  if (!isset($LS_ID[$key])) {
                     $LS_ID[$key] = $val->LS_ID;
                  }
                  else {
                     $newKey = $key . $val->product_key; 
                     $LS_ID[$newKey] =  $val->LS_ID;
                  }
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
                            if (!isset($LS_ID_no_key[$product_cat]))
                              $LS_ID_no_key[$product_category] = $val->LS_ID;
                        }
                        if ($this->is_word_match($pro->product_name, $val->product_key)) {
                           // keyword matched. now give the ls_id to the product. 
                           $key = $product_category;
                           if (!isset($LS_ID[$key])) {
                              $LS_ID[$key] = $val->LS_ID;
                           }
                           else {
                              $newKey = $key . $val->product_key; 
                              $LS_ID[$newKey] =  $val->LS_ID;
                           }
                        }
                     }
                  }
               }
            }
         }

         $LS_ID_val = array();
         if (sizeof($LS_ID) == 0) $LS_ID_val = $LS_ID_no_key;
         else $LS_ID_val = $LS_ID;
         
         echo "Product Name: " . $pro->product_name . "LS_ID: " . implode(",", $LS_ID) . "\n";
         $this->db->set("LS_ID", implode(",", $LS_ID_val))
            ->where("product_sku", $pro->product_sku)
            ->update("cb2_products_new_new");
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
               $this->db->from("cb2_products_variations");
               $this->db->where('variation_sku', $variation->SKU);
               $this->db->where('product_sku', $origin_sku);
               $this->db->where('variation_name', $variation->ChoiceName);
               $num_rows = $this->db->count_all_results(); // number
           // if ($product_sku != $variation['SKU']) {

               if ($num_rows == 0) {

                  echo "[VARIATIONS INSERT].\n";
                  
                  $variation_fields = array(
                     'product_sku'      => $origin_sku,
                     'variation_sku'    => $variation->SKU,
                     'variation_name'   => $variation->ChoiceName,
                     'choice_code'      => isset($variation->ChoiceCode) ? $variation->ChoiceCode : null,
                     'option_code'      => isset($variation->OptionCode) ? $variation->OptionCode : null,
                     'swatch_image'       => isset($variation->ColorImage) ? $this->multiple_download(array($variation->ColorImage), '/var/www/html/cb2/images') : null,
                     'variation_image'  => isset($variation->Image) ? $this->multiple_download(array($variation->Image), '/var/www/html/cb2/images') : null,
                  );


                  if ($variation->SKU != NULL) {
                     //echo "Variations " . "\n";
                     //var_dump($variation_fields);
                     $this->db->insert('cb2_products_variations', $variation_fields);
                  }
               }
               else {
                  echo "[VARIATIONS DUPLICATE].\n";
               }
               
           // }
         }
      }
   }

   


   public function clean_str($str)
   {
      return str_replace($this->$CLEAN_SYMBOLS, '', $str);
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
      return $this->format_cb2($str);
   }

   public function format_pier1($str)
   {

      $str = $this->clean_str($str);

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

               if (isset($this->$DIMS[$val_pair[1]])) {
                  $label = $this->$DIMS[$val_pair[1]];
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
      return $this->format_pier1($this->clean_str($str));
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
      
      if (is_array($dims)) {
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
       
      }
      else {
            foreach($dims_val as $key => $val) {
            $dims_val[$key] = null;
            } 
      }
     return $dims_val;
     

   }

   public function merge_with_dims() 
   {
   
      $product_tables = array(
         //'cb2_products_new_new',
         /*'nw_products',
         'pier1_products',*/
         'westelm_products_parents',
         //'crateandbarrel_products'
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

   public function merge()
   {
      $product_tables = array(
         'cb2_products_new_new',
         'nw_products_API',
         'pier1_products',
         'westelm_products_parents',
         'crateandbarrel_products',
         'floyd_products_parents',
         'potterybarn_products_parents'
      );

      $offset_limit = 600;
      $batch = 0;
      $offset = 0;
      $master_table = 'master_data';

      // get all master data
      $master_skus = $this->db->query("SELECT product_sku FROM " . $master_table)->result_array();
      $master_skus = array_column($master_skus, "product_sku");
      echo "Data Size: " . sizeof ($master_skus) . "\n"; 

      //$this->db->query("TRUNCATE " . $master_table);
      $CTR = 0;
      foreach ($product_tables as $key => $table) {
         // get count of rows in the table
         $this->db->from($table);
         $this->db->where('product_status', 'active');
         $num_rows = $this->db->count_all_results(); // number
         echo "Total Products: $num_rows\n"; 
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

               if (in_array($product->site_name, ["cb2", "cab"])) {

                  $urls_bits = explode("/", $product->product_url);
                  if ($urls_bits[sizeof($urls_bits)-1][0] == "f") {
                     continue;
                  }

               }

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

               $id_SITES = ["floyd", "westelm", "potterybarn"];
               
               if (!in_array($product->site_name, $id_SITES)) {
                  $fields = $this->get_master_data($product, $min_price, $max_price, $pop_index);
                  $SKU = $product->product_sku;
               }
               else {
                  $fields = $this->get_westelm_master_data($product, $min_price, $max_price, $pop_index);  
                  $SKU = $product->product_id;               
               }
               
               
               if (in_array($SKU, $master_skus)) {
                  //echo "[UPDATE] . " . $SKU . "\n";
                 
                  $this->db->set($fields);
                  $this->db->where('product_sku', $SKU);
                  $this->db->update($master_table);
                  if ($this->db->affected_rows() == '1') {
                      $CTR++;
                  } 
               }
               else {
                  //echo "[INSERT] . " . $SKU . "\n";    
                  $this->db->insert($master_table, $fields); 
               }
              
            }

            echo "Processed: " . $processed . "\n";
         }
      }

      echo "$CTR: " . $CTR . "\n"; 
   }

   public function get_data($url)
   {
    $options = array(
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER         => false,  // don't return headers
        CURLOPT_FOLLOWLOCATION => true,   // follow redirects
        CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
        CURLOPT_ENCODING       => "",     // handle compressed
        CURLOPT_USERAGENT      => "lazysuzy", // name of client
        CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT        => 120,    // time-out on response
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $content  = curl_exec($ch);

    curl_close($ch);

    return $content;
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
         /*$this->db->query("TRUNCATE cb2_products_new_new");
         $this->db->query("TRUNCATE cb2_products_variations");*/
         // get product data urls from.
         $default_depts = array('all-bedroom-furniture', 'all-living-room-furniture', 'all-dining-room-furniture', 'office-furniture', 'all-outdoor-furniture');
         //$urls          = $this->db->query("SELECT * FROM cb2_categories")->result();
         //Take relevent action
         // loop here on $urls
         $db_skus = $this->db->select("product_sku")
            ->from('cb2_products_new_new')
            ->get()->result();

         $urls = $this->db->select("*")
            ->from('cb2_category_urls')
            ->where("is_active", 1)
            ->get()->result();

         $harveseted_SKU  = array();


         foreach ($db_skus as $sku) {
            if ($sku->product_sku != null) array_push($harveseted_SKU, $sku->product_sku);
         }


         $empty_categories = [];
         $empty_products = [];
         

         $harveseted_prod = array();

         foreach ($urls as $key => $url) {
            $url_string = $url->url;
            $id = $url->cat_id;

            echo "url: " . $url_string . "\n";
            echo "ID: " . $id. "\n";
           
            $data_retry = 5;
            //echo "\n || " . $url->url . " || \n";
            $data        = $this->cb2->get_category_by_id($id);
            $parts       = explode('/', $url_string);
            $product_cat = strtolower($parts[2]);
            $department  = strtolower($parts[1]);

            echo "Data Size: " . ($data['products']). "\n";
            if (in_array($product_cat, $default_depts)) {
               $department = $product_cat;
            }

            while ((sizeof($data) == 0) && $data_retry--) {
               // echo '\n' . sizeof($data) . "\n";
               $data = $this->cb2->get_category_by_id($id);
               echo " || DATA RETRY || " . $data_retry . "\n\n";
               sleep(20);
               if ($data_retry == 0) {
                  array_push($empty_categories, $url_string);
               }
            }

            $API_products = [];
            if (isset($data['products'])) {
               echo "products count:" . sizeof($data['products']) . "\n";
               $c = 1;
               foreach ($data['products'] as $product) {
                  $product_details = $this->cb2->get_product($product['BaseURL']);
                  
                  if (sizeof($product_details) == 0) {
                     $retry = 5;
                     while (sizeof($product_details) == 0 && $retry--) {
                        echo "retry product details... " . $product['BaseURL'] . "\n";
                        sleep(10);
                        $product_details = $this->cb2->get_product($product['BaseURL']);
                     }
                  }

                  if (isset($product['BaseSKU']) && sizeof($product_details) != 0) {
                     $API_products['SKU' . $product['BaseSKU']] = $product_details;
                     $API_products['SKU' . $product['BaseSKU']]['SKU']= $product['BaseSKU'];
                     echo $c++ , "\n";
                  }
                  else {
                     echo "[EMPTY PRODUCT_DETAILS]  " . $product['BaseURL'] . "\n"; 
                  }
               }

               file_put_contents('API_products_cb2.json', json_encode($API_products)); 
               
               $API_products = json_decode(file_get_contents('API_products_cb2.json'));
               
               if (json_last_error()) {
                  die('json_error');
               }

               echo "Product Details formed.\n";
               echo "Size: " . gettype($API_products) . "\n";

               if (isset($data['availableFilters'])) {
                  foreach($data['availableFilters'] as $filter) {
                     if (isset($data['selectedFilters'])) {
                        if (isset($data['selectedFilters'][$filter])) {
                           foreach($data['selectedFilters'][$filter] as $sfilter) {
                              $str = $id . "&" . $filter . "=" . $sfilter;
                              echo "str is : " . $str . "\n";
                             
                              $EXCLUDED_FILTERS = ['depth', 'width', 'height'];
                              //$_GET[$filter] = $sfilter;
                              
                              if (!in_array(strtolower($filter), $EXCLUDED_FILTERS)) {
                                 $params = [
                                    'category_id' => $id,
                                    'filters' => [
                                       $filter => $sfilter
                                    ]
                                 ];

                                 $filter_data = $this->cb2->get_category_by_id($params);

                                 if (strtolower($filter) == "features") {
                                    $filter = "features_";
                                 }
                                 else if (strtolower($filters) == "seat capacity") {
                                    $filter = "seat_capacity"; 
                                 }

                                 $retry = 5;
                                 while (sizeof($filter_data) == 0 && $retry--) {
                                    $filter_data = $this->cb2->get_category_by_id($str);
                                    echo "retrying filter data...\n";
                                    echo var_dump($filter_data);
                                    sleep(10);
                                 }
                                 //echo var_dump($filter_data);
                                 if (sizeof($filter_data)  && 
                                    isset($filter_data['products']) && 
                                    sizeof($filter_data['products']) ) {
                                    
                                    echo "Size Filter Data: " . sizeof($filter_data) . " - " . gettype($API_products). "\n"; 
                                   

                                    foreach($filter_data['products'] as $filter_product) {
                                       $baseSku = 'SKU' . $filter_product['BaseSKU'];
                                       if (property_exists($API_products, $baseSku)) {

                                          if (isset($API_products->$baseSku->$filter)) {
                                             echo "[APPEND] " . $filter . " = " . $sfilter . "\n";
                                             $API_products->$baseSku->$filter .= "," . $sfilter;
                                          }
                                          else {
                                             echo "[NEW FILTER] " . $filter . " = " . $sfilter . "\n";
                                             $API_products->$baseSku->$filter = $sfilter; 
                                          }
                                       }
                                       else {
                                          echo "[NOT FOUND] ". $baseSku . " "  . $filter_product['BaseSKU'] . isset($API_products->$baseSku) . "\n";
                                          // save products here. 
                                          $p_details = $this->cb2->get_product($filter_product['BaseURL']);
                                          if (sizeof ($p_details) > 0) {
                                              $API_products->$baseSku = $p_details;
                                          }
                                          else {
                                              $retry = 5;
                                                while ($retry-- && sizeof($p_details) == 0) {
                                                   echo "[RETRY - filter data API_products]\n";
                                                   sleep(20);
                                                   $p_details = $this->cb2->get_product($filter_product['BaseURL']);
                                                   $API_products->$baseSku = $p_details;
                                                }
                                          }
                                         
                                         
                                          if (isset($API_products->$baseSku)) {
                                             $API_products->$baseSku[$filter] = $sfilter;
                                             $API_products->$baseSku['SKU'] = $filter_product['BaseSKU'];
                                             echo '[FILTER NEW PRODUCT ADDED] . ' . $filter . " = " . $sfilter . "\n";
                                          }
                                         
                                       }
                                    }

                                    // dump new data in a file 
                                    file_put_contents('cb2_API_products_filter.json', json_encode($API_products));

                                 }
                                 else {
                                    echo "Filter data size not approproate! \n";
                                 }
                              }
                              else {
                                 echo "Filter not included\n";
                              }
                              
                           }
                        }
                     }
                     else {
                        echo "Selected Filters not found! \n";
                     }
                  } 
               }
               else {
                  echo "Available Filters not found \n";
               }
            }

            // json_encode transformed the array to object due to which getting values from the variable was 
            // messed up.
            //$API_products = json_decode(file_get_contents('cb2_API_products_filter.json'));
           
            foreach($API_products as $sku => $product) {
               /*=================================*/
                  $has_variations = 0;
                  $product_details = $product;

                  if (isset($product_details)) {
                     $image_links   = $this->multiple_download($product_details->SecondaryImages, '/var/www/html/cb2/images');
                     $img           = "https://cb2.scene7.com/is/image/CB2/" . $product_details->PrimaryImage;
                     $primary_image = $this->multiple_download(array($img), '/var/www/html/cb2/images');

                     if ($product_details->Variations && $product->SKU != NULL) {
                        if (sizeof($product_details->Variations) > 0) {
                           echo "Size of: " . sizeof($product_details->Variations) . "\n";
                           echo "SKU: " . $product->SKU . "\n";
                           $has_variations = 1;
                           $this->save_variations($product_details->Variations, $product->SKU);
                        }
                     } 
                     else {
                        echo "[PRODUCT_DETAILS IS NULL || VARIATIONS IS NULL].\n";   
                     }
                  } else {
                     $image_links = NULL;
                     echo "[PRODUCT_DETAILS IS NULL].\n";
                  }
                  if (!isset($product_details->familyID)) {
                     $product_details->familyID = '0000';
                  }

                  echo "\n" . $product_details->Name . " || " . $product_cat . " || " . $department . " || " . $LS_ID . "\n";

                  $fields = array(
                     'product_sku'         => $product_details->SKU,
                     'sku_hash'            => md5($product_details->SKU),
                     'model_code'          => '',
                     'product_url'         => 'https://cb2.com' . $product->URL,
                     'model_name'          => '',
                     'images'              => is_array($product_details->SecondaryImages) ? implode(",", $product_details->SecondaryImages) : "",
                     'thumb'               => 'https://www.cb2.com/is/image/CB2/' . $product_details->PrimaryImage,
                     'product_dimension'   => json_encode($product_details->Dimentions[0]->productDimensions),
                     'price'               => $product_details->CurrentPrice,
                     'was_price'           => $product_details->RegularPrice,
                     'parent_category'     => $product_details->familyID,
                     'product_category'    => $product_cat,
                     'product_name'        => $product_details->Name,
                     'department'          => $department,
                     'product_feature'     => is_array($product_details->Features) ? implode('<br>', $product_details->Features): $product_details->Features,
                     'collection'          => '',
                     'product_set'         => '',
                     'product_condition'   => '',
                     'product_description' => $product_details->Description,
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
                     'reviews'             => $product_details->Reviews->ReviewCount,
                     'rating'              => $product_details->Reviews->ReviewRating,
                     'LS_ID'               => 0,
                     'has_variations'      => $has_variations,
                     'color'               => isset($product_details->Color) ? $product_details->Color : "",
                     'material'            => isset($product_details->Material) ? $product_details->Material : "",
                     'type'                => isset($product_details->Type) ? $product_details->Type : "",
                     'fabric'              => isset($product_details->Fabric) ? $product_details->Fabric : "",
                     'designer'            => isset($product_details->Designer) ? $product_details->Designer : "",
                     'features_'           => isset($product_details->features_) ? $product_details->features_ : "",
                     'shape'               => isset($product_details->Shape) ? $product_details->Shape : "",
                     'seat_capacity'       => isset($product_details->seat_capacity) ? $product_details->seat_capacity : ""
                  );

                  echo "Product SKU: " . $product_details->SKU . "\n";
                  if (!in_array($product_details->SKU, $harveseted_SKU)) {
                     if (NULL != $product_details->SKU) {

                        array_push($harveseted_SKU, $product_details->SKU);
                        $sql = $this->db->insert_string('cb2_products_new_new', $fields);

                        echo "[SAVING PRODUCT]\n";

                        if (!$this->db->query($sql)) {
                           $log = fopen("cb2-error-log.txt", "w") or die("Unable to open file!");
                           fwrite($log, $sql . "\n\n");
                           fclose($log);
                           die('error! could not enter data in database');
                        }
                     }
                     else {
                        echo "[SKU IS NULL | ERROR]\n";
                     }
                  } else {
                     echo "[PRODUCT FOUND IN HARVERSTED ARRAY]\n";
                    
                     $x  = $product_details->SKU;
                     $ss = $this->db->query("SELECT product_category, LS_ID FROM cb2_products_new_new WHERE product_sku = '$x'")->result();

                     $product_categories_exists = explode(",", $ss[0]->product_category);

                     // only update the catgeory field if there is a new category.
                     if (!in_array($product_cat, $product_categories_exists)) {

                        $aa = array(
                           'product_category' => $ss[0]->product_category . "," . $product_cat,
                           'price'            => $product_details->CurrentPrice,
                        );

                        $this->db->where('product_sku', $product_details->SKU);
                        $this->db->update('cb2_products_new_new', $aa);
                        echo "\n\n\n || PRODUCT UPDATE FOUND || " . $ss[0]->product_category . "," . $product_cat . "\n";
                     }
                  }
                  $product_details = NULL;
               
               /*==================================*/   
            }
             
            
         }
         $this->update_variations();
         var_dump($empty_categories);

         // call the color mapper here
          //$this->product_color_mapper($urls, "cb2_products_new");
         
         
         $this->mapLS_IDs();
         //$this->merge();
      }
   }

   public function product_color_mapper($categories, $table) {
      
      $colors = [
         "Black",
         "Blue",
         "Brown",
         "Clear",
         "Copper",
         "Gold",
         "Green",
         "Grey",
         "Multicolor",
         "Pink",
         "Purple",
         "Red",
         "Silver",
         "Tan",
         "White",
      ];

      $this->db->from($table);
      $this->db->where('product_status', 'active');
      $num_rows = $this->db->count_all_results(); // number

      echo "[PRODUCT_COLOR_MAPPER] Product Count: " . $num_rows . "\n";

      $batch = 0;
      $processed = 0;
      $offset = 0;
      $offset_limit = 100;
      $product_skus = [];
      
      while ($processed < $num_rows) {

         $offset = $batch * $offset_limit;

         echo "Batch: " . $batch . "\n";

         $products = $this->db->select("")
            ->from($table)
            ->where('product_status', 'active')
            ->limit($offset_limit, $offset)
            ->get()->result();

         $batch++;
         $processed += count($products);
         echo "Processed: " . $processed . "\n";

         // preprocessing and saving all the skus to an array to 
         // reduce the processing time and empty queries count in 
         // the database.
         foreach($products as $product) {
            if (strlen($product->product_sku) != 0) {
               $product_skus[trim($product->product_sku)] = true;
            }
         }
      }

      echo "SKU COUNT: " . count($product_skus) . "\n";

      foreach($categories as $category) {
         $parts       = explode('/', $category);
         $product_cat = strtolower($parts[2]);
         $department  = strtolower($parts[1]);
         
         echo "[CATEGORY] " . $product_cat . "\n";
         
         $this->db->from($table);
         $this->db->like('product_category', $product_cat);
         $products_db_count = $this->db->count_all_results(); // number
         
         foreach($colors as $color) {
            //filters/Blue~01
            $url = $category . '/filters/' . $color . '~01';
            echo "[URL] " . $url . "\n";
            $attempts = 20;

            if ($products_db_count > 0) {
                
                $data = $this->cb2->get_category($url);
                //echo json_encode($data); die();
                while (sizeof($data) == 0 && $attempts--) {
                  echo "[RETRY]" . $attempts . "\n";
                  $data = $this->cb2->get_category($url);
                  sleep(20);
                }
                echo "[API CHECK] DB Count: " . $products_db_count . " Data Count: " . count($data) . "\n";
                if (sizeof($data) > 0 && $products_db_count != count($data)) {
                  foreach($data as $product) {
                     //echo "[Debug] BaseSKU: " . $product["BaseSKU"] . "\n";
                     if (isset($product_skus[$product["BaseSKU"]])) {
                        $this->update_product_color($table, $product["BaseSKU"], $color);
                     }
                  } 
                }
            }
            


         }
      }
   }

   public function update_product_color($table, $sku, $color) {
      echo "[COLOR UPDATE] " . $table . " " .  $sku . " " . $color . "\n";
      $color = "," . $color;

      $this->db->set('color', "CONCAT(color, '" . $color . "')", FALSE)
            ->where("product_sku", $sku)
            ->update($table);



   }

   

   public function get_master_data($product, $min_price, $max_price, $pop_index, $dim = null)
   {
      $arr =  array(
         'product_sku'         => $product->product_sku,
         'sku_hash'            => $product->product_sku,
         'model_code'          => $product->model_code,
         'product_url'         => $product->product_url,
         'model_name'          => $product->model_name,
         'images'              => $product->images,
         'thumb'               => $product->thumb,
         'product_dimension'   => $product->product_dimension,
         'color'               => $product->color,
         'price'               => $product->price !== null ? $product->price : $product->was_price,
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

      $xbg_sites = ['nw'];

      if (in_array($product->site_name, $xbg_sites)) {
         $arr['image_xbg'] = $product->image_xbg;
      }

      if (isset($dims)) {
         $arr['dim_width'] = $dim['width'];
         $arr['dim_height'] = $dim['height'];
         $arr['dim_depth'] = $dim['depth'];
         $arr['dim_length'] = $dim['length'];
         $arr['dim_diameter'] = $dim['diameter'];
         $arr['dim_square'] = $dim['square'];
      }

      return $arr;
   }

   public function get_westelm_master_data($product, $min_price, $max_price, $pop_index, $dim = null)
   {
      $arr =  array(
         'product_sku'         => $product->product_id,
         'sku_hash'            => $product->product_id_hash,
         'model_code'          => null,
         'product_url'         => $product->product_url,
         'model_name'          => null,
         'images'              => $product->product_images_path,
         'thumb'               => $product->thumb_path,
         'product_dimension'   => $product->product_dimension,
        
         'color'               => $product->color,
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

      if (isset($dims)) {
         $arr['dim_width'] = $dim['width'];
         $arr['dim_height'] = $dim['height'];
         $arr['dim_depth'] = $dim['depth'];
         $arr['dim_length'] = $dim['length'];
         $arr['dim_diameter'] = $dim['diameter'];
         $arr['dim_square'] = $dim['square'];
      }

      return $arr;
   }

   public function get_all_matches($value, $array) {
      $keys = [];
      for($i = 0; $i < sizeof($array); $i++) {
         if ($array[$i] == $value) array_push($keys, $i);
      }

      //var_dump($keys);
      return $keys;
   }

   public function map_match($keys, $map, $value) {

      for($i = 0; $i < sizeof($keys); $i++) {
         if ($map[$keys[$i]] == $value) return $keys[$i];
      }

     // echo "[RETURNING -1] . $value\n";
      return -1;
   
   }

  
}
