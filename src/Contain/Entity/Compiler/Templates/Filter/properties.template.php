
        $this->add($factory->createInput(array(
            'name' => '<?php echo $this->name; ?>',
<?php if ($this->required): ?>
            'required' => true,
            'allow_empty' => false,
<?php else: ?>
            'allow_empty' => true,
<?php endif; ?>
<?php if ($this->validators): ?>
            'validators' => <?php var_export($this->validators); ?>,
<?php endif; ?>
<?php if ($this->filters): ?>
            'filters' => <?php var_export($this->filters); ?>,
<?php endif; ?>
        )));
