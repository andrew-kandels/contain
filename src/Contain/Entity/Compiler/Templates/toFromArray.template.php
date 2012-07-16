    /**
     * Gets an array of all the entity's properties.
     *
<?php if ($this->hasExtended): ?>
     * @param   boolean             Include extended properties
<?php endif; ?>
     * @return  array
     */
    public function getProperties(<?php if ($this->hasExtended): ?>$includeExtended = false<?php endif; ?>)
    {
        $result = array();
<?php foreach ($this->v as $property): ?>
        if ($this->has<?php echo ucfirst($property); ?>()) {
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

