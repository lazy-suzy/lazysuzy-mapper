<?php
ini_set('memory_limit', '-1'); // danger! add fail safe in the script

$conn = mysqli_connect("localhost", "homestead", "L@zyS@zy19!", "lazysuzy") or die("Could not connect");


function update_LSID($sku, $LS_ID)
{

    global $conn;
    $q = "UPDATE pier1_products SET LS_ID = '" . $LS_ID . "' WHERE product_sku = '" . $sku . "'";
    if (!mysqli_query($conn, $q)) {
        echo $q . "\n";
        die("ERROR!" . mysqli_error($conn));
    } else {
        echo "UPDATED: " . $sku . "\n";
    }
}

function update_product_color($sku, $product_new_color)
{

    global $conn;
    $q = "UPDATE pier1_products SET color = '" . $product_new_color . "' WHERE product_sku = '" . $sku . "'";
    if (!mysqli_query($conn, $q)) {
        echo $q;
        die("\n");
    }
}

function get_all($data_handle)
{
    $arr = [];
    while ($row = mysqli_fetch_assoc($data_handle)) {

        array_push($arr, $row);
    }

    return $arr;
}

function is_keyword_found($keyword, $name)
{

    if (strpos($name, $keyword) === false) return false;
    else return true;
}

function  get_LS_ID_str($LS_ID, $LS_ID_zero_key, $LS_ID_zero_category)
{

    $LS_ID_real = [];

    if (sizeof($LS_ID) > 0) {
        foreach ($LS_ID as $key => $value)
            array_push($LS_ID_real, $value);
    } else if (sizeof($LS_ID) == 0 && sizeof($LS_ID_zero_category) == 0) {

        foreach ($LS_ID_zero_key as $key => $value)
            array_push($LS_ID_real, $value);
    } else if (sizeof($LS_ID) == 0 && sizeof($LS_ID_zero_key) == 0) {

        foreach ($LS_ID_zero_category as $key => $value)
            array_push($LS_ID_real, $value);
    }

    return implode(",", $LS_ID_real);
}

//$empty_tbl = $engine->sql_query("TRUNCATE pier1_products");
//$data_flow = $engine->sql_query('INSERT INTO pier1_products SELECT * FROM pier1_products_raw');

function mapLSID()
{

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
                foreach ($dir as $val) {
                    if (strlen($val['product_department']) > 1 && strlen($val['product_category']) > 1) {
                        if ($p['department'] == $val['product_department'] && $p['product_category'] == $val['product_category']) {
                            if (!isset($LS_ID[$val['product_category']])) {
                                $LS_ID[$val['product_category']] = $val['LS_ID'];
                                break;
                            }
                        }
                    } else {
                        if ($val['product_department'] == $p['department']) {
                            if (!isset($LS_ID[$val['product_department']])) {
                                $LS_ID_zero_category[$val['product_department']] = $val['LS_ID'];
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
                    if (
                        strlen($val['product_department']) > 0
                        && strlen($val['product_category']) > 0
                        && strlen($val['product_key']) > 0
                    ) {

                        if (is_keyword_found($val['product_key'], $p['product_name'])) {
                            if ($val['product_department'] == $p['department']) {
                                if ($val['product_category'] == $p['product_category']) {

                                    $key = $val['product_category'];
                                    if (!isset($LS_ID[$key])) {
                                        $LS_ID[$key] = $val['LS_ID'];
                                    } else {
                                        $newKey = $key . $val['product_key'];
                                        $LS_ID[$newKey] =  $val['LS_ID'];
                                    }
                                }
                            }
                        }
                    } else if (strlen($val['product_key'] == 0)) {
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
                                } else {
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

function mapColors()
{
    global $conn;

    $r_colors = mysqli_query($conn, "SELECT * FROM color_mapping WHERE Pier1 = 'Y'");
    $colors_db = get_all($r_colors);

    $r_prods = mysqli_query($conn, "SELECT product_name, product_sku, color, product_feature, model_name FROM pier1_products");
    $prods = get_all($r_prods);

    $color_map = [];

    foreach ($colors_db as $row) {
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
    $UMMAPPED_PRODUCTS = sizeof($prods);
    $MAPPED_PRODUCTS = 0;
    foreach ($prods as $key => $row) {

        $product_new_color = [];
        // first find color in model name then if not is model name check product name
        // finally check for product features
        $model_name_bits = explode(" ", strtolower($row['model_name']));
        foreach ($model_name_bits as $bit) {
            if (isset($color_map[$bit])) {
                if (!in_array($color_map[$bit]['name'], $product_new_color)) {
                    //echo "FROM MODEL MATCH " . $color_map[$bit]['name'] . "\n";
                    $product_new_color[] = $color_map[$bit]['name'];
                }
            }
        }

        if (sizeof($product_new_color) == 0) {
            $product_name_bits = explode(" ", strtolower($row['product_name']));
            foreach ($product_name_bits as $bit) {
                if (isset($color_map[$bit])) {
                    if (!in_array($color_map[$bit]['name'], $product_new_color)) {
                        $product_new_color[] = $color_map[$bit]['name'];
                    }
                }
            }
        }

        $product_f = str_replace(["\"", "\n", ':', ',', '/'], " ", strtolower($row['product_feature']));
        $product_f_bits = explode(" ", $product_f);

        for ($i = 0; $i < 7; $i++) {
            if (
                isset($product_f_bits[$i])
                && isset($color_map[$product_f_bits[$i]]) !== false
            ) {

                $c = $color_map[$product_f_bits[$i]]['name'];
                if (!in_array($c, $product_new_color)) {
                    //echo "FROM FEATURES MATCH " . $c . "\n";
                    $product_new_color[] = $c;
                }
            }
        }


        if (sizeof($product_new_color) !== 0) $MAPPED_PRODUCTS++;

        echo implode(",", $product_new_color) . "\n";
        update_product_color($row['product_sku'], implode(",", $product_new_color));
    }

    echo $MAPPED_PRODUCTS . "/" . $UMMAPPED_PRODUCTS . "COLOR MAPPED\n";
}


function resize_image($file, $w, $h, $crop = FALSE)
{
    list($width, $height) = getimagesize($file);
    $r = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width - ($width * abs($r - $w / $h)));
        } else {
            $height = ceil($height - ($height * abs($r - $w / $h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        if ($w / $h > $r) {
            $newwidth = $h * $r;
            $newheight = $h;
        } else {
            $newheight = $w / $r;
            $newwidth = $w;
        }
    }
    $src = imagecreatefromjpeg($file);
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}

// enlarge images from 400 X 400 to 640 X 640
function enlarge_product_images()
{
	echo "==============IMAGE RESIZE=================\n\n";

    global $conn;
    $query = "SELECT product_images FROM pier1_products WHERE 1";
    $rx = mysqli_query($conn, $query);
    $rows = get_all($rx);

    $prefix_path = "/var/www/html";

    foreach ($rows as $row) {

        $images = explode(",", $row['product_images']);
        foreach ($images as $i_path) {

            $img_path = $prefix_path . $i_path;
            $new_img = resize_image($img_path, 640, 640);
            imagejpeg($new_img, $img_path);

            echo "PROCESSED: " . $img_path . "\n";

        }
    }

    echo "================DONE==================\n\n";

}

enlarge_product_images();

mapLSID();
mapColors();
