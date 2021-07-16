<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['default_controller'] = 'default/index/index';
$route['ajax-time-keeping'] = 'default/index/ajaxTimeKeeping';

$route['(:any)'] = 'default/index/index';



$route['(.*)'] = 'default/index/index';