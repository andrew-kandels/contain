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
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://andrewkandels.com/contain
 */

namespace Contain\Entity\Compiler;

use Contain\Entity\Definition\AbstractDefinition;
use Contain\Exception\RuntimeException;
use Contain\Exception\InvalidArgumentException;
use ReflectionMethod;

/**
 * Compiles an entity definition into a entity class.
 *
 * @category    akandels
 * @package     contain
 * @copyright   Copyright (c) 2012 Andrew P. Kandels (http://andrewkandels.com)
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Compiler
{
    /** 
     * @var Contain\Entity\Definition\AbstractDefinition
     */
    protected $definition;

    /**
     * @var resource
     */
    protected $handle;

    /**
     * Sets the definition file in which to compile.
     *
     * @param   Contain\Entity\Definition\AbstractDefinition|string
     * @return  $this
     */
    public function setDefinition($definition)
    {
        if (!$definition instanceof Contain\Entity\Definition\AbstractDefinition) {
            if (!is_subclass_of($definition, 'Contain\Entity\Definition\AbstractDefinition')) {
                throw new InvalidArgumentException('$definition must either be an instance of Contain\Entity\Definition\AbstractDefinition '
                    . 'or the name of a class that is.'
                );
            }

            $definition = new $definition();
        }

        $this->definition = $definition;
        $this->targetPath = $definition->getTargetPath();
    }

    /**
     * Returns the full path to the target entity class file.
     *
     * @return  string
     */
    public function getTargetFile()
    {
        return $this->targetPath . '/' . $this->definition->getName() . '.php';
    }

    /**
     * Returns the namespace for the entity class.
     *
     * @return  string
     */
    public function getNamespace()
    {
        $path = $this->targetPath;
        if (preg_match('!module/([^/]+)/src/(.*)!', $path, $matches)) {
            $path = $matches[2];
        }

        return str_replace('/', '\\', $path);
    }

    /**
     * Renders a template and writes the output to the active file
     * handle.
     *
     * @param   string                  Template name
     * @param   array                   View variables
     * @return  $this
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
     * @return  $this
     */
    public function compile($definition)
    {
        $this->setDefinition($definition);

        $outputFile = $this->getTargetFile();
        if (!$this->handle = fopen($outputFile, 'wt')) {
            throw new RuntimeException("Cannot open '$outputFile' for writing.");
        }

        $v = array();
        foreach ($this->definition as $property) {
            $v[$property->getName()] = get_class($property->getType());
        }

        $this->append('base', array(
            'hasEvents'    => $this->definition->hasEvents(),
            'namespace'    => $this->getNamespace(),
            'hasExtended'  => $this->definition->hasExtended(),
            'name'         => $this->definition->getName(),
            'v'            => $v,
            'hasIteration' => $this->definition->hasIteration(),
            'implementors' => $this->definition->getImplementors(),
            'extends'      => $this->definition->getParentClass(),
        ));

        foreach ($this->definition as $property) {
            $this->append('constructProperty', array(
                'property'  => $property,
                'options'   => $property->getType()->serialize(),
            ));
        }

        $this->append('constructClose', array(
            'hasEvents'   => $this->definition->hasEvents(),
            'hasExtended' => $this->definition->hasExtended(),
            'name'        => $this->definition->getName(),
            'init'        => $this->importMethods('init'),
        ));

        $v = array();
        foreach ($this->definition as $property) {
            $v[] = $property->getName();
        }

        $this->append('toFromArray', array(
            'hasExtended' => $this->definition->hasExtended(),
            'v'           => $v,
            'name'        => $this->definition->getName(),
        ));

        foreach ($this->definition as $property) {
            $type = get_class($property->getType());

            if (preg_match('!([A-Za-z]+)Type$!', $type, $matches)) {
                $type = strtolower($matches[1]);
            }

            $this->append('accessors', array(
                'property'  => $property,
                'type'      => $type,
                'hasEvents' => $this->definition->hasEvents(),
            ));
        }

        if ($this->definition->hasIteration()) {
            $this->append('iterator');
        }

        foreach ($this->definition->getRegisteredMethods() as $method) {
            fputs($this->handle, $this->importMethod($method[0], $method[1], true) . PHP_EOL);
        }

        fputs($this->handle, '}');

        fclose($this->handle);

        return $this;
    }


}
