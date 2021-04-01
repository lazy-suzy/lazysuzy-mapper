<?php
ini_set('max_execution_time', 300000); //300 seconds = 5 minutes

$conn = mysqli_connect("localhost", "homestead", "L@zyS@zy19!", "lazysuzy");
if (!$conn) die('Could not connect to database' . mysqli_error($conn));
/*
	mysqli_query($conn, "TRUNCATE TABLE nw_products_API_outdoor");
	mysqli_query($conn, "TRUNCATE TABLE nw_variations_API");
*/

// fixing categories;
/* $dataQuery = "SELECT product_sku, product_category, department FROM nw_products_API WHERE 1";
$dataRef = mysqli_query($conn, $dataQuery);

while($dataRow = mysqli_fetch_assoc($dataRef)) {

    $dept = explode(",", $dataRow['department']);
    $cat = explode(",", $dataRow['product_category']);

    $dept = array_unique($dept);
    $cat = array_unique($cat);

    $dept_str = implode(",", $dept);
    $cat_str = implode(",", $cat);
    $sku = $dataRow['product_sku'];

    $updateQuery = "UPDATE nw_products_API SET product_category = '{$cat_str}' , department = '{$dept_str}' WHERE product_sku = '{$sku}'"; 

    echo $updateQuery , "\n";
    mysqli_query($conn, $updateQuery);
}


die("DONE"); */


