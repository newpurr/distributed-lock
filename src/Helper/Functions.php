<?php declare(strict_types=1);

if (!function_exists('swoole_random_int')) {
    /**
     * Generate a random value via the Mersenne Twister Random Number Generator
     *
     * @link https://www.php.net/manual/zh/function.random-int.php
     * @param int $min
     * @param int $max
     * @return int A random integer value between min (or 0)
     * @throws \Throwable
     * and max (or mt_getrandmax, inclusive)
     * @since 4.0
     * @since 5.0
     */
    function swoole_random_int($min, $max)
    {
        mt_srand();
        
        try {
            return random_int($min, $max);
        } catch (Exception $e) {
            throw $e;
        }
    }
}

