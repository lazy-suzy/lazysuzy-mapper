<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CrateAndBarrel extends CI_Controller
{

    private $counter_exclude_categories = [
        '/furniture/top-rated-furniture',
        '/outdoor-furniture/top-rated-outdoor-furniture',
        '/kids/top-rated-baby-and-kids-furniture',
        '/furniture/home-office-furniture',
        '/furniture/entryway-furniture',
        '/furniture/bedroom-furniture',
        '/furniture/dining-kitchen-storage',
        '/furniture/living-room-furniture'
    ];

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

                if (sizeof($path_arr) > 4) {
                    $limit_path = sizeof($path_arr) - 4;
                } else {
                    $limit_path = 2;
                }

                if (sizeof($path_arr) >= $limit_path) {
                    $path_arr_str = implode('', array_slice($path_arr, $limit_path));
                    $file   = $save_path . '/' . $path_arr_str . basename($url);
                    $s_file = "/cnb/img/" . $path_arr_str . basename($url);
                    array_push($file_paths, $s_file);
                } else {
                    $file   = $save_path . '/'  . basename($url);
                    $s_file = "/cnb/img/" . basename($url);
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
    }

    public function update_variations()
    {
        // update variations field
        $dis_SKU = $this->db->distinct()->select('product_sku')->from('crateandbarrel_products')->get()->result();
        $dis_variation_SKU = $this->db
            ->select('product_sku, variation_sku')
            ->from('crateandbarrel_products_variations')
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
                    ->update('crateandbarrel_products_variations');
            }
        }

        echo "\n========= UPDATED VARIATIONS `has_parent_sku` FIELD ==========\n";
    }

    public function save_variations($variations, $product_sku)
    {
        echo "======== SAVING VARIATIONS ==========\n";

        $origin_sku = $product_sku;
        // echo print_r($variations, true);
        if (sizeof($variations) > 0) {
            foreach ($variations as $key => $variation) {

                $var_name = $variation->ChoiceName;
                $var_name = str_replace([" ", ",", "\"", ".", "/"], ["", "_", "", "", "_"], $var_name);

                //$varaition->SKU = $variation->SKU . '_' . $origin_sku . '_' . $var_name;


                $this->db->from("crateandbarrel_products_variations");
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
                        'swatch_image'       => isset($variation->ColorImage) ? $this->multiple_download(array($variation->ColorImage), '/var/www/html/cnb/img') : null,
                        'variation_image'  => isset($variation->Image) ? $this->multiple_download(array($variation->Image), '/var/www/html/cnb/img') : null,
                    );


                    if ($variation->SKU != NULL) {
                        $this->db->insert('crateandbarrel_products_variations', $variation_fields);
                    } else {
                        echo "VARIATION SKU NULL\n";
                    }
                } else {
                    // update the variations images.
                    $aa = [
                        'swatch_image' => isset($variation->ColorImage) ? $this->multiple_download(array($variation->ColorImage), '/var/www/html/cnb/img') : null,
                        'variation_image' => isset($variation->Image) ? $this->multiple_download(array($variation->Image), '/var/www/html/cnb/img') : null
                    ];

                    $this->db->where('variation_sku', $variation->SKU);
                    $this->db->where('product_sku', $origin_sku);
                    $this->db->where('variation_name', $variation->ChoiceName);

                    $this->db->update('crateandbarrel_products_variations', $aa);
                    echo "[VARIATIONS DUPLICATE].\n";
                }

                // }
            }
        }
    }

    public function merge()
    {
    }

    public function update_master_id()
    {
        $query = "UPDATE crateandbarrel_products SET master_id = '' WHERE 1";
        $this->db->query($query);

        $skus = $this->db->distinct()
            ->select('product_sku')
            ->where('has_parent_sku', 1)
            ->from('crateandbarrel_products_variations')
            ->get()->result_array();

        foreach ($skus as $sku) {
            $product_sku = $sku['product_sku'];
            $master_id = "-" . $product_sku;

            $variations = $this->db->select('variation_sku')
                ->where('product_sku', $product_sku)
                ->from('crateandbarrel_products_variations')
                ->get()->result_array();

            foreach ($variations as $v_sku) {
                $sku = $v_sku['variation_sku'];
                $query = "UPDATE crateandbarrel_products SET master_id = CONCAT(master_id, '" . $master_id  . "') WHERE product_sku = '$sku'";
                $this->db->query($query);
            }
        }

        $this->make_master_id_unique();
    }

    public function make_master_id_unique()
    {

        $query = "SELECT product_sku, master_id FROM crateandbarrel_products WHERE LENGTH(master_id) > 0";
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
            $query = "UPDATE crateandbarrel_products SET master_id = '$new_master_id' WHERE product_sku = '$product_sku'";
            $this->db->query($query);
        }
    }


    public function index()
    {

        //Store the get request
        $status = $this->input->get();

        //Initialize CAB Module
        $this->load->library('CNB', array(
            'proxy' => '5.79.66.2:13010',
            'debug' => false,
        ));

        if (isset($status['category'])) {
            header('Content-Type: application/json');
            echo json_encode($this->cnb->get_category($status['category']));
        } else if (isset($status['product'])) {
            header('Content-Type: application/json');
            echo json_encode($this->cnb->get_product($status['product']));
        } else {

            // change accessibility for this statement. 
            // $this->db->query("TRUNCATE crateandbarrel_products");
            // $this->db->query("TRUNCATE crateandbarrel_products_variations");
            // get product data urls from.
            $default_depts = array('living-room-furniture', 'dining-kitchen-furniture', 'storage-and-modular-furniture', 'bedroom-furniture', 'home-office-furniture', 'entryway-furniture');
            //$urls          = $this->db->query("SELECT * FROM cb2_categories")->result();
            //Take relevent action
            // loop here on $urls
            $db_skus = $this->db->select("product_sku")
                ->from('crateandbarrel_products')
                ->get()->result();

            $urls = $this->db->select("*")
                ->from('cab_category_urls')
                ->where("is_active", 1)
                //->where('id', 19)
                ->get()->result();

            $harveseted_SKU  = array();
            $set_inactive = array();

            foreach ($db_skus as $sku) {
                if ($sku->product_sku != null) {
                    array_push($harveseted_SKU, $sku->product_sku);
                    $set_inactive[$sku->product_sku] = false;
                }
            }


            $empty_categories = [];
            $empty_products = [];


            $harveseted_prod = array();


            foreach ($urls as $key => $url) {
                $product_counter = 0;
                $update_product_counter = false;
                
                $url_string = $url->url;
                $id = $url->cat_id;
                
                // keep track of sequence of products that come from the API.
                if(!in_array($id, $this->counter_exclude_categories)) {
                    $update_product_counter = true;
                }


                echo "url: " . $url_string . "\n";
                echo "ID: " . $id . "\n";

                $data_retry = 5;
                //echo "\n || " . $url->url . " || \n";
                $data        = $this->cnb->get_category_by_id($id);
                $parts       = explode('/', $url_string);
                $product_cat = strtolower($parts[2]);
                $department  = strtolower($parts[1]);

                echo "Data Size: " . ($data['products']) . "\n";
                if (in_array($product_cat, $default_depts)) {
                    $department = $product_cat;
                }

                while ((sizeof($data) == 0) && $data_retry--) {
                    // echo '\n' . sizeof($data) . "\n";
                    $data = $this->cnb->get_category_by_id($id);
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

                        $product_details = $this->cnb->get_product($product['BaseURL']);

                        if (sizeof($product_details) == 0) {
                            $retry = 5;
                            while (sizeof($product_details) == 0 && $retry--) {
                                echo "retry product details... " . $product['BaseURL'] . "\n";
                                sleep(10);
                                $product_details = $this->cnb->get_product($product['BaseURL']);
                            }
                        }

                        if (isset($product['BaseSKU']) && sizeof($product_details) != 0) {

                            if ($update_product_counter)
                                $product_counter += 1; // product sequence 

                            $product_details['sequence'] = $update_product_counter ? $product_counter : NULL;
                            $product_details['department'] = $department;
                            $product_details['catgeory'] = $product_cat;

                            $API_products['SKU' . $product['BaseSKU']] = $product_details;
                            $API_products['SKU' . $product['BaseSKU']]['SKU'] = $product['BaseSKU'];



                            echo $c++, "\n";
                        } else {
                            echo "[EMPTY PRODUCT_DETAILS]  " . $product['BaseURL'] . "\n";
                        }
                    }

                    file_put_contents('API_products_cnb.json', json_encode($API_products));

                    $API_products = json_decode(file_get_contents('API_products_cnb.json'));

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

                                            var_dump($params);

                                            $filter_copy = $filter;

                                            $filter_data = $this->cnb->get_category_by_id($params);

                                            if (strtolower($filter_copy) == "features") {
                                                $filter_copy = "features_";
                                            } else if (strtolower($filter_copy) == "seat capacity") {
                                                $filter_copy = "seat_capacity";
                                            }

                                            $retry = 5;
                                            while (sizeof($filter_data) == 0 && $retry--) {
                                                $filter_data = $this->cnb->get_category_by_id($str);
                                                echo "retrying filter data...\n";
                                                sleep(10);
                                            }
                                            //echo var_dump($filter_data);
                                            if (
                                                sizeof($filter_data)  &&
                                                isset($filter_data['products']) &&
                                                sizeof($filter_data['products'])
                                            ) {

                                                echo "Size Filter Data: " . sizeof($filter_data) . " - " . gettype($API_products) . "\n";


                                                foreach ($filter_data['products'] as $filter_product) {
                                                    $baseSku = 'SKU' . $filter_product['BaseSKU'];
                                                    if (property_exists($API_products, $baseSku)) {

                                                        if (isset($API_products->$baseSku->$filter)) {
                                                            echo "[APPEND] " . $filter_copy . " = " . $sfilter . "\n";
                                                            $API_products->$baseSku->$filter_copy .= "," . $sfilter;
                                                        } else {
                                                            echo "[NEW FILTER] " . $filter_copy . " = " . $sfilter . "\n";
                                                            $API_products->$baseSku->$filter_copy = $sfilter;
                                                        }
                                                    }
                                                    /*else {
                                                            echo "[NOT FOUND] ". $baseSku . " "  . $filter_product['BaseSKU'] . isset($API_products->$baseSku) . "\n";
                                                            // save products here. 
                                                            $p_details = $this->cnb->get_product($filter_product['BaseURL']);
                                                            if (sizeof ($p_details) > 0) {
                                                                $API_products->$baseSku = $p_details;
                                                            }
                                                            else {
                                                                $retry = 5;
                                                                    while ($retry-- && sizeof($p_details) == 0) {
                                                                    echo "[RETRY - filter data API_products]\n";
                                                                    sleep(20);
                                                                    $p_details = $this->cnb->get_product($filter_product['BaseURL']);
                                                                    $API_products->$baseSku = $p_details;
                                                                    }
                                                            }
                                                            
                                                            
                                                            if (isset($API_products->$baseSku)) {
                                                                $API_products->$baseSku[$filter] = $sfilter;
                                                                $API_products->$baseSku['SKU'] = $filter_product['BaseSKU'];
                                                                echo '[FILTER NEW PRODUCT ADDED] . ' . $filter . " = " . $sfilter . "\n";
                                                            }
                                                            
                                                        }*/
                                                }

                                                // dump new data in a file 
                                                file_put_contents('cnb_API_products_filter.json', json_encode($API_products));
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
                        file_put_contents('cnb_API_products_filter.json', json_encode($API_products));
                    }

                }


                // json_encode transformed the array to object due to which getting values from the variable was 
                // messed up.
                //$API_products = json_decode(file_get_contents('cnb_API_products_filter.json'));

                $API_products = json_decode(file_get_contents('API_products_cnb.json'));

                foreach ($API_products as $sku => $product) {
                    /*=================================*/
                    $has_variations = 0;
                    $product_details = $product;

                    if (isset($product_details)) {
                        $image_links   = $this->multiple_download($product_details->SecondaryImages, '/var/www/html/cnb/img');
                        $img           = $product_details->PrimaryImage;
                        $primary_image = $this->multiple_download(array($img), '/var/www/html/cnb/img');


                        echo "==\n";
                        if ($product_details->Variations && $product->SKU != NULL) {
                            echo "===\n";
                            if (sizeof($product_details->Variations) > 0) {
                                echo "Size of: " . sizeof($product_details->Variations) . "\n";
                                echo "SKU: " . $product->SKU . "\n";
                                $has_variations = 1;
                                $this->save_variations($product_details->Variations, $product->SKU);
                            } else {
                                echo "VARIATION SIZE 0\n";
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
                        'product_url'         => 'https://www.crateandbarrel.com' . $product->URL,
                        'model_name'          => '',
                        'images'              => is_array($product_details->SecondaryImages) ? implode(",", $product_details->SecondaryImages) : "",
                        'thumb'               => $product_details->PrimaryImage,
                        'product_dimension'   => json_encode($product_details->Dimentions[0]->productDimensions),
                        'price'               => $product_details->CurrentPrice !== null ? $product_details->CurrentPrice : $product_details->RegularPrice,
                        'was_price'           => $product_details->RegularPrice,
                        'parent_category'     => $product_details->familyID,
                        'product_category'    => $product_cat,
                        'product_name'        => $product_details->Name,
                        'department'          => $department,
                        'product_feature'     => is_array($product_details->Features) ? implode('<br>', $product_details->Features) : "",
                        'collection'          => '',
                        'product_set'         => '',
                        'product_condition'   => '',
                        'product_description' => $product_details->Description,
                        'product_status'      => 'active',

                        'shipping_code'       => isset($product_details->ShippingLevel) ? $product_details->ShippingLevel : null, // newly added param 07-07-2020

                        'created_date'        => gmdate('Y-m-d h:i:s \G\M\T'),
                        'updated_date'        => gmdate('Y-m-d h:i:s \G\M\T'),
                        'is_moved'            => '0',
                        'update_status'       => '',
                        'product_images'      => $image_links,
                        'main_product_images' => $primary_image,
                        'site_name'           => 'cab',
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
                        'shape'               => isset($product_details->Shape) ? $product_details->Shape : "",
                        'seat_capacity'       => isset($product_details->seat_capacity) ? $product_details->seat_capacity : "",
                        'features_'           => isset($product_details->features_) ? $product_details->features_ : "",
                        'serial'              => $product_details->sequence

                    );

                    echo "Product SKU: " . $product_details->SKU . "\n";


                    if (!in_array($product_details->SKU, $harveseted_SKU)) {
                        if (NULL != $product_details->SKU) {

                            array_push($harveseted_SKU, $product_details->SKU);
                            $sql = $this->db->insert_string('crateandbarrel_products', $fields);

                            echo "[SAVING PRODUCT]\n";

                            if (!$this->db->query($sql)) {
                                $log = fopen("cnb-error-log.txt", "w") or die("Unable to open file!");
                                fwrite($log, $sql . "\n\n");
                                fclose($log);
                                die('error! could not enter data in database');
                            }
                        } else {
                            echo "[SKU IS NULL | ERROR]\n";
                        }
                    } else {

                        unset($set_inactive[$product_details->SKU]);

                        echo "[PRODUCT FOUND IN HARVERSTED ARRAY]\n";

                        $x  = $product_details->SKU;
                        $ss = $this->db->query("SELECT department,product_category, LS_ID FROM crateandbarrel_products WHERE product_sku = '$x'")->result();

                        $product_categories_exists = explode(",", $ss[0]->product_category);
                        $product_department_exists = explode(",", $ss[0]->department);
                        
                        $new_cat = "";
                        $new_department_str = "";

                        if (!in_array($department, $product_department_exists)) {
                            $new_department_str = $ss[0]->department . "," . $department;
                        } else {
                            $new_department_str = implode(",", $product_department_exists);
                        }

                        // only update the catgeory field if there is a new category.
                        
                        if (!in_array($product_cat, $product_categories_exists)) {
                            $new_cat = $ss[0]->product_category . "," . $product_cat;
                        } else {
                            $new_cat = $ss[0]->product_category;
                        }

                        $aa = array(
                            'product_category' => $new_cat,
                            'department' => $new_department_str,
                            'price'            => $product_details->CurrentPrice,
                            'images'              => is_array($product_details->SecondaryImages) ? implode(",", $product_details->SecondaryImages) : "",
                            'main_product_images' => $primary_image,
                            'product_images'      => $image_links,
                            'product_dimension'  => json_encode($product_details->Dimentions[0]->productDimensions),
                            'shipping_code'       => isset($product_details->isInHomeDelivery) ? ($product_details->isInHomeDelivery ? "400" : "100") : null, // newly added param 07-07-2020
                            'product_status'    => 'active',
                            'serial'              => $product_details->sequence

                        );

                        $this->db->where('product_sku', $product_details->SKU);
                        $this->db->update('crateandbarrel_products', $aa);
                        echo "\n|| PRODUCT UPDATE FOUND || " . $ss[0]->product_category . "," . $product_cat . "\n";
                    }
                    $product_details = NULL;

                    /*==================================*/
                }
                
            }

            $this->update_variations();
            var_dump($empty_categories);
            $this->update_master_id();
            $this->mapCABLS_IDs();


            // set remaining product skus to inactive status 
            foreach($set_inactive as $sku => $val) {
                $this->db->where('product_sku', $sku)   
                    ->update('crateandbarrel_products', ['product_status' => 'inactive']);
            }

            file_put_contents('marked-inactive-cab.json', json_encode($set_inactive));
            //$this->merge();
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
            ///->like("product_category", "ottomans-and-cubes")
            //->where("LENGTH(LS_ID)", 0)
            //->where("product_sku", "288258")
            ->get()->result();

        $default_depts = array('living-room-furniture', 'dining-kitchen-furniture', 'storage-and-modular-furniture', 'bedroom-furniture', 'home-office-furniture', 'entryway-furniture');

        echo "size: " . sizeof($products) . "\n" ;
        foreach ($products as $key => $pro) {
            $LS_ID = array();
            $LS_ID_zero_key = array();
            $product_categories = explode(",", $pro->product_category);
            $product_type = explode(",", $pro->type);
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
                $dept_arr = explode(",", strtolower($pro->department));

                if (
                    in_array(trim($product_cat), $product_all_cat)
                    && in_array(strtolower($val->department), $dept_arr)
                ) {

                    // first check the type in mapping table
                    if (strlen($val->type) > 0) {

                        if(strlen($pro->type) > 0) {
                            if (in_array($val->type, $product_type) && sizeof($product_type) > 0) {
                                if (!isset($LS_ID[$product_cat]))
                                    $LS_ID[$product_cat] = $val->LS_ID;
                            }
                        }

                        
                    } else {

                            if (!isset($LS_ID[$product_cat]))
                                $LS_ID[$product_cat] = $val->LS_ID;
                    }
                }
            }

            // direct mapping.
            foreach ($direct_map as $key => $val) {
                $product_cat = preg_replace('/\s+/', '-', strtolower(trim($val->product_category)));
                $dept_arr = explode(",", strtolower($pro->department));
                if (in_array(strtolower($val->department), $dept_arr)) {
                    if (in_array($product_cat, $product_all_cat)) {
                        // department matched. 
                        // give the LS_ID to product for department.
                        // match for keywords
                        if (strlen($val->product_key) == 0) {
                            if (!in_array($val->LS_ID, $LS_ID)) {
                                //array_push($LS_ID, $val->LS_ID);
                                $LS_ID_zero_key[$product_cat] = $val->LS_ID;
                            }
                        } else {
                            if ($this->is_word_match($pro->product_name, $val->product_key)) {
                                // keyword matched 
                                // give product the LS_ID
                                if (strlen($val->type) > 0) {
                                    if (in_array($val->type, $product_type) && sizeof($product_type) > 0) {
                                        if (!isset($LS_ID[$product_cat]))
                                            $LS_ID[$product_cat] = $val->LS_ID;
                                    }
                                } else {
                                    if (!isset($LS_ID[$product_cat]))
                                        $LS_ID[$product_cat] = $val->LS_ID;
                                }
                            }
                        }
                    }
                }
            }


            $LS_ID_val = [];

            foreach ($LS_ID as $key => $val) {
                if (!in_array($val, $LS_ID_val)) {
                    array_push($LS_ID_val, $val);
                }
            }

            foreach ($LS_ID_zero_key as $key => $val) {
                if (!isset($LS_ID[$key]) && !in_array($val, $LS_ID_val)) {
                    array_push($LS_ID_val, $val);
                }
            }


            echo "Product Name: " . $pro->product_name . " LS_ID: " . implode(",", $LS_ID_val) . "\n";
            $this->db->set("LS_ID", implode(",", $LS_ID_val))
                ->where("product_sku", $pro->product_sku)
                ->update("crateandbarrel_products");
        }

        echo "\n == MAPPING COMPLETED == \n";
    }

    public function get_master_data($product, $min_price, $max_price, $pop_index)
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

    public function get_westelm_master_data($product, $min_price, $max_price, $pop_index)
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

    public function update_dates() 
    {
    	$old_rows = $this->db->query("SELECT product_sku, created_date FROM cb2_products_new WHERE 1")->result();
    	echo "Products: " . sizeof($old_rows) . " \n";
    	foreach($old_rows as $row) {
    		//$query = "UPDATE crateandbarrel_products SET created_date = $row->created_date WHERE product_sku = $row->product_sku";
    		 $this->db->set("created_date",  $row->created_date)
                ->where("product_sku", $row->product_sku)
                ->update("master_data");
             echo $row->product_sku . " " . $row->created_date . "\n";
    	}
    }
}
