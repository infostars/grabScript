<?php

namespace greevex\gss\objects;

use greevex\gss\lib\error;

class Call
    extends _objectBase
{

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var array
     */
    private $arguments;

    public function __construct()
    {
        $arguments = func_get_args();
        $this->callable = array_shift($arguments);
        if(!is_callable($this->callable)) {
            error::throwNewCompileException('Parameter for object Call is not callable');
        }
        $this->arguments = $arguments;
    }

    public function execute()
    {
        $result = null;

        $callable =& $this->callable;

        switch(count($this->arguments)) {
            case 1:
                $result = $callable($this->arguments[0]);
                break;
            case 2:
                $result = $callable($this->arguments[0], $this->arguments[1]);
                break;
            case 3:
                $result = $callable($this->arguments[0], $this->arguments[1], $this->arguments[2]);
                break;
            case 4:
                $result = $callable($this->arguments[0], $this->arguments[1], $this->arguments[2], $this->arguments[3]);
                break;
            default:
                $result = call_user_func_array($callable, $this->arguments);
                break;
        }

        return $result;
    }
}