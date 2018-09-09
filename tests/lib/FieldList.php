<?php
/**
 * Created by PhpStorm.
 * User: kirill
 * Date: 6/09/18
 * Time: 3:12 PM
 */

class FieldList
{
    protected $list = [];

    public function get($index)
    {
        if (!is_int($index)) {
            throw new \InvalidArgumentException('Unable to get a value with a non-numeric delta in a list.');
        }

        return isset($this->list[$index]) ? $this->list[$index] : null;
    }

    public function set($index, $properties)
    {
        if (!is_int($index)) {
            throw new \InvalidArgumentException('Unable to set a value with a non-numeric delta in a list.');
        }

        if (!is_array($properties)) {
            throw new \InvalidArgumentException('Properties must be an array.');
        }

        if (!isset($this->list[$index])) {
            $this->list[$index] = new Field();
        }

        foreach ($properties as $property_name => $property_value) {
            if (!is_string($property_name)) {
                throw new \InvalidArgumentException('Unable to set a value of a property with a non string name.');
            }

            $this->list[$index]->{$property_name} = $property_value;
        }
    }

    public function count()
    {
        return count($this->list);
    }

    public function getFieldDefinition()
    {
        return $this;
    }

    public function getFieldStorageDefinition()
    {
        return $this;
    }

    public function getPropertyDefinitions()
    {
        $definition = new PropertyDefinition();
        $definitions = [];
        foreach ($this->list as $field) {
            foreach ($field->getProperties() as $property) {
                $definitions[$property] = $definition;
            }
        }

        return $definitions;
    }
}
