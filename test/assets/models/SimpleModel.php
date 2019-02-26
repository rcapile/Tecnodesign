<?php
namespace TecnodesignTest;

class SimpleModel extends \Tecnodesign_Model
{
    public static $schema = [
        'database' => 'firstwww',
        'tableName' => 'countries',
        'className' => 'TecnodesignTest\\SimpleModel',
        'columns' => [
            'id' => ['type' => 'string', 'size' => '2', 'min-size' => '2', 'null' => false, 'primary' => true,],
            'country' => ['type' => 'string', 'size' => '100', 'null' => false,],
            'region' => ['type' => 'string', 'size' => '100', 'null' => false,],
        ],
        'relations' => [
            'CountryIso' => [
                'local' => 'id',
                'foreign' => 'id',
                'type' => 'one',
                'className' => 'Tecnodesign\\CountryIso',
            ],
        ],
        'scope' => [
            'string' => ['country'],
            'choices' => ['country'],
        ],
        'events' => [],
        'order' => ['country' => 'asc'],
        'form' => [
            'id' => ['bind' => 'id',],
            'country' => ['bind' => 'country',],
            'region' => ['bind' => 'region',],
        ],
    ];
}
