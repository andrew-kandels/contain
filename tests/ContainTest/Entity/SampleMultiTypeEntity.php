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
        $this->define('string', 'string', array(
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
        $this->define('entity', 'entity', array(
            'className' => 'ContainTest\Entity\SampleChildEntity',
        ));
        $this->define('boolean', 'boolean');
        $this->define('dateTime', 'dateTime');
        $this->define('date', 'date');
        $this->define('double', 'double');
        $this->define('enum', 'enum', array(
            'options' => array('one', 'two', 'three'),
        ));
        $this->define('integer', 'integer');
        $this->define('list', 'list', array(
            'type' => 'integer'
        ));
        $this->define('listEntity', 'list', array(
            'type' => 'entity',
            'className' => '\ContainTest\Entity\SampleChildEntity',
        ));
        $this->define('mixed', 'mixed');
        $this->define('string', 'string');
    }
}
