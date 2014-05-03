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

namespace Contain\Entity\Property;

use Contain\Entity\EntityInterface;
use Contain\Entity\Exception\InvalidArgumentException;
use Contain\Manager\TypeManager;
use ContainMapper\Cursor;
use Traversable;

/**
 * Represents a single property for an entity.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Property
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var mixed
     */
    protected $currentValue;

    /**
     * @var mixed
     */
    protected $persistedValue;

    /**
     * @var mixed
     */
    protected $unsetValue;

    /**
     * @var mixed
     */
    protected $emptyValue;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * @var EntityInterface|null
     */
    protected $parent;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var TypeManager
     */
    protected $typeManager;

    /**
     * Injects the parent entity.
     *
     * @param EntityInterface|null $parent
     *
     * @return self
     */
    public function setParent(EntityInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Gets or sets a type manager.
     *
     * @param TypeManager|null $manager
     *
     * @return TypeManager
     */
    public function typeManager(TypeManager $manager = null)
    {
        if ($manager) {
            $this->typeManager = $manager;
        }

        if (!$this->typeManager) {
            $this->typeManager = new TypeManager();
        }

        return $this->typeManager;
    }

    /**
     * Hydrates the property from a syntax compatible with export().
     *
     * @param array $arr Serialized Options/Values
     *
     * @return self
     */
    public function import(array $arr)
    {
        // unset the parent so we don't make any save()'s during hydration
        $parent = $this->parent;
        $this->parent = null;

        $this->options = array();
        if (!empty($arr['options'])) {
            $this->setOptions($arr['options']);
        }

        if (empty($arr['type'])) {
            throw new InvalidArgumentException('import expects a type index');
        }
        $this->type = $arr['type'];

        if (empty($arr['name'])) {
            throw new InvalidArgumentException('import expects a name index');
        }
        $this->name = $arr['name'];

        $this->unsetValue = array_key_exists('unsetValue', $arr)
            ? $arr['unsetValue']
            : $this->getType()->getUnsetValue();

        $this->emptyValue = array_key_exists('emptyValue', $arr)
            ? $arr['emptyValue']
            : $this->getType()->getEmptyValue();

        $this->currentValue = array_key_exists('currentValue', $arr)
            ? $arr['currentValue']
            : $this->unsetValue;

        if (array_key_exists('persistedValue', $arr)) {
            $this->persistedValue = $arr['persistedValue'];
        } else {
            $this->persistedValue = $this->currentValue;
        }

        // re-enable saves
        $this->parent = $parent;

        return $this;
    }

    /**
     * Entities have self-contained dirty, clean and persisted
     * values that need to "bubble" up to a single property-compatible
     * export so they can be hydrated into a parent container property.
     *
     * @param EntityInterface $entity
     *
     * @return self
     */
    public function importEntity(EntityInterface $entity)
    {
        $type         = $this->getType();
        $properties   = $entity->properties(true);
        $export       = $this->export();

        $export['currentValue'] = $export['persistedValue'] = $type->getUnsetValue();

        foreach ($properties as $name) {
            $property   = $entity->property($name);
            $unsetValue = $property->getType()->getUnsetValue();

            if ($unsetValue !== ($currentValue = $property->getExport())) {
                if (!$export['currentValue']) {
                    $export['currentValue'] = array();
                }

                $export['currentValue'][$name] = $currentValue;
            }

            if ($unsetValue !== ($persistedValue = $property->getPersistedValue())) {
                if (!$export['persistedValue']) {
                    $export['persistedValue'] = array();
                }

                $export['persistedValue'][$name] = $persistedValue;
            }
        }

        $this->import($export)->save();
        $this->parent->trigger('change');

        if ($e = $this->parent->getExtendedProperty('_property')) {
            extract($e);
            /* @var $parent EntityInterface */
            $parent->property($name)->importEntity($this->parent);
        }

        return $this;
    }

    /**
     * Serializes the property for later hydration.
     *
     * @return  array
     */
    public function export()
    {
        return array(
            'name'           => $this->name,
            'options'        => $this->options,
            'currentValue'   => $this->currentValue,
            'persistedValue' => $this->persistedValue,
            'emptyValue'     => $this->emptyValue,
            'unsetValue'     => $this->unsetValue,
            'type'           => $this->getTypeAlias(),
        );
    }

    /**
     * Sets the value for this property.
     *
     * @param mixed $value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->currentValue = $this->getType()->export($value);
        return $this->save();
    }

    /**
     * Sets the value for the index of the value, assuming the value itself is an
     * array. This is really only used internally for updating event callbacks in
     * lists and cursors of entites and should probably not be used outside of
     * the Contain internals.
     *
     * @param integer $index Index
     * @param mixed   $value export() value
     *
     * @return self
     *
     * @throws InvalidArgumentException
     */
    public function setValueAtIndex($index, $value)
    {
        if (!isset($this->currentValue[$index])) {
            throw new InvalidArgumentException('$index invalid for current value');
        }

        $this->currentValue[$index] = $this->getType()->getType()->export($value);

        return $this->save();
    }

    /**
     * Gets the value for the index of the value, assuming the value itself is an
     * array. This is really only used internally for updating event callbacks in
     * lists and cursors of entites and should probably not be used outside of
     * the Contain internals.
     *
     * @param integer $index
     *
     * @return mixed
     *
     * @throws InvalidArgumentException
     */
    public function getValueAtIndex($index)
    {
        if (!isset($this->currentValue[$index])) {
            throw new InvalidArgumentException('$index invalid for current value');
        }

        return $this->currentValue[$index];
    }

    /**
     * Watches an entity for changes to its values, rolling those events
     * back up to the parent entity's property.
     *
     * @param EntityInterface $entity
     * @param integer         $index
     *
     * @return self
     */
    public function watch(EntityInterface $entity, $index = null)
    {
        $entity->setExtendedProperty('_property', array(
            'parent' => $this->parent,
            'name'   => $this->name,
            'index'  => $index,
        ));

        // changing any value should persist back to be stored in the property's serialized version
        $entity->attach('change', function ($event) {
            $entity = $event->getTarget();
            extract($entity->getExtendedProperty('_property'));

            /* @var $parent EntityInterface */
            /* @var $name string */
            /* @var $index int|null */
            $property = $parent->property($name);

            if ($index !== null) {
                $property->setValueAtIndex($index, $entity);
                if ($parent = $property->getParent()) {
                    $parent->trigger('change');
                }
            } else {
                $property->importEntity($entity);
            }
        }, -100);

        return $this;
    }

    /**
     * getValue() for entity properties, which must always return an actual
     * entity that can be acted upon with events to send back change actions.
     *
     * @return EntityInterface
     */
    public function getEntityValue()
    {
        $type       = $this->getType();
        $export     = $this->export();
        $entity     = $type->parse(array()); // empty entity
        $properties = $entity->properties(true);
        $indexes    = array('currentValue', 'persistedValue');

        // sync the current/persisted values with that stored in the parent
        foreach ($properties as $name) {
            $property       = $entity->property($name);
            $propertyExport = $property->export();
            $unsetValue     = $propertyExport['unsetValue'];

            foreach ($indexes as $index) {
                if (isset($export[$index][$name]) && $export[$index][$name] !== $unsetValue) {
                    $propertyExport[$index] = $export[$index][$name];
                }
            }

            $property->import($propertyExport)->save();
        }

        $this->watch($entity);

        return $entity;
    }

    /**
     * getValue() for lists of entity types, which slow-hydrate entities from a
     * cursor. Changes to those entities should cycle back to the parent property.
     *
     * @return \ContainMapper\Cursor|array
     */
    public function getListEntityValue()
    {
        $value        = $this->getType()->parse($this->currentValue) ?: array();
        $propertyName = $this->name;
        $parent       = $this->parent;

        if ($value instanceof Cursor) {
            $value->getEventManager()->attach('hydrate', function ($event) use ($parent, $propertyName) {
                $entity = $event->getTarget();
                $parent->property($propertyName)->watch($entity, $event->getParam('index'));
            }, -1000);
        }

        return $value;
    }

    /**
     * getValue() for lists which needs to watch any entities it spawns for changes to
     * propogate back to the parent property.
     *
     * @return array
     */
    public function getListValue()
    {
        $value = $this->getType()->parse($this->currentValue) ?: array();

        if ($this->getType()->getOption('type') == 'entity') {
            foreach ($value as $index => $entity) {
                $this->watch($entity, $index);
            }
        }

        return $value;
    }

    /**
     * Gets the value for this property.
     *
     * @return mixed
     */
    public function getValue()
    {
        $type = $this->getType();

        if ($type instanceof Type\EntityType) {
            return $this->getEntityValue();
        }

        if ($type instanceof Type\ListEntityType) {
            return $this->getListEntityValue();
        }

        if ($type instanceof Type\ListType) {
            return $this->getListValue();
        }

        return $type->parse($this->currentValue);
    }

    /**
     * Returns true if the property has an unset value.
     *
     * @return bool
     */
    public function isUnset()
    {
        return $this->currentValue === $this->unsetValue;
    }

    /**
     * Returns true if the property has an empty value.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->currentValue === $this->emptyValue;
    }

    /**
     * Clears a property, setting it to an unset state.
     *
     * @return self
     */
    public function clear()
    {
        $this->currentValue = $this->unsetValue;
        return $this->save();
    }

    /**
     * Sets a property to its empty value.
     *
     * @return self
     */
    public function setEmpty()
    {
        $this->currentValue = $this->emptyValue;
        return $this->save();
    }

    /**
     * Sets a property as dirty, which is tracked by the persisted value not equaling
     * the current value of the property, which is ensured by making the persisted value
     * something one-of-a-kind.
     *
     * @return self
     */
    public function setDirty()
    {
        $this->persistedValue = $this->getType()->getDirtyValue();
        return $this->save();
    }

    /**
     * Marks the current value as having been persisted for the sake of
     * dirty tracking.
     *
     * @return self
     */
    public function clean()
    {
        $this->persistedValue = $this->currentValue;
        return $this->save();
    }

    /**
     * Exports a serializable version of the current value.
     *
     * @return mixed
     */
    public function getExport()
    {
        return $this->currentValue;
    }

    /**
     * Returns true if the current value of the property differs from its
     * last persisted value.
     *
     * @return bool
     */
    public function isDirty()
    {
        return $this->currentValue !== $this->persistedValue;
    }

    /**
     * Returns the value of this property when it was last persisted.
     *
     * @return mixed
     */
    public function getPersistedValue()
    {
        return $this->persistedValue;
    }

    /**
     * Returns the type object which defines how the data type
     * behaves.
     *
     * @return \Contain\Entity\Property\Type\TypeInterface
     */
    public function getType()
    {
        return $this->typeManager()->type(
            $this->type,
            $this->options
        );
    }

    /**
     * Returns the alias of the current type as set in the define().
     *
     * @return  string
     */
    public function getTypeAlias()
    {
        return $this->type;
    }

    /**
     * Set entity options.
     *
     * @param   array|Traversable       Option name/value pairs
     * @return self
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new InvalidArgumentException(
                '$options must be an instance of Traversable or an array.'
            );
        }

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        return $this->save();
    }

    /**
     * Sets the value for an entity property's option.
     *
     * @param string $name  Option name
     * @param mixed  $value Option value
     * @return self
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this->save();
    }

    /**
     * Retrieves entity property's options as an array.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Retrieves entity property's property by name.
     *
     * @param   string     $name Option name
     *
     * @return  array|null
     */
    public function getOption($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Gets the name of the property this object currently represents.
     *
     * @return  string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the parent entity.
     *
     * @return EntityInterface|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Persists changes back to the parent's property array.
     *
     * @return self
     */
    public function save()
    {
        if ($this->parent) {
            $this->parent->saveProperty($this);
        }

        return $this;
    }
}
