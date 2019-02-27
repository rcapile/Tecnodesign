<?php

/**
 * Schema Builder/Parser
 *
 * This is an action for managing schemas for all available models
 *
 * PHP version 5.4
 *
 * @category  Ui
 * @package   Tecnodesign
 * @author    Guilherme Capilé, Tecnodesign <ti@tecnodz.com>
 * @copyright 2019 Tecnodesign
 * @license   https://creativecommons.org/licenses/by/3.0  CC BY 3.0
 * @link      https://tecnodz.com/
 */
class Tecnodesign_Schema implements ArrayAccess
{
    const JSON_SCHEMA_VERSION = 'draft-07';

    public static
        $errorInvalid = 'This is not a valid value for %s.',
        $errorInteger = 'An integer number is expected.',
        $errorMinorThan = '%s is less than the expected minimum %s',
        $errorGreaterThan = '%s is more than the expected maximum %s',
        $errorMandatory = '%s is mandatory and should not be a blank value.',
        $error;

    protected
        $database,
        $className,
        $tableName,
        $view,
        $properties,
        //$patternProperties = ['/^_/' => ['type' => 'text']],
        $overlay,
        $scope,
        $relations,
        $events,
        $orderBy,
        $groupBy;

    protected static $meta = [
        'database' => ['type' => 'string'],
        'className' => ['type' => 'string'],
        'tableName' => ['type' => 'string'],
        'view' => ['type' => 'string'],
        'properties' => ['type' => 'array'],
        //'patternProperties' => ['type' => 'array'],
        'overlay' => ['type' => 'array'],
        'scope' => ['type' => 'array'],
        'relations' => ['type' => 'array'],
        'events' => ['type' => 'array'],
        'orderBy' => ['type' => 'array'],
        'groupBy' => ['type' => 'array'],
        'columns' => ['alias' => 'properties'],
        'form' => ['alias' => 'overlay'],
    ];

