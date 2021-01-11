<?php
/**
 * Created by PhpStorm.
 * User: kirill
 * Date: 6/09/18
 * Time: 3:31 PM
 */

use PHPUnit\Framework\TestCase;

class FieldListTest extends TestCase
{

    public function testGetException()
    {
        $this->expectException(InvalidArgumentException::class);
        $field = new FieldList();
        $field->get('sdfkjskljdf');
    }

    public function testGet()
    {
        $field = new FieldList();
        $this->assertEquals(null, $field->get(0), 'Null returned for the empty index.');
        $this->assertEquals(null, $field->get(1), 'Null returned for the empty index.');
    }

    public function testSetIndexException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to set a value with a non-numeric delta in a list.');
        $field = new FieldList();
        $field->set('bad_index', 'something');
    }

    public function testSetBadPropertiesException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Properties must be an array.');
        $field = new FieldList();
        $field->set(0, 'something');
    }

    public function testSetBadPropertyNameException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to set a value of a property with a non string name.');
        $field = new FieldList();
        $field->set(0, ['value']);
    }


    public function testSet()
    {
        $field = new FieldList();
        $this->assertEquals(null, $field->get(0), 'Null returned for the empty index.');
        $field->set(0, ['value' => 1]);
        $this->assertEquals(1, $field->get(0)->value, 'Returned expected property value');
    }

    public function testCount()
    {
        $field = new FieldList();
        $this->assertEquals(null, $field->get(0), 'Null returned for the empty index.');
        $field->set(0, ['value' => 1]);
        $this->assertEquals(1, $field->get(0)->value, 'Returned expected property value at index 0');
        $field->set(1, ['value' => 2]);
        $this->assertEquals(2, $field->get(1)->value, 'Returned expected property value at index 1');
        $this->assertEquals(2, $field->count(), 'Returned expected quantity');
    }

    public function testGetFieldDefinition()
    {
        $field = new FieldList();
        $this->assertEquals($field, $field->getFieldDefinition(), 'Returned $this for getFieldDefinition()');
    }

    public function testGetFieldStorageDefinition()
    {
        $field = new FieldList();
        $this->assertEquals($field, $field->getFieldStorageDefinition(), 'Returned $this for getFieldStorageDefinition()');
    }

    public function testGetPropertyDefinitions()
    {
        $field = new FieldList();
        $field->set(0, ['property_1' => 0, 'property_2' => true]);
        $field->set(1, ['property_1' => 0]);
        $field->set(2, ['property_2' => true]);

        $propertyDefinitions = $field->getPropertyDefinitions();
        $this->assertEquals(2, count($propertyDefinitions), 'Returned expected amount of property definitions');
        $this->assertTrue(array_key_exists('property_1', $propertyDefinitions), 'Returned definition for expected property 1');
        $this->assertTrue(is_a($propertyDefinitions['property_1'], PropertyDefinition::class), 'Returned definition for property 1');
        $this->assertTrue(array_key_exists('property_2', $propertyDefinitions), 'Returned definition for expected property 2');
        $this->assertTrue(is_a($propertyDefinitions['property_2'], PropertyDefinition::class), 'Returned definition for property 2');
    }
}
