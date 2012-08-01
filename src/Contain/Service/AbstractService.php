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

namespace Contain\Service;

use Contain\Mapper\Driver\DriverInterface;
use Contain\AbstractQuery;

/**
 * Abstract Service
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class AbstractService extends AbstractQuery implements ServiceInterface
{
    /**
     * Prepares a mapper for a method's invokation. Passes along 
     * options, sort, limiting and other query specific 
     * attributes.
     *
     * @param   Contain\Mapper\Driver       Mapper
     * @param   boolean                     Clears the query properties after prepping
     * @return  Contain\Mapper\Driver
     */
    protected function prepare(DriverInterface $mapper, $clearProperties = true)
    {
        if ($this->limit !== null) {
            $mapper->limit($this->limit);
        }

        if ($this->skip !== null) {
            $mapper->skip($this->skip);
        }

        if ($this->sort !== null) {
            $mapper->sort($this->sort);
        }

        $mapper->setOptions($this->getOptions())
               ->properties($this->getProperties());

        // reset above options to a blank state
        if ($clearProperties) {
            $this->clear();
        }

        return $mapper;
    }
}
