<?php

class Database {

    private static ?PDO $pdo = null;

    public $RSArray = [];
    public $rows = 0;

    public function __construct() {
        
        if (self::$pdo !== null) {
            return; // db bereits verbunden
        }

        if(getenv("SERVER") == "dev") {
            require("DBConnection.php");
        }

        $host = getenv("DB_IP");
        $port = getenv("DB_PORT");
        $user = getenv("DB_USER");
        $pass = getenv("DB_PASS");
        $name = "postgres"; // Datenbank Name

        try {
            self::$pdo = new PDO('pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $name, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            self::$pdo->exec('SET search_path TO extremedemonlist');
        } catch (PDOException $e) {
            handle_error($e);
        }
    }

    public function query($sql) {
        try {
            $stmt = self::$pdo->query($sql);

            $this->RSArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->rows = count($this->RSArray);

        } catch (PDOException $e) {
            handle_error($e);
        }
    }

    public function query2($sql, $binds = []) {
        try {
            $stmt = self::$pdo->prepare($sql);

            foreach ($binds as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            $this->RSArray = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->rows = count($this->RSArray);

        } catch (PDOException $e) {
            handle_error($e);
        }
    }
}