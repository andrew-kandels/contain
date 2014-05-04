<?php
namespace ContainTest\Entity;

use Contain\Entity\AbstractEntity;
use Contain\Entity\Property\Property;

class SampleGrandfatherEntity extends AbstractEntity
{
    public function init()
    {
        $this->define('firstName', 'string');
        $this->define('parent', 'ContainTest\Entity\SampleEntity');
    }
}
