<?php

defined('BASEPATH') or exit('No direct script access allowed');
ini_set('memory_limit', '-1');
ini_set('display_errors', 1);

// this will update the cb2 and cab variations 
// with correct price values 
class VariationsUpdate extends CI_Controller {

	private $cb2_variations_table = 'cb2_products_variations';
	private $cab_variations_table = 'crateandbarrel_products_variations';
    private $cb2_table = 'cb2_products_new_new';
    private $cab_table = 'crateandbarrel_products';

	function __constructor() {
		
	}

	public function get_data($sku, $type) {
		$retry = 10;

		
		$data = $type == 'cb2' ? $this->cb2->get_variations($sku) : $this->cnb->get_variations($sku);

		while(sizeof($data) == 0 && $retry--) {
			echo "retry data for " . $sku . "\n";
			$data = $type == 'cb2' ? $this->cb2->get_variations($sku) : $this->cnb->get_variations($sku);
			sleep(15);
		}

		return $data;
	}

	public function test() {
		$this->load->library('CB2', array(
                'proxy' => '5.79.66.2:13010',
                'debug' => false,
        ));

		$data = $this->cb2->get_variations(288789);
		echo json_encode($data);
		
		$data = $this->cb2->get_variations(529300);
		echo json_encode($data);

		$data = $this->cb2->get_variations(682273);
		echo json_encode($data);
		
		$data = $this->cb2->get_variations("476085");
		echo json_encode($data);
	}
	
	public function update_cb2_variations_price() {

		$this->load->library('CB2', array(
                'proxy' => '5.79.66.2:13010',
                'debug' => false,
        ));

		// get distinct varaition skus from the table
		$variations_SKUs = $this->db->distinct()
                ->select('variation_sku')
                ->where('has_parent_sku', 0)
                ->from($this->cb2_variations_table)
                ->get()->result_array();

        echo "distinct sku size: " . sizeof($variations_SKUs) . "\n";

        $not_processed = [];
        foreach($variations_SKUs as $var) {
        	$sku = $var['variation_sku'];
        	echo "processing sku: " . $sku . "\n";
        	$sku_collection = $this->get_data(trim($sku), 'cb2');

        	if(sizeof($sku_collection) == 0)
        		$not_processed[] = $sku;

        	foreach($sku_collection as $collection) {

        		$to_update = [
        			'price' => floatval($collection['CurrentPrice']),
        			'was_price' => floatval($collection['RegularPrice']),
        		];

        		$this->db->where('variation_name', $collection['ChoiceName'])
        			->where('variation_sku', $sku)
        			->update($this->cb2_variations_table, $to_update);
        	}

        }

        file_put_contents('not-processed-cb2-variations-for-price-update.json', 
        	json_encode($not_processed));
    }

	public function update_cab_variations_price() {
		
		$this->load->library('CNB', array(
            'proxy' => '5.79.66.2:13010',
            'debug' => false,
        ));

        // get distinct varaition skus from the table
		$variations_SKUs = $this->db->distinct()
                ->select('variation_sku')
                ->where('has_parent_sku', 0)
                ->from($this->cab_variations_table)
                ->get()->result_array();

        echo "distinct sku size: " . sizeof($variations_SKUs) . "\n";

        $not_processed = [];
        foreach($variations_SKUs as $var) {
        	$sku = $var['variation_sku'];
        	echo "processing sku: " . $sku . "\n";
        	$sku_collection = $this->get_data(trim($sku), 'cab');

        	if(sizeof($sku_collection) == 0)
        		$not_processed[] = $sku;

        	foreach($sku_collection as $collection) {

        		$to_update = [
        			'price' => floatval($collection['CurrentPrice']),
        			'was_price' => floatval($collection['RegularPrice']),
        		];

        		$this->db->where('variation_name', $collection['ChoiceName'])
        			->where('variation_sku', $sku)
        			->update($this->cab_variations_table, $to_update);
        	}

        }

        file_put_contents('not-processed-cab-variations-for-price-update.json', 
        	json_encode($not_processed));
	}

    public function update_zero_and_null_variations($brand = null) {

        // this will update variations that have zero or null price values 
        // for cb2 and cab variations
        
        if(!isset($brand))
            die("param missing, please give a brand name. hint: cb2 or cab \n");

        $table = null;
        $parent_table  = null; 
        $no_parents_found = [];

        if($brand == 'cab') {
            $table = $this->cab_variations_table;
            $parent_table = $this->cab_table;
        }
        else {
            $table = $this->cb2_variations_table;
            $parent_table = $this->cb2_table;
        }

        // get variations with 0 or null prices
        $rows = $this->db->distinct()
            ->select('variation_sku, product_sku')
            ->where('price', 0)
            ->or_where('price', NULL)
            ->from($table)
            ->get()->result_array();

        echo "row count: " . sizeof($rows) . "\n";
        $updated_skus = 0;
        foreach ($rows as $key => $row) {
            // get parent price
            $p_row = $this->db->select('price, was_price')
                ->where('product_sku', $row['product_sku'])
                ->from($parent_table)
                ->get()->result_array();

            if(sizeof($p_row) > 0) {
                $collection = $p_row[0];

                $to_update = [
                    'price' => floatval($collection['price']),
                    'was_price' => floatval($collection['was_price']),
                ];

                $this->db->where('variation_sku', $row['variation_sku'])
                    ->update($table, $to_update);
                echo $row['product_sku'] . " " . $row['variation_sku'] . " " . $to_update['price'] . "\n";
                $updated_skus++;
            }
            else {
                $no_parents_found[] = $row['variation_sku'];
            }


        }

        file_put_contents('no-parents-found.json', json_encode($no_parents_found));
        echo "updated skus: " . $updated_skus . "\n";
    }

    public function update_cb2_variationsSKUs() {
      $rows = $this->db->select(['id', 'product_sku', 'variation_sku', 'variation_name'])
               ->from('cb2_products_variations')
               ->where('has_parent_sku', 0)
               ->get()->result();

      foreach($rows as $row) {

        $var_name = $row->variation_name;
        $var_name = str_replace([" ", ",", "\"", ".", "/"], ["", "_", "", "", "_"], $var_name);

        $var_sku = $row->variation_sku;
        if(!is_numeric($var_name[0]))
        	$var_sku = $row->variation_sku . "_" . $var_name;
         //$var_sku = explode("_", $row->variation_sku)[0];

         $this->db->set('variation_sku', $var_sku)
                  ->where('id', $row->id)
                  ->update('cb2_products_variations'); // cb2_products_variations
      }
   }

   public function update_cab_variationsSKUs() {
      $rows = $this->db->select(['id', 'product_sku', 'variation_sku', 'variation_name'])
               ->from('crateandbarrel_products_variations')
                ->where('has_parent_sku', 0)
               ->get()->result();

      foreach($rows as $row) {

        $var_name = $row->variation_name;
        $var_name = str_replace([" ", ",", "\"", ".", "/"], ["", "_", "", "", "_"], $var_name);

        $var_sku = $row->variation_sku;
        if(!is_numeric($var_name[0]))
        	$var_sku = $row->variation_sku . "_" . $var_name;
         //$var_sku = explode("_", $row->variation_sku)[0];
         $this->db->set('variation_sku', $var_sku)
                  ->where('id', $row->id)
                  ->update('crateandbarrel_products_variations'); //crateandbarrel_products_variations
      }
   }
}