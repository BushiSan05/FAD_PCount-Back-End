<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['bruh'] = 'Welcome/index';
// $route['app_bcode']                           = 'AppController/app_bcode_ctrl';
// $route['app_countbcode']                      = 'AppController/app_countbcode_ctrl';

$route['mapi/checkConnection']                = 'AppController/checkConnection';
$route['mapi/getItemMasterfileCount']         = 'AppController/getItemMasterfileCount';
$route['mapi/getItemMasterfileOffset']        = 'AppController/getItemMasterfileOffset';
$route['mapi/getAssetTypesOffset']            = 'AppController/getAssetTypesOffset';
$route['mapi/getCatTypesOffset']              = 'AppController/getCatTypesOffset';
$route['mapi/getUsersCount']                  = 'AppController/getUsersCount';
$route['mapi/getUsers']                       = 'AppController/getUsers';
$route['mapi/getSourceOffset']                = 'AppController/getSourceOffset';

$route['mapi/getCsv/(:any)']                  = 'AppController/getCsv/$1';
$route['mapi/listCsv']                        = 'AppController/listCsv';
$route['mapi/uploadCsv']                      = 'AppController/uploadCsv';
$route['mapi/uploadNfCsv']                    = 'AppController/uploadNfCsv';
$route['mapi/checkNfImages']                  = 'AppController/checkNfImages';
$route['mapi/uploadNfImagesBatch']            = 'AppController/uploadNfImagesBatch';
$route['mapi/addUser']                        = 'AppController/addUser';
$route['mapi/addLogs']                        = 'AppController/addLogs';
$route['mapi/csvstatus']                      = 'AppController/csvStatus';
$route['mapi/csvmanifest']                    = 'AppController/csvManifest';
$route['mapi/uploadLogs']                     = 'AppController/uploadLogs';
$route['mapi/getCsvFiles']                    = 'AppController/getCsvFiles';
$route['mapi/getAllCsvFiles']                 = 'AppController/getAllCsvFiles';

$route['mapi/uploadNfXlsx']                   = 'AppController/uploadNfXlsx';

$route['menu']                                = 'login/menu';
$route['csvmonitor/view']                     = 'CsvMonitor/monitoring';
$route['csvmonitor/upload']                   = 'CsvMonitor/upload';

$route['nfitemmonitor/nfitem']                = 'CsvMonitor/nfitem';
$route['masterfilemonitor/list']              = 'MasterfileMonitor/index';
$route['masterfilemonitor/getDepartments']    = 'MasterfileMonitor/getDepartmentsByLocation';
$route['masterfilemonitor/getBarpost']        = 'MasterfileMonitor/getBarpostByDepartment';


$route['default_controller'] = 'login';
$route['login']      = 'login';
$route['login/auth'] = 'login/auth';
$route['logout']     = 'login/logout';


$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
