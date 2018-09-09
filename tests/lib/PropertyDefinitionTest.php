<?php
/**
 * Created by PhpStorm.
 * User: kirill
 * Date: 6/09/18
 * Time: 4:09 PM
 */

use PHPUnit\Framework\TestCase;

class PropertyDefinitionTest extends TestCase
{

    public function testPropertyDefinition()
    {
        $property = new PropertyDefinition();
        $this->assertFalse($property->isComputed(), 'Returns false for isComputed check.');
        $this->assertFalse($property->isInternal(), 'Returns false for isInternal check.');
    }
}
