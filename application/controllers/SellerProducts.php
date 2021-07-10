<?php
defined('BASEPATH') or exit('No direct script access allowed');
ini_set('max_execution_time', 300); //300 seconds = 5 minutes


/*================================
    FIRST RUN wm_save_data.php 
=================================*/

class SellerProducts extends CI_Controller
{

    private $file_path = "csv/wc-products.csv";
    private $product_table = "seller_products";
    private $product_variations_table = "seller_products_variations";
    private $moku_products = "moku_products";
    private $moku_variations = "moku_products_variations";


    // reads and saved the csv file to DB;
    public function index()
    {

        $count = 0;

        // map of SKU => product details;
        // and product details will contain variations as well.
        $products = [];
        if (($handle = fopen($this->file_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
                if ($count == 0) {
                    $count++;
                    continue;
                }
                $count++;

                if ($data[4] == "1") {
                    // check if row is for product or variation
                    if ($data[1] == 'variable') {
                        // product
                        if (strlen($data[2]) > 0) {

                            if (!isset($products[$data[2]])) {
                                $products[$data[2]] = [
                                    'details' => [],
                                    'variations' => []
                                ];
                                $products[$data[2]]['details'] = $data;
                            }
                        }
                    } else if ($data[1] == 'variation') {
                        // variation
                        if (strlen($data[2]) > 0) {
                            $var_sku = $data[2];
                            $parent_sku = $data[32];
                            if (isset($products[$parent_sku])) {
                                $products[$parent_sku]['variations'][] = $data;
                            }
                        }
                    }
                }
            }
        }

        // save to DB
        $replace = [];
        foreach ($products as $sku => $product_details) {
            $details = $product_details['details'];
            $variations = $product_details['variations'];

            $prices = $this->get_price($product_details);
            $replace = [
                'product_sku' => $sku,
                'product_status' => $details[4] == "1" ? 'active' : 'inactive',
                'brand' => 'mok',
                'product_name' => $details[3],
                'product_description' => $this->get_desc($details[7]),
                'product_feature' => $this->get_desc($details[8]),
                'product_dimension' => $this->get_dims($variations),
                'min_price' => $prices['min_price'],
                'max_price' => $prices['max_price'],
                'min_was_price' => $prices['min_was_price'],
                'max_was_price' => $prices['max_was_price'],
                'product_images' => json_encode(explode(",", $this->multiple_download(explode(",", $details[29]), '/var/www/html/seller/MokuArtisan/img', '/seller/MokuArtisan/img/'))),
                'main_product_images' => $this->multiple_download(array(explode(",", $details[29])[0]), '/var/www/html/seller/MokuArtisan/img', '/seller/MokuArtisan/img/'),
                'color' => $this->get_color($details),
                'variations' => $this->generate_var_data($details),
                'variations_count' => sizeof($variations)
            ];

            $this->db->replace($this->product_table, $replace);
            $this->db->replace($this->moku_products, $replace);

            // save variations to DB
            $this->save_variations($variations, json_decode($details[39]));
        }
    }

    public function get_desc($str) {

        $str = str_replace(["\\n", '<li style="font-weight: 400;" aria-level="1">', "</li>"], "", $str);
        return $str;
    }

    public function save_variations($variations, $swatch_attr = null)
    {
        $data = [];
        foreach ($variations as $var) {
            $sku = $var[2];
            $parent_sku = $var[32];

            $attr_1 = trim($var[40]);
            $attr_1_val = trim($var[41]);
            $attr_2 = trim($var[45]);
            $attr_2_val = trim($var[46]);

            $swatch_images = [];
            if(isset($swatch_attr->$attr_1)) {
                // find image with @attr_1_val 
                $ar = $swatch_attr->$attr_1->terms;
                if(isset($ar->$attr_1_val)) {
                    $swatch_images[] = $this->multiple_download([$ar->$attr_1_val->image], '/var/www/html/seller/MokuArtisan/img', '/seller/MokuArtisan/img/');
                }
            }

            if(isset($swatch_attr->$attr_2)) {
                // find image with @attr_2_val 
                $ar = $swatch_attr->$attr_2->terms;
                if(isset($ar->$attr_2_val)) {
                    $swatch_images[] = $this->multiple_download([$ar->$attr_2_val->image], '/var/www/html/seller/MokuArtisan/img', '/seller/MokuArtisan/img/');
                }
            }


            $data = [
                'product_id' => $parent_sku,
                'sku' => $sku,
                'name' => $var[3],
                'price' => strlen($var[24]) > 0 ? $var[24] : $var[25],
                'was_price' => $var[25],
                'attribute_1' => $var[40] . ":" . $var[41],
                'attribute_2' => $var[45] . ":" . $var[46],
                'image_path' => $this->multiple_download(explode(",", $var[29]), '/var/www/html/seller/MokuArtisan/img', '/seller/MokuArtisan/img/'),
                'swatch_image_path' => sizeof($swatch_images) > 0 ? $swatch_images[0] : "",
                'status' => $var[13] == "1" ? 'active' : 'inactive'
            ];

            $this->db->replace($this->product_variations_table, $data);
            $this->db->replace($this->moku_variations, $data);

        }
    }

    public function get_dims($variations)
    {
        $dims = [];
        $dims_mapping = [
            'H' => 'height',
            'W' => 'width',
            'D' => 'Depth'
        ];
        $dims['groupName'] = null;
        $dims['groupValue'] = [];
        foreach($variations as $var) {
            $dims_str = $var[8];
            if(strlen($dims_str) > 0) {
                // H 27'' / W 54'' / D 30''
                $dims_arr = explode("/", $dims_str);
                $dim = [];
                foreach($dims_arr as $val) {
                    $val = str_replace("''","", trim($val));
                    $val = explode(" ", $val);
                    if(sizeof($val) == 2) {
                        $dim[$dims_mapping[$val[0]]] = $val[1];
                    }
                }

                $dims['groupValue'] = [$dim];
                if(sizeof($dim) > 0) break;
            }
        }
        $str = json_encode($dims);
        return str_replace("\u201d", "", $str);
    }

    public function get_price($product_details)
    {
        $variations = $product_details['variations'];
        if (sizeof($variations) == 0)
            return [
                'min_price' => $product_details['details'][24],
                'max_price' => $product_details['details'][24],
                'min_was_price' => $product_details['details'][25],
                'max_was_price' => $product_details['details'][25]
            ];

        $max_price = $variations[0][24];
        $min_price = $variations[0][24];
        $max_was_price = $variations[0][25];
        $min_was_price = $variations[0][25];

        foreach ($variations as $var) {

            if (strlen($var[24]) >= 1) {
                $max_price = max($max_price, (float)$var[24]);
                $min_price = min($min_price, (float)$var[24]);
            }

            if(strlen($var[25]) >= 1) {
                $max_was_price = max($max_was_price, (float)$var[25]);
                $max_was_price = min($max_was_price, (float)$var[25]);

            }
        }

        if($min_price == 0) $min_price = $min_was_price;
        if($max_price == 0) $max_price = $max_was_price;

        return [
            'min_price' => $min_price,
            'max_price' => $max_price,
            'min_was_price' => $min_was_price,
            'max_was_price' => $max_was_price
        ];
    }

    public function get_color($details)
    {
        if(trim($details[40]) == 'Color')
            return $details[41];
        else if(trim($details[45]) == 'Color')
            return $details[46];
    }

    public function generate_var_data($details)
    {
        // logic here
        $var_attrs = [];
        $attr_name_1 = $details[40];
        $attr_value_1 = $details[41];
        $attr_name_2 = $details[45];
        $attr_value_2 = $details[46];

        $var_attrs[] = ["attribute_name" => $attr_name_1, "attribute_options" => explode(",", $attr_value_1)];
        $var_attrs[] = ["attribute_name" => $attr_name_2, "attribute_options" => explode(",", $attr_value_2)];

        return json_encode($var_attrs);
    }

    public function multiple_download($urls, $save_path = '/tmp', $save_path_core = "/cnb/images/")
    {
        $multi_handle  = curl_multi_init();
        $file_pointers = array();
        $curl_handles  = array();
        $file_paths    = array();

        // Add curl multi handles, one per file we don't already have
        if (sizeof($urls) > 0) {
            foreach ($urls as $key => $url) {
                $url = trim($url);
                $image_url = str_replace('$', '', trim($url));
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
}
