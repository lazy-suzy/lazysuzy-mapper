<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ShipCodeMapper
{
    private $code_map = [
        '100' => 'SV',
        '400' => 'WG',
    ];
    private $inventory_maintained_products = [
        1 => 'cb2',
        2 => 'nw',
        3 => 'cab',
        4 => 'westelm',
    ];

    private $variation_sku_tables = array(
        'cb2_products_variations' => 'cb2',
        'crateandbarrel_products_variations' => 'cab',
        'westelm_products_skus' => 'westelm',
    );

    private function get_nw_ship_code($shipping_code)
    {
        return $shipping_code == 49 ? 'WGNW' : 'SCNW';
    }

    public function getShipCode($product, $brand)
    {
        if ($product->site_name === 'cb2') {
            return $this->getCb2ShipCode($product);
        }
        if ($product->site_name === 'cab') {
            return $this->getCabShipCode($product);
        }
        if ($product->site_name === 'nw') {
            return $this->getNwShipCode($product);
        }
        if ($product->site_name === 'westelm') {
            return $this->get_wm_ship_code($brand, $product->site_name, $product->description_shipping);
        }
    }

    private function getCb2ShipCode($product)
    {
        return $this->code_map[$product->shipping_code] . strtoupper('cb2');
    }

    private function getCabShipCode($product)
    {
        return $this->code_map[$product->shipping_code] . strtoupper('cab');
    }
    private function getNwShipCode($product)
    {
        return $this->get_nw_ship_code($product->shipping_code);
    }
    private function get_wm_ship_code($brand, $site_name, $product_desc)
    {

        if ($brand != $site_name) {
            return "F0";
        }

        // match the product desc
        $possible_matches = [
            "free shipping" => "F0",
            "front door delivery" => "SVwestelm",
            "UPS" => "SVwestelm",
        ];

        $possible_keys = array_keys($possible_matches);
        foreach ($possible_keys as $key) {
            if (strpos(strtolower($product_desc), strtolower($key)) !== false) {
                return $possible_matches[$key];
            }
        }

        return "WGwestelm";
    }
}
