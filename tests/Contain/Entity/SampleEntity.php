<?php
namespace ContainTest\Entity;

use Contain\Entity\AbstractEntity;
use Contain\Entity\Property\Property;

class SampleEntity extends AbstractEntity
{
    public function init()
    {
        $this->properties['firstName'] = array('type' => 'string');
        $this->properties['child']     = array('type' => 'entity', 'options' => array('className' => '\ContainTest\Entity\SampleChildEntity'));
    }
}