    private static $schemaValidator = [
        '$schema' => 'http://json-schema.org/draft-07/schema',
        '$id' => 'http://tecnodz.com/schemas/model.json',
        'title' => 'Tecnodesign Model Schema',
        'type' => 'object',
        'required' => ['className'],
        'properties' => [
            'database' => ['type' => 'string'],
            'className' => ['type' => 'string'],
            'tableName' => ['type' => 'string'],
            'view' => ['type' => 'string'],
            'properties' => [
                'type' => 'array',
                'description' => 'Database definition',
                'items' => [
                    'type' => 'object',
                    'required' => ['id', 'type', 'null'],
                    'additionalProperties' => false,
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'type' => ['type' => 'string', 'enum' => ['int', 'string', 'date', 'datetime']],
                        'null' => ['type' => 'boolean'],
                        'default' => ['type' => ['number', 'string']],
                        'primary' => ['type' => 'boolean', 'enum' => [true]],
                        'increment' => ['type' => 'string', 'enum' => ['auto']],
                        'size' => ['type' => ['number', 'string']],
                        'min-size' => ['type' => ['number', 'string']],
                        'max' => ['type' => ['number', 'string']],
                    ],
                ]
            ]
        ],
        'overlay' => [
            'type' => 'array',
            'description' => 'Form definition',
            'items' => [
                'type' => 'object',
                'required' => ['id', 'bind'],
                'additionalProperties' => false,
                'properties' => [
                    'id' => ['type' => 'string'],
                    'bind' => ['type' => 'string'],
                    'type' => ['type' => 'string', 'enum' => ['select', 'radio', 'textarea']],
                    'null' => ['type' => 'boolean'],
                    'default' => ['type' => ['number', 'string']],
                    'primary' => ['type' => 'boolean', 'enum' => [true]],
                    'increment' => ['type' => 'string', 'enum' => ['auto']],
                    'size' => ['type' => ['number', 'string']],
                    'min-size' => ['type' => ['number', 'string']],
                    'max' => ['type' => ['number', 'string']],
                ],
            ],
        ],
        'scope' => [
            'type' => 'array',
            'description' => 'Scope?',
            'items' => ['type' => 'object']
        ],
        'relations' => [
            'type' => 'array',
            'description' => 'Relationships with other tables',
            'items' => [
                'type' => 'object',
                'required' => ['id', 'local', 'foreign', 'type', 'className'],
                'additionalProperties' => false,
                'properties' => [
                    'id' => ['type' => 'string'],
                    'local' => ['type' => ['string', 'array']],
                    'foreign' => ['type' => ['string', 'array']],
                    'type' => ['type' => 'string', 'enum' => ['one', 'many']],
                    'className' => ['type' => 'string'],
                    'params' => ['type' => ['array', 'object']],
                    'on' => ['type' => 'array'],
                ],
            ]
        ],
        'events' => [
            'type' => 'array',
            'description' => 'Events thrown',
            'items' => ['type' => 'object']
        ],
        'orderBy' => ['type' => 'array'],
        'groupBy' => ['type' => 'array'],
    ];

    public function __construct($default = null)
    {
        if ($default !== null) {
            if (is_array($default)) {
                static::apply($this, $default, static::$meta);
            } elseif ($default instanceof Tecnodesign_Model) {
                $this->loadSchema($default::$schema);
            } else {
                throw new \InvalidArgumentException('$default invalid');
            }
        }

    }

    /**
     * @param Tecnodesign_Model|array $model
     * @param array $values
     * @param array $metadata
     * @return array|bool|Tecnodesign_Model
     * @throws Tecnodesign_Exception
     */
    public static function apply($model, $values, $metadata = null)
    {
        if (is_object($model)) {
            if ($metadata === null && ($model instanceof Tecnodesign_Model)) {
                $metadata = $model::$schema['columns'];
            }
            $arr = [];
            $hasModel = true;
        } elseif (is_array($model)) {
            $arr = $model;
            $hasModel = false;
        } else {
            $hasModel = false;
            $arr = [];
        }

        if (!is_array($metadata)) {
            throw new \InvalidArgumentException('$metadata must be an array');
        }

        foreach ($values as $name => $value) {
            if ($metadata) {
                $i = 10;
                while (isset($metadata[$name]['alias']) && $i--) {
                    $name = $metadata[$name]['alias'];
                }
                unset($i);
                if (isset($metadata[$name])) {
                    $value = static::validateProperty($metadata[$name], $value, $name);
                }
            } else {
                unset($name, $value);
                continue;
            }

            if ($model !== false) {
                $model->$name = $value;
            } elseif ($arr) {
                $arr[$name] = $value;
            }

            /**
             * @todo necessary for PHP < 7
             */
            unset($name, $value);
        }

        return $hasModel ? $model : $arr;
    }

    /**
     * @param array $definition
     * @param mixed $value
     * @param string $label
     * @return false|int|string
     * @throws Tecnodesign_Exception
     */
    public static function validateProperty($definition, $value, $label = null)
    {
        $label = isset($definition['label'])
            ? $definition['label']
            : tdz::t(ucwords(str_replace('_', ' ', $label)), 'labels');

        $label = sprintf(tdz::t(static::$errorInvalid, 'exception'), $label);

        if (!isset($definition['type']) || $definition['type'] === 'string') {
            if (is_array($value)) {
                if (isset($definition['serialize'])) {
                    $value = tdz::serialize($value, $definition['serialize']);
                } else {
                    $value = tdz::implode($value);
                }
            } else {
                $value = (string)$value;
            }

            if (isset($definition['size']) && $definition['size'] && strlen($value) > $definition['size']) {
                $value = mb_strimwidth($value, 0, (int)$definition['size'], '', 'UTF-8');
            }

        } elseif ($definition['type'] === 'int') {
            if (!is_numeric($value) && $value !== '') {
                throw new Tecnodesign_Exception($label . ' ' . tdz::t(static::$errorInteger, 'exception'));
            }

            if (!tdz::isempty($value)) {
                $value = (int)$value;
            }

            if (isset($definition['min']) && $value < $definition['min']) {
                throw new Tecnodesign_Exception(
                    sprintf(tdz::t(static::$errorInvalid, 'exception'), $label)
                    . ' '
                    . sprintf(tdz::t(static::$errorMinorThan, 'exception'), $value, $definition['min']));
            }

            if (isset($definition['max']) && $value > $definition['max']) {
                throw new Tecnodesign_Exception($label . ' '
                    . sprintf(tdz::t(static::$errorGreaterThan, 'exception'), $value, $definition['max']));
            }

        } elseif (strpos($definition['type'], 'date') === 0) {
            if ($value) {
                $time = false;
                $d = false;
                if (!preg_match('/^\d{4}\-\d{2}\-\d{2}/', $value)) {
                    $format = tdz::$dateFormat;
                    if (strpos($definition['type'], 'datetime') === 0) {
                        $format .= ' ' . tdz::$timeFormat;
                        $time = true;
                    }
                    $d = date_parse_from_format($format, $value);
                }

                if ($d && !isset($d['errors'])) {
                    $value = str_pad((int)$d['year'], 4, '0', STR_PAD_LEFT)
                        . '-' . str_pad((int)$d['month'], 2, '0', STR_PAD_LEFT)
                        . '-' . str_pad((int)$d['day'], 2, '0', STR_PAD_LEFT);
                    if ($time) {
                        $value .= ' ' . str_pad((int)$d['hour'], 2, '0', STR_PAD_LEFT)
                            . ':' . str_pad((int)$d['minute'], 2, '0', STR_PAD_LEFT)
                            . ':' . str_pad((int)$d['second'], 2, '0', STR_PAD_LEFT);
                    }
                } elseif ($d = strtotime($value)) {
                    $value = (strpos($definition['type'], 'datetime') === 0)
                        ? date('Y-m-d H:i:s', $d)
                        : date('Y-m-d', $d);
                }
            }
        }

        // @TODO: write other validators
        if (($value === '' || $value === null) && isset($definition['default'])) {
            $value = $definition['default'];
        }

        if (($value === '' || $value === null) && isset($definition['null']) && !$definition['null']) {
            throw new Tecnodesign_Exception(sprintf(tdz::t(static::$errorMandatory, 'exception'), $label));
        }

        if ($value === '') {
            $value = false;
        }

        return $value;
    }

    public function uid($expand = false)
    {
        //return $this->properties(null, false, array('primary'=>true), $expand);
        $r = array();
        foreach ($this->properties as $n => $d) {
            if ($d && isset($d['primary']) && $d['primary']) {
                if ($expand) {
                    $r[$n] = $d;
                } else {
                    $r[] = $n;
                }
            }
            unset($n, $d);
        }
        return $r;
    }

    public function properties($scope = null, $overlay = false, $filter = null, $expand = 10, $add = array())
    {
        $R = array();
        if (is_string($scope)) {
            if (isset($this->scope[$scope])) {
                $scope = $this->scope[$scope];
            } else {
                return $R;
            }
        } else {
            if (!$scope) {
                $scope = $this->properties;
            }
        }
        if (!$scope || !is_array($scope)) {
            return $R;
        }

        if (!is_array($add)) {
            $add = array();
        }
        if (isset($scope['__default'])) {
            $add = $scope['__default'] + $add;
            unset($scope['__default']);
        }
        foreach ($scope as $n => $def) {
            $base = $add;
            $ref = $this;

            if (is_string($def)) {
                if (preg_match('/^([a-z0-9\-\_]+)::([a-z0-9\-\_\,]+)(:[a-z0-9\-\_\,\!]+)?$/i', $def, $m)) {
                    if (isset($m[3])) {
                        if (!isset($U)) {
                            $U = tdz::getUser();
                        }
                        if (!$U || !$U->hasCredential(preg_split('/[\,\:]+/', $m[3], null, PREG_SPLIT_NO_EMPTY),
                                false)) {
                            continue;
                        }
                    }
                    if ($m[1] == 'scope' && $expand) {
                        $R = array_merge($R, $ref->properties($m[2], $overlay, $filter, $expand--, $add));
                    }
                    unset($base, $n, $def);
                    continue;
                } else {
                    if (substr($def, 0, 2) == '--' && substr($def, -2) == '--') {
                        $add['fieldset'] = substr($def, 2, strlen($def) - 4);
                        unset($base, $n, $def);
                        continue;
                    } else {
                        $base['bind'] = $def;
                        if (preg_match('/^([^\s\`]+)(\s+as)?\s+[a-zA-Z0-9\_\-]+$/', $def, $m)) {
                            $def = $m[1];
                        }

                        while (strpos($def, '.') !== false) {
                            list($rn, $def) = explode('.', $def, 2);
                            if (isset($ref->relations[$rn])) {
                                $cn = (isset($ref->relations[$rn]['className'])) ? ($ref->relations[$rn]['className']) : ($rn);
                                $ref = $cn::schema($cn, array('className' => $cn), true);
                            } else {
                                $def = null;
                                break;
                            }
                        }
                        if ($def !== null && isset($ref->properties[$def])) {
                            $def = $ref->properties[$def];
                            $i = 10;
                            while (isset($def['alias'])) {
                                if (!isset($ref->properties[$def['alias']])) {
                                    $def = array();
                                    break;
                                } else {
                                    $def = $def['alias'];
                                    $i--;
                                }
                            }
                            unset($i);
                        } else {
                            $def = array('type' => 'string', 'null' => true);
                        }
                    }
                }
            }

            /*
            if(!is_int($n)) $base['label'] = $n;

            if(is_array($def) && isset($def['bind'])) $n = $def['bind'];
            else if(isset($base['bind'])) $n = $base['bind'];
            else if(is_string($def)) $n = $def;
            */
            if (is_int($n)) {
                if (is_array($def) && isset($def['bind'])) {
                    $n = $def['bind'];
                } else {
                    if (isset($base['bind'])) {
                        $n = $base['bind'];
                    } else {
                        if (is_string($def)) {
                            $n = $def;
                        }
                    }
                }
            }

            if (strpos($n, ' ')) {
                $n = substr($n, strrpos($n, ' ') + 1);
            }

            if ($ref->patternProperties) {
                foreach ($ref->patternProperties as $re => $addDef) {
                    if (preg_match($re, $n)) {
                        if (!is_string($def)) {
                            $def = $addDef;
                        } else {
                            $base += $addDef;
                        }
                    }
                    unset($re, $addDef);
                }
            }

            if (is_array($def)) {
                if ($base) {
                    $def += $base;
                }
                if ($overlay && isset($def['bind'])) {
                    if (isset($ref->overlay[$n])) {
                        $def = $ref->overlay[$n] + $def;
                    }
                }
                if (isset($def['credential'])) {
                    if (!isset($U)) {
                        $U = tdz::getUser();
                    }
                    if (!$U || !$U->hasCredentials($def['credential'], false)) {
                        $def = null;
                    }
                }

                if ($def) {
                    if ($filter) {
                        foreach ($filter as $p => $value) {
                            if (!isset($def[$p]) || $def[$p] != $value || (is_array($value) && !in_array($def[$p],
                                        $value))) {
                                $def = null;
                                break;
                            }
                        }
                    }

                    if ($def) {
                        $R[$n] = $def;
                    }
                }
            }
            unset($base, $n, $def, $ref);
        }

        if ($R && $expand === false) {
            $r = array();
            foreach ($R as $n => $d) {
                if (isset($d['bind'])) {
                    $r[$n] = $d['bind'];
                }
                unset($n, $d);
            }
            return $r;
        }
        return $R;
    }

    public function toJsonSchema($scope = null, &$R = array())
    {
        // available scopes might form full definitions (?)
        $fo = $this->properties($scope);
        $cn = $this->className;

        if (!is_array($R)) {
            $R += array(
                '$schema' => 'http://json-schema.org/draft-07/schema#',
                '$id' => tdz::buildUrl($this->link() . $qs),
                'title' => (isset($this->text['title'])) ? ($this->text['title']) : ($cn::label()),
            );
        }
        $R += array('type' => 'object', 'properties' => array(), 'required' => array());

        $types = array(
            'bool' => 'boolean',
            'array' => 'object',
            'form' => 'object',
            'integer' => 'integer',
            'number' => 'number',
        );

        $properties = array(
            'label' => 'title',
            'description' => 'description',
            'placeholder' => 'description',
            'default' => 'default',
            'readonly' => 'readOnly',
        );

        foreach ($fo as $fn => $fd) {
            $bind = (isset($fd['bind'])) ? ($fd['bind']) : ($fn);
            if ($p = strrpos($bind, ' ')) {
                $bind = substr($bind, $p + 1);
            }
            if (isset($cn::$schema['columns'][$bind])) {
                $fd += $cn::$schema['columns'][$bind];
            }
            $type = (isset($fd['type']) && isset($types[$fd['type']])) ? ($types[$fd['type']]) : ('string');
            if (isset($fd['multiple']) && $fd['multiple']) {
                if (isset($fd['type']) && $fd['type'] == 'array') {
                    $type = 'array';
                } else {
                    $type = array($type, 'array');
                }
            }
            $R['properties'][$fn] = array(
                'type' => $type,
            );

            foreach ($properties as $n => $v) {
                if (isset($fd[$n]) && !isset($R['properties'][$fn][$n])) {
                    $R['properties'][$fn][$n] = $fd[$n];
                }
                unset($n, $v);
            }
            if (isset($fd['null']) && !$fd['null']) {
                $R['required'][] = $fn;
            }

            if (!is_array($type) && method_exists($this, $m = '_jsonSchema' . ucfirst($type))) {
                $this->$m($fd, $R['properties'][$fn]);
            }

            if (isset($fd['choices']) && is_array($fd['choices'])) {
                $R['properties'][$fn]['enum'] = array_keys($fd['choices']);
            }
        }

        return $R;
    }

    /**
     * @return string
     */
    public function getJsonSchemaValidator()
    {
        return json_encode(static::$schemaValidator, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return string
     */
    public function toJson()
    {
        $currentClass = new $this->className;
        if (! $currentClass instanceof Tecnodesign_Model) {
            throw new \RuntimeException("$currentClass must be Tecnodesign_Model");
        }

        $json = [
            '$schema' => 'http://json-schema.org/draft-07/schema#',
            '$id' => '1234',//tdz::buildUrl($this->link() . $qs),
        ];

        foreach (self::$meta as $key => $definition) {
            if (isset($definition['alias'])) {
                continue;
            }

            if (empty($this->$key)) {
                continue;
            }

            $json[$key] = $this->$key;
        }

        // Fix named arrays
        foreach (['properties', 'relations', 'scope', 'overlay'] as $key) {
            if (isset($json[$key])) {
                foreach ($json[$key] as $id => $values) {
                    $json[$key][$id] = ['id' => $id] + $json[$key][$id];
                }
                $json[$key] = array_values($json[$key]);
            }
        }

        return json_encode($json,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $json
     * @return array
     */
    public function fromJson($json)
    {
        if (!is_string($json)) {
            throw new \InvalidArgumentException('$json must be a string');
        }

        $json = json_decode($json, JSON_OBJECT_AS_ARRAY);

        unset($json['$schema'], $json['$id']);

        foreach (['properties', 'relations', 'scope', 'overlay'] as $key) {
            if (isset($json[$key])) {
                $keys = array_keys($json[$key]);
                foreach ($keys as $k) {
                    $id = $json[$key][$k]['id'];
                    unset($json[$key][$k]['id']);
                    $json[$key][$id] = $json[$key][$k];
                    unset($json[$key][$k]);
                }
            }
        }

        return $json;
    }


    protected function _jsonSchemaInteger($fd, &$R = array())
    {
        return $this->_jsonSchemaNumber($fd, $R);
    }

    protected function _jsonSchemaNumber($fd, &$R = array())
    {
        if (isset($fd['min_size'])) {
            $R['minimum'] = $fd['min_size'];
        }
        if (isset($fd['size'])) {
            $R['maximum'] = $fd['size'];
        }
        // exclusiveMaximum
        // exclusiveMinimum
        // multipleOf
    }

    protected function _jsonSchemaString($fd, &$R = array())
    {
        if (isset($fd['min_size'])) {
            $R['minLength'] = $fd['min_size'];
        }
        if (isset($fd['size'])) {
            $R['maxLength'] = $fd['size'];
        }
        // pattern

        static $format = array(
            'date' => 'date',
            'datetime' => 'date-time',
            'time' => 'time',
            'email' => 'email',
            'ipv4' => 'ipv4',
            'ipv6' => 'ipv6',
            'url' => 'uri',
        );
        if (isset($fd['type']) && isset($format[$fd['type']])) {
            $R['format'] = $format[$fd['type']];
        }
    }

    protected static function _jsonSchemaArray($fd, &$R = array())
    {
        if (isset($fd['scope'])) {
            $R['items'] = $this->toJsonSchema($fd['scope'], $R);
        }
        // additionalItems
        // pattern
        if (isset($fd['min_size'])) {
            $R['minItems'] = $fd['min_size'];
        }
        if (isset($fd['size'])) {
            $R['maxItems'] = $fd['size'];
        }
        // uniqueItems
        // contains
    }

    protected static function _jsonSchemaObject($fd, &$R = array())
    {
        if (isset($fd['scope'])) {
            $R = $this->toJsonSchema($fd['scope'], $R);
        }
        // maxProperties
        // minProperties
        // patternProperties
        // additionalProperties
        // dependencies
        // propertyNames
    }

    /**
     * ArrayAccess abstract method. Gets stored parameters.
     *
     * @param string $name parameter name, should start with lowercase
     *
     * @return mixed the stored value, or method results
     */
    public function &offsetGet($name)
    {
        if (isset(static::$meta[$name]['alias'])) {
            $name = static::$meta[$name]['alias'];
        }

        if (method_exists($this, $m = 'get' . ucfirst(tdz::camelize($name)))) {
            return $this->$m();
        }

        if (isset($this->$name)) {
            return $this->$name;
        }

        /**
         * It's silly but PHP 5.4, 5.5 and 5.6 throws a notice:
         * Only variable references should be returned by reference
         */
        $returnNull = null;
        return $returnNull;
    }

    /**
     * ArrayAccess abstract method. Sets parameters to the PDF.
     *
     * @param string $name parameter name, should start with lowercase
     * @param mixed $value value to be set
     *
     * @return Tecnodesign_Schema
     * @throws Tecnodesign_Exception
     */
    public function offsetSet($name, $value)
    {
        if (isset(static::$meta[$name]['alias'])) {
            $name = static::$meta[$name]['alias'];
        }
        if (method_exists($this, $m = 'set' . tdz::camelize($name))) {
            $this->$m($value);
            unset($m);
        } elseif (!property_exists($this, $name)) {
            throw new Tecnodesign_Exception(array(
                tdz::t('Column "%s" is not available at %s.', 'exception'),
                $name,
                get_class($this)
            ));
        } else {
            $this->$name = $value;
        }

        return $this;
    }

    /**
     * ArrayAccess abstract method. Searches for stored parameters.
     *
     * @param string $name parameter name, should start with lowercase
     *
     * @return bool true if the parameter exists, or false otherwise
     */
    public function offsetExists($name)
    {
        if (isset(static::$meta[$name]['alias'])) {
            $name = static::$meta[$name]['alias'];
        }

        return isset($this->$name);
        // It should use property_exists because array_key_exists will fail with null values
        //return property_exists($this, $name);
    }

    /**
     * ArrayAccess abstract method. Unsets parameters to the PDF. Not yet implemented
     * to the PDF classes — only unsets values stored in $_vars
     *
     * @param string $name parameter name, should start with lowercase
     * @return Tecnodesign_Schema
     *
     * @throws Tecnodesign_Exception
     */
    public function offsetUnset($name)
    {
        if (isset(static::$meta[$name]['alias'])) {
            $name = static::$meta[$name]['alias'];
        }
        return $this->offsetSet($name, null);
    }

    private function loadSchema($schema)
    {
        foreach (self::$meta as $key => $definition) {
            $correctKey = $key;
            if (isset(static::$meta[$correctKey]['alias'])) {
                $correctKey = static::$meta[$correctKey]['alias'];
            }
            if (isset($schema[$key])) {
                $this->$correctKey = $schema[$key];
            }
        }
    }
}
