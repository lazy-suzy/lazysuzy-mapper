<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Reviews extends CI_Controller
{

    private $master_table = "master_data";
    private $review_table_cb2 = "cb2_products_reviews";
    private $review_table_cab = "cab_products_reviews";

    private function load_lib($lib_type)
    {

        if ($lib_type == 'cb2') {
            //Initialize CB2 Module
            $this->load->library('CB2', array(
                'proxy' => '5.79.66.2:13010',
                'debug' => false,
            ));
        } else {
            //Initialize CNB Module
            $this->load->library('CNB', array(
                'proxy' => '5.79.66.2:13010',
                'debug' => false,
            ));
        }
    }

    public function cab()
    {
        $this->load_lib('cab');
        // get product SKU list
        $product_skus = $this->get_skus('cab');
        //$product_skus = ["335376"];
        foreach ($product_skus as $sku) {
            $reviews = $this->get_reviews('cab', 's' . $sku);
            $this->save_reviews($reviews, $sku, 'cab');
        }
    }

    public function cb2()
    {
        $this->load_lib('cb2');
        // get product SKU list
        $product_skus = $this->get_skus('cb2');
        foreach ($product_skus as $sku) {
            $reviews = $this->get_reviews('cb2', 's' . $sku);
            $this->save_reviews($reviews, $sku, 'cb2');
        }
    }

    private function generate_hash($product_sku, $username, $submission_date) {

        $hash_input = "";

        if(isset($product_sku) && strlen($product_sku) > 0) $hash_input .= $product_sku;
        if(isset($username)  && strlen($username) > 0) $hash_input .= $username;
        if(isset($submission_date)  && strlen($submission_date) > 0) $hash_input .= $submission_date;

        return md5($hash_input);
    }

    private function save_reviews($reviews, $sku, $site_name)
    {

        $site_name_ = $site_name == 'cab' ? 'cnb' : $site_name;
        if (!isset($reviews) || empty($reviews)) {
            return;
        }

        $review_table = $site_name == 'cab' ? $this->review_table_cab : $this->review_table_cb2;
        $to_save_reviews = [];
        foreach ($reviews as $review) {

            $image_arr = sizeof($review->Photos) == 0 ? [] : $review->Photos;
            $image_urls = [];
            foreach ($image_arr as $image_detail) {
                $order = $image_detail->SizesOrder;
                foreach ($order as $order_name) {
                    if (!isset($image_urls[$order_name]))
                        $image_urls[$order_name] = [];
                    $image_urls[$order_name][] = $image_detail->Sizes->$order_name->Url;
                }
            }

            $to_save_reviews[] = [
                'product_sku' => $sku,
                'review_hash' => $this->generate_hash($sku, $review->UserNickname, $review->SubmissionTime),
                'review_title' => $review->Title,
                'review_text' => $review->ReviewText,
                'username' => $review->UserNickname,
                'review_rating' => $review->Rating,
                'review_images' => $this->multiple_download($image_urls['normal'], '/var/www/html/' . $site_name_ . '/images/reviews', '/' . $site_name_ . '/images/reviews/'),
                'review_images_thumbnails' => $this->multiple_download($image_urls['thumbnail'], '/var/www/html/' . $site_name_ . '/images/reviews', '/' . $site_name_ . '/images/reviews/'),
                'review_images_caption' => '',
                'feedback_positive' => $review->TotalPositiveFeedbackCount,
                'feedback_negative' => $review->TotalNegativeFeedbackCount,
                'submission_time' => $review->SubmissionTime
            ];
        }

        $this->db->insert_on_duplicate_update_batch($review_table, $to_save_reviews);
    }

    private function get_reviews($site_name, $sku)
    {
        echo "for sku: " . $sku . "\n";
        $reviews = [];
        $retry = 5;
        $_GET['offset'] = 0;
        if ($site_name == 'cb2') {
            $review_data = $this->cb2->get_reviews($sku);
        } else {
            $review_data = $this->cnb->get_reviews($sku);
        }
        echo "try with offset: ", $_GET['offset'] . "\n";
        while ((!isset($review_data) || empty($review_data)) && $retry) {
            if ($site_name == 'cb2') {
                $review_data = $this->cb2->get_reviews($sku);
            } else {
                $review_data = $this->cnb->get_reviews($sku);
            }
            sleep(5);
            $retry--;
        }

        if (!isset($review_data) || empty($review_data)) {
            return [];
        }

        $review_data = json_decode(json_encode($review_data));
        $total_reviews = $review_data->TotalResults;
        echo "total_result: " . $total_reviews . "\n";

        while (sizeof($reviews) < $total_reviews) {
            if (isset($review_data->Reviews)) {
                foreach ($review_data->Reviews as $rev) {
                    $reviews[] = $rev;
                }
            }

            echo "review size: " . sizeof($reviews) . "\n";
            $_GET['offset'] += 100;
            echo "try with offset: ", $_GET['offset'] . "\n";
            if ($site_name == 'cb2') {
                $review_data = $this->cb2->get_reviews($sku);
            } else {
                $review_data = $this->cnb->get_reviews($sku);
            }
            $review_data = json_decode(json_encode($review_data));

            echo "=> new offset: " . $review_data->Offset . "\n";
            echo "=> type: " . gettype($review_data) . "\n";
            while ((!isset($review_data) || empty($review_data)) && $retry) {
                if ($site_name == 'cb2') {
                    $review_data = $this->cb2->get_reviews($sku);
                } else {
                    $review_data = $this->cnb->get_reviews($sku);
                }
                sleep(5);
                $retry--;
            }
        }

        return $reviews;
    }

    private function get_skus($site_name)
    {
        $product_skus = $this->db->select('product_sku')
            ->distinct()->from($this->master_table)
            ->where('site_name', $site_name)->get()->result();
        $product_skus = array_column($product_skus, 'product_sku');
        return $product_skus;
    }

    public function multiple_download($urls, $save_path = '/tmp', $save_path_core)
    {
        if (!isset($urls) || empty($urls)) {
            return '';
        }

        //echo $save_path , " == " , $save_path_core . "\n";
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

    public function merge()
    {
        $tables = [
           // 'cb2_products_reviews',
           // 'cab_products_reviews',
            'user_reviews'
        ];

        $offset_limit = 600;
        $batch = 0;
        $offset = 0;

        foreach ($tables as $table) {
            $total_reviews = $this->db->select("*")->from($table);

            if ($table == 'user_reviews') {
                $total_reviews = $total_reviews->where_in('status', ['2', '3']);
            }

            $total_reviews = $total_reviews->count_all_results();

            $batch = 0;
            $processed = 0;
            $offset = 0;
            while ($processed < $total_reviews) {
                $to_insert = [];
                $rows = $this->db->select("*")
                    ->from($table);

                if ($table == 'user_reviews') {
                    $rows = $rows->where_in('status', ['2', '3']);
                }

                $rows = $rows->limit($offset_limit, $offset)
                    ->get()->result();

                $batch++;
                $processed += count($rows);
                $offset = $batch * $offset_limit;
                echo "batch: $batch, processed: $processed, table: $table\n";

                if ($table == 'user_reviews') {
                    $this->merge_user_reviews($rows, $table);
                    continue;
                }

                foreach ($rows as $row) {
                    $to_insert[] = [
                        'user_id' => $table == 'cb2_products_reviews' ? '2' : '3',
                        'product_sku' => $row->product_sku,
                        'headline' => $row->review_title,
                        'review' => $row->review_text,
                        'rating' => $row->review_rating,
                        'review_images' => $row->review_images,
                        'user_name' => $row->username,
                        'user_email' => null,
                        'user_location' => null,
                        'status' => "2",
                        'count_helpful' => $row->feedback_positive,
                        'count_reported' => $row->feedback_negative,
                        'source' => 'mapper',
                        'submission_time' => $row->submission_time,
                        'review_id' => $row->id,
                        'source' => $table,

                    ];
                }

                if (!empty($to_insert))
                    $this->db->insert_on_duplicate_update_batch('master_reviews', $to_insert);
            }
        }
    }

    private function merge_user_reviews($rows, $table)
    {
        $to_insert = [];
        foreach ($rows as $row) {
            $to_insert[] = [
                'user_id' => $row->user_id,
                'product_sku' => $row->product_sku,
                'headline' => $row->headline,
                'review' => $row->review,
                'rating' => $row->rating,
                'review_images' => $row->review_images,
                'user_name' => $row->user_name,
                'user_email' => $row->user_email,
                'user_location' => $row->user_location,
                'status' => $row->status,
                'count_helpful' => $row->count_helpful,
                'count_reported' => $row->count_reported,
                'source' => $table,
                'submission_time' => $row->submission_time,
                'review_id' => $row->id,
            ];
        }

        if (!empty($to_insert))
            $this->db->insert_on_duplicate_update_batch('master_reviews', $to_insert);
    }

    public function nw_reviews() {
        $table_mappings = [
            'Title' => 'headline',
            'ReviewText' => 'review',
            'Rating' => 'rating',
            'TotalFeedbackCount' => 'count_reported',
            'TotalNegativeFeedbackCount' => 'count_negative',
            'TotalPositiveFeedbackCount' => 'count_helpful',
            'UserNickname' => 'user_name',
            'Url' => 'review_images',
            'Caption' => 'image_title', 
            'SubmissionTime' => 'submission_time'
          
        ];

        $base = "http://four-nodes.com/projects/scripts/worldmarket.php?reviews=33327";
        $json = file_get_contents($base);
        $json = json_decode($json, true);
        
        // retry once if no data is received.
        if(empty($json)) {
            $json = file_get_contents($base);
            $json = json_decode($json, true);
        }

        $total_fetched_reviews = isset($json['Reviews']) ? sizeof($json['Reviews']) : 0;
        $total_reviews = (int) $json['TotalResults'];
        $limit = (int) $json['Limit'];
        $offset = 0;
        $product_sku = $json['ProductCode'];

        while($total_fetched_reviews < $total_reviews) {
            $url = $base . '&offset=' . $offset;
            echo $url . "\n";
            $json = file_get_contents($url);
            $json = json_decode($json, true);

            $to_insert = [];
            // make table compatible data and save to DB.
            if(isset($json['Reviews'])) {
                $data = [];
                $data['product_sku'] = $product_sku;
                foreach($json['Reviews'] as $review) {
                    foreach($review as $key => $value) {
                        if(is_array($value) && sizeof($value) > 0) {
                            foreach($value[0] as $valKey => $val) {
                                // pictures
                                if(array_key_exists($valKey, $table_mappings)) {
                                    $data[$table_mappings[$valKey]] = $val;
                                }
                            }
                        }
                        else {
                            // normal attrs 
                            if(array_key_exists($key, $table_mappings)) {
                                $data[$table_mappings[$key]] = $value;
                            }
                        }
                    }
                }

                // save to db;
            }


            $total_fetched_reviews = isset($json['Reviews']) ? $total_fetched_reviews + sizeof($json['Reviews']) : $total_fetched_reviews . "\n";
            echo "review size: " . $total_reviews , " fetched: " . $total_fetched_reviews . "\n";
            echo "offset: " . $offset . "\n";
            $offset += $limit;
            
        }



    }
}
