<?php

trait DbConnector
{
    protected static $db;

    public static function connectToDb(PDO $db)
    {
        self::$db = $db;
    }
}