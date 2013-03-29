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

namespace Contain\Entity\Property\Type;

/**
 * Creates and locates type objects for entity property values.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class TypeFactory
{
    /**
     * Singleton, prevent instantiation
     */
    private function __construct() {}
    private function __clone() {}

    private static $instance;

    /**
     * @var array
     */
    protected $defaultTypes = array(
        'string' => 'Contain\Entity\Property\Type\StringType',
        'integer' => 'Contain\Entity\Property\Type\IntegerType',
        'boolean' => 'Contain\Entity\Property\Type\BooleanType',
        'dateTime' => 'Contain\Entity\Property\Type\DateTimeType',
        'date' => 'Contain\Entity\Property\Type\DateType',
        'list' => 'Contain\Entity\Property\Type\ListType',
        'entity' => 'Contain\Entity\Property\Type\EntityType',
        'enum' => 'Contain\Entity\Property\Type\EnumType',
        'mixed' => 'Contain\Entity\Property\Type\MixedType',
        'double' => 'Contain\Entity\Property\Type\DoubleType',
    );

    /**
     * @var array
     */
    protected $types = array();

    /**
     * Fetches the single instance.
     *
     * @return  Contain\Entity\Property\Type\TypeFacetory
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }

        self::$instance = new TypeFactory();
        self::$instance->init();

        return self::$instance;
    }

    /**
     * Initializes the stock types.
     *
     * @return  void
     */
    public function init()
    {
        foreach ($this->defaultTypes as $alias => $type) {
            $this->types[$alias] = new $type();
        }
    }

    /**
     * Gets (or creates) a type instance.
     *
     * @param   string          Alias or class name
     * @return  Contain\Entity\Property\Type\TypeInterface
     */
    public static function get($name)
    {
        return self::getInstance()->fetchOrCreate($name);
    }

    /**
     * Gets (or creates) a type instance.
     *
     * @param   string          Alias or class name
     * @return  Contain\Entity\Property\Type\TypeInterface
     */
    public function fetchOrCreate($name)
    {
        if (is_string($name) && isset($this->types[$name])) {
            return $this->types[$name];
        }

        if ($name instanceof TypeInterface) {
            $this->types[get_class($name)] = $name;
            return $name;
        }

        $type = new $name();

        if (!$type instanceof TypeInterface) {
            throw new \InvalidArgumentException('\'' . $name . '\' does not descend '
                . __NAMESPACE__ . '\TypeInterface'
            );
        }

        return ($this->type[$name] = $type);
    }
}
