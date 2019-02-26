<?php

namespace TecnodesignTest;

use Swaggest\JsonSchema\Schema as SwaggestSchema;

require_once __DIR__ .'/../assets/models/SimpleModel.php';
require_once __DIR__ .'/../assets/models/ComplexModel.php';

class SchemaJsonTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {

    }

    public function testLoadSchema()
    {
        $class = new \ReflectionClass('Tecnodesign_Schema');
        //$schemaMetadata = $class->getStaticPropertyValue('meta');
        $schemaMetadata = $class->getStaticProperties()['meta'];

        foreach (['TecnodesignTest\\SimpleModel', 'TecnodesignTest\\ComplexModel'] as $className) {
            $originalSchema = $className::$schema;
            $schema = new \Tecnodesign_Schema(new $className());
            foreach ($schemaMetadata as $key => $definition) {
                $this->assertTrue(isset($schema[$key]), "\$schema has $key");
                if (array_key_exists($key, $originalSchema)) {
                    // There should be not alias
                    if ($definition['alias']) {
                        $this->assertNull($schema[$key], 'Alias should be null');
                        $key = $definition['alias'];
                    }
                    $this->assertEquals($originalSchema[$key], $schema[$key],
                        "\$schema $key is equal $className::\$schema");

                } elseif ($key === 'patternProperties') {
                    $this->assertEquals($schema[$key], ['/^_/' => ['type' => 'text']],
                        "\$schema $key has default value");
                } else {
                    $this->assertNull($schema[$key], "\$schema $key is null");
                }
            }
        }
    }

    public function testToJson()
    {
        $class = new \ReflectionClass('Tecnodesign_Schema');
        //$schemaMetadata = $class->getStaticPropertyValue('meta');
        $schemaMetadata = $class->getStaticProperties()['meta'];

        foreach (['TecnodesignTest\\SimpleModel', 'TecnodesignTest\\ComplexModel'] as $className) {
            $schema = new \Tecnodesign_Schema(new $className());
            $json = $schema->toJson();
            $jsonSchema = $schema->getJsonSchema();

            $this->assertInternalType('string', $json);
            $this->assertInternalType('string', $jsonSchema);

            $schemaValidate = SwaggestSchema::import(json_decode($jsonSchema));
            $schemaValidate->in((object)json_decode($json, JSON_OBJECT_AS_ARRAY));
        }
    }
}
