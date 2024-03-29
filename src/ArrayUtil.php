<?php

namespace Mufuphlex\Util;

class ArrayUtil
{
    public static function cutByWhitelist(array &$array, array $map): array
    {
        foreach ($array as $key => $value) {
            if (isset($map[$key])) {
                if (is_array($map[$key]) and is_array($value)) {
                    $array[$key] = self::cutByWhitelist($value, $map[$key]);
                } elseif ($map[$key] instanceof \Closure) {
                    $array[$key] = $map[$key]($value);
                }
            } else {
                $unset = true;
                $mapKeys = array_keys($map);

                foreach ($mapKeys as $mapKey) {
                    if (preg_match('@^/.+/[siu]*$@', $mapKey) and preg_match($mapKey, $key)) {
                        if (is_array($map[$mapKey])) {
                            $array[$key] = self::cutByWhitelist($value, $map[$mapKey]);
                        } elseif ($map[$mapKey] instanceof \Closure) {
                            $array[$key] = $map[$mapKey]($value);
                        }
                        $unset = false;
                        break;
                    }
                }

                if ($unset) {
                    unset($array[$key]);
                }
            }
        }

        return $array;
    }

    public static function cutByBlacklist(array &$array, array $map): array
    {
        $mapKeys = array_keys($map);

        foreach ($array as $key => $value) {
            $unset = false;

            if (isset($map[$key])) {
                if (is_array($map[$key])) {
                    $array[$key] = self::cutByBlacklist($value, $map[$key]);
                } elseif ($map[$key] instanceof \Closure) {
                    $array[$key] = $map[$key]($value);
                } else {
                    $unset = true;
                }
            } else {
                foreach ($mapKeys as $mapKey) {
                    if (preg_match('@^/.+/[siu]*$@', $mapKey) and preg_match($mapKey, $key)) {
                        if (is_array($map[$mapKey])) {
                            $array[$key] = self::cutByBlacklist($value, $map[$mapKey]);
                        } elseif ($map[$mapKey] instanceof \Closure) {
                            $array[$key] = $map[$mapKey]($value);
                        } else {
                            $unset = true;
                        }
                        break;
                    }
                }
            }

            if ($unset) {
                unset($array[$key]);
            }
        }

        return $array;
    }

    public static function unique(array $array, bool $keepKeys = false): array
    {
        if ($keepKeys) {
            $array = array_reverse($array, true);
        }

        $flip = array_flip($array);

        if (!$keepKeys) {
            return array_keys($flip);
        }

        return array_flip($flip);
    }

    public static function intersect(array $array): array
    {
        if (($argsCnt = func_num_args()) < 2) {
            throw new \InvalidArgumentException('At least 2 arrays must be passed');
        }

        $array2 = func_get_arg(1);

        if (!is_array($array2)) {
            throw new \InvalidArgumentException('All the arguments must be array');
        }

        $flip = array_flip($array2);

        foreach ($array as $key => $value) {
            if (!isset($flip[$value])) {
                unset($array[$key]);
            }
        }

        if ($argsCnt > 2) {
            $args = func_get_args();
            $args = array_slice($args, 2);
            array_unshift($args, $array);
            return call_user_func_array([__CLASS__, __FUNCTION__], $args);
        }

        return $array;
    }
}
