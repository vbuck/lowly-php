<?php

declare(strict_types=1);

/**
 * @author    Rick Buczynski <richard.buczynski@gmail.com>
 * @copyright 2019 Rick Buczynski. All Rights Reserved.
 * @package   LowlyPHP
 * @license   MIT
 */

namespace LowlyPHP;

/**
 * Configuration merge utility.
 */
class ConfigMerger
{
    /**
     * Merge two or more configuration files.
     *
     * Additional arguments are also loaded and merged.
     *
     * @param string $file1
     * @param string $file2
     * @return array
     */
    public function merge(string $file1, string $file2) : array
    {
        $data = [];
        $paths = \array_unique(
            \array_filter(
                \func_get_args(),
                function ($argument) {
                    return !empty($argument) && \is_string($argument);
                }
            )
        );

        foreach ($paths as $path) {
            $data[] = (array) \json_decode(\file_get_contents($path), true);
        }

        $result = \array_shift($data);

        foreach ($data as $additional) {
            $result = $this->mergeData($result, $additional);
        }

        return $result;
    }

    /**
     * Merge two arrays recursively.
     *
     * @param array $data1
     * @param array $data2
     * @return array
     */
    private function mergeData(array $data1, array $data2) : array
    {
        foreach ($data1 as $key1 => &$value1) {
            foreach ($data2 as $key2 => $value2) {
                if (!isset($data1[$key2])) {
                    $data1[$key2] = $value2;
                    continue;
                } elseif ($key1 !== $key2) {
                    continue;
                }

                if (\is_array($value1) && \is_array($value2)) {
                    $value1 = $this->mergeData($value1, $value2);
                } else {
                    $value1 = $value2;
                }
            }
        }

        return $data1;
    }
}
