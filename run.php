<?php

use Subapp\Router\Pattern;
use Subapp\Router\Router;
use Subapp\Router\Source;

include_once __DIR__ . '/vendor/autoload.php';

$router = new Router();

$route = new \Subapp\Router\Route(['controller' => 'User', 'action' => 'GetUserById',]);

$route->addPattern(Pattern::of('path', '/users/:id'));
$route->addPattern(Pattern::of('domain', ':name.:noMatch'));
$route->regex('noMatch', '(?:.*)');

$router->addRoute($route);

$result = $router->handle(
    Source::of('domain', 'sasha-grey.static-hub.com'),
    Source::of('path', '/user/9071')
);

var_dump($result);
