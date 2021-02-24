<?php
defined('BASEPATH') or exit('No direct script access allowed');
ini_set('memory_limit', '-1');
ini_set('display_errors', 1);


class FixWestelm extends CI_Controller
{
  public function index()
  {
    $master_table = 'master_data';
    $master_products = $this->db->query("SELECT product_sku,product_feature FROM " . $master_table . " where site_name = 'westelm'")->result_array();
    foreach ($master_products as $master_product) {
      $feature = trim($master_product['product_feature']);
      if ($feature[0] === '*') {
        $product = $this->db->select("*")
          ->from('westelm_products_parents')
          ->where('price IS NOT NULL')
          ->where('product_id LIKE "' . $master_product['product_sku'] . '"')
          ->get()->result();
        $fields = $this->get_westelm_master_data($product[0], $master_product);
        if($fields){
          echo "Setting feature for sku ". $master_product['product_sku']."\n";
          $this->db->set($fields);
          $this->db->where('product_sku', $master_product['product_sku']);
          $this->db->update($master_table);
        }
      }
    }
  }

  public function fixEmptyFeature(){
    $master_table = 'master_data';
    $master_products = $this->db->query("SELECT product_sku,product_feature FROM " . $master_table . " where site_name = 'westelm'")->result_array();
    foreach ($master_products as $master_product) {
      $feature = trim($master_product['product_feature']);
      if (!$feature) {
        $product = $this->db->select("*")
          ->from('westelm_products_parents')
          ->where('price IS NOT NULL')
          ->where('product_id LIKE "' . $master_product['product_sku'] . '"')
          ->get()->result();
        $details = $this->extract_westelm_details($product[0]->description_overview);
        if ($details && $details['feature']) {
          echo "Setting feature for sku " . $master_product['product_sku'] . "\n";
          $fields['product_description']= $details['overview'];
          $fields['product_feature'] = $details['feature'];
          $this->db->set($fields);
          $this->db->where('product_sku', $master_product['product_sku']);
          $this->db->update($master_table);
        }
      }
    }
  }

  


  public function get_westelm_master_data($product, $master_product)
  {
    $feature = trim($master_product['product_feature']);

    if ($feature[0] === '*' && $feature[1] === '*') {
      $description = $this->extract_westelm_details($product->description_overview);
      $arr['product_description'] = $description['overview'];
      $arr['product_feature'] = str_replace('*', '', $description['feature']);

      $features = $this->extract_westelm_features($product->description_details);
      $arr['product_assembly'] = $features['assembly_instructions'];
      $arr['product_care'] = $features['care'];
      if ($features['features']) {
        $arr['product_feature'] = str_replace('*', '', $features['features']);
      }
    } else if ($feature[0] === '*') {
      $feature = str_replace('*', '', $feature);
      $arr['product_feature'] = $feature;
    }
    return $arr;
  }

  public function checkFeatureCondition($feature)
  {
    if ($feature[0] === '*' && $feature[1] !== '*') {
      return false;
    }
    return true;
  }

  public function extract_westelm_details($details)
  {
    $newDescription = [];
    $overviewArray = [];
    $overview = '';
    $featuresArray = [];
    $features = '';
    $i = 0;
    $details = explode("\n", $details);
    while ($i < count($details) && $this->checkFeatureCondition(trim($details[$i]))) {
      $overviewArray[] = $details[$i];
      $i++;
    }
    $overview = trim(implode("\n", $overviewArray));
    while ($i < count($details) && isset($details[$i])) {
      $featuresArray[] = $details[$i];
      $i++;
    }
    $features = trim(implode("\n", $featuresArray));
    $newDescription['overview'] = trim(str_replace(['###### KEY DETAILS','**KEY DETAILS**'], '', $overview));
    $newDescription['feature'] = str_replace('*', '', $features);
    return $newDescription;
  }


  public function extract_westelm_features($features)
  {
    $newFeatures = [];
    $header = '';
    $newLine = '';
    $i = 0;
    $features = str_replace('\n', '', $features);
    while ($i < strlen($features)) {
      if ($features[$i] == '*' && $features[$i + 1] == '*') {
        $i = $i + 2;
        while (isset($features[$i]) && ($features[$i] !== '*' || $features[$i + 1] !== '*')) {
          $header .= $features[$i++];
        }
        if (trim($header) === 'ASSEMBLY INSTRUCTIONS') {
          $i = $i + 2;
          while (isset($features[$i]) && ($features[$i] !== '*' || $features[$i + 1] !== '*')) {
            $newLine .= $features[$i++];
          }
          $newFeatures['assembly_instructions'] = trim($newLine);
          $newLine = '';
          $header = '';
        }
        if (trim($header) === 'CARE') {
          $i = $i + 2;
          while (isset($features[$i]) && ($features[$i] !== '*' || $features[$i + 1] !== '*')) {
            $newLine .= $features[$i++];
          }
          $newFeatures['care'] = trim($newLine);
          $newLine = '';
          $header = '';
        }
        if ($header !== '') {
          $i++;
          $header = '';
        }
      } else {
        $i++;
      }
    }
    if (count($newFeatures) === 0) {
      $newFeatures['features'] = $features;
    }
    return $newFeatures;
  }
}