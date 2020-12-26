<?php
defined('BASEPATH') or exit('No direct script access allowed');
ini_set('max_execution_time', 300); //300 seconds = 5 minutes


/*================================
    FIRST RUN wm_save_data.php 
=================================*/

class NW_Reader extends CI_Controller
{
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
                $s_file = "/nw/images/" . basename($url);
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

    public function is_keyword_found($keyword, $name)
    {
        if (strpos($name, $keyword) === false) return false;
        return true;
    }

    public function index()
    {
        $file_path = "csv/Cost_Plus_World_Market-Cost_Plus_World_Market_Google_Feed-shopping.txt";
        $direct_map = $this->db->select("*")->from("nw_mapping_direct")->get()->result();
        $key_map = $this->db->select("*")->from("nw_mapping_keyword")->get()->result();

        $categories = $this->db->distinct()->select("product_category")->from("nw_mapping_keyword")->get()->result();
        $categories2 = $this->db->distinct()->select("product_category")->from("nw_mapping_direct")->get()->result();
        $all_categories = array();
        $notFound = array();
        foreach ($categories as $key => $val) {
            if (
                !in_array($val->product_category, $all_categories)
                && strlen($val->product_category) > 0
            ) {
                array_push($all_categories, $val->product_category);
            }
        }

        foreach ($categories2 as $key => $val) {
            if (
                !in_array($val->product_category, $all_categories)
                && strlen($val->product_category) > 0
            ) {
                array_push($all_categories, $val->product_category);
            }
        }
        //echo "<pre>".print_r($all_categories, true);

        //$data = $this->csvreader->parse_file($file_path);
        $count = 0;
        $mapped = 0;
        $not_mapped_categories = array();
        $i = 0;

        $table_skus = $this->db->query("SELECT product_sku FROM nw_products_API")->result_array();
        $table_skus = array_column($table_skus, "product_sku");
        file_put_contents("nw_table_skus.json", json_encode($table_skus));
        //$this->db->query("TRUNCATE TABLE nw_products");

        // initialize table skus as active once in the start so that we can mark them inactive 
        // at the end
        $table_skus_rel = [];
        foreach($table_skus as $sku) $table_skus_rel[$sku] = false;

        if (($handle = fopen($file_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 5000, "\t")) !== FALSE) {


                $LS_ID = array();
                if ($count == 0) {
                    echo "<pre>" . print_r($data, true);
                    $count++;
                    continue;
                }
                $count++;

                if (in_array($data[4], $table_skus)) {
                    //echo $data[5] . " || " . $data[24];
                    //var_dump($data[15]);
                    $x = explode(" ", $data[15]);
                    $was_price = $x[0];
                    $y = explode(" ", $data[16]);
                    $price = $y[0];
                    //echo "Image : " . $data[9];
                    $images =  $this->multiple_download([$data[9]], '/var/www/html/nw/images');
                    //echo $images . "\n";

                    //shipping details
                    $ship_arr = explode(":", $data[56]);
                    $ship_cost = floatval(explode(" ", $ship_arr[sizeof($ship_arr)-1])[0]);

                    $fields = array(
                        //'product_sku'         => $data[4],
                        'sku_hash'            => md5($data[4]),
                        //'model_code'          => '',
                        'product_url'         => $data[7],
                        //'model_name'          => '',
                        //'images'              => $images,
                        'thumb'               => $images,
                        //'product_dimension'   => '',
                        'color'               => $data[35],
                        'price'               => $price,
                        'was_price'           => $was_price,
                        'parent_category'     => $data[23],
                        'product_category_feed'    => $data[24],
                        //'product_name'        => $data[5],
                        'department_feed'          => $data[24],
                        //'product_feature'     => '',
                        //'collection'          => '',
                        //'product_set'         => '',
                        //'product_condition'   => '',
                        //'product_description' => '',
                        'product_status'      => strlen($data[12]) > 1 ? 'active' : '',
                        //'created_date'        => gmdate('Y-m-d h:i:s \G\M\T'),
                        'updated_date'        => gmdate('Y-m-d h:i:s \G\M\T'),
                        //'is_moved'            => '0',
                        //'update_status'       => '',
                        //'product_images'      => $images,
                        'main_product_images' => $images,
                        'site_name'           => 'nw',
                        'shipping_code'           => $ship_cost, // shipping code is calculated based on this cost
                        // 'reviews'             => '',
                        // 'rating'              => '',
                        //'master_id'           => '',
                        //'reviews'             => '',
                        //'rating'              => '',
                        'LS_ID'               => implode(",", $LS_ID),
                    );
                    array_push($notFound, $data[4]);

                    $this->db->set($fields);
                    $this->db->like('product_sku',  $data[4]);
                    $this->db->update("nw_products_API");

                    if(isset($table_skus_rel[$data[4]]))
                        $table_skus_rel[$data[4]] = true;

                    /* if ($i > 40) {
                        break;
                    }
                    $i++; */
                } else {
                    echo "[PRODUCT NOT FOUND IN THE API DATA] . " . $data[4] . "\n";
                }
            }
            file_put_contents('nw_not_found.json', json_encode($notFound));
            echo $count . " => " . $mapped;
            fclose($handle);
        }

