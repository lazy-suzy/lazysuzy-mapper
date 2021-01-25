<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Westelm extends CI_Controller
{

    private $DIMS = [
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
    public function index()
    {
        echo "Welcome to Westelm Mapper\n";
    }

    public function is_word_match($needle, $hay_stack)
    {
        $hay_stack = strtolower($hay_stack);
        $needle = strtolower($needle);

        $words = explode(" ", $hay_stack);

        if (in_array($needle, $words)) {
            return true;
        }

        return false;
    }
    public function mapperInit()
    {
        $this->load->helper('file');

        // maps based on departments and product_category
        $m_direct = $this->db->select("*")
            ->from("westelm_mapping_direct")
            ->get()->result();

        // maps based on departments, product_category and 
        // product_sub_category
        $m_sub_cat = $this->db->select("*")
            ->from("westelm_mapping_direct_sub_cat")
            ->get()->result();

        // maps based on matched keyword,departments, product_category and 
        // product_sub_category
        $m_keywords = $this->db->select("*")
            ->from("westelm_mapping_keyword")
            ->get()->result();

        $product_count = $this->db->count_all("westelm_products_parents");
        $product_limit = 1000;
        $products_processed = 0;
        $offset = 0;
        $batch = 0;
        $mapped_products = 0;

        while ($products_processed < $product_count) {
            $offset = $product_limit * $batch;
            $products = $this->db->get("westelm_products_parents", $product_limit, $offset)->result();
            // will map `product_limit` products at a time.
            $not_mapped = [];
            foreach ($products as $product) {
                $LS_ID = [];
                $LS_ID_no_key = [];
                $department = strtolower($product->department);
                $category = strtolower($product->product_category);
                $sub_category = strtolower($product->product_sub_category);
                // direct mapping
                foreach ($m_direct as $row) {
                    $m_department = strtolower($row->department);
                    $m_category = strtolower($row->product_category);

                    if (strlen($m_category) > 0) {
                        if ($m_department == $department && $m_category == $category) {
                            if (!isset($LS_ID[$m_category])) {
                                $LS_ID[$m_category] = $row->LS_ID;
                                $mapped_products++;
                            }
                            //break;
                        }
                    } else {
                        if ($m_department == $department) {
                            if (!isset($LS_ID[$m_category])) {
                                $LS_ID[$m_category] = $row->LS_ID;
                                $mapped_products++;
                            }
                            //break;
                        }
                    }
                }

                // direct with sub_cat
                foreach ($m_sub_cat as $row) {
                    $m_department = strtolower($row->department);
                    $m_category = strtolower($row->product_category);
                    $m_sub_category = strtolower($row->product_sub_category);

                    if ($m_department == $department && $m_category == $category) {
                        if (strlen($m_sub_category) > 0) {
                            if ($m_sub_category == $sub_category) {
                                if (!isset($LS_ID[$m_category])) {
                                    $LS_ID[$m_category] = $row->LS_ID;
                                    $mapped_products++;
                                }
                                //break;
                            }
                        } else {
                            if (!isset($LS_ID[$m_category])) {
                                $LS_ID[$m_category] = $row->LS_ID;
                                $mapped_products++;
                            }
                            // break;
                        }
                    }
                }

                // keyword mapping
                foreach ($m_keywords as $row) {
                    $m_department = strtolower($row->department);
                    $m_category = strtolower($row->product_category);
                    $m_sub_category = strtolower($row->product_sub_category);
                    $m_keyword = strtolower($row->product_key);

                    if ($m_department == $department && $m_category == $category) {
                        if (strlen($m_sub_category) > 0) {
                            if (strlen($m_keyword) > 0) {
                                if (Westelm::is_word_match($m_keyword, $product->product_name)) {
                                    if (!isset($LS_ID[$m_category])) {
                                        $LS_ID[$m_category] = $row->LS_ID;
                                        $mapped_products++;
                                    }
                                    // break;
                                }
                            } else {
                                if ($m_sub_category == $sub_category) {
                                    if (!isset($LS_ID[$m_category])) {
                                        $LS_ID[$m_category] = $row->LS_ID;
                                        $mapped_products++;
                                    }
                                    //break;
                                }
                            }
                        } else {
                            if (strlen($m_keyword) > 0) {
                                if (Westelm::is_word_match($m_keyword, $product->product_name)) {
                                    if (!isset($LS_ID[$m_category])) {
                                        $LS_ID[$m_category] = $row->LS_ID;
                                        $mapped_products++;
                                    }
                                    //break;
                                }
                            } else {
                                if (!isset($LS_ID[$m_category])) {
                                    $LS_ID_no_key[$m_category] = $row->LS_ID;
                                    $mapped_products++;
                                }
                                //break;
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
                    foreach ($LS_ID_no_key as $key => $val) {
                        if (!isset($LS_ID[$key]) && !in_array($val, $LS_ID_val)) {
                            array_push($LS_ID_val, $val);
                        }
                    }
                }


                if (sizeof($LS_ID) == 0 && sizeof($LS_ID_no_key) == 0) {

                    array_push($not_mapped, [
                        $product->product_name,
                        $department,
                        $category,
                        $sub_category,

                    ]);
                } else {

                    // update the database table with LS_ID
                    $this->db->set("LS_ID", implode(",", $LS_ID_val))
                        ->where("id", $product->id)
                        ->update("westelm_products_parents");
                }
            }

            $batch++;
            $products_processed += count($products);
        }
        echo "Mapped Products: " . ($product_count - count($not_mapped)) . "/" . $product_count . "\n";
        echo "Copying Prices Now: ";
        $this->copy_prices();
        $this->update_images();
        $this->map_colors();
        $this->execute_set_queries();
        //$this->update_price();
        // echo print_r($not_mapped, true);
        /* foreach($not_mapped as $pro) {
            if (!write_file("./not-mapped-2.csv", implode(",", $pro) . "\n", "a+")) {
                echo "not saved";
            }
        } */
    }

    public function execute_set_queries()
    {

        // Fix swatch image paths for avery-wishbone-dining-table-h5056
        // Black
        $this->db->query('UPDATE `westelm_products_skus` SET swatch_image = "https://www.westelm.com/weimgs/rk/images/wcm/products/202025/0021/img90l.jpg" where sku in ("738022","9939300","5505106","7102737")');
        $this->db->query('UPDATE `westelm_products_skus` SET swatch_image_path = "/westelm/westelm_images/202025_0021_img90l.jpg" where sku in ("738022","9939300","5505106","7102737");');

        // Cool Walnut
        $this->db->query('UPDATE `westelm_products_skus` SET swatch_image = "https://www.westelm.com/weimgs/rk/images/wcm/products/202028/0011/img9l.jpg" where sku in ("4945851","6071306","4899104","6150379")');
        $this->db->query('UPDATE `westelm_products_skus` SET swatch_image_path = "/westelm/westelm_images/202028_0011_img9l.jpg" where sku in ("4945851","6071306","4899104","6150379")');

        // Natural
        $this->db->query('UPDATE `westelm_products_skus` SET swatch_image = "https://www.westelm.com/weimgs/rk/images/wcm/products/202040/0818/img40l.jpg" where sku in ("3817468","7622034")');
        $this->db->query('UPDATE `westelm_products_skus` SET swatch_image_path = "/westelm/westelm_images/202040_0818_img40l.jpg" where sku in ("3817468","7622034")');

        // Winter Wood
        $this->db->query('UPDATE `westelm_products_skus` SET swatch_image = "https://www.westelm.com/weimgs/rk/images/wcm/products/202025/0023/img94l.jpg" where sku in ("1157745","8897184","4699220","2150458")');
        $this->db->query('UPDATE `westelm_products_skus` SET swatch_image_path = "/westelm/westelm_images/202025_0023_img94l.jpg" where sku in ("1157745","8897184","4699220","2150458")');
    }

    public function copy_prices()
    {

        $product_count = $this->db->count_all("westelm_products_parents");
        $product_limit = 500;
        $products_processed = 0;
        $offset = 0;
        $batch = 0;

        while ($products_processed < $product_count) {
            $offset = $product_limit * $batch;
            $products = $this->db->get("westelm_products_parents", $product_limit, $offset)->result();

            foreach ($products as $product) {

                $skus = $this->db->get_where("westelm_products_skus", [
                    "product_id" => $product->product_id,
                    "status" => "active"
                ])->result();

                $max_price = -1;
                $max_was_price = -1;
                $min_was_price = -1;
                $min_price = -1;
                $range = "";
                $was_range = "";

                if (count($skus) == 1) {
                    //print_r($skus);
                    $max_price = $min_price = (float)$skus[0]->price;
                    $max_was_price = $min_was_price = (float)$skus[0]->was_price;
                    $range = $min_price . "-" . $max_price;
                    $was_range = $min_was_price . "-" . $max_was_price;

                    if ($min_price == $max_price) $range = $min_price;
                    if ($min_was_price == $max_was_price) $was_range = $min_was_price;

                    //echo "Min: " . $min_price . " Max: ". $max_price . " Price: " . $range . " was_price: " . $was_range . "\n";

                    Westelm::update_price($product->product_id, $min_price, $max_price, $range, $was_range);
                } else if (count($skus) > 1) {
                    $min_price = (float) $skus[0]->price;
                    $min_was_price = (float)$skus[0]->was_price;

                    $max_price = (float) $skus[0]->price;
                    $max_was_price = (float)$skus[0]->was_price;

                    foreach ($skus as $sku) {
                        if ((float) $sku->price < $min_price) $min_price = (float) $sku->price;
                        if ((float)$sku->price > $max_price) $max_price = (float)$sku->price;

                        if ((float)$sku->was_price < $min_was_price) $min_was_price = (float)$sku->price;
                        if ((float)$sku->was_price > $max_was_price) $max_was_price = (float)$sku->was_price;
                    }

                    $range = $min_price . "-" . $max_price;
                    $was_range = $min_was_price . "-" . $max_was_price;

                    if ($min_price == $max_price) $range = $min_price;
                    if ($min_was_price == $max_was_price) $was_range = $min_was_price;

                    //echo "id: " . $product->product_id . " Min: " . $min_price . " Max: " . $max_price . " Price: " . $range . " was_price: " . $was_range . "\n";

                    Westelm::update_price($product->product_id, $min_price,  $max_price, $range, $was_range);
                }
                //echo "found SKUs: " . count($skus) . "\n";

            }

            $batch++;
            $products_processed += count($products);
            echo "batch: " . $batch . " processed: " . $products_processed . " \n";
        }
    }

    public function update_price($id, $min_price, $max_price, $range, $was_range)
    {

        $to_set = [
            "price" => $range,
            "was_price" => $was_range
        ];

        $this->db->set($to_set)
            ->where("product_id", $id)
            ->update("westelm_products_parents");
    }

    public function update_images()
    {
        echo "UPDATING IMAGES NOW...\n";

        $images = $this->db->select(['product_id', 'product_images_path'])
            ->from("westelm_products_parents")
            ->get()->result();

        $URL = "/var/www/html/";

        echo "Size :" . sizeof($images) . "\n";
        foreach ($images as $image) {
            $img_urls = [];

            $i_arr = explode(",", $image->product_images_path);
            foreach ($i_arr as $i) {
                $image_data = getimagesize($URL . $i);

                if ((exif_imagetype($URL . $i) !== IMAGETYPE_PNG) && isset($image_data['channels']))
                    array_push($img_urls, $i);
            }

            $this->db->set([
                "product_images_path" => implode(",", $img_urls)
            ])->where("product_id", $image->product_id)
                ->update("westelm_products_parents");
        }
    }

    public function map_colors()
    {
        $colors_db = $this->db->where('WestElm', 'Y')->from('color_mapping')->get()->result_array();
        $prods = $this->db->select(['product_id'])
            ->from('westelm_products_parents')
            //->where('product_id', 'avalon-platform-bed-h5197')
            ->get()->result_array();

        $color_map = [];

        foreach ($colors_db as $row) {
            $color_map[strtolower(trim($row['color_alias']))] = [
                'name' => strtolower($row['color_name']),
                'hex' => strtolower($row['color_hex'])
            ];

            $color_map[strtolower(trim($row['color_name']))] = [
                'name' => strtolower($row['color_name']),
                'hex' => strtolower($row['color_hex'])
            ];
        }

        $all__colors = [];
        foreach ($prods as $key => $p) {
            $variations = $this->db->select(['attribute_1', 'attribute_2', 'attribute_3', 'attribute_4', 'attribute_5', 'attribute_6'])
                ->from('westelm_products_skus')
                ->where('product_id', $p['product_id'])
                ->get()->result_array();

            $a_colors = [];
            foreach ($variations as $row) {
                for ($i = 1; $i <= 6; $i++) {
                    $col_name = 'attribute_' . $i;
                    $cell_val = $row[$col_name];

                    // check if color key is present in this 
                    if (strpos(strtolower($cell_val), 'color:') !== false) {
                        $color_str = explode(":", $cell_val);

                        if (isset($color_str[1])) {
                            $color_keys_to_check = $color_str[1];

                            // multiple explode
                            $color_keys_to_check = str_replace(['/', ',', ':'], " ", $color_keys_to_check);
                            $p_colors = explode(" ", $color_keys_to_check);

                            foreach ($p_colors as $color) {
                                $color = strtolower(trim(str_replace([",", ":", "&", "'"], "", $color)));

                                if (isset($color_map[$color]) && strlen($color) > 0) {
                                    if (!in_array($color_map[$color]['name'], $a_colors)) {
                                        array_push($a_colors, $color_map[$color]['name']);
                                    }
                                } else {

                                    if (!in_array($color, $all__colors)) {
                                        array_push($all__colors, $color);
                                    }
                                }
                            }
                        }
                    }
                }
            }



            if (sizeof($a_colors) > 0) {
                // save color
                echo "COLOR FOUND | " . implode(",", $a_colors) . "\n";
                Westelm::update_product_color($p['product_id'], implode(",", $a_colors));
            }
        }
        echo implode(",", $all__colors);
        echo "\nSize of Products: " . sizeof($prods) . "\n";
    }

    public function update_product_color($sku, $product_new_color)
    {

        $this->db->set("color", $product_new_color)
            ->where("product_id", $sku)
            ->update("westelm_products_parents");
    }

    public function update_serials()
    {

        // get distinct product_categories
        $product_categories = $this->db->distinct()
            ->select(["product_category"])
            ->from("westelm_products_parents")
            ->get()->result_array();

        foreach ($product_categories as $row) {

            // get products in this catgeory and update serial numbers
            $products = $this->db->select(['product_id'])
                ->where('product_category', $row['product_category'])
                ->from('westelm_products_parents')
                ->get()->result_array();

            // update serial for each product 
            foreach ($products as $key => $row) {
                $this->db->set('serial', $key + 1)
                    ->where('product_id', $row['product_id'])
                    ->update('westelm_products_parents');

                echo $row['product_id'], " => ", $key + 1, "\n";
            }
        }
    }

    public function westelm_normalize_dimensions()
    {
        $rows = $this->db->select(["product_feature", "site_name", "id"])->from("master_data")->where('site_name', 'westelm')->get()->result();
        echo "=> size: " . sizeof($rows)."\n";
        foreach($rows as $row) {
            $dimension_data = $this->westelm_extract_dimensions($row->product_feature);
            if(gettype($dimension_data) == gettype([]))
                $dimension_data = $this->format_wetselm_dimension_attributes($dimension_data);
            else {
                $dimension_data = null;
            }
            echo json_encode($dimension_data) . "\n\n";
            $this->db->set('product_dimension', json_encode($dimension_data))->where('id', $row->id)->update('master_data');
        }
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

        if(empty($dimensions_data['overall']))
            unset($dimensions_data['overall']);

        $final_dims = [];
        foreach($dimensions_data as $key => $data) {
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
                
                if(strpos($dimension_data_row, "!") !== false) continue;
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

    public function test() {
        $rows = $this->db->select(["product_dimension", "site_name", "id"])->from("master_data")->where_in('site_name', ['cab', 'cb2'])->get()->result();

        foreach($rows as $row) {
            $dimension_data = $this->transform_format_dimensions($row->product_dimension, $row->site_name);
            //$dimension_data = $this->format_cb2_dimension_attributes($dimension_data);
            echo json_encode($dimension_data) . "\n";
            echo $row->id . "\n\n";
            $this->db->set('product_dimension', json_encode($dimension_data))->where('id', $row->id)->update('master_data');

        }
    }

    public function transform_format_dimensions($dims_str, $brand) {

        $dims = null;
        $dims_str = preg_replace('/[[:cntrl:]]/', '', $dims_str);
        switch($brand) {
            case 'cb2':
                $dims = $this->format_cb2_to_westelm_dimensions($dims_str);
                break;
            case 'cab':
                $dims = $this->format_cb2_to_westelm_dimensions($dims_str);
                break;
        }

        return $dims;
    }

    private function format_cb2_to_westelm_dimensions($dims_str) {
        $dims_arr = json_decode($dims_str);
        if(json_last_error()) {
            echo json_last_error_msg() , "\n";
            echo $dims_str;
            return null;
        }
    
        $dims = [];
        $dims['overall'] = [];
        $dims['overall']['name'] = 'Overall Dimensions';
        $dims['overall']['value'] = [];

        if(gettype($dims_arr) != gettype([]))
            return null;
        foreach($dims_arr as $dims_data) {
            if($dims_data->hasDimensions) {
               $desc = $dims_data->description;
               if($desc == "") $desc = "NULL";
            
               if($desc == "Overall Dimensions") {
                   foreach($this->dimension_attrs as $attr) {
                       if(isset($dims_data->$attr)) {
                            if($dims_data->$attr != 0)
                                $dims['overall']['value'][$attr] = $dims_data->$attr;
                       }
                   }
               }
               else {
                   if(!isset($dims[$desc])) {
                       $dims[$desc] = [];
                       $dims[$desc]['name'] = $desc;
                       $dims[$desc]['value'] = [];
                   }

                    foreach($this->dimension_attrs as $attr) {
                        if(isset($dims_data->$attr)) {
                            if($dims_data->$attr != 0)
                                $dims[$desc]['value'][$attr] = $dims_data->$attr;
                        }
                    }
              }
            }
        }
        
        $final_dims = [];
        $final_dims[] = [
            'groupName' => 'Overall',
            'groupValue' => []
        ];
        foreach($dims as $key => $value) {
            $final_dims[0]['groupValue'][] = [
                'name' => $value['name'],
                'value' => $value['value']
            ];
        }

        return $final_dims;
    }

};
