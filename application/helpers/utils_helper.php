<?php

function get_sale_price($str)
{
    $str = str_replace("class", "id", $str);
    $dom = new DOMDocument();
    @$dom->loadHTML($str);
    $sale_price = $dom->getElementById('salePrice')->textContent;
    $sale_price = trim($sale_price);
    $sale_price = str_replace(["$", ","], "", $sale_price);

    return $sale_price;
}
