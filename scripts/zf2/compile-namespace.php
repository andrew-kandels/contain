<?php
/**
 * Contain Project
 *
 * Compiles a fully qualified namespace that points to an entity definition
 * into a ready-to-use entity class with an optional filter class.
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

require_once(__DIR__ . '/abstract-script.php');

if (empty($argv[1])) {
    fprintf(STDERR, "Syntax: compile FULL_ENTITY_NAMESPACE (e.g.: Contain\Entity\Definition\Setting)\n"
        . "Note: Your project must support autoloading for the specified class.\n"
    );
    exit(1);
}

$compiler = $serviceManager->get('Contain\Entity\Compiler\Compiler');
try {
    $compiler->compile($argv[1]);
} catch (Exception $e) {
    fprintf(STDERR, "Exception: %s\n--\n%s\n\n", $e->getMessage(), $e->getTraceAsString());
    exit(1);
}

exit(0);
