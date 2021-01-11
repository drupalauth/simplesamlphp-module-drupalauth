<?php

use PHPUnit\Framework\TestCase;
use SimpleSAML\Module\drupalauth\DrupalHelper;

class DrupalHelperTest extends TestCase
{

    /**
     * @var PHPUnit\Framework\MockObject\MockObject
     */
    protected $stub;

    /**
     * @var ReflectionClass
     */
    protected $class;

    protected function setUp(): void
    {

        $this->stub = $this->createMock(DrupalHelper::class);
        $this->class = new \ReflectionClass($this->stub);
    }


    public function getPropertyNameProvider()
    {
        return [
            [[], 'value'],
            [['field_property' => 'target_id'], 'target_id'],
            [['field_property' => 'arbitrary_name'], 'arbitrary_name'],
        ];
    }

    /**
     * @dataProvider getPropertyNameProvider
     */
    public function testGetPropertyName($attribute_definition, $expected_property_name)
    {
        $method = $this->class->getMethod('getPropertyName');
        $method->setAccessible(true);

        $property_name = $method->invokeArgs($this->stub, [$attribute_definition]);
        $this->assertEquals($expected_property_name, $property_name, 'Expected property name returned');
    }


    public function getAttributeNameProvider()
    {
        return [
            [['field_name' => 'some_field'], 'some_field:value'],
            [['field_name' => 'some_field', 'field_index' => 0], 'some_field:0:value'],
            [['field_name' => 'some_field', 'field_property' => 'target_id'], 'some_field:target_id'],
            [['field_name' => 'some_field', 'field_property' => 'target_id', 'field_index' => 0], 'some_field:0:target_id'],
            [['field_name' => 'some_field', 'field_property' => 'target_id', 'field_index' => 0, 'attribute_name' => ''], 'some_field:0:target_id'],
            [['field_name' => 'some_field', 'field_property' => 'target_id', 'field_index' => 0, 'attribute_name' => 'someAttr'], 'someAttr'],
            [['field_name' => 'some_field', 'field_property' => 'target_id', 'attribute_name' => 'someAttr'], 'someAttr'],
            [['field_name' => 'some_field', 'attribute_name' => 'someAttr'], 'someAttr'],
        ];
    }

    /**
     * @dataProvider getAttributeNameProvider
     */
    public function testGetAttributeName($attribute_definition, $expected_attribute_name)
    {
        $method = $this->class->getMethod('getAttributeName');
        $method->setAccessible(true);

        $attribute_name = $method->invokeArgs($this->stub, [$attribute_definition]);

        $this->assertEquals($expected_attribute_name, $attribute_name, 'Expected attribute name returned');
    }

