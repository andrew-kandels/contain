    /**
     * Gets an array of all the entity's properties.
     *
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function getProperties($includeUnset = false)
    {
        $result = array();
<?php foreach ($this->v as $property): ?>
        if ($includeUnset || $this->has<?php echo ucfirst($property); ?>()) {
            $result[] = '<?php echo $property; ?>';
        }
<?php endforeach; ?>

        return $result;
    }

    /**
     * Returns an array of all the entity properties.
     *
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function toArray($includeUnset = false)
    {
        $properties = $this->getProperties($includeUnset);
        $result     = array();

        foreach ($properties as $property) {
            $method = 'get' . ucfirst($property);
            $value = $this->$method();
            if ($includeUnset || $this->_types[$property]->getUnsetValue() !== $value) {
                $result[$property] = $value;
            }
        }

        return $result;
    }

    /**
     * Hydrates entity properties from an array.
     *
     * @param   array|Traversable   Property key/value pairs
     * @param   boolean             Ignore errors
     * @param   boolean             Set undefined keys as extended properties
     * @return  $this
     */
    public function fromArray($properties, $ignoreErrors = false, $autoExtended = false)
    {
        if (!is_array($properties) && !$properties instanceof Traversable) {
            throw new InvalidArgumentException('$properties must be an array or an instance of Traversable.');
        }

        foreach ($properties as $key => $value) {
            if ($autoExtended && !$this->hasProperty($key)) {
                $this->setExtendedProperty($key, $value);
                continue;
            }

            switch ($key) {
<?php foreach ($this->v as $property): ?>
                case '<?php echo $property; ?>':
                    if ($ignoreErrors) {
                        try {
                            $this->set<?php echo ucfirst($property); ?>($value);
                        } catch (\Exception $e) {
                            // ignored
                        }
                    } else {
                        $this->set<?php echo ucfirst($property); ?>($value);
                    }
                    break;

<?php endforeach; ?>
                default:
                    if (!$ignoreErrors) {
                        throw new InvalidArgumentException("'$key' is not a valid property of "
                            . "the <?php echo $this->name; ?> entity."
                        );
                    }
                    break;
            }
        }

        return $this;
    }

    /**
     * Returns an array of all the entity properties
     * as an array of string-converted values (no objects).
     *
     * @param   Traversable|array|null              Properties
     * @param   boolean             Include unset properties
     * @return  array
     */
    public function export($includeProperties = null, $includeUnset = false)
    {
        $properties = $this->getProperties($includeUnset);
        $result     = array();

        if ($includeProperties) {
            if ($includeProperties instanceof Traversable) {
                $result = array();
                foreach ($includeProperties as $property) {
                    $result[] = $property;
                }
                $includeProperties = $result;
            } elseif (is_string($includeProperties)) {
                $includeProperties = array($includeProperties);
            } elseif (!is_array($includeProperties)) {
                throw new InvalidArgumentException('$includeProperties must be null, '
                    . 'a single property, or an array or Traversable object of '
                    . 'properties to export.'
                );
            }
        } else {
            $includeProperties = null;
        }

        foreach ($properties as $property) {
            if ($includeProperties && !in_array($property, $includeProperties)) {
                continue;
            }

            $method       = 'get' . ucfirst($property);
            $hasMethod    = 'has' . ucfirst($property);
            $unsetValue   = $this->_types[$property]->getUnsetValue();
            $defaultValue = $this->_types[$property]->getOption('defaultValue') ?: $unsetValue;
            $value        = $this->$hasMethod() ? $this->$method() : $defaultValue;
            if ($includeUnset || $unsetValue !== $value) {
                $result[$property] = $this->_types[$property]->parseString($value);
            }
        }

        return $result;
    }

