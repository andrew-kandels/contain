<?php
/**
 * Contain Project
 *
 * This source file is subject to the BSD license bundled with
 * this package in the LICENSE.txt file. It is also available
 * on the world-wide-web at http://www.opensource.org/licenses/bsd-license.php.
 * If you are unable to receive a copy of the license or have
 * questions concerning the terms, please send an email to
 * me@andrewkandels.com.
 *
 * @category    akandels
 * @package     contain
 * @author      Andrew Kandels (me@andrewkandels.com)
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://andrewkandels.com/contain
 */

namespace Contain\Entity\Compiler;

use Contain\Entity\Definition\AbstractDefinition;
use Contain\Entity\Exception\InvalidArgumentException;
use Contain\Entity\Exception\RuntimeException;
use Contain\Entity\Property\Type;
use Contain\Entity\Property\Type\EntityType;
use ReflectionMethod;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

/**
 * Compiles an entity definition into a entity class.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2013 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Compiler implements EventManagerAwareInterface
{
    /**
     * @var Contain\Entity\Definition\AbstractDefinition
     */
    protected $definition;

    /**
     * @var Zend\EventManager\EventManagerInterface
     */
    protected $events;

    /**
     * @var resource
     */
    protected $handle;

    /**
     * Sets the definition file in which to compile.
     *
     * @param   Contain\Entity\Definition\AbstractDefinition|string
     * @return  self
     */
    protected function setDefinition($definition)
    {
        if (!$definition instanceof AbstractDefinition) {
            $definition = new $definition();

            if (!$definition instanceof AbstractDefinition) {
                throw new InvalidArgumentException('$definition must extend '
                    . 'Contain\Entity\Definition\AbstractDefinition.'
                );
            }
        }

        $this->definition = $definition;

        $this->getEventManager()->trigger('definition.set', $this, array(
            'definition' => $definition,
        ));

        return $this;
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param  Zend\EventManager\EventManagerInterface $events
     * @return mixed
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $identifiers = array(__CLASS__, get_class($this));
        if (isset($this->eventIdentifier)) {
            if ((is_string($this->eventIdentifier))
                || (is_array($this->eventIdentifier))
                || ($this->eventIdentifier instanceof Traversable)
            ) {
                $identifiers = array_unique(array_merge($identifiers, (array) $this->eventIdentifier));
            } elseif (is_object($this->eventIdentifier)) {
                $identifiers[] = $this->eventIdentifier;
            }
            // silently ignore invalid eventIdentifier types
        }
        $events->setIdentifiers($identifiers);
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return Zend\EventManager\EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->events instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * Returns the full path to the target based on its key.
     *
     * @param   string                  Target key (entity, filter, etc.)
     * @return  string
     */
    public function getTargetFile($target)
    {
        $path = $this->definition->getTarget($target);
        if (empty($path)) {
            throw new InvalidArgumentException(
                "Target '$target' unspecified in definition, use setTarget() to configure."
            );
        }

        if (!is_dir($path = realpath($path))) {
            throw new InvalidArgumentException(
                "Target '$target' -> '$path' is not a directory, use setTarget() to configure."
            );
        }

        return sprintf('%s/%s.php',
            $path,
            $this->definition->getName()
        );
    }

    /**
     * Returns the namespace for a given target key.
     *
     * @param   string                  Target key (entity, filter, etc.)
     * @return  string
     */
    public function getTargetNamespace($target)
    {
        $path = $this->definition->getTarget($target);
        if (empty($path)) {
            throw new InvalidArgumentException(
                "Target '$target' unspecified in definition, use setTarget() to configure."
            );
        }

        if (!is_dir($path = realpath($path))) {
            throw new InvalidArgumentException(
                "Target '$target' -> '$path' is not a directory, use setTarget() to configure."
            );
        }

        if (preg_match('!(module|vendor|library)/([^/]+)/([^/]+)/src/(.*)!', $path, $matches)) {
            $path = $matches[4];
        } elseif (preg_match('!(module|vendor|library)/([^/]+)/src/(.*)!', $path, $matches)) {
            $path = $matches[3];
        } elseif (preg_match('!src/(.*)!', $path, $matches)) {
            $path = $matches[1];
        }

        return str_replace('/', '\\', $path);
    }

    /**
     * Renders a template and writes the output to the active file
     * handle.
     *
     * @param   string                  Template name
     * @param   array                   View variables
     * @return self
     */
    public function append($name, array $params = array())
    {
        $file = __DIR__ . "/Templates/$name.template.php";

        $properties = get_object_vars($this);
        foreach ($properties as $name => $value) {
            unset($this->$name);
        }

        foreach ($params as $name => $value) {
            $this->$name = $value;
        }

        ob_start();
        include($file);
        $return = ob_get_contents();
        ob_end_clean();

        foreach ($params as $name => $value) {
            unset($this->$name);
        }

        foreach ($properties as $name => $value) {
            $this->$name = $value;
        }

        fputs($this->handle, $return);

        return $this;
    }

    /**
     * Imports the raw source code from a method in the definition class and anything
     * it imports.
     *
     * @param   string              Method name
     * @return  string
     */
    protected function importMethods($method)
    {
        $imports = array_merge(array($this->definition), $this->definition->getImports());
        $result  = array();

        foreach ($imports as $import) {
            $result[] = str_repeat(' ', 8) . trim($this->importMethod($import, $method));
        }

        return implode(PHP_EOL . PHP_EOL, $result);
    }

    /**
     * Imports the raw source code from a single method in an object.
     *
     * @param   object              Class object instance
     * @param   string              Method name
     * @param   boolean             Include function definition?
     * @return  string
     */
    protected function importMethod($className, $method, $withDefinition = false)
    {
        $func       = new ReflectionMethod($className, $method);
        $contents   = file($func->getFileName());
        $startLine  = $func->getStartLine() - ($withDefinition ? 1 : 0);
        $endLine    = $func->getEndLine();
        $content    = implode('', array_slice($contents, $startLine, $endLine - $startLine));
        $result     = array();

        if ($withDefinition) {
            if ($comment = $func->getDocComment()) {
                $result[] = str_repeat(' ', 4) . $comment;
            }
        } else {
            $content = preg_replace('/^[^{]*{/', '', $content);
            $content = preg_replace('/}\s*$/', '', $content);
        }

        $result[] = $content;

        return implode(PHP_EOL, $result);
    }

    /**
     * Builds the entity class and writes it to the filesystem.
     *
     * @return self
     */
    public function compile($definition)
    {
        if (!$definition) {
            return $this;
        }

        $this->setDefinition($definition);

        foreach ($this->definition->getProperties() as $name => $property) {
            // dependency must be compiled first
            if ($property->getType() instanceof EntityType &&
                $property->getOption('className') != 'Contain\Entity\EntityInterface') {
                $className = $property->getType()->getOption('className');
                if (!class_exists($className)) {
                    $className = str_replace('Entity\\', 'Entity\Definition\\', $className);
                    $this->compile($className);
                }
            }
        }

        $targets = $this->definition->getTargets();

        $filter = null;
        if (!empty($targets[AbstractDefinition::FILTER])) {
            $this->compileFilter();

            $filter = sprintf('%s\%s',
                $this->getTargetNamespace(AbstractDefinition::FILTER),
                $this->definition->getName()
            );
        }

        if (!empty($targets[AbstractDefinition::ENTITY])) {
            $this->compileEntity($filter);
        }

        if (!empty($targets[AbstractDefinition::FORM])) {
            $this->compileForm($filter);
        }

        return $this;
    }

    /*
     * Compiles the entity object target.
     *
     * @param   string              FQDN to the filter class
     * @return self
     */
    protected function compileEntity($filter = null)
    {
        $outputFile = $this->getTargetFile(AbstractDefinition::ENTITY);
        if (!$this->handle = fopen($outputFile, 'wt')) {
            throw new RuntimeException("Cannot open '$outputFile' for writing. Use "
                . 'setTarget() in the definition to configure or set permissions.'
            );
        }

        $properties = array();
        foreach ($this->definition->getProperties() as $name => $property) {
            $properties[$name] = $property;
        }

        $this->append('Entity/open', array(
            'namespace'    => $this->getTargetNamespace(AbstractDefinition::ENTITY),
            'name'         => $this->definition->getName(),
            'aliases'      => $this->definition->getAliases(),
            'allProperties' => array_merge(array_combine(array_keys($properties), array_keys($properties)), $this->definition->getAliases()),
            'constants'    => $this->definition->getConstants(),
            'properties'   => $properties,
            'implementors' => $this->definition->getImplementors(),
            'init'         => $this->importMethods('init'),
            AbstractDefinition::FILTER       => $filter,
        ));

        foreach ($this->definition->getRegisteredMethods() as $method) {
            fputs($this->handle, $this->importMethod($method[0], $method[1], true) . PHP_EOL);
        }

        $this->append('Entity/close');

        fclose($this->handle);

        return $this;
    }

    /**
     * Compiles the entity filter target, an instance of
     * Zend\InputFilter\InputFilter.
     *
     * @return self
     */
    protected function compileFilter()
    {
        $outputFile = $this->getTargetFile(AbstractDefinition::FILTER);
        if (!$this->handle = fopen($outputFile, 'wt')) {
            throw new RuntimeException("Cannot open '$outputFile' for writing. Use "
                . 'setTarget() in the definition to configure or set permissions.'
            );
        }

        $v = array();
        foreach ($this->definition->getProperties() as $name => $property) {
            $v[$name] = get_class($property->getType());
        }

        $this->append('Filter/construct', array(
            'namespace'    => $this->getTargetNamespace(AbstractDefinition::FILTER),
            'name'         => $this->definition->getName(),
        ));

        foreach ($this->definition->getProperties() as $name => $property) {
            $validators = $property->getType()->getValidators();

            if ($extra = $property->getOption('validators')) {
                foreach ($validators as $index => $validator) {
                    foreach ($extra as $subIndex => $subValidator) {
                        if ($validator['name'] == $subValidator['name']) {
                            unset($validators[$index]);
                            break;
                        }
                    }
                }

                foreach ($extra as $subIndex => $subValidator) {
                    $validators[] = $subValidator;
                }

                $validators = array_merge(array(), $validators); // fix zero-indexing
            }

            $this->append('Filter/properties', array(
                'name'       => $name,
                'required'   => $property->getOption('required'),
                'filters'    => $property->getOption('filters'),
                'validators' => $validators,
            ));
        }

        $this->append('Filter/close');

        fclose($this->handle);

        return $this;
    }

    /**
     * Compiles the entity form target, an instance of
     * Zend\Form\Form.
     *
     * @return self
     */
    protected function compileForm($filter)
    {
        $outputFile = $this->getTargetFile(AbstractDefinition::FORM);
        if (!$this->handle = fopen($outputFile, 'wt')) {
            throw new RuntimeException("Cannot open '$outputFile' for writing. Use "
                . 'setTarget() in the definition to configure or set permissions.'
            );
        }

        $v = array();
        foreach ($this->definition->getProperties() as $name => $property) {
            $v[$name] = get_class($property->getType());
        }

        $this->append('Form/open', array(
            'namespace'    => $this->getTargetNamespace(AbstractDefinition::FORM),
            'name'         => $this->definition->getName(),
            'filter'       => $filter,
        ));

        foreach ($this->definition->getProperties() as $name => $property) {
            if (!$property->getOption('formType') && $property->getType() instanceof Type\ListType) {
                continue;
            }

            $attributes = $property->getOption('attributes') ?: array();
            $options    = $property->getOption('options') ?: array();
            $type       = $property->getOption('formType') ?: $property->getOption('type');

            if (!$type) {
                if ($property->getType() instanceof Type\EnumType) {
                    $type = 'Zend\Form\Element\Radio';
                } else {
                    $type = 'Zend\Form\Element\Text';
                }
            }

            $this->append('Form/property', array(
                'name'       => $name,
                'type'       => $type,
                'options'    => $options,
                'attributes' => $attributes,
            ));
        }

        $this->append('Form/close');

        fclose($this->handle);

        return $this;
    }
}
