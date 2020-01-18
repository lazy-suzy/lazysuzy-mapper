<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WestelmMapper extends CI_Controller
{
    public function index()
    {
        echo "Welcome to Westelm Mapper\n";
        WestelmMapper::copy_prices();

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
                                if (WestelmMapper::is_word_match($m_keyword, $product->product_name)) {
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
                                if (WestelmMapper::is_word_match($m_keyword, $product->product_name)) {
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
        
                    foreach($LS_ID as $key => $val) {
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
       // echo print_r($not_mapped, true);
        /* foreach($not_mapped as $pro) {
            if (!write_file("./not-mapped-2.csv", implode(",", $pro) . "\n", "a+")) {
                echo "not saved";
            }
        } */
    }

    public function copy_prices() {

        $product_count = $this->db->count_all("westelm_products_parents");
        $product_limit = 500;
        $products_processed = 0;
        $offset = 0;
        $batch = 0;

        while ($products_processed < $product_count) {
            $offset = $product_limit * $batch;
            $products = $this->db->get("westelm_products_parents", $product_limit, $offset)->result();

            foreach($products as $product) {

                $skus = $this->db->get_where( "westelm_products_skus", [
                    "product_id" => $product->product_id
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

                    WestelmMapper::update_price( $product->product_id, $min_price, $max_price, $range, $was_range);
                }
                else if(count($skus) > 1) {
                    $min_price = (float) $skus[0]->price;
                    $min_was_price = (float)$skus[0]->was_price;
                    
                    $max_price = (float) $skus[0]->price;
                    $max_was_price = (float)$skus[0]->was_price;
                    
                    foreach($skus as $sku) {
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

                    WestelmMapper::update_price($product->product_id, $min_price,  $max_price, $range, $was_range);
                }
                //echo "found SKUs: " . count($skus) . "\n";
                
            }

            $batch++;
            $products_processed += count($products);
            echo "batch: " . $batch . " processed: " . $products_processed . " \n";
        }
    }

    public function update_price($id, $min_price, $max_price, $range, $was_range) {
    
        $to_set = [
            "price" => $range,
            "was_price" => $was_range
        ];

        $this->db->set($to_set)
            ->where("product_id", $id)
            ->update("westelm_products_parents");
    }
}
