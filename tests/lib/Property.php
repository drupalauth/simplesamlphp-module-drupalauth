<?php
/**
 * Created by PhpStorm.
 * User: kirill
 * Date: 6/09/18
 * Time: 4:07 PM
 */

class Property
{
    protected $name;
    protected $value;

    /**
     * Property constructor.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function set($value)
    {
        $this->value = $value;
    }


    public function __get($name)
    {
        if ($this->name !== $name) {
            throw new \Exception('Property name mismatch.');
        }

        return $this->value;
    }
}
