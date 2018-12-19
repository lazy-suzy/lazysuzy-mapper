<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
 */
$route['default_controller'] = 'user'; //admin
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

//$route['product_list'] 				= 'user/product_list';
//$route['product_list/(:any)'] 		= 'user/product_list/$1';
//$route['ajax_more'] 					= 'user/ajax_more';

$route['auth'] = 'userauth';
$route['auth/register-me/'] = 'user/register';
$route['auth/login'] = 'userauth/login';

$route['loadRecord'] = 'user/loadRecord';
$route['loadRecord/(:any)'] = 'user/loadRecord/$1';

//14-11-18
$route['products'] = 'user/products';
$route['products/(:any)'] = 'user/products/$1';
$route['load_all_Record'] = 'user/load_all_Record';
$route['products/(:any)/(:any)'] = 'user/products/$1/$2';
$route['products/(:any)/(:any)/(:any)'] = 'user/products/$1/$2/$3';
//14-11-18

//22-11-18
$route['product_details_page/(:any)/(:any)'] = 'user/product_details_page/$1/$2';
//22-11-18

$route['admin-cp/admin'] = 'admin';
$route['admin'] = 'admin';
$route['dashboard'] = 'admin/dashboard';

$route['products_page'] = 'products';
$route['products_page/(:any)/(:any)'] = 'products/$1/$2';
$route['products_page/(:any)/(:any)/(:any)'] = 'products/$1/$2/$3';

//1-12-18
$route['filter/(:any)'] = 'user/filter/$1';
$route['filter/(:any)/(:any)'] = 'user/filter/$1/$2';

$route['unset_session'] = 'user/unset_session';

$route['home_page'] = 'user/home_page';
