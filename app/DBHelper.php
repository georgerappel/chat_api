<?php

namespace App;

use SQLite3;

class DBHelper extends SQLite3
{
    static $default;

    function __construct()
    {
        $this->open('../db/chat_db.db');
        self::$default = $this;
    }
}