        // mark all unprocessed SKUs as inactive 
        foreach($table_skus_rel as $sku => $flag) {
            if($flag == false) {

                // set inactive 
                $this->db->set(['product_status'=>'inactive']);
                $this->db->like('product_sku',  $sku);
                $this->db->update("nw_products_API");

                echo "SET INACTIVE => " . $sku . "\n";

                // remove product from inventory
                $this->db->set(['is_active' => '0']);
                $this->db->like('product_sku',  $sku);
                $this->db->update("lz_inventory");
            }
        }

        $this->mapNWLS_IDs();
        //echo "<pre>".print_r($data, true);

    }

    public function mapNWLS_IDs()
    {
        // get mapping info

        $ultra_direct_map = $this->db->query("SELECT * FROM nw_mapping_direct")->result();

        $direct_map = $this->db->select("*")
            ->from("nw_mapping_keyword")
            ->order_by("product_category")
            ->get()->result();

        // get products to map.
        $products = $this->db->select("*")
            ->from("nw_products_API")
            ->get()->result();

        // redundant in a way but do not touch this because don't want to dirty my hands right now.
        $default_depts = array('living-room-furniture', 'dining-kitchen-furniture', 'storage-and-modular-furniture', 'bedroom-furniture', 'home-office-furniture', 'entryway-furniture');

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
                // using strpos to match catgeories.

                if(strlen($pro->product_category) > 0 && strlen($val->product_category) > 0)
                    if (strpos($pro->product_category, $val->product_category) !== false) {
                        $LS_ID[$val->product_category] = $val->LS_ID;
                    }
            }

            // direct mapping.
            foreach ($direct_map as $key => $val) {
                $product_cat = preg_replace('/\s+/', '-', strtolower(trim($val->product_category)));

                if (in_array($product_cat, $product_all_cat)) {
                    // category matched. 
                    // give the LS_ID to product for department.
                    // match for keywords
                    if (strlen($val->product_key) == 0) {
                        if (!isset($LS_ID_zero_key[$product_cat])) {
                            //array_push($LS_ID, $val->LS_ID);
                            $LS_ID_zero_key[$product_cat] = $val->LS_ID;
                        }
                    } else {
                        if ($pro->product_sku == "57001890") {
                            echo "Matching KeyWords\n";
                        }
                        if ($this->is_keyword_found($val->product_key, $pro->product_name)) {
                            // keyword matched 
                            // give product the LS_ID
                            if ($pro->product_sku == "57001890") {
                                echo "KeyWords Matched\n";
                            }
                            if (strlen($val->type) > 0) {
                                if (in_array($val->type, $product_type) && sizeof($product_type) > 0) {
                                    if (!isset($LS_ID[$product_cat]))
                                        $LS_ID[$product_cat] = $val->LS_ID;
                                }
                            } else {
                                $key = $product_cat;
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

            $LS_ID_val = [];

            foreach ($LS_ID as $key => $val) {
                if (!in_array($val, $LS_ID_val)) {
                    array_push($LS_ID_val, $val);
                }
            }

            if (sizeof($LS_ID) == 0) {
                foreach ($LS_ID_zero_key as $key => $val) {
                    if (!isset($LS_ID[$key]) && !in_array($val, $LS_ID_val)) {
                        array_push($LS_ID_val, $val);
                    }
                }
            }

            if ($pro->product_sku == "57001890") {
                var_dump($LS_ID);
                var_dump($LS_ID_zero_key);
                //  die();
            }
            echo "Product Name: " . $pro->product_name . "LS_ID: " . implode(",", $LS_ID_val) . "\n";
            $this->db->set("LS_ID", implode(",", $LS_ID_val))
                ->where("product_sku", $pro->product_sku)
                ->update("nw_products_API");
        }

        echo "\n == MAPPING COMPLETED == \n";
    }


    public function resize_image($file, $w, $h, $crop = FALSE)
    {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width - ($width * abs($r - $w / $h)));
            } else {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w / $h > $r) {
                $newwidth = $h * $r;
                $newheight = $h;
            } else {
                $newheight = $w / $r;
                $newwidth = $w;
            }
        }
        $src = imagecreatefromjpeg($file);
        $dst = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        return $dst;
    }

    // enlarge images from 400 X 400 to 640 X 640
    public function enlarge_product_images()
    {
        echo "==============IMAGE RESIZE=================\n\n";

        $rows = $this->db->select("product_images")
                         ->from("nw_products_API")
                         ->get()
                         ->result();

        $prefix_path = "/var/www/html";

        foreach ($rows as $row) {

            $images = explode(",", $row->product_images);
            foreach ($images as $i_path) {

                $img_path = $prefix_path . $i_path;
                $new_img = $this->resize_image($img_path, 640, 640);
                imagejpeg($new_img, $img_path);

                echo "PROCESSED: " . $img_path . "\n";
            }
        }

        echo "================DONE==================\n\n";
    }
}