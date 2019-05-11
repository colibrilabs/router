<?php

namespace Subapp\Router;

/**
 * Class Source
 * @package Subapp\Router
 */
final class Source
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $value;

    /**
     * Source constructor.
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param string $name
     * @param string $value
     * @return Source
     */
    public static function of($name, $value) {
        return new Source($name, $value);
    }

}