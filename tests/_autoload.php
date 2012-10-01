<?php
/**
 * Setup autoloading
 */
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    include_once __DIR__ . '/../../vendor/autoload.php';
} else {
    // if composer autoloader is missing, explicitly load the standard
    // autoloader by relativepath
    require_once __DIR__ . '/../../../zendframework/zendframework/library/Zend/Loader/StandardAutoloader.php';
}

$loader = new Zend\Loader\StandardAutoloader(array(
    Zend\Loader\StandardAutoloader::LOAD_NS => array(
        'Zend'        => __DIR__ . '/../../../zendframework/zendframework/library/Zend',
        'ContainTest' => __DIR__ . '/Contain',
        'Contain'     => __DIR__ . '/../src/Contain',
    ),
));
$loader->register();
