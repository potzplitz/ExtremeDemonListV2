<?php

class Database {

    private $pdo;

    public $RSArray = [];
    public $rows = 0;

    public function __construct() {
        $host = "127.0.0.1";
        $port = 15432;
        $user = getenv("DB_USER");
        $pass = getenv("DB_PASS");
        $name = "postgres"; // Datenbank Name

        $this->pdo = new PDO('pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $name, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->pdo->exec('SET search_path TO extremedemonlist');
    }

    public function query($sql) {
        try {
            $stmt = $this->pdo->query($sql);

            $this->RSArray = $stmt->fetchAll();
            $this->rows = count($this->RSArray);

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }

    public function query2($sql, $binds = []) {
        try {
            $stmt = $this->pdo->prepare($sql);

            foreach ($binds as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            $this->RSArray = $stmt->fetchAll();
            $this->rows = count($this->RSArray);

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
}