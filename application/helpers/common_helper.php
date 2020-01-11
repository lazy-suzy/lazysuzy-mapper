<?php
 // Administrator URL
 function admin_url()
 {
 	return base_url() . 'admin/';
 }
  function front_url()
 {
	return base_url();
 }

function front_css_url()
{
 	return base_url() . 'assets/front/css/';
}
// JavaScript URL
function front_js_url()
{
	return base_url() . 'assets/front/js/';
}
function front_images_url()
{
 	return base_url() . 'assets/front/images/';
}


 // CSS URL
 function css_url()
 {
 	return base_url() . 'assets/css/';
 }
 // JavaScript URL
 function js_url()
 {
 	return base_url() . 'assets/js/';
 }
 // Images URL
 function images_url()
 {
 	return base_url() . 'assets/img/';
 }
 // Admin URL redirect
 function admin_redirect($url, $refresh = 'refresh') {
 	redirect('admin/'.$url, $refresh);
 }
  // User URL redirect
 function front_redirect($url, $refresh = 'refresh') {
 	//redirect('bidcex_front/'.$url, $refresh);
	redirect($url, $refresh);
 }
 
 // Admin Details
function getAdminDetails($id,$key='') 
{
 	$ci =& get_instance();
	$name = $ci->db->where('id',$id)->get('admin')->row();
	if ($name) {
		if($key!='')
		{
			return $name->$key;
		}
		else
		{
			return $name;
		}
	} else {
		return '';	
	}
}

function getUserName($user,$type='username')
{
	$username= $type;
	return $user->$username;
}

if(!function_exists('remove_spl_chars'))
{
	function remove_spl_chars($string=FALSE)
	{
		return preg_replace('/[^A-Za-z0-9\-]/', '',$string);
	}
}

function generateredeemString($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function generatesecretString($length = 64) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function getExtension($type)
{
	switch (strtolower($type))
	{        
		case 'image/jpg':
			$ext = 'jpg';
		break;
		
		case 'image/jpeg':
			$ext = 'jpg';
		break;

		case 'image/png':
			$ext = 'png';
		break;

		case 'image/gif':
			$ext = 'gif';
		break;   
		
		default:
			$ext = FALSE;
		break;
	}
	return $ext;
}

function get_client_ip()
{
	$ipaddress = '';
	if (getenv('HTTP_CLIENT_IP'))
		$ipaddress = getenv('HTTP_CLIENT_IP');
	else if(getenv('HTTP_X_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
	else if(getenv('HTTP_X_FORWARDED'))
		$ipaddress = getenv('HTTP_X_FORWARDED');
	else if(getenv('HTTP_FORWARDED_FOR'))
		$ipaddress = getenv('HTTP_FORWARDED_FOR');
	else if(getenv('HTTP_FORWARDED'))
		$ipaddress = getenv('HTTP_FORWARDED');
	else if(getenv('REMOTE_ADDR'))
		$ipaddress = getenv('REMOTE_ADDR');
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}
