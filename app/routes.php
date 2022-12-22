<?php

namespace App;

require dirname(__DIR__) . '/vendor/autoload.php';

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

$dbConn = new DBHelper();
$app = AppFactory::create();

$app->get('/', function (RequestInterface $request, ResponseInterface $response, array $args) {
    $response->getBody()->write('Welcome, please refer to the docs.');
    return $response;
});

$app->group('/users', function (RouteCollectorProxy $group) {
    // USER CREATE
    $group->post('', function ($request, $response) {
        return  (new UserController)->createUser($request, $response);
    })->setName('user-create');

    // USER LIST ALL
    $group->get('', function ($request, $response) {
        return (new UserController)->getUsers($request, $response);
    })->setName('user-list');

    // USER READ
    $group->get('/{username}', function ($request, $response, array $args) {
        return  (new UserController)->getUser($request, $response, $args['username'],);
    })->setName('user-read');
});

$app->group('/chat', function (RouteCollectorProxy $group) {
    // GET MESSAGES

    // GET NEW_MESSAGES

    // POST MESSAGE

})->add(new AuthMiddleware());
