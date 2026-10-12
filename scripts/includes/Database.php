<?php

class Database {

    private $pdo;

    public function __construct() {
        $host = "127.0.0.1";
        $port = 15432;
        $user = getenv("DB_USER");
        $pass = getenv("DB_PASS");
        $name = "postgres" // Datenbank Name

        $this->pdo = new PDO('pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $name, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    public function query2($query, $binds) {

    }

}