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

use Contain\Exception\InvalidArgumentException;
use Iterator;
use Traversable;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;

/**
 * <?php echo $this->name; ?> Entity (auto-generated by the Contain module)
 *
 * This instance should not be edited directly. Edit the definition file instead 
 * and recompile.
 */
class <?php echo $this->name; ?><?php 
    if ($this->extends): ?> extends <?php echo $this->extends; endif;
    if ($this->implementors): ?> implements <?php echo implode(', ', $this->implementors); endif; ?>

{
<?php foreach ($this->v as $name => $type): ?>
    /** @var <?php echo $type; ?> */
    protected $<?php echo $name; ?>;

<?php endforeach; ?>
    /** @var array */
    protected $_types = array();

<?php if ($this->hasIteration): ?>
    /** @var integer */
    protected $_iterator = 0;

<?php endif; ?>
<?php if ($this->hasEvents): ?>
    /** @var Zend\EventManager\EventManager */
    protected $_eventManager;

<?php endif; ?>
    /** @var array */
    protected $_extendedProperties = array();

    /**
     * Constructor
     *
     * @param   array|Traversable               Properties
     * @return  $this
     */
    public function __construct($properties = null)
    {
