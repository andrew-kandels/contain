<?php
namespace ContainTest\Entity;

use Contain\Entity\AbstractEntity;
use Contain\Entity\Property\Property;

class SampleChildEntity extends AbstractEntity
{
    /**
     * @var integer
     */
    public static $initCount;
    public static $instanceCount;

    public function __construct($properties = null)
    {
        parent::__construct($properties);
        self::$instanceCount++;
    }

    public function init()
    {
        self::$initCount++;

        $this->define('firstName', 'string', array('primary' => true));
        $this->define('lastName', 'string');
    }

    public function __destruct()
    {
        self::$instanceCount--;
    }
}
