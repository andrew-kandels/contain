<?php
/**
 * Contain Project
 *
 * Abstract included by command line scripts to initiate Zend\Console
 * and the framework based on typical paths and settings.
 *
 * This source file is subject to the BSD license bundled with
 * this package in the LICENSE.txt file. It is also available
 * on the world-wide-web at http://www.opensource.org/licenses/bsd-license.php.
 * If you are unable to receive a copy of the license or have
 * questions concerning the terms, please send an email to
 * me@andrewkandels.com.
 *
 * @category    akandels
 * @package     contain
 * @author      Andrew Kandels (me@andrewkandels.com)
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://andrewkandels.com/contain
 */

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

/********************************************************************************/
// Configuration (defaults for a typical ZF2 application installing Contain via Composer)

define('ZF2_APPLICATION_PATH', realpath(__DIR__ . '/../../../..'));
define('ZF2_IS_MODULE', file_exists(ZF2_APPLICATION_PATH . '/Module.php'));
if (ZF2_IS_MODULE) {
    $modulesPath = dirname(ZF2_APPLICATION_PATH);
} else {
    $modulesPath = ZF2_APPLICATION_PATH . '/module';
}
define('ZF2_MODULES_PATH', $modulesPath);
define('ZF2_FRAMEWORK_PATH', ZF2_APPLICATION_PATH . '/vendor/zendframework/zendframework');
define('COMPOSER_AUTOLOADER', ZF2_APPLICATION_PATH . '/vendor/autoload.php');

define('IS_PRODUCTION', false);
define('IS_DEVELOPMENT', true);
define('APPLICATION_CONFIG_FILE', ZF2_APPLICATION_PATH . '/config/application.config.php');

// DO NOT EDIT Below this line
/********************************************************************************/

define('CONTAIN_PATH', realpath(__DIR__ . '/..'));

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

chdir(ZF2_APPLICATION_PATH);

require_once(COMPOSER_AUTOLOADER);
if (ZF2_IS_MODULE) {
    $moduleName = glob(ZF2_APPLICATION_PATH . '/src/*', GLOB_ONLYDIR);
    $moduleName = basename(array_shift($moduleName));
    $config = array(
        'service_manager' => array(),
        'module_listener_options' => array(
            'module_paths' => array(dirname(ZF2_APPLICATION_PATH)),
        ),  
        'modules' => array($moduleName),
    );  
} else {
    $config = include(APPLICATION_CONFIG_FILE);
    if (!isset($config['service_manager'])) {
        $config['service_manager'] = array();
    }
}
$serviceManager = new ServiceManager(new ServiceManagerConfig($config['service_manager']));
$serviceManager->setService('ApplicationConfig', $config);
$serviceManager->get('ModuleManager')->loadModules();
