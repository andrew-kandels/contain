<?php
namespace ContainTest\Entity\Filter;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

class SampleMultiTypeEntity extends InputFilter
{
    /**
     * Construct and initialize the filters for the entity properties.
     *
     * @return $this
     */
    public function __construct()
    {
        $factory = new InputFactory();

        $this->add($factory->createInput(array(
            'name' => 'string',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array('name' => 'Digits'),
            ),
        )));
    }
}
