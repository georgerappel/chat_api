<?php

namespace App;

class UserController
{
    function __construct()
    {
        $this->db = DBHelper::$default;
    }

    function getUsers($request, $response)
    {
        $query = $request->getQueryParam('query', $default = null);
        if (is_null($query)) {
            $result = $this->db->query('SELECT * from user');
        } else {
            $result = $this->findUsers($query);
        }

        $users = [];
        while($row = $result->fetchArray(SQLITE3_ASSOC)){
            array_push($users, $row);
        }
        
        $body = ["users" => $users];
        return $response->withJson($body);
    }

    function findUsers($username)
    {
        $username = strtolower($username);
        $statement = $this->db->prepare("SELECT * from user WHERE username like :username");
        $statement->bindValue(':username', '%'.$username.'%');
        return $statement->execute();
    }

    function getUserFromName(String $username)
    {
        if (strlen($username) > 100) {
            return null;
        }

        $username = strtolower($username);

        $statement = $this->db->prepare('SELECT * FROM user WHERE username=:username');
        $statement->bindValue(':username', $username);
        $result = $statement->execute();
        if (!$result) {
            return null;
        }
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if (!$row) {
            return null;
        }
        return $row;
    }

    function createUser($request, $response)
    {
        $username = $request->getParsedBodyParam('username');
        if (is_null($username)) {
            return $response->withJson(["error" => "Please provide a username in the body"], 400);
        }
        if (strlen($username) > 100 || strlen($username) < 1) {
            return $response->withJson(["error" => "Username must be between 1 and 100 characters"], 400);
        }

        $username = strtolower($username);

        if (!is_null($this->getUserFromName($username))) {
            return $response->withJson(["error" => "Username already taken", 409]);
        }

        $statement = $this->db->prepare('INSERT INTO user (username) values (:username);');
        $statement->bindValue(':username', $username);
        $result = $statement->execute();
        if (!$result) {
            return $response->withJson(["error" => "Something went wrong"], 500);
        }
        return $response->withJson($this->getUserFromName($username));
    }
}