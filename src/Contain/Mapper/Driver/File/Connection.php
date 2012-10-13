<?php
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

namespace Contain\Mapper\Driver\File;

use Contain\Mapper\Exception\InvalidArgumentException;
use Contain\Mapper\Driver\ConnectionInterface;

/**
 * MongoDB File Mapper
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Connection implements ConnectionInterface
{
    /**
     * @var array
     */
    protected $path;

    /**
     * Constructor
     *
     * @param   array|Traversable           Configuration
     * @return  $this
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Builds a connection to MongoDB and returns the Mongo object.
     *
     * @return  Mongo
     */
    public function getConnection()
    {
        if (!is_dir($this->path)) {
            throw new InvalidArgumentException("'{$this->path}' is not a directory");
        }

        return $this->path;
    }
}
