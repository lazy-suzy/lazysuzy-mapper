<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Google API Configuration
| -------------------------------------------------------------------
|
| To get API details you have to create a Google Project
| at Google API Console (https://console.developers.google.com)
|
|  client_id         string   Your Google API Client ID.
|  client_secret     string   Your Google API Client secret.
|  redirect_uri      string   URL to redirect back to after login.
|  application_name  string   Your Google application name.
|  api_key           string   Developer key.
|  scopes            string   Specify scopes
 */
$config['googleplus']['client_id'] = '937636462062-69vsvlahsd16jlog6u3stspsklmr92lt.apps.googleusercontent.com';
$config['googleplus']['client_secret'] = '1rvvbXsQL0Bc4h4S6M_s4EK0';
$config['googleplus']['redirect_uri'] = 'http://localhost/LazySuzy/lazysuzy/full_backup/user/google/auth';
$config['googleplus']['application_name'] = 'LazySuzy';
$config['googleplus']['api_key'] = '';
$config['googleplus']['scopes'] = array();

?>