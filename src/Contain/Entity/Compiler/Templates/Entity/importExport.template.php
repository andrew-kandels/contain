    /**
     * Gets an array of all the entity's properties.
     *
     * @param   boolean             Include unset properties
<?php if ($this->hasExtended): ?>
     * @param   boolean             Include extended properties
<?php endif; ?>
     * @return  array
     */
    public function getProperties($includeUnset = false, <?php if ($this->hasExtended): ?>$includeExtended = false<?php endif; ?>)
    {
        $result = array();
<?php foreach ($this->v as $property): ?>
        if ($includeUnset || $this->has<?php echo ucfirst($property); ?>()) {
            $result[] = '<?php echo $property; ?>';
        }
<?php endforeach; ?>
<?php if ($this->hasExtended): ?>

        if ($includeExtended) {
            foreach ($this->_extendedProperties as $key => $value) {
                if (!in_array($key, $result)) {
                    $result[] = $key;
                }
            }
        }
<?php endif; ?>

        return $result;
    }

    /**
     * Returns an array of all the entity properties<?php if ($this->hasExtended): ?> (including extended properties)<?php endif; ?>.
     *
<?php if ($this->hasExtended): ?>
     * @param   boolean             Include extended properties
<?php endif; ?>
     * @return  array
     */
    public function toArray(<?php if ($this->hasExtended): ?>$includeExtended = false<?php endif; ?>)
    {
        $properties = $this->getProperties();
        $result     = array();

        foreach ($properties as $property) {
            $method = 'get' . ucfirst($property);
            $value = $this->$method();
            if ($this->_types[$property]->getUnsetValue() !== $value) {
                $result[$property] = $value;
            }
        }
<?php if ($this->hasExtended): ?>

        if ($includeExtended) {
            foreach ($this->_extendedProperties as $key => $value) {
                if (!isset($result[$key])) {
                    $result[$key] = $value;
                }
            }
        }
<?php endif; ?>

        return $result;
    }

    /**
     * Hydrates entity properties from an array.
     *
     * @param   array               Property key/value pairs
<?php if ($this->hasExtended): ?>
     * @param   boolean             Use extended properties for any undefined index
<?php endif; ?>
     * @return  $this
     */
    public function fromArray($properties<?php if ($this->hasExtended): ?>, $allowExtended = false<?php endif; ?>)
    {
        if (!is_array($properties) && !$properties instanceof Traversable) {
            throw new InvalidArgumentException('$properties must be an array or an instance of Traversable.');
        }

        foreach ($properties as $key => $value) {
            switch ($key) {
<?php foreach ($this->v as $property): ?>
                case '<?php echo $property; ?>':
                    $this->set<?php echo ucfirst($property); ?>($value);
                    break;

<?php endforeach; ?>
                default:
<?php if ($this->hasExtended): ?>
                    if ($allowExtended) {
                        $this->_extendedProperties[$key] = $value;
                        return $this;
                    }
<?php endif; ?>
                    throw new InvalidArgumentException("'$key' is not a valid property of the <?php echo $this->name; ?> entity.");
                    break;
            }
        }

        return $this;
    }

    /**
     * Returns an array of all the entity properties
     * as an array of string-converted values (no objects).
     *
     * @param   boolean                 Include unset properties
<?php if ($this->hasExtended): ?>
     * @param   boolean             Use extended properties for any undefined index
<?php endif; ?>
     * @return  array
     */
    public function export($includeUnset = false, <?php if ($this->hasExtended): ?>$includeExtended = false<?php endif; ?>)
    {
        $properties = $this->getProperties(true);
        $result     = array();

        foreach ($properties as $property) {
            $method       = 'get' . ucfirst($property);
            $unsetValue   = $this->_types[$property]->getUnsetValue();
            $defaultValue = $this->_types[$property]->getOption('defaultValue') ?: $unsetValue;
            $value        = $this->$method() ?: $defaultValue;
            if ($includeUnset || $unsetValue !== $value) {
                $result[$property] = $this->_types[$property]->parseString($value);
            }
        }
<?php if ($this->hasExtended): ?>

        if ($includeExtended) {
            foreach ($this->_extendedProperties as $key => $value) {
                if (!isset($result[$key])) {
                    $result[$key] = (string) $value;
                }
            }
        }
<?php endif; ?>

        return $result;
    }