function multiple_download($urls, $save_path = '/tmp')
{
    $multi_handle  = curl_multi_init();
    $file_pointers = array();
    $curl_handles  = array();
    $file_paths    = array();

    // Add curl multi handles, one per file we don't already have
    if (sizeof($urls) > 0) {
        foreach ($urls as $key => $url) {
            if (strlen($url) > 0) {

                $url = str_replace("wid=480", "wid=2000", $url);

                $basename = basename($url);
                $basename = str_replace(".", "", $basename);

                $file   = $save_path . '/' . $basename . ".jpg";
                $s_file = "/nw/new-09062020/" . $basename . ".jpg";
                array_push($file_paths, $s_file);
                if (!is_file($file)) {
                    $curl_handles[$key]  = curl_init($url);
                    $file_pointers[$key] = fopen($file, "w");
                    curl_setopt($curl_handles[$key], CURLOPT_FILE, $file_pointers[$key]);
                    curl_setopt($curl_handles[$key], CURLOPT_HEADER, 0);
                    curl_setopt($curl_handles[$key], CURLOPT_CONNECTTIMEOUT, 60);
                    curl_multi_add_handle($multi_handle, $curl_handles[$key]);
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

function get_data($url)
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,   // return web page
        CURLOPT_HEADER         => false,  // don't return headers
        CURLOPT_FOLLOWLOCATION => true,   // follow redirects
        CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
        CURLOPT_ENCODING       => "",     // handle compressed
        CURLOPT_USERAGENT      => "lazysuzy", // name of client
        CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
        CURLOPT_TIMEOUT        => 120,    // time-out on response
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, $options);

    $content  = curl_exec($ch);

    curl_close($ch);

    return $content;
}


function update_category($product, $category)
{
    global $conn;
    //dining-chairs,dining-benches,entryway,dining-benches,entryway,dining-benches,entryway,dining-benches,entryway,dining-benches,entryway,dining-benches,entryway,dining-benches,entryway,dining-benches,entryway,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches,dining-benches

    $get_categories = "SELECT product_category FROM nw_products_API WHERE product_sku = '" . $product['SKU'] . "'";
    $dataRef = mysqli_query($conn, $get_categories);
    $data = mysqli_fetch_assoc($dataRef);

    $cat = explode(",", $data['product_category']);

    if (!in_array($category, $cat)) {
        $category = "," . $category;

        $str = "UPDATE nw_products_API SET product_category = concat(product_category, '$category') WHERE product_sku = '" .  $product['SKU'] . "'";
        if (!mysqli_query($conn, $str) || mysqli_affected_rows($conn) <= 0) {
            echo $str;
            die("\n Could not Update category " . mysqli_error($conn));
        }
    } else {
        echo "[CATEGORY UPDATED]\n";
    }
}

function update_product($product)
{
    global $conn;
    $spec = json_encode($product['specifications']);

    $query = "UPDATE nw_products SET product_name = '" . $product['product_name'] . "', product_images = '" . $product['images'] . "', product_feature = '" . $product['specifications'] . "', product_status = 'active', " . "product_description = '" . $product['description'] . "', reviews = '" . $product['reviews'] .  "', rating = '" . $product['rating'] . "' WHERE product_sku ='" .  $product['SKU'] . "'" . ", serial = '" . $product['serial'] . "'";

    echo $query . " \n";
    if (!mysqli_query($conn, $query)) {
        die('Could not update' . $query . " => " . mysqli_error($conn));
    } else {
        if (isset($product['variations'])) {
            $var_p = $product['variations'];

            foreach ($var_p as $var) {

                if (is_array($var['images'])) $img_v = multiple_download($var['images'], '/var/www/html/nw/images');
                else $img_v = "";

                // first find if product is in variations table or not
                if (!is_variation_present($var['product_sku'], $var['variation_sku'])) {
                    $str = "INSERT INTO nw_variations (product_id, sku, price, attribute_1, attribute_2, attribute_3, attribute_4, attribute_5, attribute_6, `image`, swatch_image, swatch_image_path, status) VALUES ('{$var['product_sku']}', '{$var['variation_sku']}', '{$var['min_price']}', '{$var['attribute_1']}', '{$var['attribute_2']}', '{$var['attribute_3']}', '{$var['attribute_4']}', '{$var['attribute_5']}', '{$var['attribute_6']}', '$img_v', '{$var['swatch']}', '{$var['swatch']}', 'active')";

                    if (!mysqli_query($conn, $str)) {
                        echo $str;
                        die('variation no saved ' . mysqli_error($conn));
                    }
                }
            }
        }
    }
}

function is_variation_present($product_id, $variation_sku)
{
    global $conn;
    $query = "SELECT product_id, sku from nw_variations WHERE product_id = '$product_id' AND sku = '$variation_sku'";
    $result = mysqli_query($conn, $query);
    $rows = mysqli_fetch_array($result, MYSQLI_NUM);
    echo ":" . gettype($rows);
    mysqli_free_result($result);
    return  $rows != NULL && sizeof($rows) > 0;
}

function save_product($product)
{
    global $conn;
    $img = ($product['images']);
    $spec = ($product['specifications']);
    $product['min_price'] = str_replace("$", "", $product['min_price']);
    $product['max_price'] = str_replace("$", "", $product['max_price']);
    $product['price'] = str_replace("$", "", $product['price']);

    $date = date("Y-m-d H:i:s");

    $sql = "INSERT INTO nw_products_API (product_sku, product_url, product_images, price, min_price, max_price, was_price, product_name, product_feature, product_description, site_name, reviews, rating, product_category, department, serial) VALUES ('{$product['SKU']}','', '{$img}', '{$product['price']}', '{$product['min_price']}', '{$product['max_price']}', '{$product['old_price']}' ,'{$product['product_name']}', '{$spec}', '{$product['description']}', 'nw', '{$product['reviews']}', '{$product['rating']}', '{$product['category']}', '{$product['dept']}', '{$product['serial']}') ON DUPLICATE KEY UPDATE price = '{$product['price']}', min_price = '{$product['min_price']}', max_price = '{$product['max_price']}', was_price = '{$product['old_price']}', updated_date = '{$date}', serial = '{$product['serial']}'";

    if (isset($product['variations'])) {
        $var_p = $product['variations'];

        foreach ($var_p as $var) {

            if (is_array($var['images'])) {
                $img_v = multiple_download($var['images'], '/var/www/html/nw/new-09062020');
            } else {
                $img_v =  $var['images'];
            }

            $var['was_price'] = str_replace("$", "", $var['was_price']);

            // first find if product and variation is in variations table or not
            if (!is_variation_present($var['product_sku'], $var['variation_sku'])) {

                if (strlen($var['swatch']) > 0) {
                    $var['swatch'] = 'https://www.worldmarket.com' . $var['swatch'];
                    $var['swatch'] = multiple_download([$var['swatch']], '/var/www/html/nw/new-09062020');
                }
                
                $str = "INSERT INTO nw_variations (product_id, sku, price, was_price, attribute_1, attribute_2, attribute_3, attribute_4, attribute_5, attribute_6, `image`, image_path ,swatch_image_path, status ) VALUES (
                    '{$var['product_sku']}', '{$var['variation_sku']}', '{$var['min_price']}', '{$var['was_price']}' , '{$var['attribute_1']}', '{$var['attribute_2']}', '{$var['attribute_3']}', '{$var['attribute_4']}', '{$var['attribute_5']}', '{$var['attribute_6']}', '$img_v', '$img_v' , '{$var['swatch']}', '{$var['product_status']}') ON DUPLICATE KEY UPDATE price = '{$var['min_price']}'";
                if (!mysqli_query($conn, $str)) {
                    echo $str;
                    die('variation not saved ' . mysqli_error($conn));
                }
            }
            else {
                // update price and was price
                $price = $var['min_price'];
                $was_price = $var['was_price'];
                $product_id = $var['product_sku'];
                $var_sku = $var['variation_sku'];
                $str = "UPDATE nw_variations SET price = '$price', was_price = '$was_price' WHERE product_id = '$product_id' AND sku = '$var_sku'";
                if(!mysqli_query($conn, $str)) {
                    echo $str;
                    die('variation not updated ' . mysqli_error($conn));
                }
            }
        }

        if (!mysqli_query($conn, $sql)) {
            echo $sql;
            die('not saved' . mysqli_error($conn));
        }
    }
}

$base = "http://lazysuzy.com:8081/nw-scraper/?category=";
$cat_arr = [
    "category/furniture/kids-furniture.do",
    "category/rugs/kids-rugs.do",
    "category/home-decor-pillows/wall-art-decor/kids-wall-art.do",
    "category/lighting/kids-lighting.do",
    "category/home-decor-pillows/pillows/kids-pillows.do",
    "category/home-decor-pillows/kids-decor.do",
    "category/furniture/dining-room/dining-tables.do",
    "category/furniture/dining-room/dining-chairs.do",
    "category/furniture/dining-room/dining-benches.do",
    "category/furniture/dining-room/stools.do",
    "category/furniture/dining-room/wine-storage.do",
    "category/furniture/dining-room/kitchen-storage.do",
    "category/furniture/living-room/sofas.do",
    "category/furniture/living-room/sectionals.do",
    "category/furniture/living-room/chairs.do",
    "category/furniture/living-room/coffee-tables.do",
    "category/home-decor-pillows/pillows/floor-pillows-poufs.do",
    "category/furniture/living-room/benches.do",
    "category/furniture/living-room/media-furniture.do",
    "category/furniture/living-room/cabinets-shelving.do",
    "category/furniture/living-room/accent-furniture.do",
    "category/furniture/home-office/office-desks.do",
    //"category/furniture/home-office/office-chairs.do",
    "category/furniture/home-office/bookcases.do",
    "category/furniture/home-office/storage-carts.do",
    "category/furniture/bedroom/beds.do",
    "category/furniture/custom-furniture/bedroom-headboards.do",
    "category/furniture/bedroom/chaise-daybeds.do",
    "category/furniture/bedroom/dressers.do",
    "category/furniture/bedroom/nightstands-tables.do",
    "category/furniture/bedroom/jewelry-armoires.do",
    "category/furniture/custom-furniture/upholstered-bed-frames.do",
    "category/furniture/custom-furniture/seating-benches-ottomans.do",
    "category/furniture/custom-furniture/custom-living-room-collections.do",
    "category/furniture/custom-furniture/custom-dining-room-chairs.do",
    "category/furniture/small-spaces/dining-kitchen.do",
    "category/furniture/small-spaces/sofas-daybeds.do",
    "category/furniture/small-spaces/seating-benches.do",
    "category/furniture/small-spaces/coffee-side-tables.do",
    "category/furniture/small-spaces/storage.do",
    "category/furniture/small-spaces/office.do",
    "category/furniture/small-spaces/bedroom.do",
    "category/furniture/artisan-furniture.do",
    "category/furniture/entryway.do",
    "category/furniture/bathroom.do",
    "category/outdoor/furniture/dining.do",
    "category/outdoor/furniture/seating.do",
    "category/outdoor/furniture/adirondack-chairs.do",
    "category/outdoor/furniture/accent.do",
    "category/outdoor/furniture/umbrellas.do",
    "category/outdoor/furniture/fire-pits.do",
    "category/outdoor/furniture/hammocks.do",
    "category/outdoor/furniture/covers.do",
    "category/outdoor/cushions-pillows.do",
    "category/outdoor/furniture/balcony.do",
    "category/home-decor-pillows/wall-art-decor/mirrors.do",
    "category/lighting/floor-lamps.do",
    "category/home-decor-pillows/pillows/papasan-chair-cushions-frames.do"
];

$attrs = [
    "Color" => "attribute_1",
    "Size" => "attribute_2"
];

$harvested_skus = [];
$update_product_ctr = $update_category_ctr = 0;

// DELETE OLD TABLE

foreach ($cat_arr as $cat) {
    $page_num = 1;

    $product_serial = 0;

    $url = $base . $cat . "&page=" . $page_num++;
    $cat_prods = $cat_prods_part = json_decode(get_data($url));
    echo "[API URL] => " . $url . "\n";
    echo "[DATA SIZE] => " . sizeof($cat_prods) . "\n";

    echo "\n\n\n" . gettype($cat_prods) . "\n\n\n";
    while (sizeof($cat_prods_part) != 0 && $cat_prods_part) {
        $url = $url = $base . $cat . "&page=" . $page_num++;
        $cat_prods_part = json_decode(get_data($url));
        $cat_prods = array_merge($cat_prods, $cat_prods_part);
        echo "[API URL] => " . $url . "\n";
        echo "[DATA SIZE] => " . sizeof($cat_prods_part) . "\n";
    }

    echo "[TOTAL PAGES] . " .  $page_num . "\n";
    echo "[TOTAL DATA SIZE ] . " . sizeof($cat_prods) . "\n";


    $updated_categories = [];
    if (sizeof($cat_prods) != 0) {
        $bits = explode("/", $cat);

        if (isset($bits[2])) $department = ($bits[2]);
        else $department = ($bits[1]);

        if (isset($bits[3])) $category = (explode(".", $bits[3])[0]);
        else $category = (explode(".", $bits[2])[0]);


        foreach ($cat_prods as $cat_prod) {
            $product_details = [];
            $product_variations = [];
            $url = $cat_prod->ProductAPILink;
            //$url = "http://35.174.251.34/scripts/worldmarket.php?product=/product/velvet-jacie-upholstered-platform-bed.do",?sortby=ourPicks&from=fn";
            $prod = json_decode(get_data($url));

            if (isset($prod)) {

                $product_details['category'] = $category;
                $product_details['dept'] = $department;
                $product_details['SKU'] = isset($prod->SKU) ? is_array($prod->SKU) ? (implode(",", $prod->SKU)) : ($prod->SKU) : "";

                if (!is_array($prod->SKU) && strlen($prod->SKU) == 0) {
                    if (isset($prod->Variation->Products[0])) {
                        $product_details['SKU'] = $prod->Variation->Products[0]->SKU;
                    }
                }

                $product_details['product_name'] = isset($prod->Name) ? addslashes($prod->Name) : "";
                $product_details['images'] = is_array($prod->Pictures) ? multiple_download($prod->Pictures, '/var/www/html/nw/new-09062020') : "";
                $product_details['specifications'] = isset($prod->Specification) ? is_array($prod->Specification) ? addslashes(implode("|", $prod->Specification)) : "" : "";
                $product_details['description'] = isset($prod->Description) ? addslashes($prod->Description) : "null";
                $product_details['reviews'] = strlen($prod->Reviews) > 0 ? ($prod->Reviews) : 0;
                $product_details['rating'] = strlen($prod->Rating) > 0 ? ($prod->Rating) : 0;
                $product_details['shipping'] = isset($prod->Shiping) ? addslashes(($prod->Shipping)) : "";
                $product_details['old_price'] = isset($prod->OldPrice) ? str_replace("$", "", $prod->OldPrice) : "";
                $product_details['price'] = isset($prod->Price) ? $prod->Price : "";
                $product_details['serial'] = $product_serial++;

                if (is_array($prod->Price)) $price = implode("-", $prod->Price);
                else $price = $prod->Price;

                $bits = explode("-", $price);

                /*if ($prod->Name == "Kian Upholstered Dining Chair") {
                var_dump($prod);
                echo $url . "\n";
                echo $price . "\n";
                var_dump($bits);
                die();
              }*/

                if (isset($bits[0]) && isset($bits[1])) {
                    $product_details['min_price'] = (trim(str_replace("$", "", $bits[0])));
                    $product_details['max_price'] = (trim(str_replace("$", "", $bits[1])));
                } else {
                    $product_details['min_price'] = $product_details['max_price'] = ($price);
                }

                if (isset($prod->Variation)) {
                    foreach ($prod->Variation->Products as $variation) {
                        $product_variation = [];
                        $product_variation['product_sku'] = strlen($product_details['SKU']) > 0 ? $product_details['SKU'] : $prod->Variation->Products[0]->SKU;
                        $product_variation['variation_sku'] = $variation->SKU;
                        $price = $variation->Price;
                        $bits = explode("-", $price);

                        $product_variation['product_status'] = $variation->InStock ? 'active' : '';

                        if (isset($bits[0]) && isset($bits[1])) {
                            $product_variation['min_price'] = trim(str_replace("$", "", $bits[0]));
                            $product_variation['max_price'] = trim(str_replace("$", "", $bits[1]));
                        } else {
                            $product_variation['min_price'] = $product_variation['max_price'] = trim(str_replace("$", "", $price));
                        }

                        $product_variation['was_price'] = $variation->OldPrice;

                        if (isset($variation->Attributes)) {
                            foreach ($variation->Attributes as $key => $val) {
                                $val = addslashes($val);
                                if ($key == "Color") {
                                    $product_variation['swatch'] = isset($prod->Variation->Attributes->$key->$val->Swatch) ? $prod->Variation->Attributes->$key->$val->Swatch : "";
                                }
                                if (isset($attrs[$key])) {
                                    $product_variation[$attrs[$key]] = $key . ":" . $val;
                                } else {
                                    $attrs[$key] = "attribute_x";
                                    $product_variation[$attrs[$key]] = $key . ":" . $val;
                                }
                                $var_img = [];
                                if (isset($prod->Variation->Attributes->$key->$val->Images)) {
                                    foreach ($prod->Variation->Attributes->$key->$val->Images as $img) {
                                        array_push($var_img, $img);
                                    }
                                    $product_variation['images'] = $var_img;
                                }

                                $i = 1;
                                for ($i = 1; $i <= 6; $i++) {
                                    if (!isset($product_variation['attribute_' . $i])) {
                                        $product_variation['attribute_' . $i] = "";
                                    }
                                }
                            }
                        }

                        array_push($product_variations, $product_variation);
                    }
                }

                $product_details['variations'] = $product_variations;
            }

            if (strlen($product_details['SKU']) > 1 && isset($prod->SKU)) {
                //save_product($product_details);
                if (isset($harvested_skus[$product_details['SKU']])) {
                    echo "[UPDATE CATEGORY] " . $product_details['SKU'] . "\n";

                    update_category($product_details, $category);

                    $update_category_ctr++;
                } else {
                    save_product($product_details);
                    echo "[INSERT PRODUCT] " . $product_details['SKU'] . "\n";
                    $harvested_skus[$product_details['SKU']] = true;
                    $update_product_ctr++;
                }
            } else {

                $SKU = 'GEN' . rand(0, 99) . rand(0, 9990);
                echo "[SKU GENERATION PRODUCT] $SKU\n";
                $product_details['SKU'] = $SKU;
                save_product($product_details);
            }
        }

        echo "CATGEORY UPDATE => $update_category_ctr, PRODUCT_UPDATE => $update_product_ctr\n";
    }
}
