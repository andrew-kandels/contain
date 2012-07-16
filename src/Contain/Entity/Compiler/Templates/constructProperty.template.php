        $this->_types['<?php echo $this->property->getName(); ?>'] = new \<?php echo get_class($this->property->getType()); ?>();
<?php if ($this->options): ?>
        $this->_types['<?php echo $this->property->getName(); ?>']->unserialize("<?php echo addslashes($this->options); ?>");
<?php endif; ?>
