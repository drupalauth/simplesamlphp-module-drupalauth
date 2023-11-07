<?php
/**
 * Created by PhpStorm.
 * User: kirill
 * Date: 6/09/18
 * Time: 4:31 PM
 */

class Field
{

    protected array $properties = [];

    public function __set($name, $value)
    {
        $this->properties[$name] = $value;
    }

    public function __get($name)
    {
        return $this->properties[$name] ?? null;
    }


    public function getProperties(): array
    {
        return array_keys($this->properties);
    }
}