    public function getAllAttributesDataProvider()
    {
        return [
            // Set #0.
            [
                // Values
                [
                    'field_name' => [
                        0 => [
                            'property_1' => 'property_0_1_value',
                            'property_2' => 'property_0_2_value',
                        ],
                        1 => [
                            'property_1' => 'property_1_1_value',
                            'property_2' => '',
                        ],
                        2 => [
                            'property_1' => null,
                            'property_2' => 'property_2_2_value',
                        ],
                    ],
                ],
                // Forbidden attributes.
                [],
                // Expected attributes.
                [
                    'field_name:0:property_1' => ['property_0_1_value'],
                    'field_name:0:property_2' => ['property_0_2_value'],
                    'field_name:1:property_1' => ['property_1_1_value'],
                    'field_name:2:property_2' => ['property_2_2_value'],
                ],
            ],
            // Set #1.
            [
                // Values
                [
                    'field_name' => [
                        0 => [
                            'property_1' => 'property_1_value',
                            'property_2' => 'property_2_value',
                        ]
                    ],
                    'forbidden_field' => [
                        0 => [
                            'value' => 'secret',
                        ]
                    ],
                ],
                // Forbidden attributes.
                ['forbidden_field'],
                // Expected attributes.
                [
                    'field_name:0:property_1' => ['property_1_value'],
                    'field_name:0:property_2' => ['property_2_value'],
                ],
            ],
            // Set #2.
            [
                // Values
                [
                    'field_name' => [
                        0 => [
                            'property_1' => 'property_1_value',
                            'property_2' => 'property_2_value',
                        ]
                    ],
                    'field_name_2' => [
                        0 => [
                            'property_0_1' => 'property_0_1_value',
                            'property_0_2' => 'property_0_2_value',
                        ],
                        1 => [
                            'property_1_1' => 'property_1_1_value',
                            'property_1_2' => 'property_1_2_value',
                        ]
                    ],
                ],
                // Forbidden attributes.
                [],
                // Expected attributes.
                [
                    'field_name:0:property_1' => ['property_1_value'],
                    'field_name:0:property_2' => ['property_2_value'],
                    'field_name_2:0:property_0_1' => ['property_0_1_value'],
                    'field_name_2:0:property_0_2' => ['property_0_2_value'],
                    'field_name_2:1:property_1_1' => ['property_1_1_value'],
                    'field_name_2:1:property_1_2' => ['property_1_2_value'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getAllAttributesDataProvider
     */
    public function testGetAllAttributes($values, $forbidden_attributes, $expected_attributes)
    {
        $method = $this->class->getMethod('getAllAttributes');
        $method->setAccessible(true);

        $user = new User($values);

        $attributes = $method->invokeArgs($this->stub, [$user, $forbidden_attributes]);

        $this->assertEquals($expected_attributes, $attributes, 'Expected attributes returned');
    }

    public function getAttributesDataProvider()
    {
        $field_values = [
            'field_name' => [
                0 => [
                    'property_1' => 'property_1_value',
                    'property_2' => 'property_2_value',
                ]
            ],
            'field_name_2' => [
                0 => [
                    'property_2_1' => 'property_2_1_0_value',
                    'property_2_2' => 'property_2_2_0_value',
                ],
                1 => [
                    'property_2_1' => 'property_2_1_1_value',
                    'property_2_2' => 'property_2_2_1_value',
                ],
            ],
        ];

        return [
            // Set #0.
            [
                // Values
                $field_values,
                // Requested attributes.
                [],
                // Expected attributes.
                [
                    'field_name:0:property_1' => ['property_1_value'],
                    'field_name:0:property_2' => ['property_2_value'],
                    'field_name_2:0:property_2_1' => ['property_2_1_0_value'],
                    'field_name_2:1:property_2_1' => ['property_2_1_1_value'],
                    'field_name_2:0:property_2_2' => ['property_2_2_0_value'],
                    'field_name_2:1:property_2_2' => ['property_2_2_1_value'],
                ],
            ],
            // Set #1.
            [
                // Values
                $field_values,
                // Requested attributes.
                [
                    ['field_name' => 'forbidden_field'],
                    ['field_name' => 'field_name'],
                    ['field_name' => 'field_name', 'field_property' => 'property_1'],
                    ['field_name' => 'field_name', 'field_property' => 'property_1', 'field_index' => 1],
                    ['field_name' => 'field_name', 'field_property' => 'property_1', 'field_index' => 1, 'attribute_name' => 'someAttr'],
                ],
                // Expected attributes.
                [
                    'field_name:property_1' => ['property_1_value'],
                ],
            ],
            // Set #2.
            [
                // Values
                $field_values,
                // Requested attributes.
                [
                    ['field_name' => 'field_name', 'field_property' => 'property_2'],
                    ['field_name' => 'field_name_2', 'field_property' => 'property_2_1'],
                    ['field_name' => 'field_name_2', 'field_property' => 'property_2_1', 'attribute_name' => 'someAttr'],
                    ['field_name' => 'field_name_2', 'field_property' => 'property_2_2', 'field_index' => 1],
                ],
                // Expected attributes.
                [
                    'field_name:property_2' => ['property_2_value'],
                    'field_name_2:property_2_1' => ['property_2_1_0_value', 'property_2_1_1_value'],
                    'someAttr' => ['property_2_1_0_value', 'property_2_1_1_value'],
                    'field_name_2:1:property_2_2' => ['property_2_2_1_value'],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getAttributesDataProvider
     */
    public function testGetAttributes($values, $requested_attributes, $expected_attributes)
    {
        $user = new User($values);
        $dh = new DrupalHelper();
        $attributes = $dh->getAttributes($user, $requested_attributes);

        $this->assertEquals($expected_attributes, $attributes, 'Expected attributes returned');
    }
}
