<?php

namespace Subapp\Router;

/**
 * Class Result
 * @package Subapp\Router
 */
final class Result
{

    /**
     * @var Route
     */
    protected $route;

    /**
     * @var string
     */
    protected $namespace = null;

    /**
     * @var string
     */
    protected $module = null;

    /**
     * @var string
     */
    protected $controller = null;

    /**
     * @var string
     */
    protected $action = null;

    /**
     * @var callable
     */
    protected $callback = null;

    /**
     * @var array
     */
    protected $matches = [];

    /**
     * @var array
     */
    protected $dirty = [];

    /**
     * Result constructor.
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
        }
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param Route $route
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param string $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return callable
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param callable $callback
     */
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return array
     */
    public function getMatches()
    {
        return $this->matches;
    }

    /**
     * @param array $matches
     */
    public function setMatches(array $matches)
    {
        $this->matches = $matches;
    }

    /**
     * @return array
     */
    public function getDirty()
    {
        return $this->dirty;
    }

    /**
     * @param array $dirty
     */
    public function setDirty(array $dirty)
    {
        $this->dirty = $dirty;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'namespace' => $this->namespace,
            'controller' => $this->controller,
            'action' => $this->action,
            'callback' => $this->callback,
            'matches' => $this->matches,
            'dirty' => $this->dirty,
        ];
    }

}