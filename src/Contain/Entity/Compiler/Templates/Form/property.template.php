        $this->add($factory->createElement(array(
            'name' => <?php echo var_export($this->name, true); ?>,
            'type' => <?php echo var_export($this->type, true); ?>,
            'options' => <?php echo var_export($this->options, true); ?>,
            'attributes' => <?php echo var_export($this->attributes, true); ?>,
        )));

