<?php

namespace Subapp\Router;

/**
 * Class Router
 * @package Subapp\Router
 */
class Router
{

    const MATCH_NAMESPACE = 'namespace';
    const MATCH_MODULE = 'module';
    const MATCH_CONTROLLER = 'controller';
    const MATCH_ACTION = 'action';
    const MATCH_CALLBACK = 'callback';
    const MATCH_PARAMS = 'params';

    /**
     * @var array
     */
    protected static $defaultValues = [
        self::MATCH_NAMESPACE => null,
        self::MATCH_MODULE => null,
        self::MATCH_CONTROLLER => 'index',
        self::MATCH_ACTION => 'index',
        self::MATCH_CALLBACK => null,
        self::MATCH_PARAMS => [],
    ];

    /**
     * @var array
     */
    protected $routes = [];


    /**
     * @param string $name
     * @param string $pattern
     * @param array $matches
     * @param array $methods
     * @return Route
     */
    public function add($name, $pattern, array $matches = [], array $methods = [])
    {
        $route = new Route($matches, new Pattern($name, $pattern));
        $route->setMethods($methods);

        $this->addRoute($route);

        return $route;
    }

    /**
     * @param string $pattern
     * @param array $matches
     * @param array $methods
     * @return Route
     */
    public function domain($pattern, array $matches = [], array $methods = [])
    {
        return $this->add('domain', $pattern, $matches, $methods);
    }

    /**
     * @param string $pattern
     * @param array $matches
     * @param array $methods
     * @return Route
     */
    public function path($pattern, array $matches = [], array $methods = [])
    {
        return $this->add('path', $pattern, $matches, $methods);
    }

    /**
     * @param Source ...$sources
     * @return Result
     */
    public function handle(Source ...$sources)
    {
        $result = null;

        foreach ($this->getRoutes() as $route) {
            if ($route->match(...$sources)) {

                $result = new Result($route);

                foreach (static::$defaultValues as $name => $value) {
                    $result->{$name} = $value;
                }

                foreach ($route->getMatches() as $name => $value) {
                    switch ($name) {
                        case self::MATCH_PARAMS:
                            $result->setDirty($route->getMatch($name));
                            break;
                        case self::MATCH_NAMESPACE:
                        case self::MATCH_MODULE:
                        case self::MATCH_CONTROLLER:
                        case self::MATCH_ACTION:
                        case self::MATCH_CALLBACK:
                            $result->{$name} = $value;
                            break;
                        default:
                            $matches = $result->getMatches();
                            $matches[$name] = $value;
                            $result->setMatches($matches);
                            break;
                    }
                }

                break;
            }
        }

        return $result;
    }

    /**
     * @return Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param Route $route
     * @return $this
     */
    public function addRoute(Route $route)
    {
        $this->routes[] = $route;

        return $this;
    }

}
