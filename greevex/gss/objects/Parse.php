<?php

namespace greevex\gss\objects;

use greevex\gss\lib\error;

class Parse
    extends _objectBase
{

    /**
     * @return self
     */
    public static function factory()
    {
        $arguments = func_get_args();
        $driverName = array_shift($arguments);
        static $instances = [];
        if(!isset($instances[$driverName])) {
            switch(count($arguments)) {
                case 1:
                    $instances[$driverName] = new self($arguments[0]);
                    break;
                case 2:
                    $instances[$driverName] = new self($arguments[0], $arguments[1]);
                    break;
                case 3:
                    $instances[$driverName] = new self($arguments[0], $arguments[1], $arguments[2]);
                    break;
                case 4:
                    $instances[$driverName] = new self($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
                    break;
                case 5:
                    $instances[$driverName] = new self($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
                    break;
                default:
                    error::throwNewCompileException('Expected 5 params max for Parse');
                    break;
            }
        }

        return $instances[$driverName];
    }

    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function execute()
    {
        $result = [
            'data' => [
                'status' => true,
                'items' => [],
                'links' => []
            ]
        ];

        return json_decode(json_encode($result, JSON_FORCE_OBJECT));
    }
}