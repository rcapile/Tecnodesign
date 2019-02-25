<?php

namespace TecnodesignTest;

class Tecnodesign_SchemaTest extends \PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $schema = new \Tecnodesign_Schema();
        $this->assertArrayNotHasKey('test', $schema);
        $schema = new \Tecnodesign_Schema(['test' => 'yes']);
        $this->assertArrayHasKey('test', $schema);
        $this->assertEquals('yes', $schema['test']);

        $schema = new \Tecnodesign_Schema(['test' => '']);
        $this->assertArrayHasKey('test', $schema);
        $this->assertEquals('', $schema['test']);
    }

    public function testValidatePropertyString()
    {
        $stringValues = [
            ['', false],// it's wrong?! waiting for G input
            [false, false],// it's wrong?! waiting for G input
            [null, false],// it's wrong?! waiting for G input
            [0, '0'],
            [123.12, '123.12'],
            ['áéíóúi', 'áéíóúi'],
            [['one', 'two', 'three'], 'one,two,three']
        ];

        foreach ($stringValues as $values) {
            list($actual, $expected) = $values;
            $actualValidated = \Tecnodesign_Schema::validateProperty([], $actual);
            $this->assertEquals($expected, $actualValidated, 'Value ' . var_export($actual, true) . ' failed');
            if (!in_array($actual, ['', false, null], true)) {
                $this->assertInternalType('string', $actualValidated, 'Value ' . var_export($actual, true) . ' failed');
            }
            $actualValidated = \Tecnodesign_Schema::validateProperty(['type' => 'string'], $actual);
            $this->assertEquals($expected, $actualValidated, 'Value ' . var_export($actual, true) . ' failed');
            if (!in_array($actual, ['', false, null], true)) {
                $this->assertInternalType('string', $actualValidated, 'Value ' . var_export($actual, true) . ' failed');
            }
        }
    }

    public function testValidatePropertyInteger()
    {
        $stringValues = [
            ['', false],// it's wrong?! waiting for G input
            [false, 'exception'],// it's wrong?! waiting for G input
            [null, 'exception'],// it's wrong?! waiting for G input
            [0, 0],
            [123.12, 123],
            ['áéíóúi', 'exception'],
            [['one', 'two', 'three'], 'exception']
        ];

        foreach ($stringValues as $values) {
            list($actual, $expected) = $values;
            if ($expected !== 'exception') {
                $actualValidated = \Tecnodesign_Schema::validateProperty(['type' => 'int'], $actual);
                $this->assertEquals($expected, $actualValidated, 'Value ' . var_export($actual, true) . ' failed');
                if ($actual !== '') {
                    $this->assertInternalType('int', $actualValidated,
                        'Value ' . var_export($actual, true) . ' failed');
                }
                continue;
            }

            /**
             * Test the exceptions
             */

            // With no label
            try {
                \Tecnodesign_Schema::validateProperty(['type' => 'int'], $actual);
            } catch (\Tecnodesign_Exception $exception) {
                $this->assertEquals($exception->getMessage(),
                    'This is not a valid value for . An integer number is expected.');
            }

            // With a label at definition
            try {
                \Tecnodesign_Schema::validateProperty(['type' => 'int', 'label' => 'new_label'], $actual);
            } catch (\Tecnodesign_Exception $exception) {
                $this->assertEquals($exception->getMessage(),
                    'This is not a valid value for new_label. An integer number is expected.');
            }

            // With a label at params
            try {
                \Tecnodesign_Schema::validateProperty(['type' => 'int'], $actual, 'new_label');
            } catch (\Tecnodesign_Exception $exception) {
                $this->assertEquals($exception->getMessage(),
                    'This is not a valid value for New Label. An integer number is expected.');
            }

            // With a label at definition and params
            try {
                \Tecnodesign_Schema::validateProperty(['type' => 'int', 'label' => 'new_label'], $actual, 'new_label');
            } catch (\Tecnodesign_Exception $exception) {
                $this->assertEquals($exception->getMessage(),
                    'This is not a valid value for new_label. An integer number is expected.');
            }

        }
    }
}
