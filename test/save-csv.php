<?php 

$conn = mysqli_connect("localhost", "homestead", "secret", "lazysuzy") or die(mysqli_error($conn));

$data = file_get_contents('image_xbg.csv');
$data = explode("\n", $data);

foreach($data as $row) {
	$row = str_replace(["\n", "\r"], "", $row);
	$cols = explode(",", $row);

	$sku = $cols[0];
	$img = $cols[1];
	$img = str_replace("&", "_", $img);
	$image_path = "/nw/images/" . $img  . ".png";


	$q = "UPDATE nw_products_API SET image_xbg = '$image_path' WHERE product_sku = '$sku'";
	if (!mysqli_query($conn, $q))
		echo $q;

} 

?>
