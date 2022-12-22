<?php
namespace App;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler)
    {
        if (!$request->hasHeader('Authorization')) {
            throw new \Slim\Exception\HttpForbiddenException($request);
        }

        // getHeader returns an array with the authorization value, e.g. ["username"]
        // So we get the first position as the username
        $username = $request->getHeader('Authorization')[0];
        $user_id = $this->getUserId($username, $request);

        // $user_id = 1;

        // Save the user ID in the request for usage in the controllers
        $request = $request->withAttribute('user_id', $user_id);

        // Call the controller handler
        $response = $handler->handle($request);

        return $response;
    }

    private function getUserId($username, $request) {
        $user = (new UserController())->getUserFromName($username, $request);
        if(!$user || $user == null) {
            throw new \Slim\Exception\HttpForbiddenException($request);
        }

        return $user['id'];
    }
}