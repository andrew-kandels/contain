<?php

namespace Contain\Entity\Definition;

class User extends AbstractDefinition
{
    public function setUp()
    {
        $this->setName('User')
             ->setExtended()
             ->setEvents()
             ->import('Contain\Entity\Definition\Timestampable')
             ->setTargetPath(__DIR__ . '/..')
             ->registerMethod('getPrettyName');

        $this->setProperty('id', 'integer')
             ->setRequired()
             ->setPrimary()
             ->setGenerated();

        $this->setProperty('name', 'string')
             ->setRequired();

        $status = $this->setProperty('status', 'enum')
                       ->setRequired(true);
        $status->getType()->setOptions(array('active', 'inactive'));
        $status->setDefaultValue('active');
    }

    public function init()
    {
        $this->getEventManager()->attach('property.get', function (Event $e) {
            $property = $e->getParam('property');
            if ($property['name'] == 'name') {
                $property['name'] = 'loser';
                $e->setParam('property', $property);
            }
        });
    }

    /**
     * Some shit
     */
    public function getPrettyName()
    {
        return ucfirst($this->getName());
    }
}
