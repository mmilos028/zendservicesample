<?php
if (version_compare(phpversion(), '5.2.0', '<') === true)
	die('ERROR: Your PHP version is ' . phpversion() . '. OnlineCasino Web Service requires PHP 5.2.0 or newer.');
/** Define important paths to zend libraries or modules */
define('DS', DIRECTORY_SEPARATOR);
define('PS', PATH_SEPARATOR);
define('ROOT_DIR', dirname(__FILE__));
define('APP_DIR',  ROOT_DIR . DS . 'application');
//WINDOWS
define('LIB_DIR', "C:\\xampp_7_0_27\\htdocs\\zend_library_1_12_19");
//define('LIB_DIR', "C:\\wamp\\www\\zend_library\\");
define('MODELS_DIR', APP_DIR . DS . 'models');
define('SERVICES_DIR', APP_DIR . DS . 'services');
define('HELPERS_DIR', APP_DIR . DS . 'helpers');
//LINUX
/*
define('LIB_DIR',  "/var/www/html/www.web01.localdomain/zend_library_1_12_19");
define('MODELS_DIR', APP_DIR . DS . 'models');
define('SERVICES_DIR', APP_DIR . DS . 'services');
define('HELPERS_DIR', APP_DIR . DS . 'helpers');
*/

set_include_path(PS . LIB_DIR . PS . get_include_path());
/** Enables some zend libraries */
require_once 'Zend/Registry.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Application.php';

$section_type = "testing";
$config = new Zend_Config_Ini(APP_DIR . DS .'configs' . DS .'application_' . $section_type . '.ini', $section_type);
Zend_Registry::set('config', $config);

$application =
    new Zend_Application($config->getSectionName(), APP_DIR . DS . 'configs' . DS . 'application_' . $section_type . '.ini');
$application
    ->setBootstrap(APP_DIR . DS . "bootstrap.php", "Bootstrap");
$application
    ->bootstrap(
        array(
            'settings',
            'database',
            'databaseAts',
            'routes',
            'onlinecasinoserviceLoggers',
            'websiteLoggers',
            'merchantLoggers',
            'gglIntegrationLoggers',
            'vivoGamingIntegrationLoggers',
            'cbcxIntegrationLoggers',
            'sportBettingIntegrationLoggers',
            'externalIntegrationLoggers',
			'paysafecardDirectIntegrationLoggers',
			'wirecardIntegrationLoggers',
			'apcoIntegrationLoggers',
            'layout',
            'view'
        )
    )
    ->run();
