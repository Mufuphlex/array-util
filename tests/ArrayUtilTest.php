<?php

use Mufuphlex\Util\ArrayUtil;

class ArrayUtilTest extends \PHPUnit\Framework\TestCase
{
    public function testWhitelistSimpleCase()
    {
        $map = array(
            'key' => array(
                'value' => array(
                    'low level' => true
                )
            )
        );

        $array = array(
            'key' => array(
                'value' => array(
                    'low level' => 123,
                    'another low level' => 456
                ),
                'another value' => array(
                    'low level' => 789,
                    'another low level' => 'string'
                )
            ),
            'another key' => array(
                'value' => array(
                    'low level' => 1234,
                    'another low level' => 4567
                ),
                'another value' => array(
                    'low level' => 7890,
                    'another low level' => 'another string'
                )
            )
        );

        $cutByWhitelist = ArrayUtil::cutByWhitelist($array, $map);

        $this->assertEquals(
            array(
                'key' => array(
                    'value' => array(
                        'low level' => 123
                    )
                )
            ),
            $cutByWhitelist
        );
    }

    public function testWhitelistRegexCase()
    {
        $map = array(
            '/^(?:one|another)$/' => array(
                '/\d+/' => array(
                    'low level' => true
                )
            )
        );

        $array = array(
            'one another' => array(
                1 => array(
                    'low level' => 'unexpected due to regex'
                ),
                'not numeric' => array(
                    'low level' => 'unexpected due to not numeric',
                ),
                2 => array(
                    'low level' => 'unexpected due to regex as well',
                ),
            ),
            'one' => array(
                3 => array(
                    'the first low level' => 'unexpected due to key',
                    'low level' => 'low level in one - 1'
                ),
                'not numeric' => array(
                    'low level' => 'unexpected due to not numeric',
                    'another low level' => 'unexpected in one'
                ),
                4 => array(
                    'low level' => 'low level in one - 2',
                    'the second low level' => 'unexpected due to key',
                )
            ),
            'another' => array(
                5 => array(
                    'the first low level' => 'unexpected due to key',
                    'low level' => 'low level in another - 1'
                ),
                'not numeric' => array(
                    'low level' => 'unexpected due to not numeric',
                    'another low level' => 'unexpected in another'
                ),
                6 => array(
                    'low level' => 'low level in another - 2',
                    'the second low level' => 'unexpected due to key',
                )
            )
        );

        $cutByWhitelist = ArrayUtil::cutByWhitelist($array, $map);

        $this->assertEquals(
            array(
                'one' => array(
                    3 => array(
                        'low level' => 'low level in one - 1'
                    ),
                    4 => array(
                        'low level' => 'low level in one - 2'
                    )
                ),
                'another' => array(
                    5 => array(
                        'low level' => 'low level in another - 1'
                    ),
                    6 => array(
                        'low level' => 'low level in another - 2'
                    )
                )
            ),
            $cutByWhitelist
        );
    }

    public function testWhitelistClosureCase()
    {
        $map = array(
            0 => array(
                '/\d+/' => function ($arg) {
                    return $arg*2+1;
                }
            )
        );

        $array = array(
            array(
                0,
                1,
                2,
                'internal string key' => 'unexpected internal content'
            ),
            'string key' => 'unexpected content'
        );

        $cutByWhitelist = ArrayUtil::cutByWhitelist($array, $map);

        $this->assertEquals(
            array(
                array(
                    1,
                    3,
                    5
                )
            ),
            $cutByWhitelist
        );

        $map = array(
            0 => function ($arg) {
                return $arg+2;
            }
        );

        $array = array(
            'not numeric' => 1,
            0 => 2
        );

        $cutByWhitelist = ArrayUtil::cutByWhitelist($array, $map);

        $this->assertEquals(
            array(
                0 => 4
            ),
            $cutByWhitelist
        );
    }

    public function testBlackListSimpleCase()
    {
        $map = array(
            'string' => array(
                2 => true
            )
        );

        $array = array(
            array(
                2 => array(
                    'expected'
                )
            ),
            'string' => array(
                'expected',
                2 => 'unexpected'
            )
        );

        $cut = ArrayUtil::cutByBlacklist($array, $map);

        $this->assertEquals(
            array(
                array(
                    2 => array(
                        'expected'
                    )
                ),
                'string' => array(
                    'expected'
                )
            ),
            $cut
        );
    }

    public function testBlackListRegexCase()
    {
        $map = array(
            'protocol' => array(
                '/https?/' => true
            )
        );

        $array = array(
            'not protocol' => array(
                'http' => 'expected',
                'https' => 'expected too',
                'ftp' => 'expected as well'
            ),
            'protocol' => array(
                'http' => 'unexpected',
                'https' => 'unexpected too',
                'ftp' => 'expected'
            ),
        );

        $cut = ArrayUtil::cutByBlacklist($array, $map);

        $this->assertEquals(
            array(
                'not protocol' => array(
                    'http' => 'expected',
                    'https' => 'expected too',
                    'ftp' => 'expected as well'
                ),
                'protocol' => array(
                    'ftp' => 'expected'
                )
            ),
            $cut
        );
    }

    public function testBlacklistClosureCase()
    {
        $map = array(
            '/^\d+$/' => array(
                1 => function ($arg) {
                    return $arg+5;
                }
            )
        );

        $array = array(
            'string' => 'expected',
            array(
                1,
                2
            ),
            array(
                3,
                4
            )
        );

        $cut = ArrayUtil::cutByBlacklist($array, $map);

        $this->assertEquals(
            array(
                'string' => 'expected',
                array(
                    1,
                    7
                ),
                array(
                    3,
                    9
                )
            ),
            $cut
        );

        $map = array(
            '/^\d+$/' => function ($arg) {
                return $arg+2;
            },
            'not numeric' => true
        );

        $array = array(
            'not numeric' => 1,
            0 => 2
        );

        $cut = ArrayUtil::cutByBlacklist($array, $map);

        $this->assertEquals(
            array(
                0 => 4
            ),
            $cut
        );
    }

    public function testUnique()
    {
        $array = array(1,2,3,4,5,1,2,3,1,2,1);
        $unique = array(1,2,3,4,5);
        $this->assertEquals($unique, ArrayUtil::unique($array));
        $array = array('a','b','a');
        $unique = array('a', 'b');
        $this->assertEquals($unique, ArrayUtil::unique($array));
    }

    public function testUniqueKeepingKey()
    {
        $array = array(
            'a' => 1,
            'b' => 2,
            'c' => 1,
            'd' => 2,
            'e' => 1,
            'f' => 3
        );

        $unique = array(
            'a' => 1,
            'b' => 2,
            'f' => 3
        );

        $this->assertEquals($unique, ArrayUtil::unique($array, true));
    }

    public function testIntersect()
    {
        $a = array(1,2,3);
        $b = array(3,4,5);
        $expected = array(2 => 3);
        $this->assertEquals($expected, ArrayUtil::intersect($a, $b));

        $a = array(1,2,3,4);
        $b = array(3,4,5,6);
        $c = array(4,5,6,7);
        $expected = array(3 => 4);
        $this->assertEquals($expected, ArrayUtil::intersect($a, $b, $c));
    }

    public function testIntersectThrowsExceptionOnlyOneArgument(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('At least 2 arrays must be passed');
        $a = array(1,2,3);
        ArrayUtil::intersect($a);
    }

    public function testIntersectThrowsExceptionArgumentsMustBeArrays(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All the arguments must be array');
        $a = array(1,2,3);
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
        $res2 = array();
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
