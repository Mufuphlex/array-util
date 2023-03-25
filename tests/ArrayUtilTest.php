<?php

use Mufuphlex\Util\ArrayUtil;

class ArrayUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testWhitelistSimpleCase()
    {
        $map = [
            'key' => [
                'value' => [
                    'low level' => true,
                ],
            ],
        ];

        $array = [
            'key' => [
                'value' => [
                    'low level' => 123,
                    'another low level' => 456,
                ],
                'another value' => [
                    'low level' => 789,
                    'another low level' => 'string',
                ],
            ],
            'another key' => [
                'value' => [
                    'low level' => 1234,
                    'another low level' => 4567,
                ],
                'another value' => [
                    'low level' => 7890,
                    'another low level' => 'another string',
                ],
            ],
        ];

        $cutByWhitelist = ArrayUtil::cutByWhitelist($array, $map);

        $this->assertEquals(
            [
                'key' => [
                    'value' => [
                        'low level' => 123,
                    ],
                ],
            ],
            $cutByWhitelist
        );
    }

    public function testWhitelistRegexCase()
    {
        $map = [
            '/^(?:one|another)$/' => [
                '/\d+/' => [
                    'low level' => true,
                ],
            ],
        ];

        $array = [
            'one another' => [
                1 => [
                    'low level' => 'unexpected due to regex',
                ],
                'not numeric' => [
                    'low level' => 'unexpected due to not numeric',
                ],
                2 => [
                    'low level' => 'unexpected due to regex as well',
                ],
            ],
            'one' => [
                3 => [
                    'the first low level' => 'unexpected due to key',
                    'low level' => 'low level in one - 1',
                ],
                'not numeric' => [
                    'low level' => 'unexpected due to not numeric',
                    'another low level' => 'unexpected in one',
                ],
                4 => [
                    'low level' => 'low level in one - 2',
                    'the second low level' => 'unexpected due to key',
                ],
            ],
            'another' => [
                5 => [
                    'the first low level' => 'unexpected due to key',
                    'low level' => 'low level in another - 1',
                ],
                'not numeric' => [
                    'low level' => 'unexpected due to not numeric',
                    'another low level' => 'unexpected in another',
                ],
                6 => [
                    'low level' => 'low level in another - 2',
                    'the second low level' => 'unexpected due to key',
                ],
            ],
        ];

        $cutByWhitelist = ArrayUtil::cutByWhitelist($array, $map);

        $this->assertEquals(
            [
                'one' => [
                    3 => [
                        'low level' => 'low level in one - 1',
                    ],
                    4 => [
                        'low level' => 'low level in one - 2',
                    ],
                ],
                'another' => [
                    5 => [
                        'low level' => 'low level in another - 1',
                    ],
                    6 => [
                        'low level' => 'low level in another - 2',
                    ],
                ],
            ],
            $cutByWhitelist
        );
    }

    public function testWhitelistClosureCase()
    {
        $map = [
            0 => [
                '/\d+/' => function ($arg) {
                    return $arg*2+1;
                },
            ],
        ];

        $array = [
            [
                0,
                1,
                2,
                'internal string key' => 'unexpected internal content',
            ],
            'string key' => 'unexpected content',
        ];

        $cutByWhitelist = ArrayUtil::cutByWhitelist($array, $map);

        $this->assertEquals(
            [
                [
                    1,
                    3,
                    5,
                ],
            ],
            $cutByWhitelist
        );

        $map = [
            0 => function ($arg) {
                return $arg+2;
            },
        ];

        $array = [
            'not numeric' => 1,
            0 => 2,
        ];

        $cutByWhitelist = ArrayUtil::cutByWhitelist($array, $map);

        $this->assertEquals(
            [
                0 => 4,
            ],
            $cutByWhitelist
        );
    }

    public function testBlackListSimpleCase()
    {
        $map = [
            'string' => [
                2 => true,
            ],
        ];

        $array = [
            [
                2 => [
                    'expected',
                ],
            ],
            'string' => [
                'expected',
                2 => 'unexpected',
            ],
        ];

        $cut = ArrayUtil::cutByBlacklist($array, $map);

        $this->assertEquals(
            [
                [
                    2 => [
                        'expected',
                    ],
                ],
                'string' => [
                    'expected',
                ],
            ],
            $cut
        );
    }

    public function testBlackListRegexCase()
    {
        $map = [
            'protocol' => [
                '/https?/' => true,
            ],
        ];

        $array = [
            'not protocol' => [
                'http' => 'expected',
                'https' => 'expected too',
                'ftp' => 'expected as well',
            ],
            'protocol' => [
                'http' => 'unexpected',
                'https' => 'unexpected too',
                'ftp' => 'expected',
            ],
        ];

        $cut = ArrayUtil::cutByBlacklist($array, $map);

        $this->assertEquals(
            [
                'not protocol' => [
                    'http' => 'expected',
                    'https' => 'expected too',
                    'ftp' => 'expected as well',
                ],
                'protocol' => [
                    'ftp' => 'expected',
                ],
            ],
            $cut
        );
    }

    public function testBlacklistClosureCase()
    {
        $map = [
            '/^\d+$/' => [
                1 => function ($arg) {
                    return $arg+5;
                },
            ],
        ];

        $array = [
            'string' => 'expected',
            [
                1,
                2,
            ],
            [
                3,
                4,
            ],
        ];

        $cut = ArrayUtil::cutByBlacklist($array, $map);

        $this->assertEquals(
            [
                'string' => 'expected',
                [
                    1,
                    7,
                ],
                [
                    3,
                    9,
                ],
            ],
            $cut
        );

        $map = [
            '/^\d+$/' => function ($arg) {
                return $arg+2;
            },
            'not numeric' => true,
        ];

        $array = [
            'not numeric' => 1,
            0 => 2,
        ];

        $cut = ArrayUtil::cutByBlacklist($array, $map);

        $this->assertEquals(
            [
                0 => 4,
            ],
            $cut
        );
    }

    public function testUnique()
    {
        $array = [1,2,3,4,5,1,2,3,1,2,1];
        $unique = [1,2,3,4,5];
        $this->assertEquals($unique, ArrayUtil::unique($array));
        $array = ['a','b','a'];
        $unique = ['a', 'b'];
        $this->assertEquals($unique, ArrayUtil::unique($array));
    }

    public function testUniqueKeepingKey()
    {
        $array = [
            'a' => 1,
            'b' => 2,
            'c' => 1,
            'd' => 2,
            'e' => 1,
            'f' => 3,
        ];

        $unique = [
            'a' => 1,
            'b' => 2,
            'f' => 3,
        ];

        $this->assertEquals($unique, ArrayUtil::unique($array, true));
    }

    public function testIntersect()
    {
        $a = [1,2,3];
        $b = [3,4,5];
        $expected = [2 => 3];
        $this->assertEquals($expected, ArrayUtil::intersect($a, $b));

        $a = [1,2,3,4];
        $b = [3,4,5,6];
        $c = [4,5,6,7];
        $expected = [3 => 4];
        $this->assertEquals($expected, ArrayUtil::intersect($a, $b, $c));
    }

    public function testIntersectThrowsExceptionOnlyOneArgument(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least 2 arrays must be passed');
        $a = [1,2,3];
        ArrayUtil::intersect($a);
    }

    public function testIntersectThrowsExceptionArgumentsMustBeArrays(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All the arguments must be array');
        $a = [1,2,3];
        $b = 123;
        ArrayUtil::intersect($a, $b);
    }

    public function testPerformanceUnique()
    {
        $this->expectNotToPerformAssertions();

        $max = 100000;
        $arr = range(1, $max, 3);
        $arr2 = range(1, $max, 2);
        $arr = array_merge($arr, $arr2);

        $time = -microtime(true);
        $res1 = array_unique($arr);
        $time += microtime(true);
        echo "\nstd array_unique:\t".count($res1)." in ".$time;

        $time = -microtime(true);
        $res2 = [];
        foreach ($arr as $key=>$val) {
            $res2[$val] = true;
        }
        $res2 = array_keys($res2);
        $time += microtime(true);
        echo "\nvia std array_keys:\t".count($res2)." in ".$time;

        $time = -microtime(true);
        $res3 = ArrayUtil::unique($arr);
        $time += microtime(true);
        echo "\nmufuphlex unique:\t".count($res3)." in ".$time;

        $time = -microtime(true);
        $res4 = ArrayUtil::unique($arr, true);
        $time += microtime(true);
        echo "\npreserving keys:\t".count($res4)." in ".$time;
    }

    public function testPerformanceIntersect()
    {
        $this->expectNotToPerformAssertions();

        $max = 100000;
        $arr = range(1, $max, 3);
        $arr2 = range(1, $max, 2);

        $time = -microtime(true);
        $res1 = array_intersect($arr, $arr2);
        $time += microtime(true);
        echo "\nstd array_intersect:\t".count($res1)." in ".$time;

        $time = -microtime(true);
        $res2 = ArrayUtil::intersect($arr, $arr2);
        $time += microtime(true);
        echo "\nmufuphlex intersect:\t".count($res2)." in ".$time;
    }
}
