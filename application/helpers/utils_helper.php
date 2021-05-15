<?php

function get_sale_price($str)
{
    $str = str_replace("class", "id", $str);
    $dom = new DOMDocument();
    @$dom->loadHTML($str);
    $sale_price = $dom->getElementById('salePrice')->textContent;
    $sale_price = trim($sale_price);
    $sale_price = explode("$", $sale_price);

    if(gettype($sale_price) == gettype([]))
        return $sale_price[0];

}

function is_instance_running($brand) {

    $file = 'stat/cab_run_stat.txt';
    if($brand == 'cb2') {
        $file = 'stat/cb2_run_stat.txt';
    }

    $fexec = fopen($file, 'r');
    $line = fgets($fexec);
    echo $brand , " Status: " . $line . '\n';

    if($line == "RUNNING")
        return true;
    else {
        file_put_contents($file, 'RUNNING');
        return false;
    }

}
