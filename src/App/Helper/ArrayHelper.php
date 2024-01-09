<?php

declare(strict_types=1);

namespace App\Helper;

class ArrayHelper
{
    /**
     * Функция для сравнения двух многомерных массивов по принципу array_diff
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function arrayDiffRecursive(array $array1, array $array2): array
    {
        $result = [];

        foreach ($array1 as $key => $value) {
            if (array_key_exists($key, $array2)) {
                if (is_array($value)) {
                    $recursiveDiff = self::arrayDiffRecursive($value, $array2[$key]);

                    if (count($recursiveDiff)) {
                        $result[$key] = $recursiveDiff;
                    }
                } else {
                    if ($value != $array2[$key]) {
                        $result[$key] = $value;
                    }
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
