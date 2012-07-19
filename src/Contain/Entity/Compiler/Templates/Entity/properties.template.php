        $this->_types['<?php echo $this->property->getName(); ?>'] = new \<?php echo get_class($this->property->getType()); ?>();
<?php if ($options = $this->property->getType()->getOptions()): ?>
        $this->_types['<?php echo $this->property->getName(); ?>']->setOptions(<?php var_export($options); ?>);
<?php endif; ?>
