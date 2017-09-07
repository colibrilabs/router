<?php

namespace Colibri\Router;

use Colibri\Http\Request;

/**
 * Class Router
 * @package Colibri\Router
 */
class Router
{
  
  const URI_SOURCE_GET_VAR = 1;
  const URI_SOURCE_SERVER = 2;
  
  const MATCH_NAMESPACE = 'namespace';
  const MATCH_MODULE = 'module';
  const MATCH_CONTROLLER = 'controller';
  const MATCH_ACTION = 'action';
  const MATCH_CALLBACK = 'callback';
  const MATCH_PARAMS = 'params';
  
  /**
   * @var array
   */
  protected static $reservedNames = [
    self::MATCH_NAMESPACE,
    self::MATCH_MODULE,
    self::MATCH_CONTROLLER,
    self::MATCH_ACTION,
    self::MATCH_CALLBACK,
    self::MATCH_PARAMS,
  ];
  
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
  
  protected $request = null;
  
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
  protected $dirtyMatches = [];
  
  /**
   * @var bool
   */
  protected $founded = false;
  
  /**
   * @var array
   */
  protected $routes = [];
  
  /**
   * @var string
   */
  protected $targetUri = '/';
  
  /**
   * @var int
   */
  protected $uriSource = 0;
  
  /**
   * @var string
   */
  protected $uriGetVar = '_route';
  
  /**
   * @var null
   */
  protected $matchedRoute = null;
  
  /**
   * @param Request $request
   */
  public function __construct(Request $request)
  {
    $this->request = $request;
    
    $this->setUriSource(self::URI_SOURCE_GET_VAR);
    $this->setController('index')->setAction('index');
  }
  
 /**
   * @param string $pattern
   * @param array  $matches
   * @param array  $methods
   * @return Route
   */
  public function add($pattern = '', array $matches = [], array $methods = [])
  {
    return $this->routes[] = new Route($this, $pattern, $matches, $methods);
  }
  
  /**
   * @param null $uri
   * @return $this
   * @throws RouterException
   */
  public function handle($uri = null)
  {
    $this->setMatchedRoute(null);
    $this->setFounded(false);
    
    if ($uri === null) {
      
      $uri = $this->getUriSource() == self::URI_SOURCE_GET_VAR
        ? $this->request->getQuery($this->getUriGetVar(), '/')
        : $this->request->getServer('request_uri', '/');
    }
    
    $this->setTargetUri($uri);
    
    foreach ($this->getRoutes() as $route) {
      if ($route->handleUri() === true) {
        
        foreach (static::$reservedNames as $name) {
          if ($name !== static::MATCH_PARAMS) {
            $this->{$name} = $route->hasMatch($name)
              ? $route->getMatch($name) : static::$defaultValues[$name];
          } else {
            $this->setDirtyMatches($route->getMatch(static::MATCH_PARAMS));
          }
        }
        
        $this->setMatches($route->getMatches());
        $this->setFounded(true);
        
        break;
      }
    }

    return $this;
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
   * @return static
   */
  public function setModule($module)
  {
    $this->module = $module;
    
    return $this;
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
   * @return static
   */
  public function setController($controller)
  {
    $this->controller = $controller;
    
    return $this;
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
   * @return static
   */
  public function setAction($action)
  {
    $this->action = $action;
    
    return $this;
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
   * @return $this
   */
  public function setCallback($callback)
  {
    $this->callback = $callback;
    
    return $this;
  }
  
  /**
   * @return bool
   */
  public function hasCallback()
  {
    return null !== $this->callback && is_callable($this->callback, true);
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
   * @return static
   */
  public function setMatches($matches)
  {
    if (count($matches) > 0) {
      foreach (static::$reservedNames as $name) {
        unset($matches[$name]);
      }
      
      if (count($matches) > 0) {
        $this->matches = $matches;
      }
    }
    
    return $this;
  }
  
  /**
   * @return array
   */
  public function getDirtyMatches()
  {
    return $this->dirtyMatches;
  }
  
  /**
   * @param array $dirtyMatches
   * @return static
   * @throws RouterException
   */
  public function setDirtyMatches($dirtyMatches)
  {
    if (is_string($dirtyMatches)) {
      $this->dirtyMatches = explode('/', trim($dirtyMatches, '/'));
    } else if (is_array($dirtyMatches)) {
      $this->dirtyMatches = $dirtyMatches;
    } else {
      throw new RouterException('Bad set dirty matches');
    }
    
    return $this;
  }
  
  /**
   * @return array
   */
  public function getRawMatches()
  {
    return $this->getMatchedRoute()->getMatches();
  }
  
  /**
   * @return boolean
   */
  public function isFounded()
  {
    return $this->founded;
  }
  
  /**
   * @param boolean $founded
   * @return static
   */
  public function setFounded($founded)
  {
    $this->founded = $founded;
    
    return $this;
  }
  
  /**
   * @return Route[]
   */
  public function getRoutes()
  {
    return $this->routes;
  }
  
  /**
   * @param array $routes
   * @return static
   */
  public function setRoutes($routes)
  {
    $this->routes = $routes;
    
    return $this;
  }
  
  /**
   * @return string
   */
  public function getTargetUri()
  {
    return $this->targetUri;
  }
  
  /**
   * @param string $targetUri
   * @return static
   */
  public function setTargetUri($targetUri)
  {
    $this->targetUri = '/' . trim(preg_replace('~/+~u', '/', $targetUri), '/');
    
    return $this;
  }
  
  /**
   * @return null|Route
   */
  public function getMatchedRoute()
  {
    return $this->matchedRoute;
  }
  
  /**
   * @param Route $matchedRoute
   * @return static
   */
  public function setMatchedRoute($matchedRoute)
  {
    $this->matchedRoute = $matchedRoute;
    
    return $this;
  }
  
  /**
   * @return int
   */
  public function getUriSource()
  {
    return $this->uriSource;
  }
  
  /**
   * @param int $uriSource
   * @return static
   * @throws RouterException
   */
  public function setUriSource($uriSource)
  {
    if (!in_array($uriSource, [self::URI_SOURCE_GET_VAR, self::URI_SOURCE_SERVER])) {
      throw new RouterException('Incorrect URI source');
    }
    $this->uriSource = $uriSource;
    
    return $this;
  }
  
  /**
   * @return string
   */
  public function getUriGetVar()
  {
    return $this->uriGetVar;
  }
  
  /**
   * @param string $uriGetVar
   * @return static
   */
  public function setUriGetVar($uriGetVar)
  {
    $this->uriGetVar = $uriGetVar;
    
    return $this;
  }
  
  /**
   * @return Request
   */
  public function getRequest()
  {
    return $this->request;
  }
  
  /**
   * @param Request $request
   */
  public function setRequest(Request $request)
  {
    $this->request = $request;
  }
  
}
