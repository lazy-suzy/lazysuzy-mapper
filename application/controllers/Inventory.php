<?php

defined('BASEPATH') or exit('No direct script access allowed');
ini_set('memory_limit', '-1');
ini_set('display_errors', 1);

class Inventory extends CI_Controller
{

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
		'nw_products_API',
		'cb2_products_new_new',
		'cb2_products_variations',
		'crateandbarrel_products',
		'crateandbarrel_products_variations',
		'westelm_products_parents'
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

	private function backup()
	{
		$this->db->query('TRUNCATE ' . $this->inventory_backup_table);
		$this->db->query('INSERT INTO ' . $this->inventory_backup_table . ' SELECT * FROM ' . $this->inventory_table);

		//$this->db->query('TRUNCATE ' . $this->inventory_table);
	}

	private function get_nw_ship_code($shipping_code)
	{

		return $shipping_code == 49 ? 'WGNW' : 'SCNW';
	}

	public function move_to_inventory($tables = null)
	{

		$this->backup();

		$inventory_skus = $this->db->select('product_sku')
			->from($this->inventory_table)
			->get()->result_array();

		$locked_skus = $this->db->select('product_sku')
			->from('master_data')
			->where('is_locked', 1)
			->get()->result_array();

		$inventory_skus = array_column($inventory_skus, 'product_sku', 'product_sku');
		$locked_skus = array_column($locked_skus, 'product_sku', 'product_sku');
		echo "current locked SKUs: " . count($locked_skus);

		// $tables is a comma separated string that can be passed to this function 
		// from command line. It will have table names that need to be moved to 
		// inventory table

		if (isset($tables)) {
			$move_to_inventory_tables = explode(",", $tables);
			foreach ($move_to_inventory_tables as $t) {
				if (!array_key_exists($t, $this->table_site_map))
					die("[ERROR] " . $t . " - invalid table found in input\n");
			}
		} else {
			$move_to_inventory_tables = $this->inventory_ready_tables;
		}


		foreach ($move_to_inventory_tables as $product_table) {

			echo "for ", $product_table . "\n";
			if ($product_table == 'westelm_products_parents') {
				$this->westelm_products_move($locked_skus);
				continue;
			}

			// here we'll add products to inventory table by using 
			// table pagination
			$offset_limit = 300;
			$batch = $processed = $offset = 0;

			$select = 'product_sku, product_sku as parent_sku, was_price, price, shipping_code, product_status';
			$code_field = 'shipping_code';
			$is_nw = false;

			if ($product_table == 'nw_products_API') {
				$select = 'product_sku, product_sku as parent_sku,  was_price, price, shipping_code, product_status';
				$code_field = 'shipping_code';
				$is_nw = true;
			}

			$sku_field = array_key_exists($product_table, $this->variation_tables) ? 'variation_sku' : 'product_sku';
			$is_variations_table = array_key_exists($product_table, $this->variation_tables);
			$variations_select = "distinct(variation_sku) as product_sku, shipping_code, {$product_table}.price, {$product_table}.was_price";

			if ($is_variations_table) {
				$total_table_products = $this->db
					->select($variations_select . ',' . $this->variation_tables[$product_table] . '.product_sku as parent_sku')
					->where("{$product_table}.price != ", NULL)
					->where("{$this->variation_tables[$product_table]}.shipping_code != ", NULL)

					->where("{$this->variation_tables[$product_table]}.shipping_code > ", 0)
					->join($this->variation_tables[$product_table], "{$this->variation_tables[$product_table]}.product_sku = {$product_table}.product_sku")
					->from($product_table)
					->count_all_results();

				/*print_r($this->db->last_query());    */
			} else {
				$total_table_products = $this->db
					->where('price != ', NULL)
					->where($code_field . ' != ', NULL)
					->from($product_table)
					->count_all_results();
			}

			echo "Total: " . $total_table_products . "\n";
			while ($processed < $total_table_products) {
				$offset = $batch * $offset_limit;

				if ($is_variations_table) {
					$product_rows = $this->db
						->select($variations_select . ',' . $this->variation_tables[$product_table] . '.product_sku as parent_sku')
						->from($product_table)
						->where("{$product_table}.price != ", NULL)

						->where("{$this->variation_tables[$product_table]}.shipping_code != ", NULL)
						->where("{$this->variation_tables[$product_table]}.shipping_code > ", 0)
						->join($this->variation_tables[$product_table], "{$this->variation_tables[$product_table]}.product_sku = {$product_table}.product_sku")

						->limit($offset_limit, $offset)
						->get()->result();
				} else {

					$product_rows = $this->db->select($select)
						->from($product_table)
						->where('price != ', NULL)
						->where($code_field . ' != ', NULL)
						->limit($offset_limit, $offset)
						->get()->result();
				}

				$to_insert = [];
				$to_insert_nw = [];
				foreach ($product_rows as $row) {

					if ($row->shipping_code == 0) continue;

					if (
						!array_key_exists($row->product_sku, $locked_skus) &&
						!array_key_exists($row->parent_sku, $locked_skus)
					) {
						if (!array_key_exists($row->product_sku, $inventory_skus)) {

							unset($inventory_skus[$row->product_sku]);

							if (!$is_nw) {
								$to_insert[] = [
									'product_sku' => $row->product_sku,
									'price' => $row->price,
									'was_price' => isset($row->was_price) ? $row->was_price : $row->price,
									'ship_code' => $this->code_map[$row->shipping_code] . strtoupper($this->table_site_map[$product_table]),
									'quantity' => 1000,
									'is_active' => $row->product_status == 'active' ? '1' : '0'
								];
							} else {
								$to_insert_nw[] = [
									'product_sku' => $row->product_sku,
									'price' => $row->price,
									'was_price' => isset($row->was_price) ? $row->was_price : $row->price,
									'ship_code' => $this->get_nw_ship_code($row->shipping_code),
									'quantity' => 1000,
									'ship_custom' => $this->get_nw_ship_code($row->shipping_code) == 'SCNW' ? $row->shipping_code : NULL,
									'is_active' => $row->product_status == 'active' ? '1' : '0'
								];
							}
						} else {
							// update the ship code if SKU is already present in the inventory table

							$ship_code = $is_nw ? $this->get_nw_ship_code($row->shipping_code) :  $this->code_map[$row->shipping_code] . strtoupper($this->table_site_map[$product_table]);
							$this->db->set([
								//'ship_code' => $ship_code,
								'price' => $row->price,
								'was_price' => $row->was_price,
								'is_active' => $row->product_status == 'active' ? '1' : '0'
							])
								->where('product_sku', $row->product_sku)
								->where('brand != ', 'westelm')
								->update($this->inventory_table);

							echo "UPDATE: " . $row->product_sku . " " . $row->product_status . "\n";
						}
					} else {
						echo "[LOCKED SKU] " . $row->product_sku . "\n";
					}
				}

				file_put_contents('to-insert.json', json_encode($to_insert));
				file_put_contents('to-insert-nw.json', json_encode($to_insert_nw));

				// insert into inventory
				if (sizeof($to_insert) > 0)
					$this->db->insert_batch($this->inventory_table, $to_insert);
				if (sizeof($to_insert_nw) > 0)
					$this->db->insert_batch($this->inventory_table, $to_insert_nw);

				$batch += 1;
				$processed += sizeof($product_rows);

				if (sizeof($product_rows) == 0) break;
				echo $batch . " => " . $processed . " (" . sizeof($to_insert) . "," . sizeof($to_insert_nw) . ")" . "\n";
			}
		}
	}

