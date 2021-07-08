<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PriceHistory extends CI_Controller
{ 

    public function merge_product_price_history(){
        $product_rows = $this->db->select(['product_sku', 'min_price', 'max_price', 'min_was_price', 'max_was_price'])
            ->from('master_data')
            ->where("product_status", 'active')
            ->get()
            ->result();
        
        if(sizeof($product_rows)>0){
            foreach ($product_rows as $row) {
                $this->db->from('product_price_history');
                $this->db->where('product_sku', $row->product_sku);
                $cnt = $this->db->count_all_results();
                if($cnt>0){
                            $history_data = $this->db->query("SELECT * FROM product_price_history WHERE product_sku = '".$row->product_sku."' AND end_date IS NULL")->result();
                            $history_data = $history_data[0];

                            if($history_data->min_price == $row->min_price && $history_data->max_price == $row->max_price){
                                //do nothing
                            }
                            else{
                                   // Update existing row end_date with yesterday date
                                    $olddate = date('Y-m-d H:i:s',strtotime("-1 days"));
                                    $this->db->set(['end_date'=> $olddate])->where('product_sku', $row->product_sku)->where('id', $history_data->id)->update('product_price_history');

                                    // Then insert new row with the updated price and end date null
                                    $datetime = date("Y-m-d H:i:s");

                                    $data_fields = array(
                                        'product_sku'   => $row->product_sku,
                                        'min_price'     => $row->min_price,
                                        'max_price'     => $row->max_price,
                                        'min_was_price' => $row->min_was_price,
                                        'max_was_price' => $row->max_was_price,
                                        'from_date'     => $datetime
                                    );

                                   $this->db->insert('product_price_history', $data_fields);
                            
                            }
                    

                }
                else{
                        $datetime = date("Y-m-d H:i:s");

                        $data_fields = array(
                            'product_sku'   => $row->product_sku,
                            'min_price'     => $row->min_price,
                            'max_price'     => $row->max_price,
                            'min_was_price' => $row->min_was_price,
                            'max_was_price' => $row->max_was_price,
                            'from_date'     => $datetime
                        );

                        $this->db->insert('product_price_history', $data_fields);
                }

            }
          //  $this->db->insert_on_duplicate_update_batch('product_price_history', $to_save_reviews);
        }
        
    }

    
}
