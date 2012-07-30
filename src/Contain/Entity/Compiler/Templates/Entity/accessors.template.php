    /**
     * Getter for the <?php echo $this->property->getName(); ?> property.
     *
     * @return  <?php echo $this->type; ?>

     */
    public function get<?php echo ucfirst($this->property->getName()); ?>()
    {
<?php if ($this->hasEvents): ?>
        $value = $this->onEventGetter(
            '<?php echo $this->property->getName(); ?>', 
            $this-><?php echo $this->property->getName(); ?>,
            $this->_types['<?php echo $this->property->getName(); ?>']->getUnsetValue() !== $this-><?php echo $this->property->getName(); ?>

        );
<?php else: ?>
        $value = $this-><?php echo $this->property->getName(); ?>;
<?php endif; ?>
<?php if ($defaultValue = $this->property->getOption('defaultValue')): ?>
        if ($value === $this->_types['<?php echo $this->property->getName(); ?>']->getUnsetValue()) {
            $value = $this->_types['<?php echo $this->property->getName(); ?>']->parse(<?php var_export($defaultValue); ?>);
        }
<?php endif; ?>
        return $value;
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
        $value = $this->_types['<?php echo $this->property->getName(); ?>']->parse($value);
<?php if (!$this->property->getType() instanceof \Contain\Entity\Property\Type\EntityType): ?>
        if (!isset($this->_dirty['<?php echo $this->property->getName(); ?>']) && $value !== $this-><?php echo $this->property->getName(); ?>) {
            $this->_dirty['<?php echo $this->property->getName(); ?>'] = true;
        }
<?php endif; ?>
        $this-><?php echo $this->property->getName(); ?> = $value;
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

