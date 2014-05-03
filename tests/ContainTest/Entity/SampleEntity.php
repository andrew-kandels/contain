<?php
namespace ContainTest\Entity;

use Contain\Entity\AbstractEntity;
use Contain\Entity\Property\Property;

class SampleEntity extends AbstractEntity
{
    public function init()
    {
        $this->define('firstName', 'string');
        $this->define('child', 'ContainTest\Entity\SampleChildEntity');
    }
}
