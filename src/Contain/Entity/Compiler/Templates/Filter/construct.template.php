<?php echo '<?php'; ?>

/**
 * Contain Project
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

namespace <?php echo $this->namespace; ?>;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

/**
 * <?php echo $this->name; ?> Entity InputFilter (auto-generated by the Contain module)
 *
 * This instance should not be edited directly. Edit the definition file instead 
 * and recompile.
 */
class <?php echo $this->name; ?> extends InputFilter
{
    /**
     * Construct and initialize the filters for the entity properties.
     *
     * @return $this
     */
    public function __construct()
    {
        $factory = new InputFactory();
