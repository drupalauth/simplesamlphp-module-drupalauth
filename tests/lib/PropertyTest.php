<?php
/**
 * Created by PhpStorm.
 * User: kirill
 * Date: 6/09/18
 * Time: 4:09 PM
 */

use PHPUnit\Framework\TestCase;

class PropertyTest extends TestCase
{

    public function testSet()
    {
        $property = new Property('test');
        $property->set('value');
        $this->assertEquals('value', $property->test);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Property name mismatch.
     */
    public function testBadName()
    {
        $property = new Property('test');
        $test = $property->bad_name;
    }

    public function testName()
    {
        $property = new Property('test');
        $this->assertEquals(null, $property->test, 'Return value for proper name');
    }
}
