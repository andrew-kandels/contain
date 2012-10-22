<?php
// Generated by ZF2's ./bin/classmap_generator.php
return array(
    'Contain\Module'                                    => __DIR__ . '/Module.php',
    'Contain\AbstractQuery'                             => __DIR__ . '/src/Contain/AbstractQuery.php',
    'Contain\Entity\AbstractEntity'                     => __DIR__ . '/src/Contain/Entity/AbstractEntity.php',
    'Contain\Entity\Compiler\Compiler'                  => __DIR__ . '/src/Contain/Entity/Compiler/Compiler.php',
    'Contain\Entity\Definition\AbstractDefinition'      => __DIR__ . '/src/Contain/Entity/Definition/AbstractDefinition.php',
    'Contain\Entity\Definition\Setting'                 => __DIR__ . '/src/Contain/Entity/Definition/Setting.php',
    'Contain\Entity\Definition\Settings'                => __DIR__ . '/src/Contain/Entity/Definition/Settings.php',
    'Contain\Entity\Definition\Timestampable'           => __DIR__ . '/src/Contain/Entity/Definition/Timestampable.php',
    'Contain\Entity\EntityInterface'                    => __DIR__ . '/src/Contain/Entity/EntityInterface.php',
    'Contain\Entity\Exception\InvalidArgumentException' => __DIR__ . '/src/Contain/Entity/Exception/InvalidArgumentException.php',
    'Contain\Entity\Exception\RuntimeException'         => __DIR__ . '/src/Contain/Entity/Exception/RuntimeException.php',
    'Contain\Entity\Filter\Setting'                     => __DIR__ . '/src/Contain/Entity/Filter/Setting.php',
    'Contain\Entity\Form\Setting'                       => __DIR__ . '/src/Contain/Entity/Form/Setting.php',
    'Contain\Entity\Property\Property'                  => __DIR__ . '/src/Contain/Entity/Property/Property.php',
    'Contain\Entity\Property\Resolver'                  => __DIR__ . '/src/Contain/Entity/Property/Resolver.php',
    'Contain\Entity\Property\Type\BooleanType'          => __DIR__ . '/src/Contain/Entity/Property/Type/BooleanType.php',
    'Contain\Entity\Property\Type\DateTimeType'         => __DIR__ . '/src/Contain/Entity/Property/Type/DateTimeType.php',
    'Contain\Entity\Property\Type\DateType'             => __DIR__ . '/src/Contain/Entity/Property/Type/DateType.php',
    'Contain\Entity\Property\Type\DoubleType'           => __DIR__ . '/src/Contain/Entity/Property/Type/DoubleType.php',
    'Contain\Entity\Property\Type\EntityType'           => __DIR__ . '/src/Contain/Entity/Property/Type/EntityType.php',
    'Contain\Entity\Property\Type\EnumType'             => __DIR__ . '/src/Contain/Entity/Property/Type/EnumType.php',
    'Contain\Entity\Property\Type\IntegerType'          => __DIR__ . '/src/Contain/Entity/Property/Type/IntegerType.php',
    'Contain\Entity\Property\Type\ListType'             => __DIR__ . '/src/Contain/Entity/Property/Type/ListType.php',
    'Contain\Entity\Property\Type\MixedType'            => __DIR__ . '/src/Contain/Entity/Property/Type/MixedType.php',
    'Contain\Entity\Property\Type\StringType'           => __DIR__ . '/src/Contain/Entity/Property/Type/StringType.php',
    'Contain\Entity\Property\Type\TypeInterface'        => __DIR__ . '/src/Contain/Entity/Property/Type/TypeInterface.php',
    'Contain\Entity\Setting'                            => __DIR__ . '/src/Contain/Entity/Setting.php',
    'Contain\Mapper\Driver\ConnectionInterface'         => __DIR__ . '/src/Contain/Mapper/Driver/ConnectionInterface.php',
    'Contain\Mapper\Driver\DriverInterface'             => __DIR__ . '/src/Contain/Mapper/Driver/DriverInterface.php',
    'Contain\Mapper\Driver\File\Connection'             => __DIR__ . '/src/Contain/Mapper/Driver/File/Connection.php',
    'Contain\Mapper\Driver\File\File'                   => __DIR__ . '/src/Contain/Mapper/Driver/File/File.php',
    'Contain\Mapper\Driver\MongoDB\Connection'          => __DIR__ . '/src/Contain/Mapper/Driver/MongoDB/Connection.php',
    'Contain\Mapper\Driver\MongoDB\MongoDB'             => __DIR__ . '/src/Contain/Mapper/Driver/MongoDB/MongoDB.php',
    'Contain\Mapper\Exception\InvalidArgumentException' => __DIR__ . '/src/Contain/Mapper/Exception/InvalidArgumentException.php',
    'Contain\Mapper\Exception\RuntimeException'         => __DIR__ . '/src/Contain/Mapper/Exception/RuntimeException.php',
    'Contain\Service\AbstractService'                   => __DIR__ . '/src/Contain/Service/AbstractService.php',
    'Contain\Service\ServiceInterface'                  => __DIR__ . '/src/Contain/Service/ServiceInterface.php',
    'ContainTest\AbstractQueryTest'                     => __DIR__ . '/tests/Contain/AbstractQueryTest.php',
    'ContainTest\Entity\AbstractEntityTest'             => __DIR__ . '/tests/Contain/Entity/AbstractEntityTest.php',
    'ContainTest\Entity\Filter\SampleMultiTypeEntity'   => __DIR__ . '/tests/Contain/Entity/Filter/SampleMultiTypeEntity.php',
    'ContainTest\Entity\Property\PropertyTest'          => __DIR__ . '/tests/Contain/Entity/Property/PropertyTest.php',
    'ContainTest\Entity\Property\ResolverTest'          => __DIR__ . '/tests/Contain/Entity/Property/ResolverTest.php',
    'ContainTest\Entity\BooleanTypeTest'                => __DIR__ . '/tests/Contain/Entity/Property/Type/BooleanTypeTest.php',
    'ContainTest\Entity\DateTimeTypeTest'               => __DIR__ . '/tests/Contain/Entity/Property/Type/DateTimeTypeTest.php',
    'ContainTest\Entity\DateTypeTest'                   => __DIR__ . '/tests/Contain/Entity/Property/Type/DateTypeTest.php',
    'ContainTest\Entity\DoubleTypeTest'                 => __DIR__ . '/tests/Contain/Entity/Property/Type/DoubleTypeTest.php',
    'ContainTest\Entity\EntityTypeTest'                 => __DIR__ . '/tests/Contain/Entity/Property/Type/EntityTypeTest.php',
    'ContainTest\Entity\EnumTypeTest'                   => __DIR__ . '/tests/Contain/Entity/Property/Type/EnumTypeTest.php',
    'ContainTest\Entity\IntegerTypeTest'                => __DIR__ . '/tests/Contain/Entity/Property/Type/IntegerTypeTest.php',
    'ContainTest\Entity\ListTypeTest'                   => __DIR__ . '/tests/Contain/Entity/Property/Type/ListTypeTest.php',
    'ContainTest\Entity\MixedTypeTest'                  => __DIR__ . '/tests/Contain/Entity/Property/Type/MixedTypeTest.php',
    'ContainTest\Entity\StringTypeTest'                 => __DIR__ . '/tests/Contain/Entity/Property/Type/StringTypeTest.php',
    'ContainTest\Entity\SampleChildEntity'              => __DIR__ . '/tests/Contain/Entity/SampleChildEntity.php',
    'ContainTest\Entity\SampleEntity'                   => __DIR__ . '/tests/Contain/Entity/SampleEntity.php',
    'ContainTest\Entity\SampleEntityToString'           => __DIR__ . '/tests/Contain/Entity/SampleEntityToString.php',
    'ContainTest\Entity\SampleMultiTypeEntity'          => __DIR__ . '/tests/Contain/Entity/SampleMultiTypeEntity.php',
    'ContainTest\SampleQuery'                           => __DIR__ . '/tests/Contain/SampleQuery.php',
    'ContainTest\Service\AbstractServiceTest'           => __DIR__ . '/tests/Contain/Service/AbstractServiceTest.php',
    'ContainTest\Service\SampleService'                 => __DIR__ . '/tests/Contain/Service/SampleService.php',
);