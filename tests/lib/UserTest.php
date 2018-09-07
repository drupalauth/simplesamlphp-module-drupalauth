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

}
