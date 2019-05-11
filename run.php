<?php

use Subapp\Router\Pattern;
use Subapp\Router\Router;
use Subapp\Router\Source;

include_once __DIR__ . '/vendor/autoload.php';

$router = new Router();

//$router->domain(':name.static-hub.com',
//    ['callback' => function ($id) {
//        var_dump($id);
//    }]
//);

$route = $router->path('/user/:id',
    ['callback' => function ($name = null, $id = 0) {
        var_dump($name, $id);

        return sprintf("Name: %s, Id: %s", $name, $id);
    }]
);

$route->addPattern(Pattern::of('domain', ':name.:noMatch'));
$route->regex('noMatch', '(?:.*)');

$route->compile();

$result = $router->handle(Source::of('domain', 'sasha-grey.static-hub.com'));

var_dump($result);

//var_dump(call_user_func($result));


//'uri' => '/user/:id/:hash',
//    'domain' => ':name.static-hub.com'


//$route = new Route([], new Pattern('path', '/user/:id/:hash'), new Pattern('domain', ':name.:noMatch'));
//
//$route->regex('noMatch', '(?:.*)');
//
//$matched = $route->match('sasha-grey.static-hub.com', '/user/321123/ASDFGH');
//
//var_dump($matched, $route->getMatches());