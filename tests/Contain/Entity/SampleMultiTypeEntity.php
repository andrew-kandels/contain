<?php
namespace ContainTest\Entity;

use Contain\Entity\AbstractEntity;
use Contain\Entity\Property\Property;

class SampleMultiTypeEntity extends AbstractEntity
{
    protected $inputFilter = 'ContainTest\Entity\Filter\SampleMultiTypeEntity';
    protected $messages = array();

    public function init()
    {
        $this->properties['string'] = array('type' => 'string', 'options' => array(
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array('name' => 'StringLength', 'options' => array(
                    'min' => 0,
                    'max' => 10,
                )),
            ),
        ));
        $this->properties['entity']  = array('type' => 'entity', 'options' => array(
            'className' => 'ContainTest\Entity\SampleChildEntity',
        ));
        $this->properties['boolean'] = array('type' => 'boolean');
        $this->properties['dateTime'] = array('type' => 'dateTime');
        $this->properties['date'] = array('type' => 'date');
        $this->properties['double'] = array('type' => 'double');
        $this->properties['enum'] = array('type' => 'enum', 'options' => array(
            'options' => array('one', 'two', 'three'),
        ));
        $this->properties['integer'] = array('type' => 'integer');
        $this->properties['list'] = array('type' => 'list', 'options' => array(
            'type' => 'integer'
        ));
        $this->properties['listEntity'] = array('type' => 'list', 'options' => array(
            'type' => 'entity',
            'className' => '\ContainTest\Entity\SampleChildEntity',
        ));
        $this->properties['mixed'] = array('type' => 'mixed');
        $this->properties['string'] = array('type' => 'string');
    }
}
