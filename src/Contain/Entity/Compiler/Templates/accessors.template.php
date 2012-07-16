    /**
     * Getter for the <?php echo $this->property->getName(); ?> property.
     *
     * @return  <?php echo $this->type; ?>

     */
    public function get<?php echo ucfirst($this->property->getName()); ?>()
    {
<?php if ($this->hasEvents): ?>
        return $this->onEventGetter(
            '<?php echo $this->property->getName(); ?>', 
            $this-><?php echo $this->property->getName(); ?>,
            $this->_types['<?php echo $this->property->getName(); ?>']->getUnsetValue() !== $this-><?php echo $this->property->getName(); ?>

        );
<?php else: ?>
        return $this-><?php echo $this->property->getName(); ?>;
<?php endif; ?>
    }

    /**
     * Setter for the <?php echo $this->property->getName(); ?> property.
     *
     * @param   <?php echo $this->type; ?>      Value to set
     * @return  $this
     */
    public function set<?php echo ucfirst($this->property->getName()); ?>($value)
    {
<?php if ($this->hasEvents): ?>
        $value = $this->onEventSetter(
            '<?php echo $this->property->getName(); ?>',
            $this-><?php echo $this->property->getName(); ?>,
            $value,
            $this->_types['<?php echo $this->property->getName(); ?>']->getUnsetValue() !== $this-><?php echo $this->property->getName(); ?>

        );
<?php endif; ?>
        $this-><?php echo $this->property->getName(); ?> = $this->_types['<?php echo $this->property->getName(); ?>']->parse($value);
        return $this;
    }

    /**
     * Returns true if the <?php echo $this->property->getName(); ?> property has been set/hydrated.
     *
     * @return  boolean
     */
    public function has<?php echo ucfirst($this->property->getName()); ?>()
    {
        return $this->_types['<?php echo $this->property->getName(); ?>']->getUnsetValue() !== $this-><?php echo $this->property->getName(); ?>;
    }

