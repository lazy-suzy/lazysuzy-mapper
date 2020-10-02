<?php
    defined('BASEPATH') or exit('No direct script access allowed');
    ini_set('memory_limit', '-1');
    ini_set('display_errors', 1);

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

        public $xbg_sites = ['nw', 'westelm'];

        private $counter_exclude_categories = [
            '/furniture/all-bedroom-furniture/1',
            '/furniture/all-living-room-furniture/1',
            '/furniture/all-dining-room-furniture/1',
            '/furniture/office-furniture/1',
            '/outdoor/all-outdoor-furniture/1',
            '/dining/dinnerware-collections/1',
            '/bed-and-bath/all-bedding/1',
            '/furniture/best-selling-furniture/1',
            '/outdoor/best-selling-outdoor/1'
        ];
    
        private $table_site_map = [
            'cb2_products_new_new'     => 'cb2',
            'nw_products_API'          => 'nw',
            'pier1_products'           => 'pier1',
            'westelm_products_parents' => 'westelm',
            'crateandbarrel_products'  => 'cab',
            'crateandbarrel_products_variations' => 'cab',
            'cb2_products_variations'  => 'cb2'
            //'floyd_products_parents',
            //'potterybarn_products_parents'
        ];

        private $variations_table_map = [
            'cb2'   => 'cb2_products_variations',
            'cnb'   => 'crateandbarrel_products_variations',
            'westelm'   => 'westelm_products_skus',
            //'nw'    => 'nw_products_API'
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

                    if (sizeof($path_arr) > 4)
                        $size_s = sizeof($path_arr) - 4;
                    else
                        $size_s = 2;

                    if (sizeof($path_arr) >= $size_s) {
                        $path_arr_str = implode('', array_slice($path_arr, $size_s));
                        $file   = $save_path . '/' . $path_arr_str . basename($url);
                        $s_file = "/cb2/img/" . $path_arr_str . basename($url);
                        array_push($file_paths, $s_file);
                    } else {
                        $file   = $save_path . '/'  . basename($url);
                        $s_file = "/cb2/img/" . basename($url);
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
                        if (strlen($val->type) > 0 && $val->type == $pro->type) {
                            if (!in_array($val->LS_ID, $LS_ID))
                                array_push($LS_ID, $val->LS_ID);
                            //break;
                        }
                        if (strlen($val->category) > 0 && $val->category == $pro->category_) {
                            if (!in_array($val->LS_ID, $LS_ID))
                                array_push($LS_ID, $val->LS_ID);
                            // break;  
                        }

                        if (strlen($val->type) == 0 && strlen($val->category) == 0) {
                            if (!in_array($val->LS_ID, $LS_ID))
                                array_push($LS_ID, $val->LS_ID);
                            // break;
                        }
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
                            } else {
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
                                        } else {
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
            $dis_SKU = $this->db->distinct()->select('product_sku')->from('cb2_products_new_new')->get()->result();
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

                    $var_name = $variation->ChoiceName;
                    $var_name = str_replace([" ", ",", "\"", "."], ["", "_", "", ""], $var_name);

                    //$varaition->SKU = $variation->SKU . '_' . $origin_sku . '_' . $var_name;

                    $this->db->from("cb2_products_variations");
                    $this->db->where('variation_sku', $variation->SKU);
                    $this->db->where('product_sku', $origin_sku);
                    $this->db->where('variation_name', $variation->ChoiceName);
                    $num_rows = $this->db->count_all_results(); // number
                    // if ($product_sku != $variation['SKU']) {

                    if ($num_rows == 0) {

                        echo "[VARIATIONS INSERT].\n";

                        $var_name = $variation->ChoiceName;
                        $var_name = str_replace(" ","", str_replace(",", "_", $var_name));

                        $variation_fields = array(
                            'product_sku'      => $origin_sku,
                            'variation_sku'    => $variation->SKU,
                            'variation_name'   => $variation->ChoiceName,
                            'choice_code'      => isset($variation->ChoiceCode) ? $variation->ChoiceCode : null,
                            'option_code'      => isset($variation->OptionCode) ? $variation->OptionCode : null,
                            'swatch_image'       => isset($variation->ColorImage) ? $this->multiple_download(array($variation->ColorImage), '/var/www/html/cb2/img') : null,
                            'variation_image'  => isset($variation->Image) ? $this->multiple_download(array($variation->Image), '/var/www/html/cb2/img') : null,
                        );


                        if ($variation->SKU != NULL) {
                            //echo "Variations " . "\n";
                            //var_dump($variation_fields);
                            $this->db->insert('cb2_products_variations', $variation_fields);
                        }
                    } else {
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
            foreach ($dims_str as $str) {
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

                foreach ($dims_val as $key => $val) {
                    $dims_val[$key] = implode(",", $dims_val[$key]);
                }
            } else {
                foreach ($dims_val as $key => $val) {
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
                //'cb2_products_new_new'
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
                            $pop_index = ((float) $product->rating / 2) + (2.5 * (1 - exp(- ((float) $product->reviews) / 200)));
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

        public function change_primary_image()
        {
            $rows = $this->db->query("SELECT product_sku, product_images FROM cb2_products_new_new WHERE 1")->result_array();

            foreach ($rows as $prod) {
                $img = explode(",", $prod['product_images'])[0];
                $sku = $prod['product_sku'];
                $this->db->query("UPDATE cb2_products_new_new SET main_product_images = '$img' WHERE product_sku = '$sku'");
            }
        }

        public function count_variations($site_name, $sku) 
        {
            if(!isset($this->variations_table_map[$site_name]))
                return null;
            
            $variations_table = $this->variations_table_map[$site_name];
            $is_westelm = in_array($site_name, ['westelm']) ? true : false; 
            $sku_field = $is_westelm ? 'product_id' : 'product_sku';
            $active_field = $is_westelm ? 'status' : 'is_active';
            $row_count = $this->db->where($sku_field, $sku)
                ->where('LENGTH(swatch_image) > ', 0, FALSE)
                ->where($active_field, 'active')
                ->group_by('swatch_image')
                ->from($variations_table)
                ->count_all_results();

            return $row_count > 0 ? $row_count : null;
        }

        public function count_var_test($site, $sku) {

            echo $site , " " , $sku , " " , $this->count_variations($site, $sku) , "\n";

            return 0;
        }

        public function merge($tables = null)
        {
            $table_site_map = array(
                'cb2_products_new_new'     => 'cb2',
                'nw_products_API'          => 'nw',
                'pier1_products'           => 'pier1',
                'westelm_products_parents' => 'westelm',
                'crateandbarrel_products'  => 'cab'
                //'floyd_products_parents',
                //'potterybarn_products_parents'
            );

            if ($tables == null) {
                $product_tables = array(
                    'cb2_products_new_new',
                    'nw_products_API',
                    'pier1_products',
                    'westelm_products_parents',
                    'crateandbarrel_products'
                    //'floyd_products_parents',
                    //'potterybarn_products_parents'
                );
            } else {
                $product_tables = explode(",", $tables);
            }

            $offset_limit = 600;
            $batch = 0;
            $offset = 0;
            $master_table = 'master_data';

            // get all master data
            $master_skus = $this->db->query("SELECT product_sku FROM " . $master_table)->result_array();
            $master_skus = array_column($master_skus, "product_sku");
            $updated_skus = [];
            echo "Data Size: " . sizeof($master_skus) . "\n";

            $CTR = 0;
            foreach ($product_tables as $key => $table) {
                // get count of rows in the table
                $this->db->from($table)
                        ->where('product_status IS NOT NULL')
                    	->where('price IS NOT NULL')
                        ->where('LENGTH(LS_ID) > 0');
                
                $num_rows = $this->db->count_all_results(); // number
                $master_skus = $this->db->query("SELECT product_sku FROM " . $master_table . " WHERE site_name = '" . $table_site_map[$table] . "'")->result_array();
                $master_skus = array_column($master_skus, "product_sku");

                echo "master skus for " . $table_site_map[$table] . " => " . sizeof($master_skus) . "\n";
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
                        ->where('product_status IS NOT NULL')
                        ->where('price IS NOT NULL')
                        ->where('LENGTH(LS_ID) > 0')
                        ->limit($offset_limit, $offset)
                        ->get()->result();

                    $batch++;
                    $processed += count($products);

                    foreach ($products as $key => $product) {

                        if (in_array($product->site_name, ["cb2", "cab"])) {

                            $urls_bits = explode("/", $product->product_url);
                            if ($urls_bits[sizeof($urls_bits) - 1][0] == "f") {
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
                            $pop_index = ((float) $product->rating / 2) + (2.5 * (1 - exp(- ((float) $product->reviews) / 200)));
                            $pop_index = $pop_index * 1000000;
                            $pop_index = (int) $pop_index;
                        }

                        $id_SITES = ["floyd", "westelm", "potterybarn"];

                        if (!in_array($product->site_name, $id_SITES)) {
                            $fields = $this->get_master_data($product, $min_price, $max_price, $pop_index);
                            $SKU = $product->product_sku;
                        } else {
                            $fields = $this->get_westelm_master_data($product, $min_price, $max_price, $pop_index);
                            $SKU = $product->product_id;
                        }


                        if (in_array($SKU, $master_skus)) {
                            //echo "[UPDATE] . " . $SKU . "\n";
                            $pos = array_search($SKU, $master_skus);
                            unset($master_skus[$pos]);

                            //if ($pos) echo "remove => " . $SKU . "\n";                  

                            $this->db->set($fields);
                            $this->db->where('product_sku', $SKU);
                            $this->db->update($master_table);
                            if ($this->db->affected_rows() == '1') {
                                $CTR++;
                            }
                        } else {
                            //echo "[INSERT] . " . $SKU . "\n";    
                            $this->db->insert($master_table, $fields);
                        }
                    }

                    echo "Processed: " . $processed . "\n";
                }

               
                             
                }
                // handle updated SKUs
                // remaining SKUs will need to be deleted from the master table because they are not active now.
                echo "remaining SKUs => " . sizeof($master_skus) . "\n";
                foreach ($master_skus as $sku) {
                    echo "mark inactive . " . $sku . "\n";
                    $this->db->set(['product_status' => 'inactive'])
                        ->where("product_sku", $sku)
                        ->update($master_table);
            }



            $this->assign_westelm_popularity();

            // this call is for setting popularity with master_id calculations
            $this->set_popularity_score();
            echo "$CTR: " . $CTR . "\n";
        }
        
        /**
         * New Merge Script to add new products to an intermediate table 
         */
        public function merge_new_products($tables = null)
        {
            $table_site_map = array(
                'cb2_products_new_new'     => 'cb2',
                'nw_products_API'          => 'nw',
                'westelm_products_parents' => 'westelm',
                'crateandbarrel_products'  => 'cab',
                // 'pier1_products'           => 'pier1',
                //'floyd_products_parents',
                //'potterybarn_products_parents'
            );

            if ($tables == null) {
                $product_tables = array(
                    'cb2_products_new_new',
                    'nw_products_API',
                    'westelm_products_parents',
                    'crateandbarrel_products'
                    // 'pier1_products',
                    //'floyd_products_parents',
                    //'potterybarn_products_parents'
                );
            } else {
                $product_tables = explode(",", $tables);
            }

            $offset_limit = 600;
            $batch = 0;
            $offset = 0;
            //master_products has all approved products and master_new has all new products yet to be approved
            $master_table = 'master_data';
            $new_products_table = 'master_new';
            $color_map_table = 'color_mapping';

        // get all master data
        $master_skus = $this->db->query("SELECT product_sku,is_locked FROM " . $master_table)->result_array();
            $updated_skus = [];
            echo "Data Size: " . sizeof($master_skus) . "\n";
            $CTR = 0;
            $colors_map = $this->db->select("color_alias,color_name")
                ->from($color_map_table)
                ->get()->result();
                
            
            foreach ($product_tables as $key => $table) {
                // get count of rows in the table
                $this->db->from($table);
                $this->db->where('product_status = "active"')
                    ->where('price IS NOT NULL');
            // ->where('LENGTH(LS_ID) > 0');

            $master_products = $this->db->query("SELECT product_sku,is_locked FROM " . $master_table . " where site_name = '" . $table_site_map[$table] . "'")->result_array();
            $master_skus = array_column($master_products, "product_sku");
            $is_locked_skus = array_column($master_products, "is_locked", "product_sku");


                echo "master skus for " . $table_site_map[$table] . " => " . sizeof($master_skus) . "\n";
                $num_rows = $this->db->count_all_results(); // number
                echo "Total Products: $num_rows\n";

                $new_skus = $this->db->query("SELECT product_sku FROM " . $new_products_table . " where site_name = '" . $table_site_map[$table] . "'")->result_array();
                $new_skus = array_column($new_skus, "product_sku");
                echo "new skus for " . $table_site_map[$table] . " => " . sizeof($new_skus) . "\n";

                $batch = 0;
                $processed = 0;
                $offset = 0;
                echo $table . "\n";

                while ($processed < $num_rows) {

                    $offset = $batch * $offset_limit;

                    echo "Batch: " . $batch . "\n";

                    $products = $this->db->select("*")
                        ->from($table)
                        ->where('product_status = "active"')
                        ->where('price IS NOT NULL')
                        // ->where('LENGTH(LS_ID) > 0')
                        ->limit($offset_limit, $offset)
                        ->get()->result();

                    $batch++;
                    $processed += count($products);
                    foreach ($products as $key => $product) {
                        
                        if (in_array($product->site_name, ["cb2", "cab"])) {

                            $urls_bits = explode("/", $product->product_url);
                            if ($urls_bits[sizeof($urls_bits) - 1][0] == "f") {
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
                            $pop_index = ((float) $product->rating / 2) + (2.5 * (1 - exp(- ((float) $product->reviews) / 200)));
                            $pop_index = $pop_index * 1000000;
                            $pop_index = (int) $pop_index;
                        }

                        $id_SITES = ["floyd", "westelm", "potterybarn"];
                        $brand = $product->site_name;
                        if (!in_array($product->site_name, $id_SITES)) {
                            $fields = $this->get_master_data($product, $min_price, $max_price, $pop_index);
                            $SKU = $product->product_sku;
                        } else {
                            $fields = $this->get_westelm_master_data($product, $min_price, $max_price, $pop_index);
                            $SKU = $product->product_id;
                        }

                        //Set custom brand name logic for westelm products
                        if ($brand == 'westelm') {
                            if (strpos($product->product_id, 'floyd') !== false) {
                                $brand = 'floyd';
                            } else if (strpos($product->product_id, 'rabbit') !== false) {
                                $brand = 'rar';
                            } else if (strpos($product->product_id, 'amigo') !== false) {
                                $brand = 'am';
                            }
                        }
                        $fields['brand'] = $brand;
                        if (in_array($SKU, $master_skus)) {
                            //echo "[UPDATE] . " . $SKU . "\n";

                            $pos = array_search($SKU, $master_skus);
                            unset($master_skus[$pos]);
                        $is_locked = $is_locked_skus[$SKU];
                        if ($is_locked === "1") {
                            continue;
                        }
                            //if ($pos) echo "remove => " . $SKU . "\n";      

                             // Only update non editable fields in master_data. Else it will overwrite previous filters            
                            if (!in_array($product->site_name, $id_SITES)) {
                                $fields = $this->get_only_non_editable_master_data($product, $min_price, $max_price, $pop_index);
                            } else {
                                $fields = $this->get_only_non_editable_westelm_data($product, $min_price, $max_price, $pop_index);
                            }
                            $this->db->set($fields);
                            $this->db->where('product_sku', $SKU);
                            $this->db->update($master_table);
                            if ($this->db->affected_rows() == '1') {
                                $CTR++;
                            }
                        }
                         else if(in_array($SKU, $new_skus)){
                            $product = $this->map_product_color($product, $colors_map);
                            $pos = array_search($SKU, $new_skus);
                            unset($new_skus[$pos]);
                            $this->db->set($fields);
                            $this->db->where('product_sku', $SKU);
                            $this->db->update($new_products_table);
                        }
                        else {
                            $product = $this->map_product_color($product, $colors_map);
                            $this->db->insert($new_products_table, $fields);
                        }
                    }
                    echo "Processed: " . $processed . "\n";
                }


                // handle updated SKUs
                // remaining SKUs will need to be deleted from the master table because they are not active now.
                echo "remaining SKUs => " . sizeof($master_skus) . "\n";
                /*foreach ($master_skus as $sku) {
            echo "deleted . " . $sku . "\n";
            $this->db->from($master_table)
                     ->where("product_sku", $sku)
                     ->delete();
         }*/
            }
            $this->assign_westelm_popularity();

            // this call is for setting popularity with master_id calculations
            $this->set_popularity_score();
            echo "$CTR: " . $CTR . "\n";
        }

        private function map_product_color($product, $color_map)
        {
            //check if product is cb2 or cnb
            if ($product->site_name == 'cb2' || $product->site_name == 'cab') {
              $product =  $this->map_cb2_cab_color($product,$color_map);
            }
            if($product->site_name=='nw')
            {
                $product = $this->map_nw_color($product,$color_map);
            }
            //map color
            return $product;
        }

        /**
         * Map Colors for CB2 or Crate&Barrel Products
         * @param mixed $product
         * @param array $color_map
         * @return mixed $product
         */
        private function map_cb2_cab_color($product,$color_map)
        {
            // Convert color string to array
            $strip_commas = str_replace(',',' ',$product->color);
            $colors = array_filter(explode(' ',$strip_commas));
            //If no colors are present Guess color from product_name
            if(count($colors)<=0){
                $product = $this->guess_color_from_product_name($product,$color_map);
                return $product;
            }
           
            // Check if an alias colors are present in the product colors
            foreach ($color_map as $map_to_color) {
                $alias = strtolower($map_to_color->color_alias);
                $key_found = array_search($alias,$colors);
                // If alias is found replace alias with base color
                if($key_found)
                {
                    $colors[$key_found]=$map_to_color->color_name;
                }
            }
            // form the colors string again seperated by comma
            $product->color = ucwords(implode(',',$colors));
            return $product;
        }

        /**
         * Map Colors for WorldMarket Products
         * Same as @method map_cb2_cab_color, just replaces ',' with '>'
         * @param mixed $product
         * @param array $color_map
         * @return mixed $product
         */
        private function map_nw_color($product,$color_map)
        {
            // Convert color string to array
            $strip_sign = str_replace('>', '', $product->color);
            $colors = array_filter(explode(' ', $strip_sign));
            if (count($colors) <= 0) {
                $product = $this->guess_color_from_product_name($product, $color_map);
                return $product;
            }

            // Check if an alias colors are present in the product colors
            foreach ($color_map as $map_to_color) {
                $alias = strtolower($map_to_color->color_alias);
                $key_found = array_search($alias, $colors);
                // If alias is found replace alias with base color
                if ($key_found) {
                    $colors[$key_found] = $map_to_color->color_name;
                }
            }
            // form the colors string again seperated by comma
            $product->color = ucwords(implode(',', $colors));
            return $product;
        }

        /**
         * Guesses color of product from its name
         * @param mixed $product
         * @param array $color_map
         * @return mixed $product
         */
        private function guess_color_from_product_name($product,$color_map)
        {
            $colors = [];
            // convert product name to lowercase for better matching
            $name = strtolower($product->product_name);

            foreach($color_map as $color)
            {
                $alias = strtolower($color->color_alias);
                $color_value = strtolower($color->color_name);

                //Check if product name contains color_alias or color_value
                $alias_pos = strpos($name,$alias);
                $color_value_pos = strpos($name,$color_value);

                if($alias_pos || $color_value_pos){
                    $colors[]=$color->color_name;
                }
            }
            //remove duplicate colors
            $colors = array_unique($colors);
            $product->color = ucwords(implode(',',$colors));
            return $product;
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

            echo "1 \n";
            //Initialize CB2 Module
            $this->load->library('CB2', array(
                'proxy' => '5.79.66.2:13010',
                'debug' => false,
            ));

            echo "2 \n";

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
                $set_inactive = array();

                foreach ($db_skus as $sku) {
                    if ($sku->product_sku != null) {
                        array_push($harveseted_SKU, $sku->product_sku);
                        $set_inactive[$sku->product_sku] = true;
                    }
                }


                $empty_categories = [];
                $empty_products = [];


                $harveseted_prod = array();
                echo "URLS: " . sizeof($urls);
                
                foreach ($urls as $key => $url) {
                    $product_counter = 0;
                    $update_product_counter = false;
                    
                    $url_string = $url->url;
                    $id = $url->cat_id;
                    
                    
                    // keep track of sequence of products that come from the API.
                    if (!in_array($id, $this->counter_exclude_categories)) {
                        $update_product_counter = true;
                    }
                    
                    
                    echo "url: " . $url_string . "\n";
                    echo "ID: " . $id . "\n";

                    $data_retry = 5;
                    //echo "\n || " . $url->url . " || \n";
                    $data        = $this->cb2->get_category_by_id($id);
                    $parts       = explode('/', $url_string);
                    $product_cat = strtolower($parts[2]);
                    $department  = strtolower($parts[1]);

                    echo "Data Size: " . ($data['products']) . "\n";
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

                                if ($update_product_counter)
                                    $product_counter += 1; // product sequence 

                                $product_details['sequence'] = $update_product_counter ? $product_counter : NULL;

                                $product_details['BaseImage'] = $product['BaseImage'];
                                $API_products['SKU' . $product['BaseSKU']] = $product_details;
                                $API_products['SKU' . $product['BaseSKU']]['SKU'] = $product['BaseSKU'];
                                echo $c++, "\n";
                            } else {
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
                            foreach ($data['availableFilters'] as $filter) {
                                if (isset($data['selectedFilters'])) {
                                    if (isset($data['selectedFilters'][$filter])) {
                                        foreach ($data['selectedFilters'][$filter] as $sfilter) {
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

                                                $filter_copy = $filter;

                                                $filter_data = $this->cb2->get_category_by_id($params);

                                                if (strtolower($filter_copy) == "features") {
                                                    $filter_copy = "features_";
                                                } else if (strtolower($filter_copy) == "seat capacity") {
                                                    $filter_copy = "seat_capacity";
                                                } else if (strtolower($filter_copy) == "category") {
                                                    $filter_copy = "category_";
                                                }

                                                $retry = 5;
                                                while (sizeof($filter_data) == 0 && $retry--) {
                                                    $filter_data = $this->cb2->get_category_by_id($params);
                                                    echo "retrying filter data...\n";
                                                    sleep(10);
                                                }
                                                //echo var_dump($filter_data);
                                                if (
                                                    sizeof($filter_data)  &&
                                                    isset($filter_data['products']) &&
                                                    sizeof($filter_data['products'])
                                                ) {

                                                    echo "Size Filter Data: " . sizeof($filter_data['products']) . " - " . gettype($API_products) . "\n";


                                                    foreach ($filter_data['products'] as $filter_product) {
                                                        $baseSku = 'SKU' . $filter_product['BaseSKU'];
                                                        if (property_exists($API_products, $baseSku)) {

                                                            echo "FOUND BaseSKU key: " . $baseSku . "\n";

                                                            if (isset($API_products->$baseSku->$filter_copy)) {
                                                                echo "[APPEND] " . $filter_copy . " = " . $sfilter . "\n";
                                                                $API_products->$baseSku->$filter_copy .= "," . $sfilter;
                                                            } else {
                                                                echo "[NEW FILTER] " . $filter_copy . " = " . $sfilter . "\n";
                                                                $API_products->$baseSku->$filter_copy = $sfilter;
                                                            }
                                                        } else {
                                                            /*  echo "[NOT FOUND] ". $baseSku . " "  . $filter_product['BaseSKU'] . isset($API_products->$baseSku) . "\n";
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
                                          }*/
                                                        }
                                                    }

                                                    // dump new data in a file 
                                                    file_put_contents('cb2_API_products_filter.json', json_encode($API_products));
                                                } else {
                                                    echo "Filter data size not approproate! \n";
                                                }
                                            } else {
                                                echo "Filter not included\n";
                                            }
                                        }
                                    }
                                } else {
                                    echo "Selected Filters not found! \n";
                                }
                            }
                        } else {
                            echo "Available Filters not found \n";
                        }
                    }

                    // json_encode transformed the array to object due to which getting values from the variable was 
                    // messed up.
                    $API_products = json_decode(file_get_contents('cb2_API_products_filter.json'));

                    foreach ($API_products as $sku => $product) {
                        /*=================================*/
                        $has_variations = 0;
                        $product_details = $product;

                        if (isset($product_details)) {
                            $image_links   = $this->multiple_download($product_details->SecondaryImages, '/var/www/html/cb2/img');
                            $img           = $product_details->BaseImage;
                            $primary_image = $this->multiple_download(array($img), '/var/www/html/cb2/img');

                            if ($product_details->Variations && $product->SKU != NULL) {
                                if (sizeof($product_details->Variations) > 0) {
                                    echo "Size of: " . sizeof($product_details->Variations) . "\n";
                                    echo "SKU: " . $product->SKU . "\n";
                                    $has_variations = 1;
                                    $this->save_variations($product_details->Variations, $product->SKU);
                                }
                            } else {
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
                            'product_feature'     => is_array($product_details->Features) ? implode('<br>', $product_details->Features) : $product_details->Features,
                            'collection'          => '',
                            'product_set'         => '',
                            'product_condition'   => '',
                            'product_description' => $product_details->Description,
                            'product_status'      => 'active',

                            'shipping_code'		  => isset($product_details->isInHomeDelivery) ? ($product_details->isInHomeDelivery ? "400" : "100" ) : null, // newly added param 07-07-2020

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
                            'seat_capacity'       => isset($product_details->seat_capacity) ? $product_details->seat_capacity : "",
                            'category_'              => isset($product_details->category_) ? $product_details->category_ : "",
                            'serial'              => $product_details->sequence

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
                            } else {
                                echo "[SKU IS NULL | ERROR]\n";
                            }
                        } else {
                            // delete this SKU so that we don't set this SKU as inactive.
                            unset($set_inactive[$product_details->SKU]);
                            
                            echo "[PRODUCT FOUND IN HARVERSTED ARRAY]\n";

                            $x  = $product_details->SKU;
                            $ss = $this->db->query("SELECT product_category, LS_ID FROM cb2_products_new_new WHERE product_sku = '$x'")->result();

                            $product_categories_exists = explode(",", $ss[0]->product_category);

                            // only update the catgeory field if there is a new category.
                            if (!in_array($product_cat, $product_categories_exists)) {
                                $n_cat = $ss[0]->product_category . "," . $product_cat;
                            } else {
                                $n_cat = $product_cat;
                            }

                            $aa = array(
                                'product_category' => $n_cat,
                                'price'            => $product_details->CurrentPrice,
                                'images'              => is_array($product_details->SecondaryImages) ? implode(",", $product_details->SecondaryImages) : "",
                                'main_product_images' => $primary_image,
                                'product_images'      => $image_links,
                                'updated_date' => gmdate('Y-m-d h:i:s \G\M\T'),
                                'category_'      => isset($product_details->category_) ? $product_details->category_ : "",
                                'shipping_code'  => isset($product_details->isInHomeDelivery) ? ($product_details->isInHomeDelivery ? "400" : "100") : null, // newly added param 07-07-2020
                                'product_status'     => 'active',
                                'serial'              => $product_details->sequence



                            );

                            $this->db->where('product_sku', $product_details->SKU);
                            $this->db->update('cb2_products_new_new', $aa);
                            echo "\n\n\n || PRODUCT UPDATE FOUND || " . $ss[0]->product_category . "," . $product_cat . "\n";
                        }

                        $product_details = NULL;

                        /*==================================*/
                    }
                }
                $this->update_variations();
                var_dump($empty_categories);


                // call the color mapper here
                //$this->product_color_mapper($urls, "cb2_products_new");

                $this->update_master_id();
                $this->mapLS_IDs();


                // set remaining product skus to inactive status 
                foreach($set_inactive as $sku => $val) {
                    $this->db->where('product_sku', $sku)   
                        ->update('cb2_products_new_new', ['product_status' => 'inactive']);
                }

                file_put_contents('marked-inactive-cb2.json', json_encode($set_inactive));
                
                //$this->merge();
            }
        }

        public function product_color_mapper($categories, $table)
        {

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
                foreach ($products as $product) {
                    if (strlen($product->product_sku) != 0) {
                        $product_skus[trim($product->product_sku)] = true;
                    }
                }
            }

            echo "SKU COUNT: " . count($product_skus) . "\n";

            foreach ($categories as $category) {
                $parts       = explode('/', $category);
                $product_cat = strtolower($parts[2]);
                $department  = strtolower($parts[1]);

                echo "[CATEGORY] " . $product_cat . "\n";

                $this->db->from($table);
                $this->db->like('product_category', $product_cat);
                $products_db_count = $this->db->count_all_results(); // number

                foreach ($colors as $color) {
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
                            foreach ($data as $product) {
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

        public function update_product_color($table, $sku, $color)
        {
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
                'was_price'           => strlen($product->was_price) > 0 ? $product->was_price : $product->price,
                'product_name'        => $product->product_name,
                'product_status'      => $product->product_status,
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
                'popularity'          => $pop_index,
                'rec_order'           => $pop_index,
                'variations_count'    => $this->count_variations($product->site_name, $product->product_sku),
                'serial'              => isset($product->serial) ? $product->serial : rand(1, 1999)
            );


            if (in_array($product->site_name, $this->xbg_sites)) {
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

            if($product->site_name == 'cb2' || $product->site_name == 'cab') {
            	$arr['shape'] = $product->shape;
            	$arr['seating'] = $product->seat_capacity;
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
                'was_price'           => strlen($product->was_price) > 0 ? $product->was_price : $product->price,
                'product_name'        => $product->product_name,
                'product_status'      => $product->product_status,
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
                'variations_count'    => $this->count_variations($product->site_name, $product->product_id),
                'serial'              => isset($product->serial) ? $product->serial : rand(1, 1999)

            );

            if ($product->site_name !== 'westelm') {
                $arr['popularity'] = $pop_index;
                $arr['rec_order']  = $pop_index;
            }


            if (in_array($product->site_name, $this->xbg_sites)) {
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

        public function get_only_non_editable_master_data($product, $min_price, $max_price, $pop_index, $dim = null)
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
                'price'               => $product->price !== null ? $product->price : $product->was_price,
                'min_price'           => $min_price,
                'max_price'           => $max_price,
                'was_price'           => strlen($product->was_price) > 0 ? $product->was_price : $product->price,
                'product_name'        => $product->product_name,
                'product_status'      => $product->product_status,
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
                'popularity'          => $pop_index,
                'rec_order'           => $pop_index,
            );


            if (in_array($product->site_name, $this->xbg_sites)) {
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
        public function get_only_non_editable_westelm_data($product, $min_price, $max_price, $pop_index, $dim = null)
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
                'price'               => $product->price,
                'min_price'           => $min_price,
                'max_price'           => $max_price,
                'was_price'           => strlen($product->was_price) > 0 ? $product->was_price : $product->price,
                'product_name'        => $product->product_name,
                'product_status'      => $product->product_status,
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
            );

            if ($product->site_name !== 'westelm') {
                $arr['popularity'] = $pop_index;
                $arr['rec_order']  = $pop_index;
            }


            if (in_array($product->site_name, $this->xbg_sites)) {
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
        public function assign_westelm_popularity()
        {
            echo "UPDATING WESTELM POPULARITY SCORES NOW...\n";
            $rows = $this->db->query("SELECT * FROM master_data WHERE site_name = 'westelm' AND LENGTH(popularity) = 1 OR popularity IS NULL")->result_array();
            echo "size: " . sizeof($rows) . "\n";
            foreach ($rows as $product) {

                $pop_index = rand(2500000, 4500000);
                $this->db->set([
                    'popularity' => $pop_index,
                    'rec_order' => $pop_index
                ])->where('product_sku', $product['product_sku'])->update('master_data');
            }
        }

        public function get_all_matches($value, $array)
        {
            $keys = [];
            for ($i = 0; $i < sizeof($array); $i++) {
                if ($array[$i] == $value) array_push($keys, $i);
            }

            //var_dump($keys);
            return $keys;
        }

        public function map_match($keys, $map, $value)
        {

            for ($i = 0; $i < sizeof($keys); $i++) {
                if ($map[$keys[$i]] == $value) return $keys[$i];
            }

            // echo "[RETURNING -1] . $value\n";
            return -1;
        }

        public function set_popularity_score()
        {

            $rows = $this->db->query("SELECT product_sku, id, master_id, popularity FROM master_data WHERE LENGTH(master_id) > 0")->result_array();


            foreach ($rows as $product) {
                $master_id = $product['master_id'];

                $skus_count = $this->db->where('master_id', $master_id)
                    ->where('id < ', $product['id'])
                    ->from('master_data')->count_all_results();

                $skus_total = $this->db->where('master_id', $master_id)
                    ->from('master_data')->count_all_results();

                //echo  " skus_count :" . $skus_count . " skus_total :" . $skus_total . " master_id :". $master_id . "\n";
                // Popularity Score - ( ( Popularity Score / No. of products with same master_ID) * No. of products appearing previously with same master_ID)

                if ($skus_count > 0 && $skus_total > 0) {
                    $new_pop =  (int) ($product['popularity'] - (($product['popularity'] / $skus_total) * $skus_count));
                    //echo  " skus_count :" . $skus_count . " skus_total :" . $skus_total . " master_id :". $master_id . "\n";
                } else {
                    $new_pop = $product['popularity'];
                }

                $this->db->set([
                    'rec_order' => $new_pop
                ])->where('id', $product['id'])
                    ->update('master_data');
            }
        }
        public function update_master_id()
        {
            $query = "UPDATE cb2_products_new_new SET master_id = '' WHERE 1";
            $this->db->query($query);

            $skus = $this->db->distinct()
                ->select('product_sku')
                ->where('has_parent_sku', 1)
                ->from('cb2_products_variations')
                ->get()->result_array();

            foreach ($skus as $sku) {
                $product_sku = $sku['product_sku'];
                $master_id = "-" . $product_sku;

                $variations = $this->db->select('variation_sku')
                    ->where('product_sku', $product_sku)
                    ->from('cb2_products_variations')
                    ->get()->result_array();

                foreach ($variations as $v_sku) {
                    $sku = $v_sku['variation_sku'];
                    $query = "UPDATE cb2_products_new_new SET master_id = CONCAT(master_id, '" . $master_id  . "') WHERE product_sku = '$sku'";
                    $this->db->query($query);
                }
            }

            $this->make_master_id_unique();
        }

        public function make_master_id_unique()
        {

            $query = "SELECT product_sku, master_id FROM cb2_products_new_new WHERE LENGTH(master_id) > 0";
            $rows = $this->db->query($query)->result_array();


            foreach ($rows as $product) {

                $master_id = $product['master_id'];
                $master_id_arr = explode('-', $master_id);

                $new_master_id = 0;
                foreach ($master_id_arr as $sku) {
                    if (strlen($sku) > 0) {
                        $new_master_id += $sku;
                    }
                }

                $product_sku = $product['product_sku'];
                $new_master_id = 'CAB' . $new_master_id;
                $query = "UPDATE cb2_products_new_new SET master_id = '$new_master_id' WHERE product_sku = '$product_sku'";
                $this->db->query($query);
            }
        }

        public function update_rec_order_westelm()
        {
            $query = "SELECT product_sku, rec_order FROM master_data_backup WHERE site_name = 'westelm'";
            $rows = $this->db->query($query)->result_array();

            foreach ($rows as $row) {
                $rec_order = $row['rec_order'];
                $sku = $row['product_sku'];
                $query = "UPDATE master_data SET rec_order = '$rec_order' WHERE product_sku = '$sku'";

                if (isset($rec_order) && intval($rec_order) > 0)
                    $this->db->query($query);
            }
        }

        public function populate_product_redirect()
        {
        	 $brand_mapping = [
		        'pier1' => 'pier1_products',
		        'cb2' => 'cb2_products_new_new',
		        'cab' => 'crateandbarrel_products',
		        'westelm' => 'westelm_products_parents',
		        'nw' => 'nw_products_API'
		    ];

		    $product_id_brands = ["floyd", "westelm", "potterybarn"];


		    $product_redirect_table = "product_redirects";

		    $this->db->query("DELETE FROM $product_redirect_table WHERE length(redirect_sku) = 0 OR redirect_sku IS NULL");

		    foreach($brand_mapping as $brand => $brand_table) {

		    	if (in_array($brand, $product_id_brands)) 
		    		$query = "SELECT product_id FROM $brand_table WHERE product_status = 'inactive'";
		    	else 
		    		$query = "SELECT product_sku FROM $brand_table WHERE product_status = 'inactive'";   	

		    	$rows = $this->db->query($query)->result_array();

		    	foreach ($rows as $key => $value) {

		    		if (in_array($brand, $product_id_brands)) {

		    			$row = $this->db->insert($product_redirect_table, [
		    						"sku" => $value['product_id'],
		    						"brand" => $brand
		    					]);
		    		}
		    		else {

						$row = $this->db->insert($product_redirect_table, [
		    						"sku" => $value['product_sku'],
		    						"brand" => $brand
    					]);
		    		}
		    	}
		    }
        }

        public function map_dining_sets() {

        	$dining_filter_rows = $this->db->from('filter_map_seating')->select("*")->get()->result();
        	$product_rows = $this->db->select(['product_name', 'LS_ID', 'product_sku'])->from('master_data')->like('LS_ID', '515')->get()->result();
        	$filter_rows = [];	
        	echo "size of products: " . sizeof($product_rows) . "\n";
        	foreach($product_rows as $row) {
        		
        		$product_name = str_replace("-", "", $row->product_name);
        		$product_name = str_replace(" ", "", $product_name);
        		$product_name = strtolower($product_name);
        		foreach($dining_filter_rows as $f) {


        			$f_key = str_replace("-", "", $f->product_key);
        			
        			if(strpos($product_name, $f_key)) {
        				echo  $row->product_sku . ": " . $row->product_name . " f: " . $f->seating  . "\n";
        				$this->db
        					->where('product_sku', $row->product_sku)
        					->update('master_data', [
        					'seating' => $f->seating
        				]);

        				break;
        			}
        		} 
        		
        	}
 
        }

        
    }
