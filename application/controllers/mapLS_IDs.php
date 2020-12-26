<?php
ini_set('memory_limit', '-1'); // danger! add fail safe in the script

$conn = mysqli_connect("localhost", "homestead", "secret", "lazysuzy") or die("Could not connect");


function update_LSID($sku, $LS_ID) {
   
   global $conn;
   $q = "UPDATE pier1_products SET LS_ID = '" . $LS_ID . "' WHERE product_sku = '" . $sku . "'";
   if (!mysqli_query($conn, $q)) { 
   		echo $q . "\n"; 
   		die("ERROR!" . mysqli_error($conn));
   	}
   	else {
   		echo "UPDATED: " . $sku . "\n";
   	}
}

function update_product_color($sku, $product_new_color) {

	global $conn;
	$q = "UPDATE pier1_products_color SET color_new = '" . $product_new_color . "' WHERE product_sku = '" . $sku . "'";
	mysqli_query($conn, $q);
}

function get_all($data_handle) {
   $arr = [];
   while($row = mysqli_fetch_assoc($data_handle)) {

      array_push($arr, $row);
   }

   return $arr;
}

function is_keyword_found($keyword, $name) {

	if (strpos($name, $keyword) === false) return false;
	else return true;
}

function  get_LS_ID_str($LS_ID, $LS_ID_zero_key, $LS_ID_zero_category) {

	$LS_ID_real = [];

	if (sizeof($LS_ID) > 0) {
		foreach ($LS_ID as $key => $value) 
			array_push($LS_ID_real, $value);
	}

	else if (sizeof($LS_ID) == 0 && sizeof($LS_ID_zero_category) == 0) {

		foreach ($LS_ID_zero_key as $key => $value) 
			array_push($LS_ID_real, $value);
	}

	else if (sizeof($LS_ID) == 0 && sizeof($LS_ID_zero_key) == 0) {

		foreach ($LS_ID_zero_category as $key => $value) 
			array_push($LS_ID_real, $value);
	}

	return implode(",", $LS_ID_real);
} 

//$empty_tbl = $engine->sql_query("TRUNCATE pier1_products");
//$data_flow = $engine->sql_query('INSERT INTO pier1_products SELECT * FROM pier1_products_raw');

