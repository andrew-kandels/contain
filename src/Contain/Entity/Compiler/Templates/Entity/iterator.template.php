    /**
     * Rewinds the internal position counter (iterator).
     *
     * @return  void
     */
    public function rewind()
    {
        $this->_iterator = 0;
    }

    /**
     * Returns the property of the current iterator position.
     *
     * @return  Contain\Entity\Property
     */
    public function current()
    {
        $properties = $this->getProperties();
        $getter     = 'get' . ucfirst($properties[$this->_iterator]);
        return $this->$getter();
    }

    /**
     * Returns the current iterator property position.
     *
     * @return  integer
     */
    public function key()
    {
        $properties = $this->getProperties();
        return $properties[$this->_iterator];
    }

    /**
     * Advances the iterator to the next property.
     *
     * @return  void
     */
    public function next()
    {
        $this->_iterator++;
    }

    /**
     * Is the iterator in a valid position.
     *
     * @return  boolean
     */
    public function valid()
    {
        $properties = $this->getProperties();
        return isset($properties[$this->_iterator]);
    }

