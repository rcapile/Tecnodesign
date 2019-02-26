<?php

namespace TecnodesignTest;

require_once __DIR__ .'/../assets/models/SimpleModel.php';
require_once __DIR__ .'/../assets/models/ComplexModel.php';

class SchemaJsonTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {

    }

    public function testLoadSchemaSimple()
    {
        $originalSchema = \SimpleModel::$schema;
        $schema = new \Tecnodesign_Schema(new \SimpleModel);
        $class = new \ReflectionClass('Tecnodesign_Schema');
        //$schemaMetadata = $class->getStaticPropertyValue('meta');
        $schemaMetadata = $class->getStaticProperties()['meta'];

        foreach ($schemaMetadata as $key=>$definition) {
            $this->assertTrue(isset($schema[$key]), "\$schema has $key");
            if (array_key_exists($key, $originalSchema)) {
                // There should be not alias
                if ($definition['alias']) {
                   $this->assertNull($schema[$key], 'Alias should be null');
                   $key = $definition['alias'];
                }
                $this->assertEquals(\SimpleModel::$schema[$key], $schema[$key],
                    "\$schema $key is equal SimpleModel::\$schema");

            } elseif($key === 'patternProperties') {
                $this->assertEquals($schema[$key], ['/^_/' => ['type' => 'text']], "\$schema $key has default value");
            } else{
                $this->assertNull($schema[$key], "\$schema $key is null");
            }
        }
    }
}
