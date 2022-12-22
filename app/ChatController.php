<?php

namespace App;

class ChatController
{
    function __construct()
    {
        $this->db = DBHelper::$default;
    }

    public function get($request, $response, $username)
    {
        $current_user_id = $request->getAttribute('user_id');
        $other_user_id = (new UserController())->getUserFromName($username)['id'];
        $before_message = $request->getQueryParam('before', $default = null);
        $after_message = $request->getQueryParam('after', $default = null);

        if (!is_null($before_message) && !is_null($after_message)) {
            return $response->withJson([
                "error" => "Please use only one of 'before' or 'after' query parameters"
            ], 400);
        }

        $result = $this->loadMessages($current_user_id, $other_user_id, $before_message, $after_message);
        if ($result && count($result) > 0) {
            $last_id = $this->getMaxId($result);
            $first_id = $this->getMinId($result);
        } else {
            $result = [];
            $last_id = 0;
            $first_id = 0;
        }

        return $response->withJson([
            "messages" => $result,
            "last_message" => $last_id,
            "first_message" => $first_id,
        ]);
    }

    public function post($request, $response, $username)
    {
        $current_user_id = $request->getAttribute('user_id');
        $other_user_id = (new UserController())->getUserFromName($username)['id'];
        $content = $request->getParsedBodyParam('content', $default = null);
        if (is_null($content)) {
            return $response->withJson(["error" => "Missing message content"], 400);
        }

        $query = 'INSERT INTO message (sender, recipient, content) VALUES (:sender, :recipient, :content)';
        $statement = $this->db->preparE($query);
        $statement->bindValue(':sender', $current_user_id);
        $statement->bindValue(':recipient', $other_user_id);
        $statement->bindValue(':content', $content);
        $result = $statement->execute();
        if (!$result) {
            return $response->withJson(["error" => "Unable to save message"], 500);
        }

        $message_id = $this->db->lastInsertRowID();
        $message = $this->db->query('SELECT * from message where id=' . $message_id);
        $message = $message->fetchArray(SQLITE3_ASSOC);
        return $response->withJson($message);
    }

    private function loadMessages($current_id, $other_id, $before_message = null, $after_message = null)
    {
        // Define query string
        $query = 'SELECT * FROM message ' .
                 'WHERE ((sender=:current_user AND recipient=:other_user) ' .
                 'OR (sender=:other_user AND recipient=:current_user)) ';
        if (!is_null($before_message)) {
            $query = $query . ' AND id < :before_id ';
        } else if (!is_null($after_message)) {
            $query = $query . ' AND id > :after_id ';
        }
        $query = $query . ' ORDER BY created_at DESC';
        $query = $query . ' LIMIT 20 ';

        // Set values on the query
        $statement = $this->db->prepare($query);
        $statement->bindValue(':current_user', $current_id);
        $statement->bindValue(':other_user', $other_id);
        if (!is_null($before_message)) {
            $statement->bindValue(':before_id', $before_message);
        } else if (!is_null($after_message)) {
            $statement->bindValue(':after_id', $after_message);
        }

        // Execute and return the array
        $result = $statement->execute();
        if (!$result) {
            return [];
        }

        $messages = [];
        while($row = $result->fetchArray(SQLITE3_ASSOC)){
            array_push($messages, $row);
        }
        return $messages;
    }

    private function getMaxId($result) {
        $max = 0;
        foreach($result as $msg) {
            if ($msg['id'] > $max) {
                $max = $msg['id'];
            }
        }
        return $max;
    }

    private function getMinId($result) {
        $min = $result[0];
        foreach($result as $msg) {
            if ($msg['id'] < $min) {
                $min = $msg['id'];
            }
        }
        return $min;
    }
}