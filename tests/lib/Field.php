<?php
/**
 * Created by PhpStorm.
 * User: kirill
 * Date: 6/09/18
 * Time: 4:31 PM
 */

class Field
{

    protected $properties = [];

    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }

    public function __get($name)
    {
        return isset($this->properties[$name]) ? $this->properties[$name] : null;
    }


    public function getProperties()
    {
        return array_keys($this->properties);
    }
}
