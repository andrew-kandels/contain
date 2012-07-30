        $this->init();

        if ($properties) {
            $className = __CLASS__;
            if (is_object($properties) && $properties instanceof $className) {
                $this->fromArray($properties->toArray());
            } else {
                $this->fromArray($properties);
            }
        }
    }

    /**
     * Called when the <?php echo $this->name; ?> entity has been initialized. Commonly used to register
     * event hooks.
     *
     * @return  void
     */
    protected function init()
    {
<?php echo $this->init; ?>

    }

<?php if ($this->hasEvents): ?>
    /**
     * Retrieves an instance of the Zend Framework event manager in order to 
     * register or trigger events.
     *
     * @return  Zend\EventManager\EventManager
     */
    public function getEventManager()
    {
        if (!$this->_eventManager) {
            $this->_eventManager = new EventManager();
        }

        return $this->_eventManager;
    }

    /**
     * Retrieves an instance of the Zend Framework event manager in order to 
     * register or trigger events.
     *
     * @param   Zend\EventManager\EventManager
     * @return  $this
     */
    public function setEventManager(EventManager $eventManager)
    {
        $this->_eventManager = $eventManager;
        return $this;
    }

    /**
     * 'property.get' event that is fired when a property is accessed.
     *
     * @param   string              Property name
     * @param   mixed               Current Value
     * @param   boolean             Is the value presently set
     * @return  mixed|null
     */
    public function onEventGetter($property, $currentValue, $isValueSet)
    {
        $eventManager = $this->getEventManager();
        $argv = $eventManager->prepareArgs(array('property' => array(
            'property'      => $property,
            'currentValue'  => $currentValue,
            'isSet'         => $isValueSet,
        )));
        $this->getEventManager()->trigger('property.get', $this, $argv);

        if (isset($argv['property']['value'])) {
            return $argv['property']['value'];
        }

        return $currentValue;
    }

    /**
     * 'property.set' event when a property is being set.
     *
     * @param   string              Property name
     * @param   mixed               Current Value
     * @param   mixed               New Value
     * @param   boolean             Is the value presently set
     * @return  mixed|null
     */
    public function onEventSetter($property, $currentValue, $newValue, $isValueSet)
    {
        $eventManager = $this->getEventManager();
        $argv = $eventManager->prepareArgs(array('property' => array(
            'property'      => $property,
            'currentValue'  => $currentValue,
            'isSet'         => $isValueSet,
            'value'         => $newValue,
        )));
        $this->getEventManager()->trigger('property.set', $this, $argv);

        return $argv['property']['value'];
    }
<?php endif; ?>

    /**
     * Fetches an extended property which can be set at anytime.
     *
     * @param   string                  Extended property name
     * @return  mixed
     */
    public function getExtendedProperty($name)
    {
<?php if ($this->hasEvents): ?>
        return $this->onEventGetter(
            $name, 
            isset($this->_extendedProperties[$name]) ? $this->_extendedProperties[$name] : null,
            isset($this->_extendedProperties[$name])
        );
<?php else: ?>
        return isset($this->_extendedProperties[$name]) ? $this->_extendedProperties[$name] : null;
<?php endif; ?>
    }

    /**
     * Fetches all extended properties.
     *
     * @return  array
     */
    public function getExtendedProperties()
    {
        $result = array();

        foreach ($this->_extendedProperties as $name => $value) {
            $result[$name] = $this->getExtendedProperty($name);
        }

        return $result;
    }

    /**
     * Injects an extended property which can be set at anytime.
     *
     * @param   string                  Extended property name
     * @param   mixed                   Value to set
     * @return  $this
     */
    public function setExtendedProperty($name, $value)
    {
<?php if ($this->hasEvents): ?>
        $this->_extendedProperties[$name] = $this->onEventSetter(
            $name, 
            isset($this->_extendedProperties[$name]) ? $this->_extendedProperties[$name] : null,
            $value,
            isset($this->_extendedProperties[$name])
        );
<?php else: ?>
        $this->_extendedProperties[$name] = $value;
<?php endif; ?>

        return $this;
    }

    /**
     * Returns an array of the columns flagged as primary as the 
     * key(s) and the current values for the keys as the property
     * values.
     *
     * @return  mixed
     */
    public function getPrimary()
    {
        return array(
<?php foreach ($this->primary as $property): ?>
            '<?php echo $property->getName(); ?>' => $this->get<?php echo ucfirst($property->getName()); ?>(),
<?php endforeach; ?>
        );
    }

    /**
     * Unsets one, some or all properties.
     *
     * @param   string|array|Traversable|null       Propert(y|ies)
     * @return  $this
     */
    public function clear($property = null)
    {
        if (!$property) {
            $property = $this->getProperties();
        }

        if (is_array($property) || $property instanceof Traversable) {
            foreach ($property as $name) {
                $this->clear($name);
            }

            return $this;
        }

        $method = 'set' . ucfirst($property);

        $this->$method($this->_types[$property]->getUnsetValue());

        return $this;
    }

    /**
     * Marks a changed property (or all properties by default) as clean, 
     * or unmodified.
     *
     * @param   string|array|Traversable|null       Propert(y|ies)
     * @return  $this
     */
    public function clean($property = null)
    {
        if (!$property) {
            $this->_dirty = array();

<?php foreach ($this->children as $entity): ?>
            if ($this-><?php echo $entity->getName(); ?> !== $this->_types['<?php echo $entity->getName(); ?>']->getUnsetValue()) {
                $this-><?php echo $entity->getName(); ?>->clean();
            }
            
<?php endforeach; ?>
            return $this;
        }

        if (is_array($property) || $property instanceof Traversable) {
            foreach ($property as $name) {
                $this->clean($name);
            }
            return $this;
        }
<?php if ($this->children): ?>

        if ($this->$name !== $this->_types[$name]->getUnsetValue() &&
            $this->_types[$name] instanceof \Contain\Entity\Property\Type\EntityType) {
            $this->$name->clean();
        }
<?php endif; ?>

        if (isset($this->_dirty[$name])) {
            unset($this->_dirty[$name]);
        }

        return $this;
    }

    /**
     * Returns dirty, modified properties with their previous undirty
     * value (or a recursive array for child entities).
     *
     * @return  array
     */
    public function getDirty()
    {
        $result = array_keys($this->_dirty);
<?php foreach ($this->children as $entity): ?>

        if ($this-><?php echo $entity->getName(); ?> !== $this->_types['<?php echo $entity->getName(); ?>']->getUnsetValue() &&
            $this-><?php echo $entity->getName(); ?>->isDirty()) {
            $result[] = '<?php echo $entity->getName(); ?>';
        }
<?php endforeach; ?>

        return $result;
    }

    /**
     * Marks a property as dirty.
     *
     * @param   string                      Property name
     * @return  $this
     */
    public function setAsDirty($property)
    {
        if ($this->hasProperty($property)) {
            $this->_dirty[$property] = true;
        }

        return $this;
    }

    /**
     * Returns true if dirty, modified properties exist.
     *
     * @return  boolean
     */
    public function isDirty()
    {
        return (bool) $this->getDirty();
    }
