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
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://andrewkandels.com/contain
 */

namespace Contain\Manager;

use Contain\Entity\Property\Type;
use Contain\Entity\Property\Type\TypeInterface;

/**
 * Creates and manages instances of entity property types.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class TypeManager
{
    /**
     * @var array
     */
    protected $types = array();

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Retrieves a type from the manager for a specific type of entity property.
     *
     * @param   string                      Type (string, etc.)
     * @param   array                       Options
     * @return  Contain\Entity\Property\Type\TypeInterface
     */
    public function type($type, array $options = array())
    {
        $search = $type;

        if (!is_string($type)) {
            throw new \InvalidArgumentException('$type should be an alias or class name of an entity '
                . 'property type'
            );
        }

        // cached
        if (isset($this->types[$search])) {
            return $this->resetOptions($search, $options);
        }

        // alias for a built-in type
        if (false === strpos($type, '\\')) {
            $type = 'Contain\Entity\Property\Type\\' . ucfirst($type) . 'Type';

            $this->types[$search] = new $type;

            return $this->resetOptions($search, $options);
        }

        if (is_subclass_of($type, '\Contain\Entity\AbstractEntity')) {
            $this->options[$search] = array('className' => $type);
            $this->types[$search]   = new Type\EntityType();

            return $this->resetOptions($search, $options);
        }

        if (is_subclass_of($type, '\Contain\Entity\Property\Type\TypeInterface')) {
            $this->types[$search] = new $type();

            return $this->resetOptions($search, $options);
        }

        throw new \InvalidArgumentException('$type invalid as type alias or class name.');
    }

    /** 
     * Resets the options, mainining any options the manager might have set for
     * alias convenience.
     *
     * @param   string                                          Type name/search
     * @param   array                                           Options
     * @return  Contain\Entity\Property\Type\TypeInterface
     */
    public function resetOptions($search, array $options)
    {
        $type = $this->types[$search];

        if (isset($this->options[$search])) {
            $options = $this->options[$search] + $options;
        }

        $type->clearOptions()->setOptions($options);

        return $type;
    }
}
