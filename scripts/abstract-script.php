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
define('ZF2_MODULES_PATH', ZF2_APPLICATION_PATH . '/module');
define('ZF2_FRAMEWORK_PATH', ZF2_APPLICATION_PATH . '/vendor/zendframework/zendframework');
define('COMPOSER_AUTOLOADER', ZF2_APPLICATION_PATH . '/vendor/autoload.php');

define('APPLICATION_CONFIG_FILE', ZF2_APPLICATION_PATH . '/config/application.config.php');

// DO NOT EDIT Below this line
/********************************************************************************/

define('CONTAIN_PATH', realpath(__DIR__ . '/..'));

ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

chdir(ZF2_APPLICATION_PATH);

require_once(COMPOSER_AUTOLOADER);

$config = include(APPLICATION_CONFIG_FILE);
$serviceManager = new ServiceManager(new ServiceManagerConfig($config['service_manager']));
$serviceManager->setService('ApplicationConfig', $config);
$serviceManager->get('ModuleManager')->loadModules();
