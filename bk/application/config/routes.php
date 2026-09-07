<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['bruh'] = 'Welcome/index';
$route['app_bcode'] = 'AppController/app_bcode_ctrl';
$route['app_countbcode'] = 'AppController/app_countbcode_ctrl';

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
$route['mapi/addUser']                        = 'AppController/addUser';
$route['mapi/addLogs']                        = 'AppController/addLogs';
$route['mapi/csvstatus']                      = 'AppController/csvStatus';
$route['mapi/csvmanifest']                    = 'AppController/csvManifest';
$route['mapi/uploadLogs']                     = 'AppController/uploadLogs';
$route['mapi/getCsvFiles']                    = 'AppController/getCsvFiles';
$route['mapi/getNfCsvFiles']                  = 'AppController/getNfCsvFiles';
$route['mapi/getAllCsvFiles']                 = 'AppController/getAllCsvFiles';

$route['csvmonitor']                          = 'csvmonitor/menu';
$route['csvmonitor/view']                     = 'csvmonitor/index';
$route['csvmonitor/upload']                   = 'csvmonitor/upload';

$route['nfitemmonitor/nfitem']                = 'csvmonitor/nfitem';