	public function westelm_products_move($locked_skus)
	{

		$locked_skus = [];
		$wm_products = "westelm_products_parents";
		$wm_variations = "westelm_products_skus";
		$to_select = ['product_id', 'description_shipping', 'price', 'was_price', 'product_status', 'product_name'];

		$inventory_rows = $this->db->select('product_sku')
			->from($this->inventory_table)
			->get()->result_array();
		$inventory_rows_sku = array_column($inventory_rows, 'product_sku', 'product_sku');
		$westelm_rows = $this->db->select($to_select)
			->from($wm_products)
			->where('price !=', NULL)
			//->where('product_id', 'mod-storage-bench-54-h4582')
			->get()->result();

		echo "[TOTAL]  " . count($westelm_rows) . "\n";
		foreach ($westelm_rows as $row) {
			$SKU = $row->product_id;
			$name = $row->product_name;
			$is_active = ($row->product_status == 'active') ? true : false;

			$variations = $this->wm_vars($SKU, $wm_variations);
			echo "[VARIATIONS] " . count($variations) . "\n";
			if (count($variations) == 1) {
				$parentSKU = null;
				$productSKU = $SKU;
				// make details and save
				$details = $this->make_details($row, null, $parentSKU, $productSKU);
				if (isset($inventory_rows_sku[$productSKU])) {

					// don't update locked SKUs
					if (!array_key_exists($productSKU, $locked_skus)) {
						echo "[UPDATE SKU] " . $SKU . "\n";

						$this->db->set('is_active', $details['is_active'])
							->set('price', $details['price'])
							->set('was_price', $details['was_price'])
							->where('product_sku', $details['product_sku'])
							->where('brand', 'westelm')
							->update($this->inventory_table);
					}
				} else {
					$this->db->insert($this->inventory_table, $details);
				}
			} else {

				foreach ($variations as $var) {
					$parentSKU = $SKU;
					$productSKU = $var->sku;

					// make details and save
					$details = $this->make_details($row, $var, $parentSKU, $productSKU);
					if (isset($inventory_rows_sku[$productSKU])) {
						// don't update locked SKUs
						if (!array_key_exists($SKU, $locked_skus) && !array_key_exists($productSKU, $locked_skus)) {
							echo "[UPDATE SKU] " . $SKU . "\n";
							$this->db->set('is_active', $details['is_active'])
								->set('price', $details['price'])
								->set('was_price', $details['was_price'])
								->where('product_sku', $details['product_sku'])
								->where('parent_sku', $parentSKU)
								->update($this->inventory_table);
						}
					} else {
						$this->db->insert($this->inventory_table, $details);
					}
				}
			}
		}
	}

