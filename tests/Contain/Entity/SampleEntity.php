<?php
namespace ContainTest\Entity;

use Contain\Entity\AbstractEntity;
use Contain\Entity\Property\Property;

class SampleEntity extends AbstractEntity
{
    public function init()
    {
        $this->properties['firstName'] = new Property('string');
        $this->properties['child']     = new Property('\ContainTest\Entity\SampleChildEntity');
    }
}
