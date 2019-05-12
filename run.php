<?php

use Subapp\Router\Pattern;
use Subapp\Router\Router;
use Subapp\Router\Source;

include_once __DIR__ . '/vendor/autoload.php';

$router = new Router();

$route = $router->path('/user/:id',
    ['controller' => 'User', 'action' => 'GetUserById',]
);

$route->addPattern(Pattern::of('domain', ':name.:noMatch'));
$route->regex('noMatch', '(?:.*)');

$route->compile();

$result = $router->handle(
    Source::of('domain', 'sasha-grey.static-hub.com'),
    Source::of('path', '/user/9071')
);

var_dump($router);
