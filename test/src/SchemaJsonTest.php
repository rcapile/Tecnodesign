<?php
/**
 * References
 * @see https://json-schema.org/understanding-json-schema/index.html
 */
namespace TecnodesignTest;

use Exception;
use Swaggest\JsonSchema\Exception\TypeException;
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
            $classSchema = $className::$schema;
            $loadedSchema = new \Tecnodesign_Schema(new $className());

            // Checks for who has an alias
            $definedAlias = [];
            foreach ($schemaMetadata as $key => $definition) {
                if (isset($definition['alias'])) {
                    $definedAlias[$definition['alias']] = $key;
                }
            }

            foreach ($schemaMetadata as $key => $definition) {
                if (array_key_exists($key, $classSchema)) {
                    if (isset($definition['alias'])) {
                        $this->assertNotNull($loadedSchema[$key], "$className::\$schema $key alias should exist");
                        $this->assertNotNull($loadedSchema[$definition['alias']], "$className::\$schema $key alias {$definition['alias']} should exist");
                        $this->assertEquals($loadedSchema[$key], $loadedSchema[$definition['alias']], "$className::\$schema $key and alias {$definition['alias']} should be equals");
                        continue;
                    }

                    $this->assertTrue(isset($loadedSchema[$key]), "$className::\$schema should has $key");
                    $this->assertEquals($classSchema[$key], $loadedSchema[$key],
                        "\$schema $key is equal $className::\$schema");
                } elseif ($key === 'patternProperties') {
                    $this->assertEquals($loadedSchema[$key], ['/^_/' => ['type' => 'text']],
                        "\$schema $key has default value");
                } else {
                    // If it has an alias, the asserts are made above
                    if (isset($definedAlias[$key])) {
                        continue;
                    }
                    $value=$loadedSchema[$key];
                    $this->assertNull($loadedSchema[$key], "\$schema $key is null");
                }
            }
        }
    }

    public function testToJsonSchema()
    {
        $class = new \ReflectionClass('Tecnodesign_Schema');
        //$schemaMetadata = $class->getStaticPropertyValue('meta');
        $schemaMetadata = $class->getStaticProperties()['meta'];

        // remove the alias
        foreach ($schemaMetadata as $key => $definition) {
            if (isset($definition['alias'])) {
                unset($schemaMetadata[$key]);
            }
        }

        $validMetadataKeys = array_merge(['$schema', '$id'], array_keys($schemaMetadata));
        foreach (['TecnodesignTest\\SimpleModel', 'TecnodesignTest\\ComplexModel'] as $className) {
            $loadedSchema = new \Tecnodesign_Schema(new $className());

            $jsonSchema = $loadedSchema->toJson();
            $jsonSchemaValidator = $loadedSchema->getJsonSchema();

            $this->assertInternalType('string', $jsonSchema);
            $this->assertInternalType('string', $jsonSchemaValidator);

            $jsonSchemaValidator = json_decode($jsonSchemaValidator);
            $jsonSchema = json_decode($jsonSchema);

            // There can be only allowed metadata keys
            $this->assertEmpty(array_diff(array_keys((array)$jsonSchema), $validMetadataKeys));
            $this->assertEmpty(array_diff(array_keys((array)$jsonSchemaValidator->properties), $validMetadataKeys));

            $schemaValidate = SwaggestSchema::import($jsonSchemaValidator);
            $schemaValidate->in($jsonSchema);

            // Make invalid schema
            $jsonSchema->database = false;
            try {
                $schemaValidate->in($jsonSchema);
            } catch (TypeException $exception) {
                $this->assertEquals($exception->getMessage(), 'String expected, false received at #->properties:database');
                continue;
            } catch (Exception $e) {
                throw $e;
            }
            $this->fail("Invalid schema validate");
        }
    }
}
