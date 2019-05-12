<?php

namespace Subapp\Router;

/**
 * Class Criteria
 * @package Subapp\Router
 */
class Pattern
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $raw;

    /**
     * Criteria constructor.
     * @param string $name
     * @param string $pattern
     */
    public function __construct($name, $pattern)
    {
        $this->name = $name;
        $this->setPattern($pattern);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * @param string $pattern
     */
    public function setPattern($pattern)
    {
        $this->pattern = addcslashes($pattern, './~');
        $this->raw = $pattern;
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @param string $name
     * @param string $pattern
     * @return Pattern
     */
    public static function of($name, $pattern)
    {
        return new Pattern($name, $pattern);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->pattern;
    }

}