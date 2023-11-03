<?php
/**
 * Created by PhpStorm.
 * User: kirill
 * Date: 6/09/18
 * Time: 3:41 PM
 */

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    public function userDataProvider()
    {
        return [
            [
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
                    ],
                ],
                1
            ],
            [
                [
                    'field_1' => [
                        0 => [
                            'property_1' => 'property_1_value',
                            'property_2' => 'property_2_value',
                        ]
                    ],
                    'field_2' => [
                        0 => [
                            'value' => 'secret',
                        ]
                    ],
                    'field_3' => 'value',
                ],
                3
            ],
        ];
    }

    /**
     * @dataProvider userDataProvider
     */
    public function testUser($values, $count)
    {
        $user = new User($values);
        $this->assertEquals($count, count($user->getIterator()), 'Returned expected quantity of fields');
    }

    public function userHasFieldDataProvider()
    {
        return [
            [
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
                    ],
                ],
                [
                    'field_name' => true,
                    'missing_field' => false,
                ],
            ],
            [
                [
                    'field_1' => [
                        0 => [
                            'property_1' => 'property_1_value',
                            'property_2' => 'property_2_value',
                        ]
                    ],
                    'field_2' => [
                        0 => [
                            'value' => 'secret',
                        ]
                    ],
                    'field_3' => 'value',
                ],
                [
                    'field_1' => true,
                    'field_2' => true,
                    'field_3' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider userHasFieldDataProvider
     */
    public function testHasField($values, $checks)
    {
        $user = new User($values);
        foreach ($checks as $field_name => $result) {
            $this->assertEquals($result, $user->hasField($field_name), 'Field check returns expected result');
        }
    }

    /**
     * @dataProvider userHasFieldDataProvider
     */
    public function test__get($values, $checks)
    {
        $user = new User($values);
        foreach ($checks as $field_name => $result) {
            if ($result) {
                $this->assertNotEmpty($user->{$field_name}, 'Returned not empty value for field');
            } else {
                $this->assertEmpty($user->{$field_name}, 'Returned empty value for field');
            }
        }
    }
}