	public function make_details($product, $variation, $parentSKU, $productSKU)
	{
		$details = [];

		$name = $product->product_name;
		$SKU = $product->product_id;
		$site_name = 'westelm';
		$details['product_sku'] = $productSKU;
		$details['parent_sku'] = $parentSKU;
		$details['quantity'] = 1000;
		$details['inventory'] = 'Direct';
		$details['is_active'] = ($product->product_status == 'active') ? '1' : '0';

		$product_desc = $product->description_shipping;

		if (!isset($variation)) {
			// this is a single entry in wm_variations table 
			$details['price'] = $product->price;
			$details['was_price'] = $product->was_price;
			$details['brand'] = $this->get_wm_brand($name, $SKU, $site_name);
			$details['ship_code'] = $this->get_wm_ship_code($details['brand'], $site_name, $product_desc);
		} else {
			// this is a valid variations case
			$details['price'] = $variation->price;
			$details['was_price'] = $variation->was_price;
			$details['brand'] = $this->get_wm_brand($name, $SKU, $site_name);
			$details['ship_code'] = $this->get_wm_ship_code($details['brand'], $site_name, $product_desc);
		}

		return $details;
	}

	public function get_wm_ship_code($brand, $site_name, $product_desc)
	{

		if ($brand != $site_name)
			return "F0";

		// match the product desc
		$possible_matches = [
			"free shipping" => "F0",
			"front door delivery" => "SVWE",
			"UPS" => "SVWE"
		];

		$possible_keys = array_keys($possible_matches);
		foreach ($possible_keys as $key) {

			if (strpos(strtolower($product_desc), strtolower($key)) !== false) {
				echo "[matched ship code]\n";
				return $possible_matches[$key];
			}
		}

		return "WGWE";
	}


	public function get_wm_brand($name, $id, $site_name)
	{

		echo $name . " ";
		$possible_brands = [
			"floyd" => "floyd",
			"rabbit" => "rar",
			"amigo" => "am",
			"burrow" => "burrow"
		];

		$possible_keys = array_keys($possible_brands);
		// search name to check if any possible key is present;
		$name_arr = explode(" ", strtolower($name));
		foreach ($possible_keys as $key) {
			if (
				in_array($key, $name_arr)
				|| strpos(strtolower($id), strtolower($key)) !== false
			) {
				echo "[matched brand]\n";
				return $possible_brands[$key];
			}
		}

		return $site_name;
	}

	public function wm_vars($SKU, $table)
	{

		$rows = $this->db->select('*')->from($table)->where('product_id', $SKU)->get()->result();
		return $rows;
	}
}