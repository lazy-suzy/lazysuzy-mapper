<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class CB2
{
	private $handle, $html;

	public function __construct($params){

		$this -> filterStorageLocation = "./application/libraries/filters/cb2/";
		$this -> handle = curl_init();
	    curl_setopt($this -> handle, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($this -> handle, CURLOPT_RETURNTRANSFER, true);	
		curl_setopt($this -> handle, CURLOPT_SSL_VERIFYPEER, false);

		if(isset($params['debug']))
			$this -> debug = $params['debug'];

		if(isset($params['proxy'])){
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
			"Cookie: Internationalization=US|USD; OriginCountry=US",
		);

		if (!file_exists($this -> filterStorageLocation))
			if(!mkdir($this -> filterStorageLocation, 0777, true))
				die('unable to create directory, check permissions');
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
		curl_setopt($this -> handle, CURLOPT_URL, "https://www.cb2.com/" . $category_url);
		$this -> html = curl_exec($this -> handle);
		// $this -> html = file_get_contents('cb2-category.html');
		
		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));


		if(isset($filters[1]) && isset($filters[2]))
			foreach ($filters[1] as $index => $filterName)
				$results['filters'][$filterName][] = $filters[2][$index];

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));
		
		if(isset($products['Minisets'])){
			foreach ($products['Minisets'] as $product) {
				$variation = array();
				if(isset($product['ColorBar']['Choices'])){
					foreach ($product['ColorBar']['Choices'] as $product_variation) {
						$variation[] = array(
							'SKU' => $product_variation['Sku'],
							'Name' => $product_variation['Name'],
							'Description' => $product_variation['Description'],
							'ColorImage' => $product_variation['ImageUrl'],
						);
					}
				}

				$results['products'][] = array(
					'BaseSKU' => $product['Sku'],
					'BaseName' => $product['Name'], 
					'BaseDescription' => $product['Description'], 
					'BaseRegularPrice' => $product['RegularPrice'], 
					'BaseCurrentPrice' => $product['CurrentPrice'],
					"BaseURL" => $product['NavigateUrl'],
					"BaseImage" => $product['ImageUrl'],
					"BaseImageAlternative" => $product['RolloverImageUrl'],
					"BaseManufacturer" => $product['Sku'], 
					'Variation' => empty($variation) ? array() : $variation
				);
			}
		}

		return $results;
	}

	public function get_product($product_url){

		curl_setopt($this -> handle, CURLOPT_HTTPHEADER , $this -> headers);
		curl_setopt($this -> handle, CURLOPT_URL, "https://www.cb2.com/" . $product_url);
		$this -> html = curl_exec($this -> handle);

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

			$x = $product_info['availability']['onlineAvailableMessage'];
			
			if(isset($product_info['availability'])){
				$result['Availability']['ZipCode'] = $product_info['availability']['zipCode'];

				$result['Availability']['IsOnlineMessageVisible'] = $product_info['availability']['isOnlineMessageVisible'];
				$result['Availability']['OnlineMessage'] = htmlspecialchars_decode($product_info['availability']['onlineAvailableMessage'], ENT_QUOTES);

				$result['Availability']['IsBackOrdered'] = $product_info['availability']['isBackOrdered'];
				$result['Availability']['BackOrderedMessage'] = htmlspecialchars_decode($product_info['availability']['backOrderedMessage'], ENT_QUOTES);
				$result['Availability']['BackOrderedMessageDate'] = $product_info['availability']['backOrderedMessageDate'];
			}

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
						'Custom' => isset($v['SkuProperty']) ? $v['SkuProperty'] : '',
						'OptionCode' => isset($v['optionCode']) ? $v['optionCode'] : '',
						'ChoiceCode' => isset($v['choiceCode']) ? $v['choiceCode'] : '',
						'ChoiceName' => isset($v['choiceName']) ? $v['choiceName'] : '',
						'ColorImage' => isset($v['imagePath']) ? $v['imagePath'] : '',
						'ColorImageZoom' => isset($v['zoomImagePath']) ? $v['zoomImagePath'] : '',
						'CurrentPrice' => $product_info['specialOrderProps']['model']['colorBar']['colorBarCount'] == 0 ? $result['CurrentPrice'] : 0,
						'RegularPrice' => $product_info['specialOrderProps']['model']['colorBar']['colorBarCount'] == 0 ? $result['RegularPrice'] : 0,
						'Image' => "https://cb2.scene7.com/is/image/CB2/item_{$product_info['specialOrderProps']['model']['collectionCode']}_{$product_info['specialOrderProps']['model']['itemTypeCode']}_{$v['choiceCode']}_{$imageParam}"
						// 'Image' => "https://cb2.scene7.com/is/image/CB2/item_{$product_info['specialOrderProps']['model']['collectionCode']}_{$product_info['specialOrderProps']['model']['itemTypeCode']}_{$v['choiceCode']}_0",
					);
				}
			}
			//handling different type of product ex atrium-tufted-black-patent-leather-bench/s677608
			else if(isset($digital_data['BrowseDto']['Grouper']['AttributeGroups'][0]['Attributes'])){
				foreach ($digital_data['BrowseDto']['Grouper']['AttributeGroups'][0]['Attributes'] as $v) {
					// var_dump($v);die();
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

	private function get_filter_by_id($id){
		// check if file exist
		// return global list of not
	}

	private function save_global_list(){
		
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

		$filterFileName = $this -> filterStorageLocation . "{$id}.json";
		$filterType = 'default';

		if(file_exists($filterFileName)){
			try{
				$filters = json_decode(file_get_contents($filterFileName), true);
				$filterType = 'custom';
			}
			catch (Exception $e) {
				// just continue;
			}
		}

		$filtersToApply = array();
		foreach ($filters as $filterName => $filterValue){
			if(isset($params[$filterName])){
				$results['appliedFilters'][str_replace('_', ' ', $filterName)] = $params[$filterName];
				$filtersToApply[] = $filterValue . ':' . str_replace('_', ' ', $filterName) . ':' . str_replace(' ', '', strtolower($params[$filterName])) . ':' . $params[$filterName] . ":false:0:0";
			}
		}
		
		curl_setopt($this -> handle, CURLOPT_URL, 'https://www.cb2.com/furniture/sofas');
		curl_setopt($this -> handle, CURLOPT_POST, 1);
		curl_setopt($this -> handle, CURLOPT_ENCODING, 'gzip, deflate');

		$headers = array();
		$headers[] = 'Sec-Fetch-Mode: cors';
		$headers[] = 'Origin: https://www.cb2.com';
		$headers[] = 'Accept-Encoding: gzip, deflate, br';
		$headers[] = 'Accept-Language: en-US,en;q=0.9,en-GB;q=0.8,es;q=0.7';
		$headers[] = 'X-Requested-With: XMLHttpRequest';
		$headers[] = 'Cookie: Internationalization=US|USD';
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Accept: */*';
		$headers[] = 'Cache-Control: max-age=0';
		$headers[] = 'Authority: www.cb2.com';
		$headers[] = 'Referer: https://www.cb2.com/furniture/sofas';
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
			$availableFilterList = array();
			
			foreach ($this -> html['topLevelFacets']['values'] as $availableFilter){
				$availableFilterList[str_replace(' ', '_', $availableFilter['name'])] = $availableFilter['id'];
				$results['availableFilters'][] = $availableFilter['name'];
			}
		
			if(count($availableFilterList) > 0){
				$results['filterType'] = $filterType;
				file_put_contents($filterFileName, json_encode($availableFilterList));
			}
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

			$results['productCount'] = count($results['products']);
		}

		return $results;
	}

	public function get_category_id($url){
		curl_setopt($this -> handle, CURLOPT_HTTPHEADER , $this -> headers);
		curl_setopt($this -> handle, CURLOPT_URL, "https://www.cb2.com/" . $url);
		$this -> html = curl_exec($this -> handle);

		eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));

		return isset($category[1]) ? array('CategoryID' => $category[1]) : false;
	}
}

?>