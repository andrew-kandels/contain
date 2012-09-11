<?php
namespace ContainTest\Entity;

use Contain\Entity\AbstractEntity;
use Contain\Entity\Property\Property;

class SampleMultiTypeEntity extends AbstractEntity
{
    public function init()
    {
        $this->properties['string']  = new Property('string');
        $this->properties['entity']  = new Property('\ContainTest\Entity\SampleChildEntity');
        $this->properties['boolean'] = new Property('boolean');
        $this->properties['dateTime'] = new Property('boolean');
        $this->properties['date'] = new Property('boolean');
        $this->properties['double'] = new Property('double');
        $this->properties['enum'] = new Property('enum');
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
