<?php
defined('BASEPATH') or exit('No direct script access allowed');
ini_set('memory_limit', '-1');
ini_set('display_errors', 1);

class Cron extends CI_Controller
{
    public $CLEAN_SYMBOLS = ['.'];
    public $DIMS = [
        "lbs" => 'weight',
        'w' => 'width',
        'h' => 'height',
        'd' => 'depth',
        'l' => 'length',
        'h.' => 'height',
        'dia' => 'diameter',
        'dia.' => 'diameter',
        'diam' => 'diameter',
        'diam.' => 'diameter',
        'sq' => 'square',
        'sq.' => 'square',
        'd.' => 'depth',
    ];
    private $dimension_attrs = [
        'width', 'depth', 'height', 'diameter', 'weight'
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
        '/outdoor/best-selling-outdoor/1',
    ];

    private $table_site_map = [
        'cb2_products_new_new' => 'cb2',
        'nw_products_API' => 'nw',
        'pier1_products' => 'pier1',
        'westelm_products_parents' => 'westelm',
        'crateandbarrel_products' => 'cab',
        'crateandbarrel_products_variations' => 'cab',
        'cb2_products_variations' => 'cb2',
        //'floyd_products_parents',
        //'potterybarn_products_parents'
    ];

    private $variations_table_map = [
        'cb2' => 'cb2_products_variations',
        'cab' => 'crateandbarrel_products_variations',
        'westelm' => 'westelm_products_skus',
        //'nw'    => 'nw_products_API'
    ];

    private $variation_table = "cb2_products_variations";
    private $product_table = "cb2_products_new_new";

    public function make_searchable()
    {
        $products = $this->db->select("product_name, product_sku")
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
                if (strlen(trim($text)) > 0) {
                    $text_t .= "," . trim($text);
                }
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

    private function multiple_download($urls, $save_path = '/tmp', $save_path_core = "/cb2/_images/")
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

                if (sizeof($path_arr) > 4) {
                    $limit_path = sizeof($path_arr) - 4;
                } else {
                    $limit_path = 2;
                }

                if (strlen(basename($url)) == 0) {
                    log_message('error', '[INFO | FILE DOWNLOAD] Empty file found, file: ' . $url);
                    continue;
                }

                // disabling this if condition
                $limit_path = -1;
                if (sizeof($path_arr) >= $limit_path && $save_path_core == "/cnb/images/") {
                    $path_arr_str = implode('', array_slice($path_arr, $limit_path));
                    $file   = $save_path . '/' . $path_arr_str . basename($url);
                    $s_file = $save_path_core . $path_arr_str . basename($url);
                    array_push($file_paths, $s_file);
                } else {
                    $file   = $save_path . '/'  . basename($url);
                    $s_file = $save_path_core . basename($url);
                    array_push($file_paths, $s_file);
                }

                if (!is_file($file) && strlen($file) > 0) {
                    $curl_handles[$key]  = curl_init($url);
                    $file_pointers[$key] = fopen($file, "w");
                    curl_setopt($curl_handles[$key], CURLOPT_FILE, $file_pointers[$key]);
                    curl_setopt($curl_handles[$key], CURLOPT_HEADER, 0);
                    curl_setopt($curl_handles[$key], CURLOPT_CONNECTTIMEOUT, 60);
                    curl_multi_add_handle($multi_handle, $curl_handles[$key]);
                } else {
                    if (strlen($file) == 0) {
                        echo "[FILE DOWNLOAD INFO] Empty file string in file variable\n";
                    }
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

            if (in_array($keyword, $name_arr)) {
                return true;
            }

            if (in_array($keyword, $name_arr)) return true;
            return false;
        }

        return false;
    }

    public function mapLS_IDs()
    {
        // get mapping info
        $multi_map = $this->db->select("*")
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
                        if (!in_array($val->LS_ID, $LS_ID)) {
                            array_push($LS_ID, $val->LS_ID);
                        }

                        //break;
                    }
                    if (strlen($val->category) > 0 && $val->category == $pro->category_) {
                        if (!in_array($val->LS_ID, $LS_ID)) {
                            array_push($LS_ID, $val->LS_ID);
                        }

                        // break;
                    }

                    if (strlen($val->type) == 0 && strlen($val->category) == 0) {
                        if (!in_array($val->LS_ID, $LS_ID)) {
                            array_push($LS_ID, $val->LS_ID);
                        }

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
                        if (!isset($LS_ID_no_key[$product_cat])) {
                            $LS_ID_no_key[$product_cat] = $val->LS_ID;
                        }
                    }

