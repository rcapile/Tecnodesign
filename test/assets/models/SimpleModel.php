<?php

class SimpleModel extends \Tecnodesign_Model
{
    public static $schema = array(
        'database' => 'firstwww',
        'tableName' => 'countries',
        'className' => 'Tecnodesign\\Countries',
        'columns' => array(
            'id' => array('type' => 'string', 'size' => '2', 'min-size' => '2', 'null' => false, 'primary' => true,),
            'country' => array('type' => 'string', 'size' => '100', 'null' => false,),
            'region' => array('type' => 'string', 'size' => '100', 'null' => false,),
        ),
        'relations' => array(
            'CountryIso' => array(
                'local' => 'id',
                'foreign' => 'id',
                'type' => 'one',
                'className' => 'Tecnodesign\\CountryIso',
            ),
        ),
        'scope' => array(
            'string' => array('country'),
            'choices' => array('country'),
        ),
        'events' => array(),
        'order' => array('country' => 'asc'),
        'form' => array(
            'id' => array('bind' => 'id',),
            'country' => array('bind' => 'country',),
            'region' => array('bind' => 'region',),
        ),
    );
}
