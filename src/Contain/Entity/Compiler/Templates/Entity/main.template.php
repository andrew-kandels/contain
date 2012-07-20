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
            'name'          => $property,
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
     * Returns a unique identifier for this entity.
     *
     * @return  mixed
     */
    public function getPrimaryValue()
    {
<?php if ($this->primary): ?>
        return $this->get<?php echo ucfirst($this->primary->getName()); ?>();
<?php else: ?>
        throw new RuntimeException('<?php echo $this->name; ?> entity does not have a primary '
            . 'property.'
        );
<?php endif; ?>
    }

    /**
     * Returns a unique property for this entity.
     *
     * @return  mixed
     */
    public function getPrimaryName()
    {
<?php if ($this->primary): ?>
        return '<?php echo $this->primary->getName(); ?>';
<?php else: ?>
        throw new RuntimeException('<?php echo $this->name; ?> entity does not have a primary '
            . 'property.'
        );
<?php endif; ?>
    }

    /**
     * Unsets one, some or all properties.
     *
     * @param   string              Property name
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

        $this->setProperty($property, $this->_types[$property]->getUnsetValue());

        return $this;
    }
