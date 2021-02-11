<?php

function get_sale_price($str)
{
    $str = "<span class='sale'><span class='salePrice'>Sale $935.00</span><span class='regPrice'>reg. $1,690.00</span></span>";
    $str = str_replace("class", "id", $str);
    $dom = new DOMDocument();
    @$dom->loadHTML($str);
    $sale_price = $dom->getElementById('salePrice')->textContent;
    $sale_price = trim($sale_price);
    $sale_price = explode("$", $sale_price);

    if(gettype($sale_price) == gettype([]))
        return $sale_price[0];

}
