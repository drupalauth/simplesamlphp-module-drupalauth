<?php
/**
 * Created by PhpStorm.
 * User: kirill
 * Date: 6/09/18
 * Time: 4:45 PM
 */

use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{

    public function testGetSet()
    {
        $field = new Field();
        $this->assertEquals(null, $field->random, 'Returns nothing for undefined property');

        $field->some_property = 'somevalue';
        $this->assertEquals('somevalue', $field->some_property, 'Returns expected property value');
    }

    public function testGetProperties()
    {
        $field = new Field();
        $properties = [
            'property_1' => '1',
            'property_2' => '2',
            'property_3' => '3',
        ];
        $expected = [
            'property_1',
            'property_2',
            'property_3',
        ];

        foreach ($properties as $name => $value) {
            $field->{$name} = $value;
        }

        $this->assertEquals($expected, $field->getProperties(), 'Returns expected list of property names');
    }
}
