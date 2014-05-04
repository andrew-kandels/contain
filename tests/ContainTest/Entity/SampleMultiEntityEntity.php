<?php
namespace ContainTest\Entity;

use Contain\Entity\AbstractEntity;
use Contain\Entity\Property\Property;

class SampleMultiEntityEntity extends AbstractEntity
{
    protected $inputFilter = 'ContainTest\Entity\Filter\SampleMultiTypeEntity';
    protected $messages = array();

    public function init()
    {
        for ($num = 1; $num <= 10; $num++) {
            $this->define(sprintf('entity%d', $num), 'entity', array(
                'className' => 'ContainTest\Entity\SampleChildEntity',
            ));
        }
    }
}
