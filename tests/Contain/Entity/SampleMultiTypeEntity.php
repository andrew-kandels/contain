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
        $this->properties['string']  = new Property('string', array(
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
        $this->properties['entity']  = new Property('\ContainTest\Entity\SampleChildEntity');
        $this->properties['boolean'] = new Property('boolean');
        $this->properties['dateTime'] = new Property('dateTime');
        $this->properties['date'] = new Property('date');
        $this->properties['double'] = new Property('double');
        $this->properties['enum'] = new Property('enum', array(
            'options' => array('one', 'two', 'three'),
        ));
        $this->properties['integer'] = new Property('integer');
        $this->properties['list'] = new Property('list', array(
            'type' => 'integer'
        ));
        $this->properties['listEntity'] = new Property('list', array(
            'type' => 'entity',
            'className' => '\ContainTest\Entity\SampleChildEntity',
        ));
        $this->properties['mixed'] = new Property('mixed');
        $this->properties['string'] = new Property('string');
    }
}