                    if ($this->is_word_match($pro->product_name, $val->product_key)) {
                        // keyword matched
                        // give product the LS_ID
                        $key = $product_cat;
                        if (!isset($LS_ID[$key])) {
                            $LS_ID[$key] = $val->LS_ID;
                        } else {
                            $newKey = $key . $val->product_key;
                            $LS_ID[$newKey] = $val->LS_ID;
                        }
                    }
                }
            }

            // multi department mapping.
            foreach ($multi_map as $key => $val) {
                foreach ($product_depts as $index => $dept) {
                    // try to match the department
                    $prod_s_dept = preg_replace('/\s+/', '-', strtolower($val->product_sec_category));
                    if (trim($dept) == trim($prod_s_dept)) {
                        // department matched now match category.
                        // add the department LS_ID to the product;
                        foreach ($product_categories as $ind => $cat) {
                            $prod_s_cat = preg_replace('/\s+/', '-', strtolower($val->product_category));

                            if (trim($cat) == trim($prod_s_cat)) {
                                // catgeory matched. now search the keyword.
                                // give department and category LS_ID for the product.
                                if (strlen($val->product_key) == 0) {
                                    if (!isset($LS_ID_no_key[$product_cat])) {
                                        $LS_ID_no_key[$product_cat] = $val->LS_ID;
                                    }
                                }
                                if ($this->is_word_match($pro->product_name, $val->product_key)) {
                                    // keyword matched. now give the ls_id to the product.
                                    $key = $product_cat;
                                    if (!isset($LS_ID[$key])) {
                                        $LS_ID[$key] = $val->LS_ID;
                                    } else {
                                        $newKey = $key . $val->product_key;
                                        $LS_ID[$newKey] = $val->LS_ID;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $LS_ID_val = array();
            if (sizeof($LS_ID) == 0) {
                $LS_ID_val = $LS_ID_no_key;
            } else {
                $LS_ID_val = $LS_ID;
            }

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
    public function has_parent($var_sku_group)
    {
        $this->db->reset_query();
        $has_parent = $this->db->from($this->product_table)
            ->where('product_sku', (string)$var_sku_group)
            ->get()->result_array();
        return  count($has_parent) > 0 ? 1 : 0;
    }
    private function has_option_code($product_sku)
    {
        $this->db->reset_query();
        $has_parent = $this->db->from($this->variation_table)
            ->where('product_id', (string)$product_sku)
            ->where('option_code >=', 0)
            ->get()->result_array();
        return  count($has_parent) > 0 ? 1 : 0;
    }
    private function is_variations_api_applicable($variations, $product_sku = null)
    {
        // if any of the variation groups has has_parent = 0 and option code is not NULL you can run the variations API
        foreach ($variations as $var_sku_group => $var_sku_group_details) {
            if (!$this->has_parent($var_sku_group) && $this->has_option_code($product_sku))
                return true;
        }

        return false;
    }

    private function get_parent_price($var_sku_group)
    {
        $row = $this->db->from($this->product_table)
            ->where('product_sku', $var_sku_group)
            ->get()->result_array();

        if (sizeof($row) > 0)
            $row = $row[0];
        else
            return [
                "price" => NULL,
                "was_price" => NULL
            ];

        return [
            "price" => $row["price"],
            "was_price" => $row["was_price"]
        ];
    }

    private function get_var_sku($parent_sku, $var_attr_data, $var_sku_group)
    {

        if ($var_attr_data == NULL || $this->has_parent($var_sku_group))
            return $var_sku_group;

        return $var_sku_group . '-' . $var_attr_data['OptionCode'] . $var_attr_data['ChoiceCode'];
    }

    private  function is_var_present($product_sku, $var_group, $var_sku)
    {
        $count =  $this->db->from($this->variation_table)
            ->where('product_id', $product_sku)
            ->where('variation_sku_group', $var_group)
            ->where('sku', $var_sku)
            ->count_all_results();

        $this->db->reset_query();
        return $count > 0;
    }

    public function save_variations($variations = null, $product_sku = null)
    {

        echo "======== SAVING VARIATIONS ==========\n";
        return;
        $variations_from_product_details = (array)$variations;

        // check if we need variations API
        $call_variations_api = $this->is_variations_api_applicable($variations_from_product_details, $product_sku);
        $variations_from_var_api_index = [];
        if ($call_variations_api) {
            $variations_from_var_api = $this->get_data($product_sku, 'cb2', 'var');
            // do indexing
            foreach ($variations_from_var_api as $var) {
                $variations_from_var_api_index[$var['ChoiceName']] = $var;
            }
        }
        $data_to_insert = [];
        foreach ($variations_from_product_details as $var_sku_group => $var_data) {
            $var_data = (array) $var_data;
            $attr_col_counter = 1;

            if (!isset($data_to_insert[$var_sku_group]))
                $data_to_insert[$var_sku_group] = [];

            if (isset($var_data['attributes'])) {
                foreach ($var_data['attributes'] as $attr_name => $attr_data) {
                    $attr_data = (array) $attr_data;
                    foreach ($attr_data as $var_attr_data) {
                        //Please Select Color:Black
                        $var_attr_data = (array) $var_attr_data;

                        $col_name = 'attribute_' . $attr_col_counter;
                        $col_val = '' . $attr_name . ":" . $var_attr_data['ChoiceName'];

                        $var_choice_details = null;
                        if (isset($variations_from_var_api_index[$var_attr_data['ChoiceName']]))
                            $var_choice_details = $variations_from_var_api_index[$var_attr_data['ChoiceName']];

                        $var_sku = $this->get_var_sku($product_sku, $var_choice_details, $var_sku_group);
                        if (!isset($data_to_insert[$var_sku_group][$var_sku]))
                            $data_to_insert[$var_sku_group][$var_sku] = [];

                        if ($var_choice_details == NULL) {
                            $price_details = $this->get_parent_price($var_sku_group);


                            // If variations data did not come 
                            // try making a call to product sku with "text/s:SKU" as product_Sku
                            if ($price_details['price'] == NULL) {
                                $sku_call = 'text/s' . $var_sku_group;
                                $product_data = $this->get_data($sku_call, 'cb2', 'product');
                                if (
                                    !empty($product_data)
                                    && isset($product_data['CurrentPrice'])
                                    && isset($product_data['RegularPrice'])
                                ) {
                                    $price_details['price'] = $product_data['CurrentPrice'];
                                    $price_details['was_price'] = $product_data['RegularPrice'];
                                } else {
                                    echo '[INFO| VARIATION DATA NOT FOUND] call for ' . $sku_call . ' returned empty data or wrong fields' . "\n";
                                }
                            }


                            $data_to_insert[$var_sku_group][$var_sku]['price'] = $price_details['price'];
                            $data_to_insert[$var_sku_group][$var_sku]['was_price'] = $price_details['was_price'];
                            $data_to_insert[$var_sku_group][$var_sku]['has_parent_sku'] = NULL;
                            $data_to_insert[$var_sku_group][$var_sku]['has_parent_sku'] = NULL;
                            $data_to_insert[$var_sku_group][$var_sku]['price_group'] = NULL;
                        } else {

                            // if current price or regular price is 0 then that 
                            // price info from the parent

                            if ($var_choice_details['CurrentPrice'] == 0) {
                                $price_details = $this->get_parent_price($product_sku);
                                if ($price_details['price'] != NULL) {
                                    $var_choice_details['CurrentPrice'] = $price_details['price'];
                                    $var_choice_details['RegularPrice'] = $price_details['was_price'];
                                }
                            }

                            $data_to_insert[$var_sku_group][$var_sku]['price'] = $var_choice_details['CurrentPrice'];
                            $data_to_insert[$var_sku_group][$var_sku]['was_price'] = $var_choice_details['RegularPrice'];
                            $data_to_insert[$var_sku_group][$var_sku]['option_code'] = $var_choice_details['OptionCode'];
                            $data_to_insert[$var_sku_group][$var_sku]['choice_code'] = $var_choice_details['ChoiceCode'];
                            $data_to_insert[$var_sku_group][$var_sku]['price_group'] = $var_choice_details['PriceGroup'];
                        }

                        $data_to_insert[$var_sku_group][$var_sku]['has_parent_sku'] = $this->has_parent($var_sku_group);

                        if ($attr_name == "Color") {

                            $data_to_insert[$var_sku_group][$var_sku]['swatch_image_path'] = $this->multiple_download(array($var_attr_data['ColorImage']), '/var/www/html/cb2/_images/swatch', '/cb2/_images/swatch/');

                            $data_to_insert[$var_sku_group][$var_sku]['swatch_image_zoom'] = $this->multiple_download(array($var_attr_data['ColorImageZoom']), '/var/www/html/cb2/_images/swatch', '/cb2/_images/swatch/');

                            $data_to_insert[$var_sku_group][$var_sku]['image_path'] = isset($var_attr_data['Image']) ? $this->multiple_download(array($var_attr_data['Image']), '/var/www/html/cb2/_images/variations', '/cb2/_images/variations/') : NULL;
                        }

                        $data_to_insert[$var_sku_group][$var_sku]['status'] = 'active';
                        $data_to_insert[$var_sku_group][$var_sku][$col_name] = $col_val;
                    }

                    $attr_col_counter++;
                }
            }
        }

        // $data_to_insert[$var_sku_group][$var_sku] =  [col => data]
        foreach ($data_to_insert as $var_sku_group => $var_sku_group_details) {
            foreach ($var_sku_group_details as $var_sku => $col_details) {

                $product_sku = (string) $product_sku;
                $var_sku_group = (string) $var_sku_group;
                $var_sku = (string) $var_sku;

                // check if variations needs to be inserted or updated
                if ($this->is_var_present($product_sku, $var_sku_group, $var_sku)) {
                    $this->update_variations_prices($product_sku, $var_sku_group, $var_sku, $col_details);
                } else {
                    $this->insert_variations($product_sku, $var_sku_group, $var_sku, $col_details);
                }
            }
        }
    }

    private function update_variations_prices($parent_sku, $var_sku_group, $var_sku, $var_details)
    {
        // prices come from VARIATIONS API that takes in parent sku
        // $var is a array 
        // update price based on variation SKU, variation SKU group and product SKU
        $this->db->reset_query();
        $this->db->update($this->variation_table, [
            'price' => $var_details['price'],
            'was_price' => $var_details['was_price'],
            'has_parent_sku' => $this->has_parent($var_sku_group)
        ], [
            'product_id' => $parent_sku,
            'variation_sku_group' => $var_sku_group,
            'sku' => $var_sku
        ]);

        $this->db->reset_query();
    }

    private function insert_variations($parent_sku, $var_sku_group, $var_sku, $var_details)
    {
        $data = $var_details;
        $data['sku'] = $var_sku;
        $data['variation_sku_group'] = $var_sku_group;
        $data['product_id'] = $parent_sku;
        $this->db->insert($this->variation_table, $data);
    }

    public function clean_str($str)
    {
        return str_replace($this->$CLEAN_SYMBOLS, '', $str);
    }

    public function format_cb2($str)
    {
        $json_string = $str;
        if ($json_string === "null") {
            return [];
        }

        $dim = json_decode($json_string);
        if (json_last_error()) {
            return [];
        }

        $d_arr = [];
        $i = 1;
        foreach ($dim as $d) {
            if ($d->hasDimensions && $d->description == "Overall Dimensions") {
                $d_arr['dimension_' . $i++] = $d;
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
        $str = str_replace(",", " x", $str);
        $str = str_replace("lbs.", '"lbs', $str);
        
        $dim_arr = explode(",", $str);
        $i = 1;
        $dims = [];
        $dim_seq = ['Width', 'Depth', 'Height', 'Diameter', 'Weight'];
        foreach ($dim_arr as $dim) {
            $dim_values = [];
            $d = explode(":", $dim);
            $d_label = isset($d[0]) ? $d[0] : null;
            $d_val = isset($d[1]) ? $d[1] : null;

            if ($d_val == null) {
                $d_val = trim($d[0]);
            }

            $d_val_arr = explode("x", strtolower($d_val));

            $x = 0;

            foreach ($d_val_arr as $val) {
                $val_pair = explode("\"", trim($val));
                if (isset($val_pair[0]) && isset($val_pair[1])) {
                    $val = trim($val_pair[0]);

                    if (isset($this->DIMS[$val_pair[1]])) {
                        $label = $this->DIMS[$val_pair[1]];
                        $x++;
                    } else {
                        $label = $val_pair[1];
                    }

                    if (strlen($val_pair[1]) == 0 || !isset($val_pair[1])) {
                        $label = $dim_seq[$x];
                    }

                    $dim_values[$label] = $val;
                    $x++;
                }
            }

            if (isset($d[1])) {
                $dim_values['label'] = $d_label;
            }

            $dim_values['filter'] = 1;
            array_push($dims, [
                'dimension_' . $i++ => $dim_values,
            ]);
        }

        return $dims;
    }

    public function format_westelm($str)
    {
        return $this->format_pier1($this->clean_str($str));
    }

    public function format_new_world($str)
    {
        $feature_arr = explode("|", $str);
        $dims = [];
        $lines = [];
        foreach ($feature_arr as $line) {
            $line = strtolower($line);
            if (
                (strpos($line, ":") !== false
                    && strpos($line, "\"") !== false)

            ) {

                $dims_ext = $this->format_pier1(($line), false);

                if ($dims_ext != null && gettype($dims_ext) == "array") {
                    $dims = array_merge($dims, $dims_ext);
                }
            } else if (strpos($line, " x ") !== false) {
                $dims_ext = $this->format_pier1(($line), false);

                if ($dims_ext != null && gettype($dims_ext) == "array") {
                    $dims = array_merge($dims, $dims_ext);
                }

                if (sizeof($dims) > 0) {
                    $dims[0]['dimension_1']['label'] = "overall";
                }
            }
        }

        return $dims;
    }

    public function convert($dims_all, $is_westelm = false)
    {
        $dims = [];
        foreach ($dims_all as $dim) {
            foreach ($dim as $dv => $d) {
                if (!$is_westelm && strtolower($d['label']) == "overall") {
                    $dims[] = $d;
                    break;
                } else if ($is_westelm) {
                    $dims[] = $d;
                    break;
                }
            }
        }

        // dims fill have w,d,h like symbols so update those.
        foreach ($this->DIMS as $key => $value) {

            if (!empty($dims) && isset($dims[0][$key])) {
                $dims[0][$value] = (float) $dims[0][$key];
            }
        }

        return ["dimension_1" => (object) $dims[0]];
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
                $dims_all = $this->format_westelm($product->product_dimension);
                $dims = $this->convert($dims_all, true);
                break;
            case 'nw':
                $dims_all = $this->format_new_world($product->product_feature);
                $dims = $this->convert($dims_all, false);

                //echo '$dims_all: ' , json_encode($dims_all) , "\n";
                //echo '$dims: ', json_encode($dims), "\n";

                break;
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
            'square',
        ];

        $dims_val = [];
        foreach ($dims_str as $str) {
            $dims_val[$str] = [];
        }

        /**
         * Crafted of a wood frame with walnut finish, faux-leather upholstery and stainless-steel legs with brushed finish|Set of 2|360-degree swivel|Available in Black, Gray and White upholstery, sold separately|Spot clean only|Assembly required|Overall: 17.25"W x 21"D x 34.25"H, 17 lbs. each|Seat: 17.25"W x 14"D|Leg height: 15"H|Floor to top of seat: 18.5"H|Top of seat to top of back: 15.75"H
         */

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

        return $this->normalise_dims($dims_val);
    }

    public function normalise_dims($dims)
    {

        /*
        $arr['dim_width'] = strlen($dims['width']) > 0 ? (float)$dims['width'] : null;
        $arr['dim_height'] = strlen($dims['height']) > 0 ? (float)explode(",", $dims['height'])[0] : null;
        $arr['dim_depth'] = strlen($dims['depth']) > 0 ? (float)$dims['depth'] : null;
        $arr['dim_length'] = strlen($dims['length']) > 0 ? (float)$dims['length'] : null;
        $arr['dim_diameter'] = strlen($dims['diameter']) > 0 ? (float)$dims['diameter'] : null;
        $arr['dim_square'] = strlen($dims['square']) > 0 ? (float)$dims['square'] : null;
         */

        // Make adjustments to conform dimensions data between retailers.
        // Conform to depth and width convention and remove length + square measurements.
        if (strlen($dims['square']) > 0) {

            $dims['width'] = $dims['depth'] = $dims['square'];
            $dims['square'] = "";
        }

        if (strlen($dims['length']) > 0) {
            if (strlen($dims['width']) > 0) {
                $dims['depth'] = $dims['width'];
            }

            $dims['width'] = $dims['length'];
            $dims['length'] = "";
        }

        return $dims;
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
        if (!isset($this->variations_table_map[$site_name]))
            return null;

        $variations_table = $this->variations_table_map[$site_name];
        $is_westelm = in_array($site_name, ['westelm', 'cb2', 'cab']) ? true : false;
        $sku_field = $is_westelm ? 'product_id' : 'product_sku';
        $active_field = $is_westelm ? 'status' : 'is_active';
        $row_count = $this->db->where($sku_field, $sku)
            ->where('LENGTH(swatch_image_path) > ', 0, FALSE)
            ->where($active_field, 'active')
            ->group_by('swatch_image_path')
            ->from($variations_table)
            ->count_all_results();

        return $row_count > 0 ? $row_count : null;
    }

    public function count_var_test($site, $sku)
    {

        echo $site, " ", $sku, " ", $this->count_variations($site, $sku), "\n";

        return 0;
    }

    public function merge($tables = null)
    {
        $table_site_map = array(
            'cb2_products_new_new' => 'cb2',
            'nw_products_API' => 'nw',
            //'pier1_products'           => 'pier1',
            'westelm_products_parents' => 'westelm',
            'crateandbarrel_products' => 'cab',
            //'floyd_products_parents',
            //'potterybarn_products_parents'
        );

        if ($tables == null) {
            $product_tables = array(
                'cb2_products_new_new',
                'nw_products_API',
                //'pier1_products',
                'westelm_products_parents',
                'crateandbarrel_products',
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
                    //->where('product_sku', "566361")
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
                    $dims = $this->get_dims($product);

                    echo $product->product_sku, ": ";

                    echo json_encode($dims), "\n";

                    if (!in_array($product->site_name, $id_SITES)) {
                        $fields = $this->get_master_data($product, $min_price, $max_price, $pop_index, $dims);
                        $SKU = $product->product_sku;
                    } else {
                        $fields = $this->get_westelm_master_data($product, $min_price, $max_price, $pop_index, $dims);
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
                        // merge script is not supposed to insert any data in master_data table now
                        // we have a product dashboard which is to be used to merge the data 
                        // in the master data table now. 

                        //$this->db->insert($master_table, $fields);
                    }

                    //die();
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
     * New Merge Script to add new products to an intermediate table `master_new`
     * It also updates master_data products with data from product tables, but only non-editable fields are updated.
     * The non_editable fields are described in @method get_only_non_editable_master_data and @method get_only_non_editable_westelm_data
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
        $master_skus = $this->db->query("SELECT product_sku, is_locked FROM " . $master_table)->result_array();
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
                    $product = $this->map_product_color($product, $colors_map);
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

                    $dims = $this->get_dims($product);
                    $brand = $product->site_name;
                    if (!in_array($product->site_name, $id_SITES)) {
                        $fields = $this->get_master_data($product, $min_price, $max_price, $pop_index, $dims);
                        $SKU = $product->product_sku;
                    } else {
                        $fields = $this->get_westelm_master_data($product, $min_price, $max_price, $pop_index, $dims);
                        $SKU = $product->product_id;
                    }

                    //Set custom brand name logic for westelm products
                    if ($brand == 'westelm') {
                        $product_name = strtolower($product->product_name);
                        if (strpos($product_name, 'floyd') !== false) {
                            $brand = 'floyd';
                        } else if (strpos($product_name, 'rabbit') !== false) {
                            $brand = 'rar';
                        } else if (strpos($product_name, 'amigo') !== false) {
                            $brand = 'am';
                        }
                    }
                    $fields['brand'] = $brand;
                    if (in_array($SKU, $master_skus)) {
                        //echo "[UPDATE] . " . $SKU . "\n";

                        $pos = array_search($SKU, $master_skus);
                        unset($master_skus[$pos]);
                        $is_locked = $is_locked_skus[$SKU];
                        //unset($is_locked_skus[$SKU]);
                        if ($is_locked === "1") {
                            continue;
                        }
                        //if ($pos) echo "remove => " . $SKU . "\n";      

                        // Only update non editable fields in master_data. Else it will overwrite previous filters            
                        $dims = $this->get_dims($product);
                        if (!in_array($product->site_name, $id_SITES)) {
                            $fields = $this->get_only_non_editable_master_data($product, $min_price, $max_price, $pop_index, $dims);
                        } else {
                            $fields = $this->get_only_non_editable_westelm_data($product, $min_price, $max_price, $pop_index, $dims);
                        }
                        $fields['brand'] = $brand;
                        $this->db->set($fields);
                        $this->db->where('product_sku', $SKU);
                        $this->db->update($master_table);
                        if ($this->db->affected_rows() == '1') {
                            $CTR++;
                        }
                    } else if (in_array($SKU, $new_skus)) {
                        $pos = array_search($SKU, $new_skus);
                        unset($new_skus[$pos]);
                        $this->db->set($fields);
                        $this->db->where('product_sku', $SKU);
                        $this->db->update($new_products_table);
                    } else {

                        $this->db->insert($new_products_table, $fields);
                    }
                }
                echo "Processed: " . $processed . "\n";
            }


            // handle updated SKUs
            // remaining SKUs will need to be deleted from the master table because they are not active now.
            echo "remaining SKUs => " . sizeof($master_skus) . "\n";
            foreach ($master_skus as $sku) {
                echo "mark inactive . " . $sku . "\n";
                if ($is_locked_skus[$sku] == "1") {
                    continue;
                }
                $this->db->set(['product_status' => 'inactive'])
                    ->where("product_sku", $sku)
                    ->update($master_table);
            }
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
            $product = $this->map_cb2_cab_color($product, $color_map);
        }
        if ($product->site_name == 'nw') {
            $product = $this->map_nw_color($product, $color_map);
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
    private function map_cb2_cab_color($product, $color_map)
    {
        // Convert color string to array
        $strip_commas = str_replace(',', ' ', $product->color);
        $colors = array_filter(explode(' ', $strip_commas));
        //If no colors are present Guess color from product_name
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
     * Map Colors for WorldMarket Products
     * Same as @method map_cb2_cab_color, just replaces ',' with '>'
     * @param mixed $product
     * @param array $color_map
     * @return mixed $product
     */
    private function map_nw_color($product, $color_map)
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
    private function guess_color_from_product_name($product, $color_map)
    {
        $colors = [];
        // convert product name to lowercase for better matching
        $name = strtolower($product->product_name);

        foreach ($color_map as $color) {
            $alias = strtolower($color->color_alias);
            $color_value = strtolower($color->color_name);

            //Check if product name contains color_alias or color_value
            $alias_pos = strpos($name, $alias);
            $color_value_pos = strpos($name, $color_value);

            if ($alias_pos || $color_value_pos) {
                $colors[] = $color->color_name;
            }
        }
        //remove duplicate colors
        $colors = array_unique($colors);
        $product->color = ucwords(implode(',', $colors));
        return $product;
    }

    // will be always called for cb2
    public function get_data($sku, $type = 'cb2', $method = 'var')
    {
        $retry = 5;
        if ($method == 'var') {
            $data = $type == 'cb2' ? $this->cb2->get_variations($sku) : $this->cnb->get_variations($sku);

            while (sizeof($data) == 0 && $retry--) {
                echo "retry data for " . $sku . "\n";
                $data = $type == 'cb2' ? $this->cb2->get_variations($sku) : $this->cnb->get_variations($sku);
                sleep(15);
            }
        } else {

            $data = $type == 'cb2' ? $this->cb2->get_product($sku) : $this->cnb->get_product($sku);

            while (sizeof($data) == 0 && $retry--) {
                echo "retry data for " . $sku . "\n";
                $data = $type == 'cb2' ? $this->cb2->get_product($sku) : $this->cnb->get_product($sku);
                sleep(15);
            }
        }

        return $data;
    }

    public function index($filter_check = null)
    {

        $this->load->helper('utils');

        //Store the get request
        $status = $this->input->get();

        $check_for_filters = !isset($filter_check);
        if (!$check_for_filters) {
            echo "The Script will not check for filters\n\n";
        } else {
            echo "The Script will check for filters\n\n";
        }
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

            $harveseted_SKU = array();
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
            echo "URLS: " . sizeof($urls) . "\n";

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
                $data = $this->cb2->get_category_by_id($id);
                $parts = explode('/', $url_string);
                $product_cat = strtolower($parts[2]);
                $department = strtolower($parts[1]);

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

                            if ($update_product_counter) {
                                $product_counter += 1;
                            }
                            // product sequence
                            $product_details['sequence'] = $update_product_counter ? $product_counter : null;

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
                    echo "gettype product details: " . gettype($API_products) . "\n";

                    if ($check_for_filters) {
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
                                                        $filter => $sfilter,
                                                    ],
                                                ];

                                                $filter_copy = $filter;
                                                $_GET = [];
                                                $_GET['page'] = 0;
                                                $_GET[$filter_copy] = $sfilter;

                                                $filter_data = $this->cb2->get_category_by_id($id);

                                                if (strtolower($filter_copy) == "features") {
                                                    $filter_copy = "features_";
                                                } else if (strtolower($filter_copy) == "seat capacity") {
                                                    $filter_copy = "seat_capacity";
                                                } else if (strtolower($filter_copy) == "category") {
                                                    $filter_copy = "category_";
                                                }

                                                $retry = 5;
                                                while (sizeof($filter_data) == 0 && $retry--) {
                                                    $filter_data = $this->cb2->get_category_by_id($id);
                                                    echo "retrying filter data...\n";
                                                    sleep(10);
                                                }
                                                //echo var_dump($filter_data);
                                                if (
                                                    sizeof($filter_data) &&
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
                }

                // json_encode transformed the array to object due to which getting values from the variable was
                // messed up.

                if ($check_for_filters) {
                    $API_products = json_decode(file_get_contents('cb2_API_products_filter.json'));
                } else {
                    $API_products = json_decode(file_get_contents('API_products_cb2.json'));
                }

                foreach ($API_products as $sku => $product) {
                    /*=================================*/
                    $has_variations = 0;
                    $product_details = $product;

                    if (isset($product_details)) {
                        $image_links =
                            $this->multiple_download($product_details->SecondaryImages, '/var/www/html/cb2/_images/main', '/cb2/_images/main/');
                        $img =
                            $product_details->BaseImage;
                        $primary_image = $this->multiple_download(array($img), '/var/www/html/cb2/_images/main', '/cb2/_images/main/');

                        // actual variations save call is made below 
                        if ($product_details->Variations && $product->SKU != null) {
                            if (sizeof($product_details->Variations) > 0) {
                                $has_variations = 1;
                            }
                        } else {
                            echo "[PRODUCT_DETAILS IS NULL || VARIATIONS IS NULL].\n";
                        }
                    } else {
                        $image_links = null;
                        echo "[PRODUCT_DETAILS IS NULL].\n";
                    }
                    if (!isset($product_details->familyID)) {
                        $product_details->familyID = '0000';
                    }

                    echo "\n" . $product_details->Name . " || " . $product_cat . " || " . $department . " || " . $LS_ID . "\n";

                    $fields = array(
                        'product_sku' => $product_details->SKU,
                        'sku_hash' => md5($product_details->SKU),
                        'model_code' => '',
                        'product_url' => 'https://cb2.com' . $product->URL,
                        'model_name' => '',
                        'images' => is_array($product_details->SecondaryImages) ? implode(",", $product_details->SecondaryImages) : "",
                        'thumb' => 'https://www.cb2.com/is/image/CB2/' . $product_details->PrimaryImage,
                        'product_dimension' => json_encode($product_details->Dimentions[0]->productDimensions),
                        'price' => $product_details->CurrentPrice,
                        'was_price' => $product_details->RegularPrice,
                        'parent_category' => $product_details->familyID,
                        'product_category' => $product_cat,
                        'product_name' => $product_details->Name,
                        'department' => $department,
                        'product_feature' => is_array($product_details->Features) ? implode('<br>', $product_details->Features) : $product_details->Features,
                        'collection' => '',
                        'product_set' => '',
                        'product_condition' => isset($product_details->LineLevelMessages->primaryMessage->shortMessage) ? $product_details->LineLevelMessages->primaryMessage->shortMessage . "," . get_sale_price($product_details->FormattedPrice) : get_sale_price($product_details->FormattedPrice),
                        'product_description' => $product_details->Description,
                        'product_status' => 'active',

                        'shipping_code' => isset($product_details->isInHomeDelivery) ? ($product_details->isInHomeDelivery ? "400" : "100") : null, // newly added param 07-07-2020

                        'created_date' => gmdate('Y-m-d h:i:s \G\M\T'),
                        'updated_date' => gmdate('Y-m-d h:i:s \G\M\T'),
                        'is_moved' => '0',
                        'update_status' => '',
                        'product_images' => $image_links,
                        'main_product_images' => $primary_image,
                        'site_name' => 'cb2',
                        'master_id' => '',
                        'reviews' => $product_details->Reviews->ReviewCount,
                        'rating' => $product_details->Reviews->ReviewRating,
                        'LS_ID' => 0,
                        'has_variations' => $has_variations,
                        'color' => isset($product_details->Color) ? $product_details->Color : "",
                        'material' => isset($product_details->Material) ? $product_details->Material : "",
                        'type' => isset($product_details->Type) ? $product_details->Type : "",
                        'fabric' => isset($product_details->Fabric) ? $product_details->Fabric : "",
                        'designer' => isset($product_details->Designer) ? $product_details->Designer : "",
                        'features_' => isset($product_details->features_) ? $product_details->features_ : "",
                        'shape' => isset($product_details->Shape) ? $product_details->Shape : "",
                        'seat_capacity' => isset($product_details->seat_capacity) ? $product_details->seat_capacity : "",
                        'category_' => isset($product_details->category_) ? $product_details->category_ : "",
                        'serial' => $product_details->sequence,

                    );

                    echo "Product SKU: " . $product_details->SKU . "\n";
                    if (!in_array($product_details->SKU, $harveseted_SKU)) {
                        if (null != $product_details->SKU) {

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

                        $x = $product_details->SKU;
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
                            'price' => $product_details->CurrentPrice,
                            'was_price' => $product_details->RegularPrice,

                            'images' => is_array($product_details->SecondaryImages) ? implode(",", $product_details->SecondaryImages) : "",
                            'main_product_images' => $primary_image,
                            'product_images' => $image_links,
                            'updated_date' => gmdate('Y-m-d h:i:s \G\M\T'),
                            'category_' => isset($product_details->category_) ? $product_details->category_ : "",
                            'shipping_code' => isset($product_details->isInHomeDelivery) ? ($product_details->isInHomeDelivery ? "400" : "100") : null, // newly added param 07-07-2020
                            'product_status' => 'active',
                            'serial' => $product_details->sequence,
                            'reviews' => $product_details->Reviews->ReviewCount,
                            'rating' => $product_details->Reviews->ReviewRating,
                            'color' => isset($product_details->Color) ? $product_details->Color : "",
                            'material' => isset($product_details->Material) ? $product_details->Material : "",
                            'type' => isset($product_details->Type) ? $product_details->Type : "",
                            'fabric' => isset($product_details->Fabric) ? $product_details->Fabric : "",
                            'designer' => isset($product_details->Designer) ? $product_details->Designer : "",
                            'features_' => isset($product_details->features_) ? $product_details->features_ : "",
                            'shape' => isset($product_details->Shape) ? $product_details->Shape : "",
                            'seat_capacity' => isset($product_details->seat_capacity) ? $product_details->seat_capacity : "",
                            'category_' => isset($product_details->category_) ? $product_details->category_ : "",
                            'product_condition' => isset($product_details->LineLevelMessages->primaryMessage->shortMessage) ? $product_details->LineLevelMessages->primaryMessage->shortMessage . "," . get_sale_price($product_details->FormattedPrice) : get_sale_price($product_details->FormattedPrice),

                        );

                        $this->db->where('product_sku', (string) $product_details->SKU);
                        $this->db->update('cb2_products_new_new', $aa);
                        echo "\n\n\n || PRODUCT UPDATE FOUND || " . $ss[0]->product_category . "," . $product_cat . "\n";
                    }

                    // actual variations save and update call.
                    echo " ======= >> :" . gettype($product_details->Variations);
                    // update variations once product SKU is being inserted or updated.
                    if (isset($product_details->Variations) && $product->SKU != NULL) {
                        echo "===\n";
                        echo "SKU: " . $product->SKU . "\n";
                        $has_variations = 1;
                        $this->save_variations($product_details->Variations, $product->SKU);
                    } else {
                        echo "[PRODUCT_DETAILS IS NULL || VARIATIONS IS NULL].\n";
                    }

                    $product_details = null;

                    /*==================================*/
                }
            }
            // $this->update_variations();
            var_dump($empty_categories);

            // call the color mapper here
            //$this->product_color_mapper($urls, "cb2_products_new");

            $this->update_master_id();
            $this->mapLS_IDs();

            // set remaining product skus to inactive status
            foreach ($set_inactive as $sku => $val) {
                $this->db->where('product_sku', $sku)
                    ->update('cb2_products_new_new', ['product_status' => 'inactive']);
            }

            file_put_contents('marked-inactive-cb2.json', json_encode($set_inactive));
            log_message('error', '[INFO | END] Cron.php index');

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
            $parts = explode('/', $category);
            $product_cat = strtolower($parts[2]);
            $department = strtolower($parts[1]);

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


    public function get_only_non_editable_master_data($product, $min_price, $max_price, $pop_index, $dims = null)
    {
        $arr =  array(
            'product_sku'         => $product->product_sku,
            //'sku_hash'            => $product->product_sku,
            //'model_code'          => $product->model_code,
            'product_url'         => $product->product_url,
            //'model_name'          => $product->model_name,
            //'images'              => $product->images,
            //'thumb'               => $product->thumb,
            // 'product_dimension'   => $product->product_dimension,
            'price'               => $product->price !== null ? $product->price : $product->was_price,
            'min_price'           => $min_price,
            'max_price'           => $max_price,
            'was_price'           => strlen($product->was_price) > 0 ? $product->was_price : $product->price,
            'product_name'        => $product->product_name,
            'product_status'      => $product->product_status,
            // 'product_feature'     => $product->product_feature,
            //                'collection'          => $product->collection,
            'product_set'         => $product->product_set,
            // 'product_condition'   => $product->product_condition,
            // 'product_description' => $product->product_description,
            'created_date'        => $product->created_date,
            'updated_date'        => $product->updated_date,
            //'product_images'      => $product->product_images,
            //'main_product_images' => $product->main_product_images,
            'site_name'           => $product->site_name,
            'reviews'             => $product->reviews,
            'rating'              => $product->rating,
            'popularity'          => $pop_index,
            'rec_order'           => $pop_index,
            'variations_count'    => $this->count_variations($product->site_name, $product->product_sku),
            'serial'              => isset($product->serial) ? $product->serial : rand(1, 1999)
        );


        if (in_array($product->site_name, $this->xbg_sites)) {
            $arr['image_xbg'] = $product->image_xbg;
        }

        if (isset($dims)) {
            $arr['dim_width'] = strlen($dims['width']) > 0 ? (float)$dims['width'] : null;
            $arr['dim_height'] = strlen($dims['height']) > 0 ? (float)explode(",", $dims['height'])[0] : null;
            $arr['dim_depth'] = strlen($dims['depth']) > 0 ? (float)$dims['depth'] : null;
            $arr['dim_length'] = strlen($dims['length']) > 0 ? (float)$dims['length'] : null;
            $arr['dim_diameter'] = strlen($dims['diameter']) > 0 ? (float)$dims['diameter'] : null;
            $arr['dim_square'] = strlen($dims['square']) > 0 ? (float)$dims['square'] : null;
        }
        return $arr;
    }
    public function get_only_non_editable_westelm_data($product, $min_price, $max_price, $pop_index, $dims = null)
    {
        $arr = array(
            'product_sku' => $product->product_id,
            // 'sku_hash' => $product->product_id_hash,
            // 'model_code' => null,
            'product_url' => $product->product_url,
            // 'model_name' => null,
            // 'images' => $product->product_images_path,
            // 'thumb' => $product->thumb_path,
            // 'product_dimension'   => $product->product_dimension,
            'price' => $product->price,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'was_price' => strlen($product->was_price) > 0 ? $product->was_price : $product->price,
            'product_name' => $product->product_name,
            'product_status' => $product->product_status,
            // 'product_feature'     => $product->description_details,
            //                'collection'          => $product->collection,
            'product_set' => null,
            // 'product_condition'   => null,
            // 'product_condition'   => $product->description_shipping,
            // 'product_description' => $product->description_overview,
            'created_date' => $product->created_date,
            'updated_date' => $product->updated_date,
            'product_images' => $product->product_images_path,
            'main_product_images' => $product->main_image_path,
            'site_name' => $product->site_name,
            'reviews' => 0,
            'rating' => 0,
            'variations_count' => $this->count_variations($product->site_name, $product->product_id),
            'serial' => isset($product->serial) ? $product->serial : rand(1, 1999),
        );

        if ($product->site_name !== 'westelm') {
            $arr['popularity'] = $pop_index;
            $arr['rec_order'] = $pop_index;
        }

        if (in_array($product->site_name, $this->xbg_sites)) {
            $arr['image_xbg'] = $product->image_xbg;
        }

        if (isset($dims)) {
            $arr['dim_width'] = strlen($dims['width']) > 0 ? (float) $dims['width'] : null;
            $arr['dim_height'] = strlen($dims['height']) > 0 ? (float) explode(",", $dims['height'])[0] : null;
            $arr['dim_depth'] = strlen($dims['depth']) > 0 ? (float) $dims['depth'] : null;
            $arr['dim_length'] = strlen($dims['length']) > 0 ? (float) $dims['length'] : null;
            $arr['dim_diameter'] = strlen($dims['diameter']) > 0 ? (float) $dims['diameter'] : null;
            $arr['dim_square'] = strlen($dims['square']) > 0 ? (float) $dims['square'] : null;
        }

        return $arr;
    }
    public function is_Handmade($description)
    {
        $features = strtolower($description);
        $handmade = 0;
        if (strpos($features, 'handmade') || strpos($features, 'handcrafted')) {
            $handmade = 1;
        }
        return $handmade;
    }

    public function get_master_data($product, $min_price, $max_price, $pop_index, $dims = null)
    {
        $arr = array(
            'product_sku' => $product->product_sku,
            // 'sku_hash' => $product->product_sku,
            // 'model_code' => $product->model_code,
            'product_url' => $product->product_url,
            // 'model_name' => $product->model_name,
            // 'images' => $product->images,
            // 'thumb' => $product->thumb,
            'product_dimension'   => $product->product_dimension,
            'color' => $product->color,
            'price' => $product->price !== null ? $product->price : $product->was_price,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'was_price' => strlen($product->was_price) > 0 ? $product->was_price : $product->price,
            'product_name' => $product->product_name,
            'product_status' => $product->product_status,
            'product_feature' => $product->product_feature,
            'collection' => $product->collection,
            'product_set' => $product->product_set,
            'product_condition' => $product->product_condition,
            'product_description' => $product->product_description,
            'created_date' => $product->created_date,
            'updated_date' => $product->updated_date,
            'product_images' => $product->product_images,
            'main_product_images' => $product->main_product_images,
            'site_name' => $product->site_name,
            'reviews' => $product->reviews,
            'rating' => $product->rating,
            'master_id' => $product->master_id,
            'LS_ID' => $product->LS_ID,
            'popularity' => $pop_index,
            'rec_order' => $pop_index,
            'variations_count' => $this->count_variations($product->site_name, $product->product_sku),
            'serial' => isset($product->serial) ? $product->serial : rand(1, 1999),
        );
        if ($product->site_name === 'cb2' || $product->site_name === 'cab') {
            $arr['is_back_order'] = $product->is_back_order;
            $arr['back_order_msg'] = $product->back_order_msg;
            $arr['back_order_msg_date'] = $product->back_order_msg_date;
            $arr['online_msg'] = $product->online_msg;
        }

        if (in_array($product->site_name, $this->xbg_sites)) {
            $arr['image_xbg'] = $product->image_xbg;
        }
        if (isset($dims)) {
            $arr['dim_width'] = strlen($dims['width']) > 0 ? (float) $dims['width'] : null;
            $arr['dim_height'] = strlen($dims['height']) > 0 ? (float) explode(",", $dims['height'])[0] : null;
            $arr['dim_depth'] = strlen($dims['depth']) > 0 ? (float) $dims['depth'] : null;
            $arr['dim_length'] = strlen($dims['length']) > 0 ? (float) $dims['length'] : null;
            $arr['dim_diameter'] = strlen($dims['diameter']) > 0 ? (float) $dims['diameter'] : null;
            $arr['dim_square'] = strlen($dims['square']) > 0 ? (float) $dims['square'] : null;
        } else {
        }
        if ($product->site_name === 'nw') {
            $arr['product_dimension'] = json_encode($this->convert_nw_to_standard_dimensions($product->product_feature));
        }
        if ($product->site_name == 'cb2' || $product->site_name == 'cab') {
            $arr['shape'] = $product->shape;
            $arr['seating'] = $product->seat_capacity;

            // add availability info too.
            $arr['is_back_order'] = isset($product->is_back_order) ? $product->is_back_order : "";
            $arr['back_order_msg'] = isset($product->back_order_msg) ? $product->back_order_msg : "";
            $arr['back_order_msg_date'] = isset($product->back_order_msg_date) ? $product->back_order_msg_date : "";
            $arr['online_msg'] = isset($product->online_msg) ? $product->online_msg : "";

            // transform dimensions data based on new json format scheme for uniform structure
            $arr['product_dimension'] = json_encode($this->format_cb2_to_westelm_dimensions($product->product_dimension));
        }

        return $arr;
    }


    public function extract_westelm_details($details)
    {
        $newDescription = [];
        $overviewArray = [];
        $overview = '';
        $featuresArray = [];
        $features = '';
        $i = 0;
      //  $details = str_replace('\n', '', $details);
        $details = explode("\n",$details);
        while($i < count($details) && trim($details[$i])[0]!=='*'){
            $overviewArray[]= $details[$i];
            $i++;
        }
        $overview = trim(implode("\n",$overviewArray));
        while($i<count($details) && isset($details[$i])){
            $featuresArray[] = $details[$i];
            $i++;
        }
        $features = trim(implode("\n",$featuresArray));
        $newDescription['overview'] = trim(str_replace('###### KEY DETAILS', '', $overview));
        $newDescription['feature'] = str_replace('*','',$features);
        return $newDescription;
    }
    public function extract_westelm_features($features)
    {
        $newFeatures = [];
        $header = '';
        $newLine = '';
        $i = 0;
        $features = str_replace('\n', '', $features);
        while ($i < strlen($features)) {
            if ($features[$i] == '*' && $features[$i + 1] == '*') {
                $i = $i + 2;
                while (isset($features[$i]) && ($features[$i] !== '*' || $features[$i + 1] !== '*')) {
                    $header .= $features[$i++];
                }
                if (trim($header) === 'ASSEMBLY INSTRUCTIONS') {
                    $i = $i + 2;
                    while (isset($features[$i]) && ($features[$i] !== '*' || $features[$i + 1] !== '*')) {
                        $newLine .= $features[$i++];
                    }
                    $newFeatures['assembly_instructions'] = trim($newLine);
                    $newLine = '';
                    $header = '';
                }
                if (trim($header) === 'CARE') {
                    $i = $i + 2;
                    while (isset($features[$i]) && ($features[$i] !== '*' || $features[$i + 1] !== '*')) {
                        $newLine .= $features[$i++];
                    }
                    $newFeatures['care'] = trim($newLine);
                    $newLine = '';
                    $header = '';
                }
                if ($header !== '') {
                    $i++;
                    $header = '';
                }
            } else {
                $i++;
            }
        }
        if(count($newFeatures) === 0){
            $newFeatures['features'] = str_replace('*', '', $features);
        }
        return $newFeatures;
    }

    public function get_westelm_master_data($product, $min_price, $max_price, $pop_index, $dims = null)
    {

        $arr = array(
            'product_sku' => $product->product_id,
            //'sku_hash' => $product->product_id_hash,
            //'model_code' => null,
            'product_url' => $product->product_url,
            //'model_name' => null,
            // 'images' => $product->product_images_path,
            // 'thumb' => $product->thumb_path,
            'product_dimension' => json_encode($this->westelm_normalize_dimensions($product->description_details)),
            'color' => $product->color,
            'price' => $product->price,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'was_price' => strlen($product->was_price) > 0 ? $product->was_price : $product->price,
            'product_name' => $product->product_name,
            'product_status' => $product->product_status,
            'product_feature' => $product->description_details,
            'collection' => $product->collection,
            'product_set' => null,
            'product_condition' => null,
            'product_description' => $product->description_overview,
            'created_date' => $product->created_date,
            'updated_date' => $product->updated_date,
            'product_images' => $product->product_images_path,
            'main_product_images' => $product->main_image_path,
            'site_name' => $product->site_name,
            'reviews' => 0,
            'rating' => 0,
            'master_id' => null,
            'LS_ID' => $product->LS_ID,
            'variations_count' => $this->count_variations($product->site_name, $product->product_id),
            'serial' => isset($product->serial) ? $product->serial : rand(1, 1999),

        );

        if ($product->site_name !== 'westelm') {
            $arr['popularity'] = $pop_index;
            $arr['rec_order'] = $pop_index;
        }
        $description = $this->extract_westelm_details($product->description_overview);
        $arr['product_description'] = $description['overview'];
        $arr['product_feature'] = $description['feature'];
        $features = $this->extract_westelm_features($product->description_details);
        $arr['product_assembly'] = $features['assembly_instructions'];
        $arr['product_care'] = $features['care'];
        if($features['features']){
            $arr['product_feature'] = $features['features'];
        }
        if (in_array($product->site_name, $this->xbg_sites)) {
            $arr['image_xbg'] = $product->image_xbg;
        }

        if (isset($dims)) {
            $arr['dim_width'] = strlen($dims['width']) > 0 ? (float) $dims['width'] : null;
            $arr['dim_height'] = strlen($dims['height']) > 0 ? (float) explode(",", $dims['height'])[0] : null;
            $arr['dim_depth'] = strlen($dims['depth']) > 0 ? (float) $dims['depth'] : null;
            $arr['dim_length'] = strlen($dims['length']) > 0 ? (float) $dims['length'] : null;
            $arr['dim_diameter'] = strlen($dims['diameter']) > 0 ? (float) $dims['diameter'] : null;
            $arr['dim_square'] = strlen($dims['square']) > 0 ? (float) $dims['square'] : null;
        }

        if (in_array($product->site_name, $this->xbg_sites)) {
            $arr['image_xbg'] = $product->image_xbg;
        }

        if (isset($dims)) {
            $arr['dim_width'] = strlen($dims['width']) > 0 ? (float)$dims['width'] : null;
            $arr['dim_height'] = strlen($dims['height']) > 0 ? (float)explode(",", $dims['height'])[0] : null;
            $arr['dim_depth'] = strlen($dims['depth']) > 0 ? (float)$dims['depth'] : null;
            $arr['dim_length'] = strlen($dims['length']) > 0 ? (float)$dims['length'] : null;
            $arr['dim_diameter'] = strlen($dims['diameter']) > 0 ? (float)$dims['diameter'] : null;
            $arr['dim_square'] = strlen($dims['square']) > 0 ? (float)$dims['square'] : null;
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
            if ($map[$keys[$i]] == $value) {
                return $keys[$i];
            }
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
                $new_pop = (int) ($product['popularity'] - (($product['popularity'] / $skus_total) * $skus_count));
                //echo  " skus_count :" . $skus_count . " skus_total :" . $skus_total . " master_id :". $master_id . "\n";
            } else {
                $new_pop = $product['popularity'];
            }

            $this->db->set([
                'rec_order' => $new_pop,
            ])->where('id', $product['id'])
                ->update('master_data');
        }
    }
    public function update_master_id()
    {
        $query = "UPDATE cb2_products_new_new SET master_id = '' WHERE 1";
        $this->db->query($query);

        $skus = $this->db->distinct()
            ->select('product_id')
            ->where('has_parent_sku', 1)
            ->from('cb2_products_variations')
            ->get()->result_array();

        foreach ($skus as $sku) {
            $product_sku = $sku['product_id'];
            $master_id = "-" . $product_sku;

            $variations = $this->db->select('sku')
                ->where('product_id', $product_sku)
                ->from('cb2_products_variations')
                ->get()->result_array();

            foreach ($variations as $v_sku) {
                $sku = $v_sku['sku'];
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

            if (isset($rec_order) && intval($rec_order) > 0) {
                $this->db->query($query);
            }
        }
    }

    public function populate_product_redirect()
    {
        $brand_mapping = [
            'pier1' => 'pier1_products',
            'cb2' => 'cb2_products_new_new',
            'cab' => 'crateandbarrel_products',
            'westelm' => 'westelm_products_parents',
            'nw' => 'nw_products_API',
        ];

        $product_id_brands = ["floyd", "westelm", "potterybarn"];

        $product_redirect_table = "product_redirects";

        $this->db->query("DELETE FROM $product_redirect_table WHERE length(redirect_sku) = 0 OR redirect_sku IS NULL");

        foreach ($brand_mapping as $brand => $brand_table) {

            if (in_array($brand, $product_id_brands)) {
                $query = "SELECT product_id FROM $brand_table WHERE product_status = 'inactive'";
            } else {
                $query = "SELECT product_sku FROM $brand_table WHERE product_status = 'inactive'";
            }

            $rows = $this->db->query($query)->result_array();

            foreach ($rows as $key => $value) {

                if (in_array($brand, $product_id_brands)) {

                    $row = $this->db->insert($product_redirect_table, [
                        "sku" => $value['product_id'],
                        "brand" => $brand,
                    ]);
                } else {

                    $row = $this->db->insert($product_redirect_table, [
                        "sku" => $value['product_sku'],
                        "brand" => $brand,
                    ]);
                }
            }
        }
    }

    public function map_dining_sets()
    {

        $dining_filter_rows = $this->db->from('filter_map_seating')->select("*")->get()->result();
        $product_rows = $this->db->select(['product_name', 'LS_ID', 'product_sku'])->from('master_data')->like('LS_ID', '515')->get()->result();
        $filter_rows = [];
        echo "size of products: " . sizeof($product_rows) . "\n";
        foreach ($product_rows as $row) {

            $product_name = str_replace("-", "", $row->product_name);
            $product_name = str_replace(" ", "", $product_name);
            $product_name = strtolower($product_name);
            foreach ($dining_filter_rows as $f) {

                $f_key = str_replace("-", "", $f->product_key);

                if (strpos($product_name, $f_key)) {
                    echo $row->product_sku . ": " . $row->product_name . " f: " . $f->seating . "\n";
                    $this->db
                        ->where('product_sku', $row->product_sku)
                        ->update('master_data', [
                            'seating' => $f->seating,
                        ]);

                    break;
                }
            }
        }
    }


    /**********************************************************
     *   DEPRECIATED
     ***********************************************************/
    /**
     * Transform westelm dimensions to be like CAB and CB2 dimensions
     *[{"hasDimensions":true,"dimensionSequence":1,"description":"Overall Dimensions","width":20,"depth":16.5,"height":23,"diameter":0,"weight":0}]
     * @return void
     */
    public function normalise_westelm_dimensions()
    {

        $westelm_rows = $this->db->select(['id', 'product_dimension'])
            ->from('master_data')
            ->where('site_name', 'westelm')
            //->limit(5)
            ->get()->result();


        foreach ($westelm_rows as $row) {

            $dim_str = $row->product_dimension;
            $id = $row->id;

            if (strlen($dim_str) == 0)
                continue;

            $dims_nr = $this->format_westelm($dim_str);
            $dims_nr = $this->convert($dims_nr, true);

            $normalised_dims = [];

            if (isset($dims_nr["dimension_1"])) {
                $obj = new stdClass;
                $obj->hasDimensions = true;
                $obj->dimensionSequence = 1;
                $obj->description = "Overall Dimensions";
                foreach ($dims_nr["dimension_1"] as $prop => $val) {
                    $obj->$prop = $val;
                }

                $normalised_dims[] = $obj;
            }

            echo $id, ' ', $dim_str . "\n";
            echo json_encode($normalised_dims) . "\n\n";

            $this->db->set('product_dimension', json_encode($normalised_dims))
                ->where('id', $id)
                ->update('master_data');
        }
    }

    public function westelm_normalize_dimensions($dims_str)
    {

        $dimension_data = $this->westelm_extract_dimensions($dims_str);
        if (gettype($dimension_data) == gettype([])) {
            $dimension_data = $this->format_wetselm_dimension_attributes($dimension_data);
        } else {
            $dimension_data = null;
        }

        return $dimension_data;
    }
    private function format_wetselm_dimension_attributes($dimensions_data)
    {

        foreach ($dimensions_data as $key => &$data) {
            if ($data == null) continue;
            $dims_data = $data;


            foreach ($data as &$value) {
                $dims_data_str = $value['value'];
                $dims_data_arr = explode(" x ", $dims_data_str);
                $dims_with_attr = [];
                foreach ($dims_data_arr as $chunks) {
                    $chunk_pieces = explode('"', $chunks);

                    if (sizeof($chunk_pieces) == 2) {
                        $dims_with_attr[$this->DIMS[$chunk_pieces[1]]] = (string)$chunk_pieces[0] . '"';
                    }
                }

                $value['value'] = $dims_with_attr;
            }
        }

        if (empty($dimensions_data['overall']))
            unset($dimensions_data['overall']);

        $final_dims = [];
        foreach ($dimensions_data as $key => $data) {
            $final_dims[] = [
                'groupName' => $key,
                'groupValue' => $data
            ];
        }
        return $final_dims;
    }

    public function westelm_extract_dimensions($str)
    {
        // if this string is not present in the data recieved 
        // we will not parse the input and return them as they are
        if (strpos($str, "*DETAILED SPECIFICATIONS") == false)
            return $str;

        // in most of the cases **PACKAGING** is the second data point in the data 
        // so exploding the string on this will give detailed specs section as 
        // starting index of the resulting array
        $str_data = explode("**PACKAGING**", $str);

        if (sizeof($str_data) == 0)
            return $str;

        $details_data = explode("\n", $str_data[0]);

        // sub sections are item names like KING, QUEEN etc that describe the product 
        // type that are included for dimensions data 
        // these will be used to show the layered output in the UI
        // these can also be NULL, in case of NULL no laying is showed on the UI and all the points 
        // are shown in one single section.
        // $sub_section[QUEEN] = [
        //       {
        //         'name': 'overall dims',
        //         'value': {
        //            'height' : 34",
        //           'width': 3'
        //          ....
        //         }
        //       }
        // ]
        $sub_sections = [];
        $sub_section["overall"] = [];
        $sub_section_name = null;

        foreach ($details_data as $data_row) {
            $data_row = str_replace("\n", "", $data_row);
            $data_row = trim($data_row);

            if ($data_row == "**DETAILED SPECIFICATIONS**") continue;
            if (strlen($data_row) == 0) continue;

            // sub section name don't start with a *
            if ($data_row[0] != "*") {
                $sub_section_name = $data_row;

                if ($sub_section_name != null && !isset($sub_section[$sub_section_name]))
                    $sub_section[$sub_section_name] = [];
            } else {

                // a row starts with a `*`, then it is most probabibly a point for dimensions data
                $dimension_data_row = str_replace("*", "", $data_row); // remove the `*`

                if (strpos($dimension_data_row, "!") !== false) continue;
                $name_value_pair = explode(":", $dimension_data_row);

                if (sizeof($name_value_pair) == 2) {
                    if ($sub_section_name != null) {
                        $sub_section[$sub_section_name][] = [
                            'name' => trim($name_value_pair[0]),
                            'value' => trim($name_value_pair[1])
                        ];
                    } else {
                        $sub_section["overall"][] = [
                            'name' => trim($name_value_pair[0]),
                            'value' => trim($name_value_pair[1])
                        ];
                    }
                }
            }
        }

        return $sub_section;
    }

    private function format_cb2_to_westelm_dimensions($dims_str)
    {
        $dims_arr = json_decode($dims_str);
        if (json_last_error()) {
            echo json_last_error_msg(), "\n";
            echo $dims_str;
            return null;
        }

        $dims = [];
        $dims['overall'] = [];
        $dims['overall']['name'] = 'Overall Dimensions';
        $dims['overall']['value'] = [];

        if (gettype($dims_arr) != gettype([]))
            return null;
        foreach ($dims_arr as $dims_data) {
            if ($dims_data->hasDimensions) {
                $desc = $dims_data->description;
                if ($desc == "") $desc = "NULL";

                if ($desc == "Overall Dimensions") {
                    foreach ($this->dimension_attrs as $attr) {
                        if (isset($dims_data->$attr)) {
                            if ($dims_data->$attr != 0)
                                $dims['overall']['value'][$attr] = $dims_data->$attr;
                        }
                    }
                } else {
                    if (!isset($dims[$desc])) {
                        $dims[$desc] = [];
                        $dims[$desc]['name'] = $desc;
                        $dims[$desc]['value'] = [];
                    }

                    foreach ($this->dimension_attrs as $attr) {
                        if (isset($dims_data->$attr)) {
                            if ($dims_data->$attr != 0 || $dims_data->attr != null)
                                $dims[$desc]['value'][$attr] = $dims_data->$attr;
                        }
                    }
                }
            }
        }

        $final_dims = [];
        $final_dims[] = [
            'groupName' => null,
            'groupValue' => []
        ];
        foreach ($dims as $key => $value) {

            if (isset($value['value']) && $value['value'] != null && $value['value'] != 0)
                $final_dims[0]['groupValue'][] = [
                    'name' => $value['name'],
                    'value' => $value['value']
                ];
        }

        return $final_dims;
    }

    private function convert_nw_to_standard_dimensions($dims_str)
    {
        $pre_dims = $this->format_new_world($dims_str);
        $dims = [];
        $dims[] = [
            'groupName'  => null,
            'groupValue' => []
        ];

        foreach($pre_dims as $pre_dim) {
            $val = array_values($pre_dim);
            foreach($val as $value) {
                $label = $value['label'];
                
                unset($value['label']);
                unset($value['filter']);
                
                if($label != null) {
                    $dims[0]['groupValue'][] = [
                        'name' => ucfirst($label),
                        'value' => $value
                    ];
                }
            }
        }

        return $dims;
    }

    public function convert_nw() {
        $table = 'master_data';
        $rows = $this->db->select(['id', 'product_feature'])
            ->from($table)
            ->where('site_name', 'nw')
            ->get()->result();

        echo 'total rows: ' , sizeof($rows) , "\n";
        foreach($rows as $row) {
            $dims = $this->convert_nw_to_standard_dimensions($row->product_feature);
            
            $update = $this->db->set('product_dimension', json_encode($dims))
            ->where('id', $row->id)
            ->update($table);            
        }
    }

    public function convert_features_nw() {
        $table = 'master_data';
        $rows = $this->db->select(['id', 'product_feature'])
            ->from($table)
            ->where('site_name', 'nw')
            ->get()->result();

        echo 'total rows: ' , sizeof($rows) , "\n";
        foreach($rows as $row) {
            $product_feature = $this->remove_dims_from_features_nw($row->product_feature);
            
            $update = $this->db->set('product_feature', $product_feature)
            ->where('id', $row->id)
            ->update($table);            
        }
    }

    public function remove_dims_from_features_nw($features) {
        $valid_features = [];
        $feature_arr = explode("|", $features);
        foreach ($feature_arr as $line) {
            if (
                (strpos($line, ":") === false
                    && strpos($line, "\"") === false)

            ) {
                $valid_features[] = $line;
            }
        }

        return implode("|", $valid_features);
    }
    public function test()
    {
        $str = 'Crafted of premium Chilean lenga wood with distressed light walnut finish|Seats up to 10|Requires two people to lift and assemble|Clean with dry or damp cloth with mild soap|WARNING: Click to read CA Prop 65 notice|Made in Vietnam|Assembly required|Overall: 108.1"L x 41.9"W x 29.9"H, approx. 280 lbs.|Leg height: 27.1"H|Between each side trestle: 36"W';
        $this->load->helper('utils');
        echo json_encode($this->convert_nw_to_standard_dimensions($str));
    }
}
