<?php

defined('BASEPATH') or exit('No direct script access allowed');
ini_set('memory_limit', '-1');
ini_set('display_errors', 1);

class Inventory extends CI_Controller { 

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

 	private $inventory_ready_tables = [
 		//'nw_products_API',
 		//'cb2_products_new_new',
 		//'cb2_products_variations',
 		'crateandbarrel_products',
 		'crateandbarrel_products_variations'
 	];

 	private $variation_tables = [
 		'crateandbarrel_products_variations' => 'crateandbarrel_products', //crateandbarrel_products_variations
 		'cb2_products_variations' => 'cb2_products_new_new' // cb2_products_variations
 	];

 	private $code_map = [
 		'100' => 'SV',
 		'400' => 'WG',
 	];

 	private $inventory_table = 'lz_inventory';
 	private $inventory_backup_table = 'lz_inventory_backup';

 	private function backup() {
 		$this->db->query('TRUNCATE ' . $this->inventory_backup_table);
 		$this->db->query('INSERT INTO ' . $this->inventory_backup_table . ' SELECT * FROM ' . $this->inventory_table);

 		//$this->db->query('TRUNCATE ' . $this->inventory_table);
 	}

 	private function get_nw_ship_code($shipping_code) {

 		return $shipping_code == 49 ? 'WGNW' : 'SCNW';
 	}

	public function move_to_inventory($tables = null) {

		$this->backup();

		$inventory_skus = $this->db->select('product_sku')
		->from($this->inventory_table)
		->where('is_active', 1)
		->get()->result_array();

		$inventory_skus = array_column($inventory_skus, 'product_sku', 'product_sku');

		// $tables is a comma separated string that can be passed to this function 
		// from command line. It will have table names that need to be moved to 
		// inventory table
		
		if(isset($tables)) {
			$move_to_inventory_tables = explode(",", $tables);
			foreach($move_to_inventory_tables as $t) {
				if(!array_key_exists($t, $this->table_site_map))	
					die($t . 'Invalid table found in input');
			}
		}
		else {
			$move_to_inventory_tables = $this->inventory_ready_tables;
		}

		
		foreach ($move_to_inventory_tables as $product_table) {
			// here we'll add products to inventory table by using 
			// table pagination
            echo "for " , $product_table . "\n"; 
			$offset_limit = 300;
        	$batch = $processed = $offset = 0;

        	$select = 'product_sku, was_price, price, shipping_code';
    		$code_field = 'shipping_code';
    		$is_nw = false;

    		if($product_table == 'nw_products_API') {
    			$select = 'product_sku, was_price, price, shipping_code';
    			$code_field = 'shipping_code';
    			$is_nw = true;
    		}
            
            $sku_field = array_key_exists($product_table, $this->variation_tables) ? 'variation_sku' : 'product_sku';
            $is_variations_table = array_key_exists($product_table, $this->variation_tables);
			$variations_select = "distinct(variation_sku) as product_sku, shipping_code, {$product_table}.price, {$product_table}.was_price";
            
            if($is_variations_table) {
            	$total_table_products = $this->db
            			->select($variations_select)
            			->where('is_active', 'active')
            			->where("{$product_table}.price != ", NULL)
                        ->where("{$this->variation_tables[$product_table]}.shipping_code != ", NULL)

            			->where("{$this->variation_tables[$product_table]}.shipping_code > ", 0)
            			->join($this->variation_tables[$product_table], "{$this->variation_tables[$product_table]}.product_sku = {$product_table}.product_sku")
            			->from($product_table)
            			->count_all_results();

            	/*print_r($this->db->last_query());    */

            }
            else {
            	$total_table_products = $this->db
            			->where('product_status', 'active')
            			->where('price != ', NULL)
            			->where($code_field . ' != ', NULL)
            			->from($product_table)
            			->count_all_results();
            }

            echo "Total: " . $total_table_products . "\n";
            while($processed < $total_table_products) {
            	$offset = $batch * $offset_limit;

            	if($is_variations_table) {
            		$product_rows = $this->db
            			->select($variations_select)
            			->from($product_table)
            			->where('is_active', 'active')
            			->where("{$product_table}.price != ", NULL)

                        ->where("{$this->variation_tables[$product_table]}.shipping_code != ", NULL)
            			->where("{$this->variation_tables[$product_table]}.shipping_code > ", 0)
            			->join($this->variation_tables[$product_table], "{$this->variation_tables[$product_table]}.product_sku = {$product_table}.product_sku")

            			->limit($offset_limit, $offset)
            			->get()->result();
            	}
            	else {

            		$product_rows = $this->db->select($select)
            		->from($product_table)
            		->where('product_status', 'active')
            		->where('price != ', NULL)
            		->where($code_field . ' != ', NULL)
            		->limit($offset_limit, $offset)
            		->get()->result();
           		}

           		$to_insert = [];
           		$to_insert_nw = [];
        		foreach($product_rows as $row) {

                    if($row->shipping_code == 0) continue;

        			if(!array_key_exists($row->product_sku, $inventory_skus)) {
	        			
	        			unset($inventory_skus[$row->product_sku]);

        				if(!$is_nw) {
                			$to_insert[] = [
		        				'product_sku' => $row->product_sku,
		        				'price' => $row->price,
		        				'was_price' => $row->was_price,
		        				'ship_code' => $this->code_map[$row->shipping_code] . strtoupper($this->table_site_map[$product_table]),
		        				'quantity' => 1000
	        				];
						}
						else {
							$to_insert_nw[] = [
		        				'product_sku' => $row->product_sku,
		        				'price' => $row->price,
		        				'was_price' => $row->was_price,
		        				'ship_code' => $this->get_nw_ship_code($row->shipping_code),
		        				'quantity' => 1000,
		        				'ship_custom' => $this->get_nw_ship_code($row->shipping_code) == 'SCNW' ? $row->shipping_code : NULL
	        				];
						}

					}
					else {
						// update the ship code if SKU is already present in the inventory table
						
						$ship_code = $is_nw ? $this->get_nw_ship_code($row->shipping_code) :  $this->code_map[$row->shipping_code] . strtoupper($this->table_site_map[$product_table]);
						$this->db->set([
							'ship_code' => $ship_code,
							'price' => $row->price,
							'was_price' => $row->was_price
							])
							->where('product_sku', $row->product_sku)
							->update($this->inventory_table);
						
						echo "UPDATE: " . $row->product_sku . " CODE: " . $ship_code . "\n";
					}

        		}

        		// insert into inventory
        		if(sizeof($to_insert) > 0)
        			$this->db->insert_batch($this->inventory_table, $to_insert);
        		if(sizeof($to_insert_nw) > 0)
        			$this->db->insert_batch($this->inventory_table, $to_insert_nw);

        		$batch += 1; $processed += sizeof($product_rows);

        		if(sizeof($product_rows) == 0) break;
        		echo $batch . " => " . $processed . " (" . sizeof($to_insert) . "," . sizeof($to_insert_nw) . ")" . "\n";

			}
		}
	}
}