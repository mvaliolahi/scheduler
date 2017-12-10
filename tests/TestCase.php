<?php
/**
 * Created by PhpStorm.
 * User: m.valiolahi
 * Date: 8/28/2017
 * Time: 8:08 AM
 */

namespace Tests;


use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class TestCase
 * @package Tests
 */
class TestCase extends PHPUnitTestCase
{
    /**
     * @param $method
     * @param $object
     * @param array $args
     * @return mixed
     */
    public function callMethod($method, $object, $args = [])
    {
        $translator = new \ReflectionClass(get_class($object));
        $method = $translator->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }

    /**
     * @param $string
     * @return bool|string
     */
    public function getProcessResult($string)
    {
        return substr($string, 0, strlen($string) - 2);
    }
}