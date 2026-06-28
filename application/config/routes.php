<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'Auth';
$route['404_override']       = '';
$route['translate_uri_dashes'] = FALSE;

$route['login']   = 'Auth/login';
$route['logout']  = 'Auth/logout';

$route['activity_log']         = 'ActivityLog/index';
$route['activity_log/(:any)']  = 'ActivityLog/$1';
$route['attendance/recap']     = 'Attendance/recap';
