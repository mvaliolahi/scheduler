<?php
/**
 * Created by PhpStorm.
 * User: m.valiolahi
 * Date: 2/10/2018
 * Time: 9:32 AM
 */

namespace Mvaliolahi\Scheduler\Contracts;


/**
 * Interface OverlappingCache
 * @package Mvaliolahi\Scheduler\Contracts
 */
interface OverlappingCache
{
    /**
     * @param $key
     * @param $value
     * @param $minute
     * @return mixed
     */
    public static function put($key, $value, $minute);

    /**
     * @param $key
     * @return mixed
     */
    public static function get($key);

    /**
     * @param $key
     * @return mixed
     */
    public static function has($key);

    /**
     * @param $key
     * @return mixed
     */
    public static function forget($key);
}