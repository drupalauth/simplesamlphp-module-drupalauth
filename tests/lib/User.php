<?php

class User implements IteratorAggregate
{
    protected $fields = [];

    /**
     * User constructor.
     *
     * @param array $values
     *   Each key corresponds to field name. Each value either scalar or array. Scalar would would be treated as index 0
     *   value of 'value' property. In case of array
     */
    public function __construct($values)
    {
        foreach ($values as $field_name => $field_value) {
            $this->fields[$field_name] = new FieldList();
            if (is_scalar($field_value)) {
                $this->fields[$field_name]->set(0, ['value' => $field_value]);
            } elseif (is_array($field_value)) {
                foreach ($field_value as $index => $properties) {
                    $this->fields[$field_name]->set($index, $properties);
                }
            }
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->fields);
    }

    public function hasField($field_name)
    {
            return isset($this->fields[$field_name]);
    }

    public function __get($field_name)
    {
        return isset($this->fields[$field_name]) ? $this->fields[$field_name] : null;
    }
}