function mapLSID() {
	
	global $conn;

	$empty_tbl = $data_flow = true;

	if ($empty_tbl && $data_flow) {
   
	   $r_dir   = mysqli_query($conn, "SELECT * FROM pier1_mapping_direct");
	   $dir = get_all($r_dir);
	   
	   $r_cat   = mysqli_query($conn, "SELECT * FROM pier1_mapping_categories");
	   $cat = get_all($r_cat);
	   
	   $r_dept  = mysqli_query($conn, "SELECT * FROM pier1_mapping_departments");
	   $dept = get_all($r_dept);
	   
	   $r_prods = mysqli_query($conn, "SELECT product_sku, product_category, department, product_name, LS_ID FROM `pier1_products` WHERE 1");
	   $prods = get_all($r_prods);

	   echo "TOTAL PRODUCTS: " . count($prods) . "\n";
	   if ($dir && $cat && $dept && $prods) {
	        $LS_ID_MAPPED = 0;
	        $echo = false;
		    foreach ($prods as $p) {
		     	$LS_ID = [];
		     	$LS_ID_zero_key = []; // key is category
		     	$LS_ID_zero_category = []; // key is department, for dept mapping

		      // =============================
		      //       DIRECT MAPPING
		      // =============================
			    foreach($dir as $val) {
			      	if (strlen($val['product_department']) > 1 && strlen($val['product_category']) > 1) {
			      		if ($p['department'] == $val['product_department'] && $p['product_category'] == $val['product_category']) {
			      			if (!isset($LS_ID[$val['product_category']])){
			      				$LS_ID[$val['product_category']] = $val['LS_ID'];
			      				break;
			      			}
			      		}
			      	}
			      	else {
			      		if ($val['product_department'] == $p['department']) {
			      			if (!isset($LS_ID[$val['product_department']])) {
			      				$LS_ID_zero_category[$val['department']] = $val['LS_ID'];
			      				break;
			      			}
			      		}
			      	}
			      }

			    if (sizeof($LS_ID) > 0 || sizeof($LS_ID_zero_category) > 0 || sizeof($LS_ID_zero_key) > 0) {
			      	$ls_id_str = get_LS_ID_str($LS_ID, $LS_ID_zero_key, $LS_ID_zero_category);
			      	update_LSID($p['product_sku'], $ls_id_str);
			      	continue;
			    }

			      // =============================
			      //      CATEGORY MAPPING
			      // =============================

			    foreach ($cat as $val) {
			      	if (strlen($val['product_department']) > 0 
			      		&& strlen($val['product_category']) > 0
			      		&& strlen($val['product_key']) > 0) {

			      		if (is_keyword_found($val['product_key'], $p['product_name'])) {
			      			if ($val['product_department'] == $p['department']) {
			      				if ($val['product_category'] == $p['product_category']) {

			      					$key = $val['product_category'];
			      					if (!isset($LS_ID[$key])) {
			      						$LS_ID[$key] = $val['LS_ID'];
			      					}
			      					else {
			      						$newKey = $key . $val['product_key']; 
			      						$LS_ID[$newKey] =  $val['LS_ID'];
			      					}
			      				}
			      			} 
			      		}
			      	}
			      	else if (strlen($val['product_key'] == 0)) {
			      		if ($val['product_category'] == $p['product_category']) {
		  					if (!isset($LS_ID_zero_key[$val['product_category']])) {
		  						$LS_ID_zero_key[$val['product_category']] = $val['LS_ID'];
		  					}
		  				}
			      	}
			    }

			    if (sizeof($LS_ID) > 0 || sizeof($LS_ID_zero_category) > 0 || sizeof($LS_ID_zero_key) > 0) {
			      	$ls_id_str = get_LS_ID_str($LS_ID, $LS_ID_zero_key, $LS_ID_zero_category);
			      	update_LSID($p['product_sku'], $ls_id_str);
			      	continue;
			    }

		 		  // =============================
			      //      CATEGORY MAPPING
			      // =============================
			      
			    foreach ($dept as $val) {
			      	if (strlen($val['product_dept']) && strlen($val['product_key']) > 0) {
			      		if (is_keyword_found($val['product_key'], $p['product_name'])) {
			      			if ($val['product_dept'] == $p['department']) {
			      				
			      				$key = $val['product_dept'];
		      					if (!isset($LS_ID_zero_category[$key])) {
		      						$LS_ID_zero_category[$key] = $val['LS_ID'];
		      					}
		      					else {
		      						$newKey = $key . $val['product_key']; 
		      						$LS_ID_zero_category[$newKey] = $val['LS_ID'];
		      					}
			      			}
			      		}
			      	} 
			    }

			    if (sizeof($LS_ID) > 0 || sizeof($LS_ID_zero_category) > 0 || sizeof($LS_ID_zero_key) > 0) {
			      	$ls_id_str = get_LS_ID_str($LS_ID, $LS_ID_zero_key, $LS_ID_zero_category);
			      	update_LSID($p['product_sku'], $ls_id_str);
			    }
	       
	   		}
	} else {
	   die("Could not fetch data from database");
	}
}
}

function mapColors() {
	global $conn;

	$r_colors = mysqli_query($conn, "SELECT * FROM color_mapping");
	$colors_db = get_all($r_colors);

	$r_prods = mysqli_query($conn, "SELECT product_sku, color, product_feature FROM pier1_products");
	$prods = get_all($r_prods);

	$color_map = [];

	foreach($colors_db as $row) {
		$color_map[strtolower($row['color_alias'])] = [
			'name' => strtolower($row['color_name']),
			'hex' => strtolower($row['color_hex'])
		];

		$color_map[strtolower($row['color_name'])] = [
			'name' => strtolower($row['color_name']),
			'hex' => strtolower($row['color_hex'])
		];
	}
	echo "Size: " . sizeof($prods) . "\n";
	foreach ($prods as $key => $row) {

		$product_new_color = "";

		if ($row['color'] != null && strlen($row['color']) > 0) {
			$colors = explode(" ", strtolower($row['color']));
			$color = $colors[0];
		}
		else {
			$row['product_feature'] = str_replace(["/", ",", "&"], " ", $row['product_feature']);
			$row['product_feature'] = str_replace([" x "], "", $row['product_feature']);
		
			$color_arr = explode(" ", strtolower(str_replace("\n", " ", $row['product_feature'])));
			$color = $color_arr[0];

			if (strpos($color, "\"") !== false || strpos($color, ":") !== false) {
				$color = $color_arr[1];
			}

		}

		if (isset($color_map[$color])) {
				$product_new_color .= $color_map[$color]['name'];
		}
		else if (isset($colors[1]) && isset($color_map[$colors[1]])) {
				$product_new_color .= $color_map[$colors[1]]['name'];
		}

		update_product_color($row['product_sku'], $product_new_color);

	}

}

mapLSID();
mapColors();