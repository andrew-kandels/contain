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
     * @return  $this
     */
    public function fromArray($properties)
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
                    throw new InvalidArgumentException("'$key' is not a valid property of "
                        . "the <?php echo $this->name; ?> entity."
                    );
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
     * @return  array
     */
    public function export($includeUnset = false)
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

        return $result;
    }

