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
|   example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|   https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|   $route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|   $route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|   $route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:   my-controller/index   -> my_controller/index
|      my-controller/my-method   -> my_controller/my_method
 */
$route['default_controller']   = 'user'; //admin
$route['404_override']         = '';
$route['translate_uri_dashes'] = FALSE;

/*
|----------------------
| login-register routes
|----------------------
 */

$route['user']               = 'user_auth';
$route['user/sign-up']       = 'user_auth/signup';
$route['user/sign-in']       = 'user_auth/signin';
$route['user/google/auth']   = 'user_auth/google_auth';
$route['user/facebook/auth'] = 'user_auth/facebook_auth';
$route['user/logout']        = 'user_auth/logout';

/*
|---------------------------
|login-register error routes
|----------------------------
 */

$route['user/auth/3']  = 'user/home_page'; //duplicate email address.
$route['user/auth/1']  = 'user/home_page'; //could not save user.
$route['user/auth/2']  = 'user/home_page'; //could not login user.
$route['user/auth/77'] = 'user/home_page'; //confirm email.
$route['user/auth/78'] = 'user/home_page'; //mail confirmed.

$route['user/mail-confirm/(:any)']           = 'user_auth/confirmEmail/$1';
$route['product_details_page/(:any)/(:any)'] = 'user/product_details_page/$1/$2';

$route['admin-cp/admin'] = 'admin';
$route['admin']          = 'admin';
$route['dashboard']      = 'admin/dashboard';

$route['products_page']                      = 'products';
$route['products_page/(:any)/(:any)']        = 'products/$1/$2';
$route['products_page/(:any)/(:any)/(:any)'] = 'products/$1/$2/$3';
$route['home_page']                          = 'user/home_page';
$route['department/(:any)/(:any)']           = 'user/get_department_products/$1/$2';
$route['department/(:any)/(:any)/(:any)']    = 'user/get_department_products/$1/$3/$2';
$route['filter']                             = 'user/filter_products';