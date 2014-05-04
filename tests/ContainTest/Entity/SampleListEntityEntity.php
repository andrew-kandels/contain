<?php
namespace ContainTest\Entity;

use Contain\Entity\AbstractEntity;
use Contain\Entity\Property\Property;

class SampleListEntityEntity extends AbstractEntity
{
    protected $inputFilter = 'ContainTest\Entity\Filter\SampleMultiTypeEntity';
    protected $messages = array();

    public function init()
    {
        $this->define('listEntity', 'listEntity', array(
            'className' => '\ContainTest\Entity\SampleChildEntity',
        ));
    }
}
