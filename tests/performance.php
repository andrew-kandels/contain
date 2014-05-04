#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';
include(__DIR__ . '/Performance/Suite.php');

$suite = new ContainTest\Performance\Suite();
$class = new ReflectionClass('ContainTest\Performance\Suite');
foreach ($class->getMethods() as $method) {
    $name = $method->getName();
    if ($method->isPublic() && preg_match('/^test/', $name)) {
        $suite->$name();
    }
}
