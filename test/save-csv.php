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
	$image_path = "/nw/xbg/" . $img  . ".png";
	$full_image_path = "/home/ec2-user/lazysuzy-code/public" . $image_path;
	$images_not_on_disk = [];

	$q = "UPDATE nw_products_API SET image_xbg = '$image_path' WHERE product_sku = '$sku'";
	if (!file_exists($full_image_path))
		$images_not_on_disk[] = $full_image_path;

	if (!mysqli_query($conn, $q))
		echo $q;
}

echo sizeof($images_not_on_disk) . " Images not found on disk \n to see the list check images-not-on-disk.json\n";
file_put_contents('images-not-on-disk.json', json_encode($images_not_on_disk));


?>
